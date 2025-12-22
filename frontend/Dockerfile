# ---------- Build stage ----------
FROM node:20-slim AS build

WORKDIR /app

# Enable pnpm
RUN corepack enable && corepack prepare pnpm@latest --activate

# Create non-root user for building
RUN useradd -m -u 1001 nodeuser \
    && chown -R nodeuser:nodeuser /app

# Switch to non-root user before pnpm install
USER nodeuser

# Copy only dependency manifests first (cache-friendly)
COPY --chown=nodeuser:nodeuser package.json pnpm-lock.yaml ./

# Install deps (including dev deps, needed for build)
RUN pnpm install --frozen-lockfile

# Copy source code (as nodeuser)
COPY --chown=nodeuser:nodeuser . .

# Build Nuxt for production
RUN pnpm build

# ---------- Runtime stage ----------
FROM node:20-slim AS production

ENV NODE_ENV=production
WORKDIR /app

# Enable pnpm (only if node_modules runtime needed)
RUN corepack enable && corepack prepare pnpm@latest --activate

# Create non-root runtime user
RUN useradd -m -u 1001 nodeuser

# Copy output + node_modules from build stage
COPY --from=build /app/.output ./.output
COPY --from=build /app/node_modules ./node_modules
COPY --from=build /app/package.json ./package.json

# Set ownership for runtime
RUN chown -R nodeuser:nodeuser /app

USER nodeuser

EXPOSE 3000
CMD ["node", ".output/server/index.mjs"]
