# ---------- Build stage ----------
FROM node:22-alpine AS build

ENV NODE_ENV=production

# System dependencies
RUN apk add --no-cache git python3 build-base curl

# Enable fixed pnpm version
RUN corepack enable && corepack prepare pnpm@10.26.1 --activate

# Create non-root user
RUN addgroup -S nodejs -g 1001 \
 && adduser -S blockchain -u 1001 -G nodejs

# Set up working directory
WORKDIR /app
RUN chown blockchain:nodejs /app

# Switch to non-root user
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

WORKDIR /app

# Enable pnpm
RUN corepack enable && corepack prepare pnpm@10.26.1 --activate

# Create non-root user
RUN addgroup -S nodejs -g 1001 \
 && adduser -S blockchain -u 1001 -G nodejs

# Copy built artifacts and node_modules from build stage
COPY --from=build --chown=blockchain:nodejs /app/dist ./dist
COPY --from=build --chown=blockchain:nodejs /app/node_modules ./node_modules
COPY --from=build --chown=blockchain:nodejs /app/package.json ./package.json

# Switch to non-root user
USER blockchain

# Expose service port
EXPOSE 3001

# Start the service
CMD ["pnpm", "start"]
