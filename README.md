Magento 2 local docker setup (minimal)

This repository includes a minimal Docker Compose setup to run the Magento 2 site locally for development.

Quick start (macOS, Docker installed):

1) Copy env and (optional) credentials
   - If your project uses private composer repos, ensure `auth.json` or composer credentials are configured.

2) Build and start containers:
```bash
cd /path/to/Vastrey-master
docker-compose up -d --build
```

3) Access services:
   - Site: http://localhost:8080
   - Adminer (DB GUI): http://localhost:8081

4) Inside php container (to run composer/magento commands):
```bash
docker exec -it magento_php bash
# inside container
composer install
php bin/magento setup:install --help
```

Notes:
- This is a minimal development configuration. Magento 2 requires Elasticsearch. The compose file provides Elasticsearch 7.10.2.
- Adjust PHP version and extensions in `docker/php/Dockerfile` if needed.
- For production use, use secure credentials, configure volumes and file permissions properly, and use HTTPS.

Troubleshooting:
- If `composer install` asks for repo.magento.com credentials, add them to `~/.composer/auth.json` or mount an `auth.json` into the container.
- If Elasticsearch fails, check container logs: `docker logs magento_elasticsearch`.
