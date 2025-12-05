# 🚀 Deployment Guide - Amazon Web Services (AWS)

## Prerequisites

1. **AWS Account** with appropriate permissions
2. **GitHub Repository** with admin access
3. **AWS CLI** installed locally (v2)
4. **Terraform** (optional, for infrastructure as code)

## 📋 Setup Steps (Automated)

We have provided automated bash scripts in the `scripts/` directory to simplify the infrastructure setup.

### 1. AWS Account Setup

```bash
# Configure AWS CLI
aws configure
# Enter your AWS Access Key ID
# Enter your AWS Secret Access Key
# Default region: ap-southeast-1 (Singapore)
# Default output format: json

# Verify configuration
aws sts get-caller-identity
```

### 2. Run Infrastructure Scripts

Run the following scripts in order to set up the entire AWS infrastructure:

```bash
# 1. Setup VPC, Subnets, IGW, NAT Gateway
bash scripts/aws-setup-vpc.sh

# 2. Setup ECR Repositories
bash scripts/aws-setup-ecr.sh

# 3. Setup Secrets Manager (Placeholders)
bash scripts/aws-setup-secrets.sh
# IMPORTANT: Go to AWS Console -> Secrets Manager and update the values for:
# - certchain/prod/db-password
# - certchain/staging/db-password
# - certchain/prod/jwt-secret
# - etc.

# 4. Setup ECS Cluster & IAM Roles
bash scripts/aws-setup-ecs.sh

# 5. Setup Load Balancer (ALB)
bash scripts/aws-setup-alb.sh

# 6. Setup RDS (Database)
# Note: This takes 10-15 minutes.
bash scripts/aws-setup-rds.sh staging
bash scripts/aws-setup-rds.sh production

# 7. Setup Redis (ElastiCache)
# Note: This takes 5-10 minutes.
bash scripts/aws-setup-redis.sh staging
bash scripts/aws-setup-redis.sh production
```

### 3. Finalize Configuration

After all resources are created (especially RDS and Redis), run the helper script to get the endpoints:

```bash
bash scripts/get-aws-info.sh
```

Copy the output values and update the following files in `ecs-task-definitions/`:
- `backend-production.json`
- `backend-staging.json`
- `migrate-production.json`

Replace placeholders like `YOUR_PROD_RDS_ENDPOINT`, `YOUR_STAGING_ELASTICACHE_ENDPOINT`, etc.

### 4. Register Task Definitions

```bash
aws ecs register-task-definition --cli-input-json file://ecs-task-definitions/backend-staging.json
aws ecs register-task-definition --cli-input-json file://ecs-task-definitions/frontend-staging.json
aws ecs register-task-definition --cli-input-json file://ecs-task-definitions/backend-production.json
aws ecs register-task-definition --cli-input-json file://ecs-task-definitions/frontend-production.json
aws ecs register-task-definition --cli-input-json file://ecs-task-definitions/migrate-production.json
```

### 5. Configure GitHub Secrets

Add the following secrets to your GitHub Repository:

- `AWS_ACCESS_KEY_ID`
- `AWS_SECRET_ACCESS_KEY`
- `AWS_ACCOUNT_ID`
- `AWS_REGION` (ap-southeast-1)
- `AWS_PRIVATE_SUBNET_1` (Get from get-aws-info.sh)
- `AWS_PRIVATE_SUBNET_2` (Get from get-aws-info.sh)
- `AWS_SECURITY_GROUP` (Get from get-aws-info.sh - ECS SG ID)


### 12. Configure GitHub Secrets

Add these secrets to your GitHub repository (Settings → Secrets and variables → Actions):

| Secret Name             | Description             | Example                                    |
| ----------------------- | ----------------------- | ------------------------------------------ |
| `AWS_ACCESS_KEY_ID`     | IAM user access key     | `AKIAIOSFODNN7EXAMPLE`                     |
| `AWS_SECRET_ACCESS_KEY` | IAM user secret key     | `wJalrXUtnFEMI/K7MDENG/bPxRfiCYEXAMPLEKEY` |
| `AWS_ACCOUNT_ID`        | AWS Account ID          | `123456789012`                             |
| `AWS_PRIVATE_SUBNET_1`  | Private subnet ID 1     | `subnet-12345678`                          |
| `AWS_PRIVATE_SUBNET_2`  | Private subnet ID 2     | `subnet-87654321`                          |
| `AWS_SECURITY_GROUP`    | ECS task security group | `sg-12345678`                              |

### 13. Setup Custom Domains with Route 53 (Optional)

```bash
# Create hosted zone
aws route53 create-hosted-zone \
  --name certchain.com \
  --caller-reference $(date +%s)

# Get ALB DNS name
export ALB_DNS=$(aws elbv2 describe-load-balancers \
  --names certchain-prod-alb \
  --query "LoadBalancers[0].DNSName" \
  --output text)

# Create A record for frontend
aws route53 change-resource-record-sets \
  --hosted-zone-id YOUR_HOSTED_ZONE_ID \
  --change-batch '{
    "Changes": [{
      "Action": "CREATE",
      "ResourceRecordSet": {
        "Name": "certchain.com",
        "Type": "A",
        "AliasTarget": {
          "HostedZoneId": "YOUR_ALB_HOSTED_ZONE_ID",
          "DNSName": "'$ALB_DNS'",
          "EvaluateTargetHealth": false
        }
      }
    }]
  }'

# Create A record for backend API
aws route53 change-resource-record-sets \
  --hosted-zone-id YOUR_HOSTED_ZONE_ID \
  --change-batch '{
    "Changes": [{
      "Action": "CREATE",
      "ResourceRecordSet": {
        "Name": "api.certchain.com",
        "Type": "A",
        "AliasTarget": {
          "HostedZoneId": "YOUR_ALB_HOSTED_ZONE_ID",
          "DNSName": "'$ALB_DNS'",
          "EvaluateTargetHealth": false
        }
      }
    }]
  }'

# Request SSL certificate from ACM
aws acm request-certificate \
  --domain-name certchain.com \
  --subject-alternative-names *.certchain.com \
  --validation-method DNS \
  --region $AWS_REGION
```

## 🔄 CI/CD Pipeline Flow

### On Pull Request:

1. ✅ Code quality checks (Prettier, PHPStan)
2. ✅ Security vulnerability scanning (Trivy)
3. ✅ Backend tests with coverage (PostgreSQL)
4. ✅ Frontend build validation

### On Push to `dev` branch:

1. ✅ All CI checks
2. 🐳 Build & push Docker images to ECR
3. 🚀 Deploy to **Staging** ECS cluster

### On Push to `main` branch:

1. ✅ All CI checks
2. 🐳 Build & push Docker images to ECR
3. 📊 Run database migrations (ECS task)
4. 🚀 Deploy to **Production** ECS cluster

## 📊 Monitoring & Logs

```bash
# View ECS service status
aws ecs describe-services \
  --cluster certchain-production \
  --services certchain-backend-prod certchain-frontend-prod

# View logs (requires CloudWatch Logs configuration)
aws logs tail /ecs/certchain-backend-prod --follow

aws logs tail /ecs/certchain-frontend-prod --follow

# Monitor tasks
aws ecs list-tasks --cluster certchain-production

# Get task details
aws ecs describe-tasks \
  --cluster certchain-production \
  --tasks TASK_ARN
```

### Setup CloudWatch Dashboard

```bash
# Create CloudWatch dashboard for monitoring
aws cloudwatch put-dashboard \
  --dashboard-name certchain-production \
  --dashboard-body file://cloudwatch-dashboard.json
```

## 🔧 Troubleshooting

### Check ECS service status

```bash
# List services
aws ecs list-services --cluster certchain-production

# Describe service
aws ecs describe-services \
  --cluster certchain-production \
  --services certchain-backend-prod
```

### Rollback to previous version

```bash
# List task definition revisions
aws ecs list-task-definitions \
  --family-prefix certchain-backend-prod

# Update service to use previous task definition
aws ecs update-service \
  --cluster certchain-production \
  --service certchain-backend-prod \
  --task-definition certchain-backend-prod:PREVIOUS_REVISION
```

### Connect to RDS locally (via bastion or VPN)

```bash
# Using SSM Session Manager port forwarding
aws ssm start-session \
  --target BASTION_INSTANCE_ID \
  --document-name AWS-StartPortForwardingSessionToRemoteHost \
  --parameters '{"host":["YOUR_RDS_ENDPOINT"],"portNumber":["5432"],"localPortNumber":["5432"]}'

# Then connect locally
psql -h localhost -U certchain_admin -d certchain
```

### Debug ECS task failures

```bash
# Get stopped tasks
aws ecs list-tasks \
  --cluster certchain-production \
  --desired-status STOPPED \
  --max-results 10

# Describe stopped task to see exit reason
aws ecs describe-tasks \
  --cluster certchain-production \
  --tasks TASK_ARN
```

## 💰 Cost Optimization

**AWS Services Pricing (ap-southeast-1):**

- ECS Fargate: ~$0.04/vCPU/hour + $0.004/GB/hour
- RDS db.t3.micro: ~$15-20/month (with reserved instances)
- ElastiCache cache.t3.micro: ~$15-20/month
- Application Load Balancer: ~$22/month + data processing
- ECR: $0.10/GB storage
- Data Transfer: First 1 GB/month free, then $0.12/GB

**Estimated Monthly Cost:**

- **Staging** (Fargate Spot, minimal resources): ~$50-80/month
- **Production** (moderate traffic, 2 tasks each): ~$120-200/month

**Cost Optimization Tips:**

1. Use Fargate Spot for staging (70% cheaper)
2. Use RDS reserved instances (up to 60% savings)
3. Enable ECS autoscaling based on CPU/memory
4. Use ECR image lifecycle policies to delete old images
5. Set CloudWatch log retention periods
6. Use VPC endpoints to avoid NAT Gateway charges

## 🔒 Security Best Practices

1. ✅ Store secrets in AWS Secrets Manager
2. ✅ Use IAM roles with least privilege
3. ✅ Enable AWS WAF on ALB for DDoS protection
4. ✅ Private subnets for ECS tasks and RDS
5. ✅ Security groups with minimal access
6. ✅ Enable VPC Flow Logs
7. ✅ Enable CloudTrail for audit logging
8. ✅ Regular security scanning with Trivy
9. ✅ Enable RDS encryption at rest
10. ✅ Use AWS Systems Manager Session Manager instead of SSH

### Enable AWS WAF

```bash
# Create WAF Web ACL
aws wafv2 create-web-acl \
  --name certchain-waf \
  --scope REGIONAL \
  --region $AWS_REGION \
  --default-action Allow={} \
  --rules file://waf-rules.json

# Associate with ALB
aws wafv2 associate-web-acl \
  --web-acl-arn WAF_ACL_ARN \
  --resource-arn ALB_ARN
```

## 📚 Additional Resources

- [ECS Best Practices](https://docs.aws.amazon.com/AmazonECS/latest/bestpracticesguide/)
- [RDS PostgreSQL on AWS](https://docs.aws.amazon.com/AmazonRDS/latest/UserGuide/CHAP_PostgreSQL.html)
- [AWS Well-Architected Framework](https://aws.amazon.com/architecture/well-architected/)
- [GitHub Actions for AWS](https://github.com/aws-actions)
