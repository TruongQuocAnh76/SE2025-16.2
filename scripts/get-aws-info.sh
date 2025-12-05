#!/bin/bash
set -e

# Check if AWS_REGION is set
if [ -z "$AWS_REGION" ]; then
    export AWS_REGION=$(aws configure get region)
fi

echo "========================================================"
echo "Fetching AWS Configuration Info for ECS Task Definitions"
echo "Region: $AWS_REGION"
echo "========================================================"

# 1. Account ID
ACCOUNT_ID=$(aws sts get-caller-identity --query Account --output text)
echo "YOUR_ACCOUNT_ID: $ACCOUNT_ID"
echo "--------------------------------------------------------"

# 2. RDS Endpoints
echo "Fetching RDS Endpoints..."
PROD_DB=$(aws rds describe-db-instances --db-instance-identifier certchain-prod-db --query "DBInstances[0].Endpoint.Address" --output text 2>/dev/null || echo "Not Found")
STAGING_DB=$(aws rds describe-db-instances --db-instance-identifier certchain-staging-db --query "DBInstances[0].Endpoint.Address" --output text 2>/dev/null || echo "Not Found")

echo "YOUR_PROD_RDS_ENDPOINT: $PROD_DB"
echo "YOUR_STAGING_RDS_ENDPOINT: $STAGING_DB"
echo "--------------------------------------------------------"

# 3. Redis Endpoints
echo "Fetching Redis Endpoints..."
PROD_REDIS=$(aws elasticache describe-cache-clusters --cache-cluster-id certchain-prod-redis --show-cache-node-info --query "CacheClusters[0].CacheNodes[0].Endpoint.Address" --output text 2>/dev/null || echo "Not Found")
STAGING_REDIS=$(aws elasticache describe-cache-clusters --cache-cluster-id certchain-staging-redis --show-cache-node-info --query "CacheClusters[0].CacheNodes[0].Endpoint.Address" --output text 2>/dev/null || echo "Not Found")

echo "YOUR_PROD_ELASTICACHE_ENDPOINT: $PROD_REDIS"
echo "YOUR_STAGING_ELASTICACHE_ENDPOINT: $STAGING_REDIS"
echo "--------------------------------------------------------"

# 4. Subnets (for reference)
echo "Fetching Private Subnets..."
SUBNET_1=$(aws ec2 describe-subnets --filters "Name=tag:Name,Values=certchain-private-1a" --query "Subnets[0].SubnetId" --output text 2>/dev/null || echo "Not Found")
SUBNET_2=$(aws ec2 describe-subnets --filters "Name=tag:Name,Values=certchain-private-1b" --query "Subnets[0].SubnetId" --output text 2>/dev/null || echo "Not Found")

echo "Private Subnet 1: $SUBNET_1"
echo "Private Subnet 2: $SUBNET_2"
echo "========================================================"
echo "INSTRUCTIONS:"
echo "1. Copy the values above."
echo "2. Open 'ecs-task-definitions/*.json' files."
echo "3. Replace the placeholders (YOUR_ACCOUNT_ID, etc.) with these values."
echo "========================================================"
