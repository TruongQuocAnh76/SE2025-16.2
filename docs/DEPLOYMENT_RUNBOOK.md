# Deployment Runbook

This document provides step-by-step procedures for deploying, maintaining, and rolling back the Certchain production environment.

## Table of Contents

- [Prerequisites](#prerequisites)
- [Deployment Overview](#deployment-overview)
- [Standard Deployment](#standard-deployment)
- [Rollback Procedures](#rollback-procedures)
- [Emergency Procedures](#emergency-procedures)
- [Health Checks](#health-checks)
- [Troubleshooting](#troubleshooting)

---

## Prerequisites

### Required Access

- SSH access to production VM
- GitHub repository access
- GitHub Container Registry (GHCR) access
- Production environment secrets

### Required Tools

- Docker and Docker Compose v2
- Git
- curl (for health checks)

### Environment Variables

Ensure these secrets are configured in GitHub Actions:

| Secret | Description |
|--------|-------------|
| `VM_HOST` | Production server IP/hostname |
| `VM_USER` | SSH username |
| `VM_SSH_KEY` | SSH private key |
| `GITHUB_TOKEN` | GitHub token for GHCR |
| `ENV_FILE` | Production .env file contents |

---

## Deployment Overview

### CI/CD Pipeline Flow

```
┌─────────────┐    ┌─────────────┐    ┌─────────────┐    ┌─────────────┐
│    Push     │───▶│   CI Tests  │───▶│ Build/Push  │───▶│   Deploy    │
│  to main    │    │  (PHPUnit)  │    │   Images    │    │   to VM     │
└─────────────┘    └─────────────┘    └─────────────┘    └─────────────┘
                                                                │
                                                                ▼
                                                         ┌─────────────┐
                                                         │ Run Migrate │
                                                         │ Clear Cache │
                                                         └─────────────┘
```

### Deployed Services

| Service | Image | Port |
|---------|-------|------|
| Frontend | `certchain-frontend:latest` | 3000 |
| Backend (nginx) | `nginx:alpine` | 8000 |
| Backend (php-fpm) | `certchain-backend:latest` | - |
| Queue Worker | `certchain-backend:latest` | - |
| RabbitMQ Worker | `certchain-backend:latest` | - |
| Blockchain | `certchain-blockchain:latest` | 3001 |
| Redis | `redis:7-alpine` | - |
| RabbitMQ | `rabbitmq:3-management` | - |
| MinIO | `minio/minio:latest` | 9000, 9001 |

---

## Standard Deployment

### Automatic Deployment (Recommended)

Deployments are triggered automatically when:
1. CI Pipeline passes on `main` branch
2. All tests pass
3. Images are built and pushed to GHCR

**Trigger deployment manually via GitHub Actions:**

1. Go to **Actions** → **CD Pipeline**
2. Click **Run workflow**
3. Select `main` branch
4. Click **Run workflow**

### Manual Deployment

**Step 1: SSH into Production Server**

```bash
ssh $VM_USER@$VM_HOST
```

**Step 2: Navigate to Application Directory**

```bash
cd /home/$VM_USER/app
```

**Step 3: Pull Latest Code (if needed)**

```bash
git pull origin main
```

**Step 4: Login to GHCR**

```bash
echo $GITHUB_TOKEN | docker login ghcr.io -u $GITHUB_ACTOR --password-stdin
```

**Step 5: Run Deployment Script**

```bash
chmod +x ./deploy.sh
./deploy.sh
```

**Step 6: Verify Deployment**

```bash
# Check container status
docker compose -f docker-compose.prod.yml ps

# Check logs
docker compose -f docker-compose.prod.yml logs --tail=50 backend
```

---

## Deployment Script Details

The `deploy.sh` script performs:

1. **Pull Images** - Download latest images from GHCR
2. **Stop Containers** - Gracefully stop existing containers
3. **Start Containers** - Start new containers
4. **Wait for Backend** - Health check for backend service
5. **Run Migrations** - Apply database migrations
6. **Optimize Laravel** - Clear and rebuild caches
7. **Smoke Test** - Optional endpoint verification
8. **Cleanup** - Prune old Docker images

### Dry Run Mode

Test deployment without executing:

```bash
# Static validation (compose file syntax)
DRY_RUN=1 DRY_RUN_MODE=static ./deploy.sh

# Execution dry run (prints commands)
DRY_RUN=1 DRY_RUN_MODE=execution ./deploy.sh
```

---

## Rollback Procedures

### Scenario 1: Failed Migration

If migrations fail during deployment:

**Step 1: Check Migration Status**

```bash
docker compose -f docker-compose.prod.yml exec backend \
  php artisan migrate:status
```

**Step 2: Rollback Last Batch**

```bash
docker compose -f docker-compose.prod.yml exec backend \
  php artisan migrate:rollback
```

**Step 3: Redeploy Previous Version**

See [Rollback to Previous Image Version](#scenario-3-rollback-to-previous-image-version)

---

### Scenario 2: Application Errors After Deployment

**Step 1: Check Application Logs**

```bash
# Backend logs
docker compose -f docker-compose.prod.yml logs --tail=200 backend

# Queue worker logs
docker compose -f docker-compose.prod.yml logs --tail=200 queue-worker

# Nginx logs
docker compose -f docker-compose.prod.yml logs --tail=200 nginx
```

**Step 2: Check Laravel Logs**

```bash
docker compose -f docker-compose.prod.yml exec backend \
  cat storage/logs/laravel.log | tail -100
```

**Step 3: Clear Caches**

```bash
docker compose -f docker-compose.prod.yml exec backend php artisan cache:clear
docker compose -f docker-compose.prod.yml exec backend php artisan config:clear
docker compose -f docker-compose.prod.yml exec backend php artisan route:clear
docker compose -f docker-compose.prod.yml exec backend php artisan view:clear
```

**Step 4: Restart Services**

```bash
docker compose -f docker-compose.prod.yml restart backend queue-worker
```

---

### Scenario 3: Rollback to Previous Image Version

**Step 1: List Available Image Tags**

```bash
# Check GHCR for available tags
docker pull ghcr.io/truongquocanh76/se2025-16.2/certchain-backend:latest
docker images | grep certchain
```

**Step 2: Tag Current Images (Backup)**

```bash
docker tag ghcr.io/truongquocanh76/se2025-16.2/certchain-backend:latest \
  ghcr.io/truongquocanh76/se2025-16.2/certchain-backend:backup-$(date +%Y%m%d)

docker tag ghcr.io/truongquocanh76/se2025-16.2/certchain-frontend:latest \
  ghcr.io/truongquocanh76/se2025-16.2/certchain-frontend:backup-$(date +%Y%m%d)
```

**Step 3: Pull Previous Version**

If you have tagged previous versions:

```bash
docker pull ghcr.io/truongquocanh76/se2025-16.2/certchain-backend:v1.0.0
docker tag ghcr.io/truongquocanh76/se2025-16.2/certchain-backend:v1.0.0 \
  ghcr.io/truongquocanh76/se2025-16.2/certchain-backend:latest
```

**Step 4: Restart Containers**

```bash
docker compose -f docker-compose.prod.yml down
docker compose -f docker-compose.prod.yml up -d
```

**Step 5: Verify Rollback**

```bash
docker compose -f docker-compose.prod.yml ps
curl -s http://localhost:8000/api | jq
```

---

### Scenario 4: Complete Database Restore

**⚠️ WARNING: This will DELETE all current data**

**Step 1: Stop Application**

```bash
docker compose -f docker-compose.prod.yml stop backend queue-worker rabbitmq-worker
```

**Step 2: Restore Database Backup**

```bash
# Assuming backup file exists
docker compose -f docker-compose.prod.yml exec -T db \
  psql -U postgres -d certchain < /path/to/backup.sql
```

**Step 3: Start Application**

```bash
docker compose -f docker-compose.prod.yml start backend queue-worker rabbitmq-worker
```

---

## Emergency Procedures

### Complete Service Restart

```bash
cd /home/$VM_USER/app
docker compose -f docker-compose.prod.yml down
docker compose -f docker-compose.prod.yml up -d
```

### Kill Stuck Containers

```bash
docker compose -f docker-compose.prod.yml kill
docker compose -f docker-compose.prod.yml down --remove-orphans
docker compose -f docker-compose.prod.yml up -d
```

### Database Connection Issues

**Step 1: Check PostgreSQL Status**

```bash
docker compose -f docker-compose.prod.yml ps db
docker compose -f docker-compose.prod.yml logs --tail=50 db
```

**Step 2: Restart PostgreSQL**

```bash
docker compose -f docker-compose.prod.yml restart db
```

**Step 3: Verify Connection**

```bash
docker compose -f docker-compose.prod.yml exec backend \
  php artisan tinker --execute="DB::connection()->getPdo(); echo 'Connected!';"
```

### Blockchain Service Down

**Step 1: Check Blockchain Service**

```bash
docker compose -f docker-compose.prod.yml logs --tail=100 blockchain
curl http://localhost:3001/v1/health
```

**Step 2: Restart Blockchain**

```bash
docker compose -f docker-compose.prod.yml restart blockchain
```

**Step 3: Verify Smart Contract**

```bash
docker compose -f docker-compose.prod.yml exec blockchain \
  curl http://localhost:3001/v1/health
```

---

## Health Checks

### Quick Health Check Script

```bash
#!/bin/bash

echo "=== Service Health Check ==="

# Frontend
echo -n "Frontend: "
curl -s -o /dev/null -w "%{http_code}" http://localhost:3000 && echo " OK" || echo " FAIL"

# Backend API
echo -n "Backend API: "
curl -s http://localhost:8000/api | jq -r '.message' 2>/dev/null || echo "FAIL"

# Blockchain
echo -n "Blockchain: "
curl -s http://localhost:3001/v1/health | jq -r '.status' 2>/dev/null || echo "FAIL"

# Redis
echo -n "Redis: "
docker compose -f docker-compose.prod.yml exec -T redis redis-cli ping 2>/dev/null || echo "FAIL"

# PostgreSQL
echo -n "PostgreSQL: "
docker compose -f docker-compose.prod.yml exec -T db pg_isready -U postgres 2>/dev/null || echo "FAIL"

# Container Status
echo ""
echo "=== Container Status ==="
docker compose -f docker-compose.prod.yml ps
```

### Monitoring Endpoints

| Endpoint | Expected Response |
|----------|-------------------|
| `GET /api` | `{"message": "CertChain API v1 is running"}` |
| `GET /v1/health` | `{"status": "healthy"}` |
| `GET /v1/metrics` | Prometheus metrics JSON |

---

## Troubleshooting

### Common Issues

#### 1. "502 Bad Gateway" from Nginx

**Cause:** Backend PHP-FPM not running

**Fix:**
```bash
docker compose -f docker-compose.prod.yml restart backend
docker compose -f docker-compose.prod.yml logs backend
```

#### 2. Queue Jobs Not Processing

**Cause:** Queue worker stopped or Redis unavailable

**Fix:**
```bash
docker compose -f docker-compose.prod.yml restart queue-worker redis
docker compose -f docker-compose.prod.yml logs queue-worker
```

#### 3. Certificate Issuance Failing

**Cause:** Blockchain service unreachable or contract not deployed

**Fix:**
```bash
docker compose -f docker-compose.prod.yml logs blockchain
docker compose -f docker-compose.prod.yml restart blockchain
```

#### 4. File Uploads Failing

**Cause:** MinIO service down or bucket missing

**Fix:**
```bash
docker compose -f docker-compose.prod.yml restart minio minio-init
```

#### 5. Memory Issues

**Cause:** Container resource limits exceeded

**Check:**
```bash
docker stats
```

**Fix:**
```bash
docker compose -f docker-compose.prod.yml down
docker system prune -f
docker compose -f docker-compose.prod.yml up -d
```

### Log Locations

| Service | Log Command |
|---------|-------------|
| Backend | `docker compose logs backend` |
| Laravel | `docker compose exec backend cat storage/logs/laravel.log` |
| Queue | `docker compose logs queue-worker` |
| Nginx | `docker compose logs nginx` |
| Blockchain | `docker compose logs blockchain` |
| PostgreSQL | `docker compose logs db` |

### Useful Commands

```bash
# View all logs
docker compose -f docker-compose.prod.yml logs -f

# Check disk usage
docker system df

# Remove unused resources
docker system prune -af --volumes

# Backup database
docker compose -f docker-compose.prod.yml exec db \
  pg_dump -U postgres certchain > backup_$(date +%Y%m%d).sql

# Enter container shell
docker compose -f docker-compose.prod.yml exec backend bash

# Run artisan command
docker compose -f docker-compose.prod.yml exec backend php artisan <command>
```

---

## Deployment Checklist

### Pre-Deployment

- [ ] All tests passing in CI
- [ ] Code reviewed and approved
- [ ] Database migrations tested locally
- [ ] Environment variables updated if needed
- [ ] Backup database (for major changes)

### Post-Deployment

- [ ] Verify all containers running
- [ ] Check health endpoints
- [ ] Test critical user flows
- [ ] Monitor error logs for 15 minutes
- [ ] Verify queue processing
- [ ] Test certificate issuance (blockchain)

### Communication

- [ ] Notify team of deployment start
- [ ] Update status page if maintenance required
- [ ] Notify team of deployment completion
- [ ] Document any issues encountered

---

## Contacts

| Role | Contact |
|------|---------|
| DevOps Lead | [Team contact] |
| Backend Lead | [Team contact] |
| On-call Engineer | [Rotation schedule] |

---

## Version History

| Date | Version | Changes |
|------|---------|---------|
| 2025-12-23 | 1.0 | Initial runbook |
