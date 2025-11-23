# Multi-stage build for blockchain microservice

# Stage 1: Build stage
FROM node:20-alpine AS builder

# Install pnpm
RUN npm install -g pnpm

WORKDIR /app

# Copy package files
COPY package.json pnpm-lock.yaml ./

# Install dependencies
RUN pnpm install --frozen-lockfile

# Copy source code and build configurations
COPY src ./src
COPY tsconfig.json ./
COPY hardhat.config.ts ./
COPY contracts ./contracts
COPY ignition ./ignition

# Build TypeScript code
RUN pnpm run build

# Compile smart contracts
RUN pnpm run compile

# Stage 2: Production stage
FROM node:20-alpine AS production

# Install pnpm
RUN npm install -g pnpm

# Create app directory
WORKDIR /app

# Create non-root user
RUN addgroup -g 1001 -S nodejs && \
    adduser -S blockchain -u 1001

# Copy package files
COPY package.json pnpm-lock.yaml ./

# Install production dependencies only
RUN pnpm install --prod --frozen-lockfile && \
    pnpm store prune

# Copy built application and contracts from builder stage
COPY --from=builder --chown=blockchain:nodejs /app/dist ./dist
COPY --from=builder --chown=blockchain:nodejs /app/artifacts ./artifacts
COPY --from=builder --chown=blockchain:nodejs /app/typechain-types ./typechain-types

# Copy environment file template
COPY --chown=blockchain:nodejs .env.example ./.env.example

# Create necessary directories and set permissions
RUN mkdir -p /app/logs && \
    chown -R blockchain:nodejs /app

# Switch to non-root user
USER blockchain

# Expose port
EXPOSE 3001

# Health check
HEALTHCHECK --interval=30s --timeout=3s --start-period=5s --retries=3 \
    CMD node -e "fetch('http://localhost:3001/v1/health').then(r => r.ok ? process.exit(0) : process.exit(1)).catch(() => process.exit(1))"

# Start the application
CMD ["node", "dist/index.js"]