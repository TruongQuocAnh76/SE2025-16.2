# ECS Task Definitions

Thư mục này chứa các ECS Task Definition templates cho deployment trên AWS.

## 📁 Files

- `backend-production.json` - Task definition cho backend production
- `frontend-production.json` - Task definition cho frontend production
- `backend-staging.json` - Task definition cho backend staging
- `frontend-staging.json` - Task definition cho frontend staging
- `migrate-production.json` - Task definition cho database migrations (production)

## 🚀 Cách sử dụng

### 1. Cập nhật placeholders

Thay thế các giá trị sau trong các file JSON:

- `YOUR_ACCOUNT_ID` - AWS Account ID của bạn
- `YOUR_RDS_ENDPOINT` - RDS endpoint cho production
- `YOUR_STAGING_RDS_ENDPOINT` - RDS endpoint cho staging
- `YOUR_ELASTICACHE_ENDPOINT` - ElastiCache endpoint cho production
- `YOUR_STAGING_ELASTICACHE_ENDPOINT` - ElastiCache endpoint cho staging

### 2. Tạo CloudWatch Log Groups

```bash
# Production
aws logs create-log-group --log-group-name /ecs/certchain-backend-prod
aws logs create-log-group --log-group-name /ecs/certchain-frontend-prod

# Staging
aws logs create-log-group --log-group-name /ecs/certchain-backend-staging
aws logs create-log-group --log-group-name /ecs/certchain-frontend-staging
```

### 3. Register task definitions

```bash
# Production
aws ecs register-task-definition --cli-input-json file://backend-production.json
aws ecs register-task-definition --cli-input-json file://frontend-production.json

# Staging
aws ecs register-task-definition --cli-input-json file://backend-staging.json
aws ecs register-task-definition --cli-input-json file://frontend-staging.json
```

### 4. Tạo ECS Services

```bash
# Backend Production Service
aws ecs create-service \
  --cluster certchain-production \
  --service-name certchain-backend-prod \
  --task-definition certchain-backend-prod \
  --desired-count 2 \
  --launch-type FARGATE \
  --network-configuration "awsvpcConfiguration={subnets=[subnet-xxx,subnet-yyy],securityGroups=[sg-xxx],assignPublicIp=DISABLED}" \
  --load-balancers "targetGroupArn=arn:aws:elasticloadbalancing:...:targetgroup/certchain-backend-prod-tg,containerName=certchain-backend,containerPort=8000"

# Frontend Production Service
aws ecs create-service \
  --cluster certchain-production \
  --service-name certchain-frontend-prod \
  --task-definition certchain-frontend-prod \
  --desired-count 2 \
  --launch-type FARGATE \
  --network-configuration "awsvpcConfiguration={subnets=[subnet-xxx,subnet-yyy],securityGroups=[sg-xxx],assignPublicIp=DISABLED}" \
  --load-balancers "targetGroupArn=arn:aws:elasticloadbalancing:...:targetgroup/certchain-frontend-prod-tg,containerName=certchain-frontend,containerPort=3000"
```

### 5. Enable Auto Scaling (Optional)

```bash
# Register scalable target
aws application-autoscaling register-scalable-target \
  --service-namespace ecs \
  --resource-id service/certchain-production/certchain-backend-prod \
  --scalable-dimension ecs:service:DesiredCount \
  --min-capacity 2 \
  --max-capacity 10

# Create scaling policy (target tracking)
aws application-autoscaling put-scaling-policy \
  --service-namespace ecs \
  --resource-id service/certchain-production/certchain-backend-prod \
  --scalable-dimension ecs:service:DesiredCount \
  --policy-name cpu-scaling \
  --policy-type TargetTrackingScaling \
  --target-tracking-scaling-policy-configuration file://scaling-policy.json
```

## 📊 Monitoring

```bash
# View service status
aws ecs describe-services \
  --cluster certchain-production \
  --services certchain-backend-prod

# View logs
aws logs tail /ecs/certchain-backend-prod --follow

# List running tasks
aws ecs list-tasks --cluster certchain-production --service-name certchain-backend-prod
```

## 🔧 Update Service

Khi có image mới từ CI/CD:

```bash
aws ecs update-service \
  --cluster certchain-production \
  --service certchain-backend-prod \
  --force-new-deployment
```

## 📚 Resources

- [ECS Task Definition Parameters](https://docs.aws.amazon.com/AmazonECS/latest/developerguide/task_definition_parameters.html)
- [Fargate Task Definitions](https://docs.aws.amazon.com/AmazonECS/latest/developerguide/fargate-task-defs.html)
- [Secrets Manager Integration](https://docs.aws.amazon.com/AmazonECS/latest/developerguide/specifying-sensitive-data-secrets.html)
