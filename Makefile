# ============================================================
#  FaciliCore — Project Makefile
#  Laravel (PHP 8.4) + Next.js (App Router) + Docker Compose
# ============================================================

DC      := docker compose
BACKEND := $(DC) exec backend
PHP     := $(BACKEND) php
ARTISAN := $(PHP) artisan

.DEFAULT_GOAL := help

# ── Colours ─────────────────────────────────────────────────
RESET  := \033[0m
BOLD   := \033[1m
GREEN  := \033[32m
YELLOW := \033[33m
CYAN   := \033[36m

.PHONY: help
help: ## Show this help message
	@echo ""
	@echo "$(BOLD)$(CYAN)FaciliCore — Available Commands$(RESET)"
	@echo "$(CYAN)═══════════════════════════════════════════════════════$(RESET)"
	@awk 'BEGIN {FS = ":.*##"; section=""} \
		/^##@/ { section=substr($$0,5); printf "\n$(BOLD)$(YELLOW)%s$(RESET)\n", section } \
		/^[a-zA-Z_-]+:.*?##/ { printf "  $(GREEN)%-20s$(RESET) %s\n", $$1, $$2 }' $(MAKEFILE_LIST)
	@echo ""

# ============================================================
##@ 🚀 First-Time Setup
# ============================================================

.PHONY: setup
setup: ## Full first-time project setup (copy .env, build, install deps, migrate & seed)
	@echo "$(BOLD)$(CYAN)▶ Setting up FaciliCore...$(RESET)"
	@$(MAKE) env
	@$(MAKE) build
	@$(MAKE) up-d
	@$(MAKE) install
	@$(MAKE) key
	@$(MAKE) migrate-seed
	@echo ""
	@echo "$(BOLD)$(GREEN)✔ Setup complete!$(RESET)"
	@echo "  App        → http://facilicore.me (or http://lvh.me)"
	@echo "  phpMyAdmin → http://localhost:8080"
	@echo "  MailHog    → http://localhost:8025"
	@echo ""

.PHONY: env
env: ## Copy .env.example → .env (skips if .env already exists)
	@if [ ! -f .env ]; then \
		cp .env.example .env; \
		echo "$(GREEN)✔ .env created from .env.example$(RESET)"; \
	else \
		echo "$(YELLOW)⚠  .env already exists — skipping$(RESET)"; \
	fi

# ============================================================
##@ 🐳 Docker
# ============================================================

.PHONY: build
build: ## Build (or rebuild) all Docker images
	$(DC) build

.PHONY: up
up: ## Start all containers in the foreground (with logs)
	$(DC) up

.PHONY: up-d
up-d: ## Start all containers in the background (detached)
	$(DC) up -d

.PHONY: down
down: ## Stop and remove all containers
	$(DC) down

.PHONY: down-v
down-v: ## Stop containers AND remove volumes (⚠ destroys data)
	$(DC) down -v

.PHONY: restart
restart: ## Restart all containers
	$(DC) restart

.PHONY: ps
ps: ## Show container status
	$(DC) ps

.PHONY: logs
logs: ## Tail logs from all containers (Ctrl+C to exit)
	$(DC) logs -f

.PHONY: logs-backend
logs-backend: ## Tail backend (Laravel) container logs
	$(DC) logs -f backend

.PHONY: logs-frontend
logs-frontend: ## Tail frontend (Next.js) container logs
	$(DC) logs -f frontend

.PHONY: logs-proxy
logs-proxy: ## Tail Nginx proxy container logs
	$(DC) logs -f proxy

# ============================================================
##@ 🔧 Laravel Backend
# ============================================================

.PHONY: install
install: ## Install Composer dependencies inside the backend container
	$(BACKEND) composer install

.PHONY: key
key: ## Generate a new APP_KEY
	$(ARTISAN) key:generate

.PHONY: migrate
migrate: ## Run database migrations
	$(ARTISAN) migrate

.PHONY: migrate-fresh
migrate-fresh: ## Drop all tables and re-run migrations (⚠ destroys data)
	$(ARTISAN) migrate:fresh

.PHONY: migrate-seed
migrate-seed: ## Run migrations and seed the database
	$(ARTISAN) migrate --seed

.PHONY: seed
seed: ## Run database seeders only
	$(ARTISAN) db:seed

.PHONY: seed-demo
seed-demo: ## Seed rich multi-sector demo dataset (all 4 sectors + composite templates)
	$(ARTISAN) db:seed --class=TenantDemoSeeder

.PHONY: db-shell
db-shell: ## Open MySQL CLI inside the database container
	$(DOCKER_COMPOSE) exec mysql mysql -u facilicore -psecret facilicore

.PHONY: rollback
rollback: ## Roll back the last database migration
	$(ARTISAN) migrate:rollback

.PHONY: cache-clear
cache-clear: ## Clear all application caches
	$(ARTISAN) cache:clear
	$(ARTISAN) config:clear
	$(ARTISAN) route:clear
	$(ARTISAN) view:clear
	@echo "$(GREEN)✔ All caches cleared$(RESET)"

.PHONY: optimize
optimize: ## Cache config, routes, and views for production
	$(ARTISAN) optimize

.PHONY: tinker
tinker: ## Open the Laravel Tinker REPL
	$(ARTISAN) tinker

.PHONY: queue
queue: ## Start the queue worker
	$(ARTISAN) queue:work --tries=3

.PHONY: shell-backend
shell-backend: ## Open a shell inside the backend container
	$(BACKEND) sh

# ============================================================
##@ 🧪 Testing
# ============================================================

.PHONY: test
test: ## Run the full PHPUnit test suite
	$(BACKEND) php artisan test

.PHONY: test-filter
test-filter: ## Run tests matching a filter  (usage: make test-filter FILTER=BookingTest)
	$(BACKEND) php artisan test --filter=$(FILTER)

.PHONY: test-coverage
test-coverage: ## Run tests with coverage report (requires Xdebug)
	$(BACKEND) php artisan test --coverage

# ============================================================
##@ 🎨 Frontend (Next.js)
# ============================================================

.PHONY: npm-install
npm-install: ## Install Node dependencies inside the frontend container
	$(DC) exec frontend npm install

.PHONY: npm-build
npm-build: ## Build the Next.js production bundle
	$(DC) exec frontend npm run build

.PHONY: shell-frontend
shell-frontend: ## Open a shell inside the frontend container
	$(DC) exec frontend sh

# ============================================================
##@ 🗄️  Database Utilities
# ============================================================

.PHONY: make-migration
make-migration: ## Create a new migration  (usage: make make-migration NAME=create_bookings_table)
	$(ARTISAN) make:migration $(NAME)

.PHONY: make-model
make-model: ## Create a model with migration  (usage: make make-model NAME=Booking)
	$(ARTISAN) make:model $(NAME) -m

.PHONY: make-seeder
make-seeder: ## Create a new database seeder  (usage: make make-seeder NAME=BookingSeeder)
	$(ARTISAN) make:seeder $(NAME)

# ============================================================
##@ 🛠  Code Generation
# ============================================================

.PHONY: make-controller
make-controller: ## Create a resource controller  (usage: make make-controller NAME=BookingController)
	$(ARTISAN) make:controller $(NAME) --resource

.PHONY: make-service
make-service: ## Stub a new Service class  (usage: make make-service NAME=BookingManager)
	@mkdir -p app/Services
	@printf "<?php\n\nnamespace App\\Services;\n\nclass $(NAME)\n{\n    //\n}\n" > app/Services/$(NAME).php
	@echo "$(GREEN)✔ app/Services/$(NAME).php created$(RESET)"

.PHONY: make-request
make-request: ## Create a Form Request class  (usage: make make-request NAME=StoreBookingRequest)
	$(ARTISAN) make:request $(NAME)

# ============================================================
##@ 🔍 Code Quality
# ============================================================

.PHONY: lint
lint: ## Run Laravel Pint (PHP CS Fixer)
	$(BACKEND) sh -c "vendor/bin/pint || echo 'Pint not installed — run: composer require laravel/pint --dev'"

.PHONY: analyse
analyse: ## Run PHPStan static analysis
	$(BACKEND) sh -c "vendor/bin/phpstan analyse || echo 'PHPStan not installed — run: composer require phpstan/phpstan --dev'"

# ============================================================
##@ 🏗  Production
# ============================================================

.PHONY: prod-build
prod-build: ## Build Docker images for production (no-cache)
	$(DC) build --no-cache

.PHONY: prod-up
prod-up: ## Start containers with production env
	APP_ENV=production $(DC) up -d

# ============================================================
##@ 🧹 Maintenance
# ============================================================

.PHONY: reset
reset: ## ⚠ Full reset: stop containers, remove volumes, wipe .env
	@echo "$(YELLOW)⚠  This will destroy all containers, volumes, and .env!$(RESET)"
	@read -p "Type 'yes' to confirm: " confirm && [ "$$confirm" = "yes" ]
	$(DC) down -v --remove-orphans
	rm -f .env
	@echo "$(GREEN)✔ Project reset. Run 'make setup' to start fresh.$(RESET)"

.PHONY: prune
prune: ## Remove unused Docker images, networks, and build cache
	docker system prune -f
