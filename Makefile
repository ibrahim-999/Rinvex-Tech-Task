.PHONY: help build up down restart shell mysql fresh seed test migrate rollback cache clear logs ps install dev prod

# Colors
GREEN=\033[0;32m
NC=\033[0m

help: ## Show this help
	@grep -E '^[a-zA-Z_-]+:.*?## .*$$' $(MAKEFILE_LIST) | sort | awk 'BEGIN {FS = ":.*?## "}; {printf "$(GREEN)%-15s$(NC) %s\n", $$1, $$2}'

# ===================
# Docker Commands
# ===================
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

destroy: ## Destroy everything (containers, images, volumes, networks)
	docker-compose down --rmi all -v --remove-orphans
	docker system prune -f

# ===================
# Laravel Commands
# ===================
install: ## Install dependencies
	docker-compose exec app composer install
	docker-compose exec app npm install

migrate: ## Run migrations
	docker-compose exec app php artisan migrate

fresh: ## Fresh migration with seed
	docker-compose exec app php artisan migrate:fresh --seed

seed: ## Run seeders
	docker-compose exec app php artisan db:seed

seed-skills: ## Run SkillSeeder only
	docker-compose exec app php artisan db:seed --class=SkillSeeder

seed-admin: ## Create default admin user
	docker-compose exec app php artisan db:seed --class=AdminUserSeeder

rollback: ## Rollback last migration
	docker-compose exec app php artisan migrate:rollback

tinker: ## Run Laravel tinker
	docker-compose exec app php artisan tinker

test: ## Run tests
	docker-compose exec app php artisan test

test-filter: ## Run specific test (use: make test-filter name=TestName)
	docker-compose exec app php artisan test --filter=$(name)

# ===================
# Cache Commands
# ===================
cache: ## Cache config, routes, views
	docker-compose exec app php artisan config:cache
	docker-compose exec app php artisan route:cache
	docker-compose exec app php artisan view:cache

clear: ## Clear all caches
	docker-compose exec app php artisan config:clear
	docker-compose exec app php artisan route:clear
	docker-compose exec app php artisan view:clear
	docker-compose exec app php artisan cache:clear

optimize: ## Optimize the application
	docker-compose exec app php artisan optimize

optimize-clear: ## Clear optimization cache
	docker-compose exec app php artisan optimize:clear

# ===================
# Assets
# ===================
dev: ## Build assets for dev (watch mode)
	docker-compose exec app npm run dev

prod: ## Build assets for production
	docker-compose exec app npm run build

# ===================
# Filament Commands
# ===================
filament-install: ## Install Filament panels
	docker-compose exec app php artisan filament:install --panels --no-interaction

filament-user: ## Create Filament admin user (interactive)
	docker-compose exec app php artisan make:filament-user

filament-resource: ## Create Filament resource (use: make filament-resource name=Post)
	docker-compose exec app php artisan make:filament-resource $(name)

filament-cache: ## Cache Filament components
	docker-compose exec app php artisan filament:cache-components

filament-clear: ## Clear Filament cache
	docker-compose exec app php artisan filament:cache-components --clear

filament-optimize: ## Optimize Filament
	docker-compose exec app php artisan filament:optimize

filament-optimize-clear: ## Clear Filament optimization
	docker-compose exec app php artisan filament:optimize-clear

filament-list: ## List all Filament resources
	docker-compose exec app php artisan filament:list

filament-check: ## Check if SkillResource loads
	docker-compose exec app php artisan tinker --execute="new App\Filament\Resources\SkillResource()"

# ===================
# Artisan Generators
# ===================
make-model: ## Create model (use: make make-model name=Post)
	docker-compose exec app php artisan make:model $(name) -mfs

make-controller: ## Create controller (use: make make-controller name=PostController)
	docker-compose exec app php artisan make:controller $(name)

make-request: ## Create form request (use: make make-request name=StorePostRequest)
	docker-compose exec app php artisan make:request $(name)

make-resource: ## Create API resource (use: make make-resource name=PostResource)
	docker-compose exec app php artisan make:resource $(name)

make-policy: ## Create policy (use: make make-policy name=PostPolicy)
	docker-compose exec app php artisan make:policy $(name)

make-observer: ## Create observer (use: make make-observer name=PostObserver)
	docker-compose exec app php artisan make:observer $(name)

make-seeder: ## Create seeder (use: make make-seeder name=PostSeeder)
	docker-compose exec app php artisan make:seeder $(name)

make-factory: ## Create factory (use: make make-factory name=PostFactory)
	docker-compose exec app php artisan make:factory $(name)

make-test: ## Create test (use: make make-test name=PostTest)
	docker-compose exec app php artisan make:test $(name)

# ===================
# Storage & Permissions
# ===================
storage-link: ## Create storage link
	docker-compose exec app php artisan storage:link

permissions: ## Fix storage permissions
	docker-compose exec app chmod -R 775 storage bootstrap/cache
	docker-compose exec app chown -R www-data:www-data storage bootstrap/cache

permissions-host: ## Fix permissions from host (use sudo)
	sudo chmod -R 777 storage bootstrap/cache

# ===================
# Queue & Jobs
# ===================
queue-work: ## Start queue worker
	docker-compose exec app php artisan queue:work

queue-restart: ## Restart queue workers
	docker-compose exec app php artisan queue:restart

queue-clear: ## Clear all queued jobs
	docker-compose exec app php artisan queue:clear

# ===================
# Debugging
# ===================
routes: ## List all routes
	docker-compose exec app php artisan route:list

routes-filter: ## Filter routes (use: make routes-filter name=skill)
	docker-compose exec app php artisan route:list --name=$(name)

env: ## Show environment
	docker-compose exec app php artisan env

about: ## Show application info
	docker-compose exec app php artisan about

# ===================
# Quick Setup
# ===================
setup: build up wait-db install key migrate filament-setup seed-admin seed-skills permissions storage-link prod ## Full setup
	@echo "$(GREEN)===================================$(NC)"
	@echo "$(GREEN)Setup complete!$(NC)"
	@echo "$(GREEN)===================================$(NC)"
	@echo "URL: http://localhost:8000/admin"
	@echo "Email: admin@acme.test"
	@echo "Password: password"
	@echo "$(GREEN)===================================$(NC)"

filament-setup: ## Install Filament (handles existing installation)
	@if [ ! -f "app/Providers/Filament/AdminPanelProvider.php" ]; then \
		docker-compose exec app php artisan filament:install --panels --no-interaction || true; \
	else \
		echo "Filament already installed, skipping..."; \
	fi


wait-db: ## Wait for MySQL to be ready
	@echo "Waiting for MySQL..."
	@until docker-compose exec -T mysql mysqladmin ping -h localhost -uroot -psecret --silent 2>/dev/null; do \
		echo "MySQL not ready, waiting..."; \
		sleep 3; \
	done
	@echo "$(GREEN)MySQL is ready!$(NC)"

key: ## Generate application key
	docker-compose exec app php artisan key:generate

reset: down destroy setup ## Full reset and setup

fix: clear filament-clear permissions ## Fix common issues
	@echo "$(GREEN)Fixes applied!$(NC)"

# ===================
# Composer
# ===================
composer-install: ## Composer install
	docker-compose exec app composer install

composer-update: ## Composer update
	docker-compose exec app composer update

composer-dump: ## Composer dump-autoload
	docker-compose exec app composer dump-autoload

# ===================
# NPM
# ===================
npm-install: ## NPM install
	docker-compose exec app npm install

npm-update: ## NPM update
	docker-compose exec app npm update

npm-audit: ## NPM audit fix
	docker-compose exec app npm audit fix
