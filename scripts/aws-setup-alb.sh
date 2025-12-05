#!/bin/bash
set -e

echo "Setting up Application Load Balancer..."

if [ -z "$VPC_ID" ]; then
    VPC_ID=$(aws ec2 describe-vpcs --filters "Name=tag:Name,Values=certchain-vpc" --query "Vpcs[0].VpcId" --output text)
fi

echo "Creating ALB Security Group..."
ALB_SG_ID=$(aws ec2 create-security-group \
  --group-name certchain-alb-sg \
  --description "Security group for Certchain ALB" \
  --vpc-id $VPC_ID \
  --query 'GroupId' --output text || aws ec2 describe-security-groups --filters "Name=group-name,Values=certchain-alb-sg" --query "SecurityGroups[0].GroupId" --output text)

aws ec2 authorize-security-group-ingress \
  --group-id $ALB_SG_ID \
  --protocol tcp \
  --port 80 \
  --cidr 0.0.0.0/0 || echo "Ingress rule 80 exists."

aws ec2 authorize-security-group-ingress \
  --group-id $ALB_SG_ID \
  --protocol tcp \
  --port 443 \
  --cidr 0.0.0.0/0 || echo "Ingress rule 443 exists."

PUBLIC_SUBNET_1=$(aws ec2 describe-subnets --filters "Name=tag:Name,Values=certchain-public-1a" --query "Subnets[0].SubnetId" --output text)
PUBLIC_SUBNET_2=$(aws ec2 describe-subnets --filters "Name=tag:Name,Values=certchain-public-1b" --query "Subnets[0].SubnetId" --output text)

echo "Creating ALB..."
ALB_ARN=$(aws elbv2 create-load-balancer \
  --name certchain-prod-alb \
  --subnets $PUBLIC_SUBNET_1 $PUBLIC_SUBNET_2 \
  --security-groups $ALB_SG_ID \
  --tags Key=Name,Value=certchain-prod-alb Key=Environment,Value=production \
  --query 'LoadBalancers[0].LoadBalancerArn' --output text)

echo "Creating Target Groups..."
aws elbv2 create-target-group \
  --name certchain-backend-prod-tg \
  --protocol HTTP \
  --port 8000 \
  --vpc-id $VPC_ID \
  --target-type ip \
  --health-check-path /api/health

aws elbv2 create-target-group \
  --name certchain-frontend-prod-tg \
  --protocol HTTP \
  --port 3000 \
  --vpc-id $VPC_ID \
  --target-type ip \
  --health-check-path /

echo "ALB Setup Complete. Please configure listeners manually or via further scripts."
