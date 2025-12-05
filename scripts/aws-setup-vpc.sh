#!/bin/bash
set -e

# Check if AWS_REGION is set
if [ -z "$AWS_REGION" ]; then
    export AWS_REGION=$(aws configure get region)
    echo "AWS_REGION not set, using default: $AWS_REGION"
fi

# Check if VPC already exists
VPC_ID=$(aws ec2 describe-vpcs --filters "Name=tag:Name,Values=certchain-vpc" --query "Vpcs[0].VpcId" --output text)

if [ "$VPC_ID" == "None" ] || [ -z "$VPC_ID" ]; then
  echo "Creating VPC..."
  VPC_ID=$(aws ec2 create-vpc \
    --cidr-block 10.0.0.0/16 \
    --tag-specifications 'ResourceType=vpc,Tags=[{Key=Name,Value=certchain-vpc}]' \
    --query 'Vpc.VpcId' --output text)
  echo "VPC Created: $VPC_ID"
else
  echo "VPC already exists: $VPC_ID"
fi

echo "Creating Public Subnets..."
# Function to create subnet if not exists
create_subnet() {
  CIDR=$1
  AZ=$2
  NAME=$3
  SUBNET_ID=$(aws ec2 describe-subnets --filters "Name=tag:Name,Values=$NAME" --query "Subnets[0].SubnetId" --output text)
  
  if [ "$SUBNET_ID" == "None" ] || [ -z "$SUBNET_ID" ]; then
    aws ec2 create-subnet \
      --vpc-id $VPC_ID \
      --cidr-block $CIDR \
      --availability-zone $AZ \
      --tag-specifications "ResourceType=subnet,Tags=[{Key=Name,Value=$NAME}]"
  else
    echo "Subnet $NAME already exists: $SUBNET_ID"
  fi
}

create_subnet "10.0.1.0/24" "${AWS_REGION}a" "certchain-public-1a"
create_subnet "10.0.2.0/24" "${AWS_REGION}b" "certchain-public-1b"

echo "Creating Private Subnets..."
create_subnet "10.0.10.0/24" "${AWS_REGION}a" "certchain-private-1a"
create_subnet "10.0.11.0/24" "${AWS_REGION}b" "certchain-private-1b"

echo "Creating Internet Gateway..."
IGW_ID=$(aws ec2 describe-internet-gateways --filters "Name=tag:Name,Values=certchain-igw" --query "InternetGateways[0].InternetGatewayId" --output text)

if [ "$IGW_ID" == "None" ] || [ -z "$IGW_ID" ]; then
  IGW_ID=$(aws ec2 create-internet-gateway \
    --tag-specifications 'ResourceType=internet-gateway,Tags=[{Key=Name,Value=certchain-igw}]' \
    --query 'InternetGateway.InternetGatewayId' --output text)
  aws ec2 attach-internet-gateway --vpc-id $VPC_ID --internet-gateway-id $IGW_ID
  echo "Internet Gateway Created and Attached: $IGW_ID"
else
  echo "Internet Gateway already exists: $IGW_ID"
fi

echo "Creating NAT Gateway..."
PUBLIC_SUBNET_1=$(aws ec2 describe-subnets --filters "Name=tag:Name,Values=certchain-public-1a" --query "Subnets[0].SubnetId" --output text)

NAT_GW_ID=$(aws ec2 describe-nat-gateways --filter "Name=tag:Name,Values=certchain-nat" --query "NatGateways[0].NatGatewayId" --output text)

if [ "$NAT_GW_ID" == "None" ] || [ -z "$NAT_GW_ID" ]; then
  EIP_ALLOC_ID=$(aws ec2 allocate-address --domain vpc --tag-specifications 'ResourceType=elastic-ip,Tags=[{Key=Name,Value=certchain-nat-eip}]' --query 'AllocationId' --output text)
  
  # Removed tag-specifications from create-nat-gateway to avoid errors
  NAT_GW_ID=$(aws ec2 create-nat-gateway \
    --subnet-id $PUBLIC_SUBNET_1 \
    --allocation-id $EIP_ALLOC_ID \
    --query 'NatGateway.NatGatewayId' --output text)
    
  # Add tags separately
  aws ec2 create-tags --resources $NAT_GW_ID --tags Key=Name,Value=certchain-nat
  
  echo "NAT Gateway Created: $NAT_GW_ID"
else
  echo "NAT Gateway already exists: $NAT_GW_ID"
fi

echo "Please wait for NAT Gateway to become available before configuring routes."
