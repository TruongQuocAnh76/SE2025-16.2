# Build stage
FROM node:18-alpine AS build

WORKDIR /app

# Copy package files
COPY package*.json ./
COPY pnpm-lock.yaml ./

# Install dependencies
RUN npm install -g pnpm && pnpm install --frozen-lockfile

# Copy source code
COPY . .

# Build the application
RUN pnpm build

# Production stage
FROM node:18-alpine AS production

WORKDIR /app

# Copy built application from build stage
COPY --from=build /app/.output ./.output

# Expose port
EXPOSE 3000

# Start the application
CMD ["node", ".output/server/index.mjs"]
