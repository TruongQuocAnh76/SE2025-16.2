# ---------- Build stage ----------
FROM node:22-alpine AS build

ENV NODE_ENV=production
WORKDIR /app

# Build deps needed for native modules + hardhat
RUN apk add --no-cache \
    git \
    python3 \
    build-base \
    curl

# Enable pnpm
RUN corepack enable && corepack prepare pnpm@latest --activate

# Non-root user (same UID/GID as dev)
RUN addgroup -S nodejs -g 1001 \
 && adduser -S blockchain -u 1001 -G nodejs -h /home/blockchain

USER blockchain

# Copy manifests first (cache)
COPY --chown=blockchain:nodejs package.json pnpm-lock.yaml ./

# Install ALL deps (build + runtime)
RUN pnpm install --frozen-lockfile

# Copy source
COPY --chown=blockchain:nodejs . .

# Build TypeScript
RUN pnpm run build

# Compile smart contracts (Hardhat)
RUN pnpm run compile


# ---------- Runtime stage ----------
FROM node:22-alpine AS production

ENV NODE_ENV=production
WORKDIR /app

# Minimal runtime deps only
RUN apk add --no-cache curl su-exec

# Enable pnpm (some libs still resolve through node_modules)
RUN corepack enable && corepack prepare pnpm@latest --activate

# Same user as dev/build
RUN addgroup -S nodejs -g 1001 \
 && adduser -S blockchain -u 1001 -G nodejs -h /home/blockchain \
 && mkdir -p /app /home/blockchain \
 && chown blockchain:nodejs /app /home/blockchain

USER blockchain

# Copy only what runtime needs
COPY --from=build --chown=blockchain:nodejs /app/package.json ./package.json
COPY --from=build --chown=blockchain:nodejs /app/node_modules ./node_modules
COPY --from=build --chown=blockchain:nodejs /app/dist ./dist
COPY --from=build --chown=blockchain:nodejs /app/blockchain-data ./blockchain-data

EXPOSE 3001

CMD ["node", "dist/index.js"]
