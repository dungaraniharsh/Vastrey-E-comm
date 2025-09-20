README

This project includes a Docker Compose stack to run Magento 2.4.2 locally.

Quick start (after installing Docker Desktop on macOS):

1. From the repo root:
```bash
cp docker/.env.sample docker/.env
docker compose up -d
```

2. Open Adminer at http://localhost:8081 to inspect the database.

3. Install Magento inside the `php` container (recommended):
```bash
docker compose exec php bash
./scripts/magento-install.sh
```

4. Visit the storefront at http://localhost:8080

Files added:
- `docker/.env.sample` - env template for Docker Compose
- `scripts/magento-install.sh` - convenience script that runs composer install and `bin/magento setup:install` with sane defaults (edit before running)

Notes:
- Docker must be running. If `docker` or `docker compose` is not found, install Docker Desktop for macOS and start it.
- The install script uses credentials in `docker/.env` if present. Update passwords before running in a non-local environment.
