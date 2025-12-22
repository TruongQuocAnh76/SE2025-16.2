# ---------- Build stage ----------
FROM node:22-alpine AS build

ENV NODE_ENV=production
ENV HOME=/home/blockchain

# System dependencies
RUN apk add --no-cache git python3 build-base curl

# Enable fixed pnpm version
RUN corepack enable && corepack prepare pnpm@10.26.1 --activate

# Create non-root user
RUN addgroup -S nodejs -g 1001 \
 && adduser -S blockchain -u 1001 -G nodejs -h /home/blockchain \
 && mkdir -p /home/blockchain/app \
 && mkdir -p /home/blockchain/.cache/node/corepack/v1 \
 && chown -R blockchain:nodejs /home/blockchain

# Fix ownership of /app
RUN chown -R blockchain:nodejs /app

# Switch to non-root user AFTER fixing ownership
USER blockchain

# Copy root manifests for caching
COPY --chown=blockchain:nodejs package.json pnpm-lock.yaml ./

# Install root dependencies
RUN pnpm install --frozen-lockfile

# Copy source files
COPY --chown=blockchain:nodejs . .

# Build root scripts
RUN pnpm run build

# ---------- Production stage ----------
FROM node:22-alpine AS production

ENV NODE_ENV=production
ENV HOME=/home/blockchain

WORKDIR /home/blockchain/app

# Enable pnpm
RUN corepack enable && corepack prepare pnpm@latest --activate

# Create non-root user
RUN addgroup -S nodejs -g 1001 \
 && adduser -S blockchain -u 1001 -G nodejs -h /home/blockchain

# Copy built artifacts and node_modules from build stage
COPY --from=build /app/dist ./dist
COPY --from=build /app/node_modules ./node_modules
COPY --from=build /app/package.json ./package.json

# Set ownership and user
RUN chown -R blockchain:nodejs /app
USER blockchain

# Expose service port
EXPOSE 3001

# Start the service
CMD ["pnpm", "start"]
