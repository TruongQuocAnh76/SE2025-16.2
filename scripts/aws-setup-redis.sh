#!/bin/bash
set -e

ENVIRONMENT=$1

if [ -z "$ENVIRONMENT" ]; then
    echo "Usage: $0 <production|staging>"
    exit 1
fi

echo "Setting up Redis for $ENVIRONMENT..."

if [ -z "$VPC_ID" ]; then
    echo "VPC_ID not set. Attempting to find 'certchain-vpc'..."
    VPC_ID=$(aws ec2 describe-vpcs --filters "Name=tag:Name,Values=certchain-vpc" --query "Vpcs[0].VpcId" --output text)
    if [ "$VPC_ID" == "None" ] || [ -z "$VPC_ID" ]; then
        echo "Error: Could not find VPC with name 'certchain-vpc'. Please run aws-setup-vpc.sh first."
        exit 1
    fi
    echo "Found VPC: $VPC_ID"
fi

PRIVATE_SUBNET_1=$(aws ec2 describe-subnets --filters "Name=tag:Name,Values=certchain-private-1a" --query "Subnets[0].SubnetId" --output text)
PRIVATE_SUBNET_2=$(aws ec2 describe-subnets --filters "Name=tag:Name,Values=certchain-private-1b" --query "Subnets[0].SubnetId" --output text)

echo "Creating Cache Subnet Group..."
aws elasticache create-cache-subnet-group \
  --cache-subnet-group-name certchain-redis-subnet \
  --cache-subnet-group-description "Certchain Redis Subnet Group" \
  --subnet-ids $PRIVATE_SUBNET_1 $PRIVATE_SUBNET_2 || echo "Cache subnet group might already exist."

echo "Creating Security Group for Redis..."
REDIS_SG_ID=$(aws ec2 create-security-group \
  --group-name certchain-redis-sg \
  --description "Security group for Certchain Redis" \
  --vpc-id $VPC_ID \
  --query 'GroupId' --output text || aws ec2 describe-security-groups --filters "Name=group-name,Values=certchain-redis-sg" --query "SecurityGroups[0].GroupId" --output text)

if [ "$ENVIRONMENT" == "production" ]; then
    aws elasticache create-cache-cluster \
      --cache-cluster-id certchain-prod-redis \
      --engine redis \
      --engine-version 7.0 \
      --cache-node-type cache.t3.micro \
      --num-cache-nodes 1 \
      --cache-subnet-group-name certchain-redis-subnet \
      --security-group-ids $REDIS_SG_ID \
      --tags Key=Name,Value=certchain-prod-redis Key=Environment,Value=production
elif [ "$ENVIRONMENT" == "staging" ]; then
    aws elasticache create-cache-cluster \
      --cache-cluster-id certchain-staging-redis \
      --engine redis \
      --engine-version 7.0 \
      --cache-node-type cache.t3.micro \
      --num-cache-nodes 1 \
      --cache-subnet-group-name certchain-redis-subnet \
      --security-group-ids $REDIS_SG_ID \
      --tags Key=Name,Value=certchain-staging-redis Key=Environment,Value=staging
fi

echo "Redis creation initiated."
