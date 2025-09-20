SHELL := /bin/bash
.PHONY: docker-up docker-down install logs shell

docker-up:
	@cp -n docker/.env.sample docker/.env || true
	@echo "Starting Docker Compose..."
	docker compose up -d

docker-down:
	@echo "Stopping Docker Compose and removing containers..."
	docker compose down --volumes --remove-orphans

install: docker-up
	@echo "Waiting for services and installing Magento inside php container..."
	docker compose exec php bash -lc "./scripts/wait-for.sh elasticsearch 9200 60 && ./scripts/magento-install.sh"

logs:
	docker compose logs -f

shell:
	docker compose exec php bash
