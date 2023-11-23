.DEFAULT_GOAL := up
COMPOSE := docker-compose -f docker-compose.yml
TEST_COMPOSE := docker-compose -f docker-compose.testing.yml

up:
	@$(COMPOSE) up -d

down:
	@$(COMPOSE) down

restart: down up

install:
	@docker network create dv_backend_network || true
	@$(COMPOSE) up -d
	@$(COMPOSE) exec app composer install --ignore-platform-reqs
	@$(COMPOSE) exec app php artisan key:generate
	@$(COMPOSE) exec app php artisan migrate --seed

bash:
	@$(COMPOSE) exec app /bin/bash
