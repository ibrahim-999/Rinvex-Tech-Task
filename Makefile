.PHONY: help build up down restart shell mysql fresh seed test migrate rollback cache clear logs ps install dev prod

# Colors
GREEN=\033[0;32m
NC=\033[0m

help: ## Show this help
	@grep -E '^[a-zA-Z_-]+:.*?## .*$$' $(MAKEFILE_LIST) | sort | awk 'BEGIN {FS = ":.*?## "}; {printf "$(GREEN)%-15s$(NC) %s\n", $$1, $$2}'

# Docker Commands
build: ## Build containers
	docker-compose build

up: ## Start containers
	docker-compose up -d

down: ## Stop containers
	docker-compose down

restart: ## Restart containers
	docker-compose restart

ps: ## Show running containers
	docker-compose ps

logs: ## Show container logs
	docker-compose logs -f

shell: ## Access app container shell
	docker-compose exec app bash

mysql: ## Access MySQL CLI
	docker-compose exec mysql mysql -uacme -psecret acme

# Laravel Commands
install: ## Install dependencies
	docker-compose exec app composer install
	docker-compose exec app npm install

migrate: ## Run migrations
	docker-compose exec app php artisan migrate

fresh: ## Fresh migration with seed
	docker-compose exec app php artisan migrate:fresh --seed

seed: ## Run seeders
	docker-compose exec app php artisan db:seed

rollback: ## Rollback last migration
	docker-compose exec app php artisan migrate:rollback

cache: ## Clear and cache config
	docker-compose exec app php artisan config:cache
	docker-compose exec app php artisan route:cache
	docker-compose exec app php artisan view:cache

clear: ## Clear all caches
	docker-compose exec app php artisan config:clear
	docker-compose exec app php artisan route:clear
	docker-compose exec app php artisan view:clear
	docker-compose exec app php artisan cache:clear

test: ## Run tests
	docker-compose exec app php artisan test

tinker: ## Run Laravel tinker
	docker-compose exec app php artisan tinker

# Assets
dev: ## Build assets for dev
	docker-compose exec app npm run dev

prod: ## Build assets for production
	docker-compose exec app npm run build

# Quick Setup
setup: build up install migrate ## Full setup (build, up, install, migrate)
	@echo "$(GREEN)Setup complete! Visit http://localhost:8000$(NC)"

# Filament
filament-user: ## Create Filament admin user
	docker-compose exec app php artisan make:filament-user
