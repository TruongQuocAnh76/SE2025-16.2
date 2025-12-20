# ---------- Build stage ----------
FROM node:22-alpine AS build

ENV NODE_ENV=production
ENV HOME=/home/blockchain

# System dependencies
RUN apk add --no-cache git python3 build-base curl

# Enable fixed pnpm version
RUN corepack enable && corepack prepare pnpm@10.26.1 --activate

# Create non-root user and home/app directories
RUN addgroup -S nodejs -g 1001 \
 && adduser -S blockchain -u 1001 -G nodejs -h /home/blockchain \
 && mkdir -p /home/blockchain/app \
 && mkdir -p /home/blockchain/.cache/node/corepack/v1 \
 && chown -R blockchain:nodejs /home/blockchain

WORKDIR /home/blockchain/app
USER blockchain

# Copy root manifests for caching
COPY --chown=blockchain:nodejs package.json pnpm-lock.yaml ./

# Install root dependencies
RUN pnpm install --frozen-lockfile

# Copy source files
COPY --chown=blockchain:nodejs . .

# Build root scripts
RUN pnpm run build

# Copy blockchain folder
COPY --chown=blockchain:nodejs blockchain ./blockchain

# Build blockchain smart contracts
WORKDIR /home/blockchain/app/blockchain
RUN pnpm install --frozen-lockfile
RUN pnpm run compile

# ---------- Runtime stage ----------
FROM node:22-alpine AS production

ENV NODE_ENV=production
ENV HOME=/home/blockchain

WORKDIR /home/blockchain/app

# Minimal runtime deps
RUN apk add --no-cache curl su-exec

# Enable pnpm
RUN corepack enable && corepack prepare pnpm@10.26.1 --activate

# Create same non-root user and directories
RUN addgroup -S nodejs -g 1001 \
 && adduser -S blockchain -u 1001 -G nodejs -h /home/blockchain \
 && mkdir -p /home/blockchain/app \
 && mkdir -p /home/blockchain/.cache/node/corepack/v1 \
 && chown -R blockchain:nodejs /home/blockchain

USER blockchain

# Copy runtime essentials
COPY --from=build --chown=blockchain:nodejs /home/blockchain/app/package.json ./package.json
COPY --from=build --chown=blockchain:nodejs /home/blockchain/app/node_modules ./node_modules
COPY --from=build --chown=blockchain:nodejs /home/blockchain/app/dist ./dist
COPY --from=build --chown=blockchain:nodejs /home/blockchain/app/blockchain-data ./blockchain-data

EXPOSE 3001

CMD ["node", "dist/index.js"]
