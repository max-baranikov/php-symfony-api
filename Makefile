.PHONY: help setup up down install migrate seed seed-bulk generate-openapi test test-unit test-functional cs-fix cs-check phpstan clean

help: ## Show this help message
	@echo 'Usage: make [target]'
	@echo ''
	@echo 'Targets:'
	@awk 'BEGIN {FS = ":.*?## "} /^[a-zA-Z_-]+:.*?## / {printf "  %-15s %s\n", $$1, $$2}' $(MAKEFILE_LIST)

setup: ## Setup the project
	@cp app/.env{.dev,}
	@MAKE up
	@MAKE install
	@MAKE migrate
	@MAKE seed-bulk

up: ## Build and start the stack
	@docker compose up --build -d

down: ## Stop the stack
	@docker compose down

install: ## Install dependencies
	@docker compose exec php composer install

migrate: ## Run database migrations
	@docker compose exec php bin/console doctrine:database:create --if-not-exists
	@docker compose exec php bin/console doctrine:migrations:migrate --no-interaction
	# For functional tests
	@docker compose exec -e APP_ENV=test php bin/console doctrine:database:create --if-not-exists
	@docker compose exec -e APP_ENV=test php bin/console doctrine:migrations:migrate --no-interaction


seed: ## Seed the database with sample data
	@docker compose exec php bin/console app:seed --bulk=0

seed-bulk: ## Seed the database with bulk data (25k products)
	@docker compose exec php bin/console app:seed --bulk=25000

generate-openapi: ## Generate OpenApi spec file
	@docker compose exec php ./vendor/bin/openapi --output public/openapi.yaml --exclude vendor --exclude migrations --exclude var .
	# docker compose exec php ./vendor/bin/openapi --output public/openapi.json --format json --exclude vendor --exclude migrations --exclude var .
	# docker compose exec php bin/console nelmio:apidoc:dump

test: ## Run tests
	@docker compose exec php ./vendor/bin/phpunit

test-unit: ## Run unit tests only
	@docker compose exec php ./vendor/bin/phpunit --testsuite Unit

test-functional: ## Run functional tests only
	@docker compose exec php ./vendor/bin/phpunit --testsuite Functional --stop-on-failure

cs-fix: ## Fix code style issues
	@docker compose exec php ./vendor/bin/php-cs-fixer fix

cs-check: ## Check code style
	@docker compose exec php ./vendor/bin/php-cs-fixer check

phpstan: ## Run static analysis
	@docker compose exec php ./vendor/bin/phpstan analyse src

pre-commit: ## Run pre-commit hooks
	@MAKE cs-fix
	@MAKE phpstan
	@MAKE test

cache-clear: ## Clear cache
	@docker compose exec php bin/console cache:clear

cache-clear-all: ## Clear cache for all environments
	@docker compose exec php bin/console cache:clear --env=dev
	@docker compose exec php bin/console cache:clear --env=test
	@docker compose exec php bin/console cache:clear --env=prod

clean: ## Clean up containers and volumes
	@docker compose down -v
	@docker system prune -f

logs: ## Show container logs
	@docker compose logs -f

status: ## Show container status
	@docker compose ps
