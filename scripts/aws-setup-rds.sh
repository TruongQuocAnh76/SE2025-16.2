#!/bin/bash
set -e

ENVIRONMENT=$1

if [ -z "$ENVIRONMENT" ]; then
    echo "Usage: $0 <production|staging>"
    exit 1
fi

if [ -z "$VPC_ID" ]; then
    echo "VPC_ID not set. Attempting to find 'certchain-vpc'..."
    VPC_ID=$(aws ec2 describe-vpcs --filters "Name=tag:Name,Values=certchain-vpc" --query "Vpcs[0].VpcId" --output text)
    if [ "$VPC_ID" == "None" ] || [ -z "$VPC_ID" ]; then
        echo "Error: Could not find VPC with name 'certchain-vpc'. Please run aws-setup-vpc.sh first."
        exit 1
    fi
    echo "Found VPC: $VPC_ID"
fi

echo "Setting up RDS for $ENVIRONMENT..."

# Fetch Subnet IDs (assuming standard naming from VPC script)
PRIVATE_SUBNET_1=$(aws ec2 describe-subnets --filters "Name=tag:Name,Values=certchain-private-1a" --query "Subnets[0].SubnetId" --output text)
PRIVATE_SUBNET_2=$(aws ec2 describe-subnets --filters "Name=tag:Name,Values=certchain-private-1b" --query "Subnets[0].SubnetId" --output text)

echo "Creating DB Subnet Group..."
aws rds create-db-subnet-group \
  --db-subnet-group-name certchain-db-subnet \
  --db-subnet-group-description "Certchain DB Subnet Group" \
  --subnet-ids $PRIVATE_SUBNET_1 $PRIVATE_SUBNET_2 || echo "Subnet group might already exist."

echo "Creating Security Group for RDS..."
RDS_SG_ID=$(aws ec2 describe-security-groups --filters "Name=group-name,Values=certchain-rds-sg" --query "SecurityGroups[0].GroupId" --output text)

if [ "$RDS_SG_ID" == "None" ] || [ -z "$RDS_SG_ID" ]; then
  RDS_SG_ID=$(aws ec2 create-security-group \
    --group-name certchain-rds-sg \
    --description "Security group for Certchain RDS" \
    --vpc-id $VPC_ID \
    --query 'GroupId' --output text)
  echo "Created RDS Security Group: $RDS_SG_ID"
else
  echo "RDS Security Group already exists: $RDS_SG_ID"
fi

# Note: Ingress rule needs ECS SG ID, which might not exist yet. 
# You may need to run this after ECS setup or update SG later.

if [ "$ENVIRONMENT" == "production" ]; then
    echo "Creating Production RDS..."
    aws rds create-db-instance \
      --db-instance-identifier certchain-prod-db \
      --db-instance-class db.t3.micro \
      --engine postgres \
      --engine-version 16.6 \
      --master-username certchain_admin \
      --master-user-password 'CHANGE_THIS_SECURE_PASSWORD' \
      --allocated-storage 20 \
      --db-subnet-group-name certchain-db-subnet \
      --vpc-security-group-ids $RDS_SG_ID \
      --backup-retention-period 7 \
      --no-publicly-accessible \
      --tags Key=Name,Value=certchain-prod-db Key=Environment,Value=production
elif [ "$ENVIRONMENT" == "staging" ]; then
    echo "Creating Staging RDS..."
    aws rds create-db-instance \
      --db-instance-identifier certchain-staging-db \
      --db-instance-class db.t3.micro \
      --engine postgres \
      --engine-version 16.6 \
      --master-username certchain_admin \
      --master-user-password 'CHANGE_THIS_PASSWORD' \
      --allocated-storage 20 \
      --db-subnet-group-name certchain-db-subnet \
      --vpc-security-group-ids $RDS_SG_ID \
      --no-publicly-accessible \
      --tags Key=Name,Value=certchain-staging-db Key=Environment,Value=staging
fi

echo "RDS creation initiated. It may take 10-15 minutes."
