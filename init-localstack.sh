#!/bin/bash

# Wait for LocalStack to be ready
echo "Waiting for LocalStack to start..."
while ! curl -s http://localstack:4566/_localstack/health > /dev/null; do
    echo "LocalStack is not ready yet. Waiting..."
    sleep 2
done

echo "LocalStack is ready. Creating S3 bucket..."

# Create the S3 bucket
aws --endpoint-url=http://localstack:4566 s3 mb s3://certchain-dev --region us-east-1

# Set bucket CORS policy to allow uploads from frontend
aws --endpoint-url=http://localstack:4566 s3api put-bucket-cors \
    --bucket certchain-dev \
    --cors-configuration '{
        "CORSRules": [
            {
                "AllowedHeaders": ["*"],
                "AllowedMethods": ["GET", "PUT", "POST", "DELETE", "HEAD"],
                "AllowedOrigins": ["*"],
                "ExposeHeaders": ["ETag", "x-amz-meta-custom-header", "Content-Length"],
                "MaxAgeSeconds": 3000
            }
        ]
    }'

# Make bucket public for read access
aws --endpoint-url=http://localstack:4566 s3api put-bucket-policy \
    --bucket certchain-dev \
    --policy '{
        "Version": "2012-10-17",
        "Statement": [
            {
                "Sid": "PublicReadGetObject",
                "Effect": "Allow",
                "Principal": "*",
                "Action": "s3:GetObject",
                "Resource": "arn:aws:s3:::certchain-dev/*"
            }
        ]
    }'

echo "S3 bucket 'certchain-dev' created and configured successfully!"
