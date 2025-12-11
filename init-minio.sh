#!/bin/sh
set -e

# Wait for MinIO to be ready
echo "Waiting for MinIO to be ready..."
until curl -sf http://minio:9000/minio/health/live; do
    echo "MinIO is not ready yet. Waiting..."
    sleep 2
done

echo "MinIO is ready. Configuring..."

# Configure mc alias
# Using default variables from docker-compose if not set
USER=${MINIO_ROOT_USER:-minioadmin}
PASSWORD=${MINIO_ROOT_PASSWORD:-minioadmin}

mc alias set myminio http://minio:9000 "$USER" "$PASSWORD"

# Create bucket
echo "Creating bucket: $AWS_BUCKET"
mc mb myminio/$AWS_BUCKET --ignore-existing

# Set policy to public (read-only for public)
echo "Setting anonymous access for bucket: $AWS_BUCKET"
mc anonymous set public myminio/$AWS_BUCKET

echo "MinIO setup completed successfully!"
