#!/bin/bash
set -e

echo "Setting up Secrets Manager..."

# Helper function to create secret if not exists
create_secret() {
    NAME=$1
    DESC=$2
    VALUE=$3
    
    echo "Creating secret: $NAME"
    aws secretsmanager create-secret \
      --name $NAME \
      --description "$DESC" \
      --secret-string "$VALUE" || echo "Secret $NAME might already exist."
}

# Production Secrets
create_secret "certchain/prod/app-key" "Laravel APP_KEY for production" "base64:GENERATE_ME"
create_secret "certchain/prod/db-password" "Database password for production" "CHANGE_ME_SECURE"
create_secret "certchain/prod/jwt-secret" "JWT secret for production" "CHANGE_ME_JWT"

# Staging Secrets
create_secret "certchain/staging/app-key" "Laravel APP_KEY for staging" "base64:GENERATE_ME"
create_secret "certchain/staging/db-password" "Database password for staging" "CHANGE_ME_STAGING"

echo "Secrets Setup Complete. Please update the values in AWS Console."
