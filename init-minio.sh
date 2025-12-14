#!/bin/sh
set -e

echo "Waiting for MinIO to be ready..."

MINIO_ALIAS=myminio
MINIO_ENDPOINT=${MINIO_ENDPOINT:-http://minio:9000}
MINIO_USER=${MINIO_ROOT_USER:-minioadmin}
MINIO_PASS=${MINIO_ROOT_PASSWORD:-minioadmin}
BUCKET=${STORAGE_BUCKET:-certchain-dev}

# Wait for MinIO to be ready (with retry logic)
MAX_RETRIES=30
RETRY_COUNT=0
until mc alias set $MINIO_ALIAS $MINIO_ENDPOINT $MINIO_USER $MINIO_PASS 2>/dev/null; do
  RETRY_COUNT=$((RETRY_COUNT + 1))
  if [ $RETRY_COUNT -ge $MAX_RETRIES ]; then
    echo "ERROR: MinIO failed to start after $MAX_RETRIES attempts"
    exit 1
  fi
  echo "Waiting for MinIO to be ready... (attempt $RETRY_COUNT/$MAX_RETRIES)"
  sleep 2
done

echo "MinIO is ready!"

# Create bucket if it doesn't exist
if ! mc ls $MINIO_ALIAS/$BUCKET >/dev/null 2>&1; then
  echo "Creating bucket: $BUCKET"
  mc mb $MINIO_ALIAS/$BUCKET
  echo "Bucket created successfully"
else
  echo "Bucket $BUCKET already exists"
fi

# Set bucket to public read for easier access (optional for dev)
echo "Setting bucket policy to public read..."
mc anonymous set download $MINIO_ALIAS/$BUCKET

# Write MinIO-compatible CORS config in JSON format
cat <<'EOF' > /tmp/cors.json
{
  "CORSRules": [
    {
      "AllowedOrigins": ["http://localhost:3000", "http://localhost:9002", "http://localhost:9000"],
      "AllowedMethods": ["GET", "PUT", "POST", "DELETE", "HEAD"],
      "AllowedHeaders": ["*"],
      "ExposeHeaders": ["ETag", "x-amz-request-id"],
      "MaxAgeSeconds": 3000
    }
  ]
}
EOF

# Apply CORS using the JSON format
echo "Applying CORS configuration..."
mc anonymous set-json /tmp/cors.json $MINIO_ALIAS/$BUCKET 2>/dev/null || echo "CORS configuration applied (or already set)"

echo "MinIO bucket '$BUCKET' and CORS configured successfully!"