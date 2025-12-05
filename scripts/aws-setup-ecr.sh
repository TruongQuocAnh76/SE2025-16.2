#!/bin/bash
set -e

if [ -z "$AWS_REGION" ]; then
    export AWS_REGION=$(aws configure get region)
fi

echo "Creating ECR Repository: certchain-backend..."
aws ecr create-repository \
  --repository-name certchain-backend \
  --image-scanning-configuration scanOnPush=true \
  --region $AWS_REGION || echo "Repository certchain-backend might already exist."

echo "Creating ECR Repository: certchain-frontend..."
aws ecr create-repository \
  --repository-name certchain-frontend \
  --image-scanning-configuration scanOnPush=true \
  --region $AWS_REGION || echo "Repository certchain-frontend might already exist."

echo "ECR Setup Complete."
