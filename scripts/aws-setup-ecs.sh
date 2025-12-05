#!/bin/bash
set -e

echo "Setting up ECS..."

if [ -z "$VPC_ID" ]; then
    VPC_ID=$(aws ec2 describe-vpcs --filters "Name=tag:Name,Values=certchain-vpc" --query "Vpcs[0].VpcId" --output text)
fi

echo "Creating IAM Roles..."
# Task Execution Role
aws iam create-role \
  --role-name ecsTaskExecutionRole \
  --assume-role-policy-document '{"Version":"2012-10-17","Statement":[{"Effect":"Allow","Principal":{"Service":"ecs-tasks.amazonaws.com"},"Action":"sts:AssumeRole"}]}' || echo "Role ecsTaskExecutionRole exists."

aws iam attach-role-policy \
  --role-name ecsTaskExecutionRole \
  --policy-arn arn:aws:iam::aws:policy/service-role/AmazonECSTaskExecutionRolePolicy

aws iam attach-role-policy \
  --role-name ecsTaskExecutionRole \
  --policy-arn arn:aws:iam::aws:policy/SecretsManagerReadWrite

echo "Creating ECS Service Linked Role..."
aws iam create-service-linked-role --aws-service-name ecs.amazonaws.com || echo "Service linked role might already exist."

echo "Creating Security Group for ECS..."
ECS_SG_ID=$(aws ec2 create-security-group \
  --group-name certchain-ecs-sg \
  --description "Security group for Certchain ECS tasks" \
  --vpc-id $VPC_ID \
  --query 'GroupId' --output text || aws ec2 describe-security-groups --filters "Name=group-name,Values=certchain-ecs-sg" --query "SecurityGroups[0].GroupId" --output text)

echo "Creating ECS Clusters..."
aws ecs create-cluster \
  --cluster-name certchain-production \
  --capacity-providers FARGATE FARGATE_SPOT \
  --default-capacity-provider-strategy capacityProvider=FARGATE,weight=1 \
  --tags key=Name,value=certchain-production key=Environment,value=production

aws ecs create-cluster \
  --cluster-name certchain-staging \
  --capacity-providers FARGATE FARGATE_SPOT \
  --default-capacity-provider-strategy capacityProvider=FARGATE_SPOT,weight=1 \
  --tags key=Name,value=certchain-staging key=Environment,value=staging

echo "ECS Setup Complete."
