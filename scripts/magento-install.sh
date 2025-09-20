#!/usr/bin/env bash
set -euo pipefail

# Convenience script to run inside the php container to install Magento
# Edit variables below if you want different credentials or base URL.

BASE_URL="http://localhost:8080/"
DB_HOST="db"
DB_NAME="magento"
DB_USER="magento"
DB_PASSWORD="magento"
ADMIN_FIRSTNAME="Admin"
ADMIN_LASTNAME="User"
ADMIN_EMAIL="admin@example.com"
ADMIN_USER="admin"
ADMIN_PASSWORD="Admin123!"
BACKEND_FRONTNAME="admin"
LANGUAGE="en_US"
CURRENCY="USD"
TIMEZONE="America/Chicago"
SEARCH_ENGINE="elasticsearch7"
ELASTIC_HOST="elasticsearch"
ELASTIC_PORT="9200"

echo "Running composer install..."
composer install --no-interaction --prefer-dist

echo "Waiting for Elasticsearch to be available..."
./scripts/wait-for.sh elasticsearch 9200 120

echo "Running Magento setup:install..."
php bin/magento setup:install \
  --base-url=${BASE_URL} \
  --db-host=${DB_HOST} \
  --db-name=${DB_NAME} \
  --db-user=${DB_USER} \
  --db-password='${DB_PASSWORD}' \
  --admin-firstname=${ADMIN_FIRSTNAME} \
  --admin-lastname=${ADMIN_LASTNAME} \
  --admin-email=${ADMIN_EMAIL} \
  --admin-user=${ADMIN_USER} \
  --admin-password='${ADMIN_PASSWORD}' \
  --backend-frontname=${BACKEND_FRONTNAME} \
  --language=${LANGUAGE} \
  --currency=${CURRENCY} \
  --timezone='${TIMEZONE}' \
  --use-rewrites=1 \
  --search-engine=${SEARCH_ENGINE} \
  --elasticsearch-host=${ELASTIC_HOST} \
  --elasticsearch-port=${ELASTIC_PORT}

echo "Post-install: set developer mode, compile, deploy static content, and flush caches"
php bin/magento deploy:mode:set developer || true
php bin/magento setup:upgrade
php bin/magento setup:di:compile || true
php bin/magento setup:static-content:deploy -f || true
php bin/magento cache:flush || true

echo "Magento install script finished. Visit ${BASE_URL}"
