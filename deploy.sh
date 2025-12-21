#!/usr/bin/env bash
set -euo pipefail

# -------- CONFIG --------
APP_DIR="${APP_DIR:-/home/${VM_USER:-appuser}/SE2025-16.2}"
COMPOSE_FILE="${COMPOSE_FILE:-docker-compose.prod.yml}"
BACKEND_SERVICE_NAME="${BACKEND_SERVICE_NAME:-backend}"   # compose service to wait for
BACKEND_HEALTH_URL="${BACKEND_HEALTH_URL:-}"             # optional health endpoint (http(s) reachable from host)
SMOKE_TEST_URL="${SMOKE_TEST_URL:-}"                     # optional smoke test after deploy
DRY_RUN="${DRY_RUN:-0}"                                  # 1 = execution dry-run, 0 = real
DRY_RUN_MODE="${DRY_RUN_MODE:-execution}"                # "static" or "execution"
RETRIES="${RETRIES:-30}"
SLEEP_SECONDS="${SLEEP_SECONDS:-2}"
# ------------------------

log() { printf '%s %s\n' "[deploy]" "$1"; }

# run wrapper: prints on dry-run, executes otherwise
run() {
  if [[ "${DRY_RUN:-0}" == "1" ]]; then
    echo "[DRY-RUN] $*"
  else
    eval "$@"
  fi
}

# quick command exists check (prints and optionally fails in real run)
check_cmd() {
  local cmd=$1
  if ! command -v "$cmd" >/dev/null 2>&1; then
    log "ERROR: required command '$cmd' not found in PATH"
    if [[ "${DRY_RUN:-0}" == "1" ]]; then
      log "DRY RUN: continuing despite missing command"
    else
      exit 2
    fi
  fi
}

# --------- STATIC DRY-RUN (compose syntax check) ----------
if [[ "${DRY_RUN:-0}" == "1" && "${DRY_RUN_MODE:-execution}" == "static" ]]; then
  log "STATIC DRY RUN: validating compose file"
  check_cmd docker
  check_cmd docker-compose || true
  # Prefer `docker compose` (v2) but fall back to `docker-compose` if needed
  if command -v docker >/dev/null 2>&1 && docker compose version >/dev/null 2>&1; then
    docker compose -f "${COMPOSE_FILE}" config
  elif command -v docker-compose >/dev/null 2>&1; then
    docker-compose -f "${COMPOSE_FILE}" config
  else
    log "No docker compose executable found; cannot validate compose file"
    exit 2
  fi
  log "Compose file is valid"
  exit 0
fi

log "Starting deployment (DRY_RUN=${DRY_RUN}, MODE=${DRY_RUN_MODE})"
log "App dir: ${APP_DIR}"
log "Compose file: ${COMPOSE_FILE}"

# --------- PREFLIGHT ----------

# basic commands should exist (in dry-run we just warn)
check_cmd docker
# docker compose (v2) preferred
if docker compose version >/dev/null 2>&1; then
  DOCKER_COMPOSE_CMD="docker compose"
elif command -v docker-compose >/dev/null 2>&1; then
  DOCKER_COMPOSE_CMD="docker-compose"
else
  log "No docker compose available; aborting"
  exit 2
fi

# ensure APP_DIR exists (create with run wrapper)
run mkdir -p "${APP_DIR}"
run cd "${APP_DIR}"

# --------- FILE / ENV SETUP ----------
run mkdir -p backend

if [[ -f backend/nginx.conf ]]; then
  run cp backend/nginx.conf nginx.conf
fi

if [[ -n "${ENV_FILE:-}" ]]; then
  # use run wrapper so dry-run prints this too
  run bash -lc "printf '%s' \"\$ENV_FILE\" > backend/.env.production"
fi

# --------- REGISTRY LOGIN ----------
log "Logging into GHCR (if token provided)"
if [[ -n "${GITHUB_TOKEN:-}" ]]; then
  # Use run wrapper
  run bash -lc "printf '%s' \"\$GITHUB_TOKEN\" | docker login ghcr.io -u \"${GITHUB_ACTOR:-github-actions}\" --password-stdin"
else
  log "No GITHUB_TOKEN provided; skipping GHCR login"
fi

# --------- DEPLOY: pull, stop, start ----------
log "Pulling images"
run ${DOCKER_COMPOSE_CMD} -f "${COMPOSE_FILE}" pull

log "Stopping old containers (if any)"
run ${DOCKER_COMPOSE_CMD} -f "${COMPOSE_FILE}" down --remove-orphans || true

log "Starting containers"
run ${DOCKER_COMPOSE_CMD} -f "${COMPOSE_FILE}" up -d

# --------- WAIT FOR BACKEND ----------
wait_for_container_running() {
  local svc=$1
  local try=1
  while [[ $try -le $RETRIES ]]; do
    # get container id for service via compose
    cid=$(${DOCKER_COMPOSE_CMD} -f "${COMPOSE_FILE}" ps -q "${svc}" 2>/dev/null || true)
    if [[ -n "$cid" ]]; then
      state=$(docker inspect -f '{{.State.Running}}' "$cid" 2>/dev/null || echo "false")
      if [[ "$state" == "true" ]]; then
        log "Service '${svc}' container is running (cid=$cid)"
        return 0
      fi
    fi
    log "Waiting for service '${svc}' to have running container ($try/${RETRIES})"
    sleep ${SLEEP_SECONDS}
    try=$((try + 1))
  done
  return 1
}

wait_for_http() {
  local url=$1
  local try=1
  while [[ $try -le $RETRIES ]]; do
    if curl -fsS --max-time 3 "${url}" >/dev/null 2>&1; then
      log "HTTP check succeeded for ${url}"
      return 0
    fi
    log "HTTP check failed for ${url} ($try/${RETRIES})"
    sleep ${SLEEP_SECONDS}
    try=$((try + 1))
  done
  return 1
}

if [[ -n "${BACKEND_HEALTH_URL:-}" ]]; then
  log "Waiting for backend health endpoint: ${BACKEND_HEALTH_URL}"
  if ! wait_for_http "${BACKEND_HEALTH_URL}"; then
    log "ERROR: backend health endpoint did not become healthy"
    # dump recent logs if possible
    run ${DOCKER_COMPOSE_CMD} -f "${COMPOSE_FILE}" logs --tail=200 "${BACKEND_SERVICE_NAME}" || true
    exit 3
  fi
else
  log "No BACKEND_HEALTH_URL supplied; waiting for backend container to be running"
  if ! wait_for_container_running "${BACKEND_SERVICE_NAME}"; then
    log "ERROR: backend container did not start"
    run ${DOCKER_COMPOSE_CMD} -f "${COMPOSE_FILE}" logs --tail=200 "${BACKEND_SERVICE_NAME}" || true
    exit 3
  fi
  # small stabilization sleep
  log "Sleeping 5s for stabilization"
  sleep 5
fi

# --------- POST-DEPLOY ACTIONS ----------
log "Running migrations"
# migrations may fail; don't let failures hide other issues - we run but do not fail the whole script if they error (configurable)
if ! run ${DOCKER_COMPOSE_CMD} -f "${COMPOSE_FILE}" exec -T "${BACKEND_SERVICE_NAME}" php artisan migrate --force; then
  log "Warning: migrations failed (non-fatal in this script). Check logs."
fi

log "Optimizing Laravel caches"
run ${DOCKER_COMPOSE_CMD} -f "${COMPOSE_FILE}" exec -T "${BACKEND_SERVICE_NAME}" php artisan config:clear || true
run ${DOCKER_COMPOSE_CMD} -f "${COMPOSE_FILE}" exec -T "${BACKEND_SERVICE_NAME}" php artisan config:cache || true
run ${DOCKER_COMPOSE_CMD} -f "${COMPOSE_FILE}" exec -T "${BACKEND_SERVICE_NAME}" php artisan route:cache || true
run ${DOCKER_COMPOSE_CMD} -f "${COMPOSE_FILE}" exec -T "${BACKEND_SERVICE_NAME}" php artisan view:cache || true

# --------- SMOKE TEST (optional) ----------
if [[ -n "${SMOKE_TEST_URL:-}" ]]; then
  log "Running smoke test: ${SMOKE_TEST_URL}"
  if ! wait_for_http "${SMOKE_TEST_URL}"; then
    log "ERROR: smoke test failed"
    # optionally mark as failure
    exit 4
  fi
  log "Smoke test passed"
fi

# --------- CLEANUP ----------
log "Pruning old images (safety: run only when not dry-run)"
if [[ "${DRY_RUN:-0}" == "1" ]]; then
  log "Dry-run mode: skipping image prune"
else
  run docker image prune -af --filter "until=24h"
fi

log "Deployment finished successfully"
exit 0
