# ---------- Build stage ----------
FROM node:20-slim AS build

WORKDIR /app

# Enable pnpm
RUN corepack enable && corepack prepare pnpm@latest --activate

# Copy only dependency manifests first (cache-friendly)
COPY package.json pnpm-lock.yaml ./

# Install deps (including dev deps, needed for build)
RUN pnpm install --frozen-lockfile

# Copy source
COPY . .

# Build Nuxt for production
RUN pnpm build


# ---------- Runtime stage ----------
FROM node:20-slim AS production

ENV NODE_ENV=production
WORKDIR /app

# Enable pnpm (needed only if Nuxt runtime needs node_modules)
RUN corepack enable && corepack prepare pnpm@latest --activate

# Create non-root user
RUN useradd -m -u 1001 nodeuser

# Copy output only (not source, not dev deps)
COPY --from=build /app/.output ./.output
COPY --from=build /app/node_modules ./node_modules
COPY --from=build /app/package.json ./package.json

USER nodeuser

EXPOSE 3000

CMD ["node", ".output/server/index.mjs"]
