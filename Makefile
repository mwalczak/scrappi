.PHONY: help setup up down restart clean install test phpstan deptrac db-create db-migrate db-reset db-test shell composer console logs chmod

.DEFAULT_GOAL := help

# Colors for output
BLUE := \033[0;34m
GREEN := \033[0;32m
YELLOW := \033[1;33m
RED := \033[0;31m
NC := \033[0m

help: ## Show this help message
	@echo "$(BLUE)Scrappi - Available Make Commands$(NC)"
	@echo ""
	@grep -E '^[a-zA-Z_-]+:.*?## .*$$' $(MAKEFILE_LIST) | sort | awk 'BEGIN {FS = ":.*?## "}; {printf "  $(GREEN)%-20s$(NC) %s\n", $$1, $$2}'
	@echo ""
	@echo "$(YELLOW)Quick Start:$(NC)"
	@echo "  make setup    - Complete environment setup (first time)"
	@echo "  make up       - Start containers"
	@echo "  make test     - Run tests"
	@echo ""

# Setup and Installation
setup: ## Complete environment setup (clean install)
	@$(MAKE) chmod
	@echo "$(BLUE)Setting up development environment...$(NC)"
	@$(MAKE) clean
	@$(MAKE) up
	@$(MAKE) wait-for-db
	@$(MAKE) install
	@$(MAKE) db-create
	@$(MAKE) db-migrate
	@$(MAKE) db-test-setup
	@$(MAKE) cache-clear
	@$(MAKE) test
	@$(MAKE) phpstan
	@$(MAKE) deptrac
	@echo ""
	@echo "$(GREEN)╔════════════════════════════════════════════════════════╗$(NC)"
	@echo "$(GREEN)║                                                        ║$(NC)"
	@echo "$(GREEN)║  🎉  Development environment setup complete!  🎉       ║$(NC)"
	@echo "$(GREEN)║                                                        ║$(NC)"
	@echo "$(GREEN)╚════════════════════════════════════════════════════════╝$(NC)"
	@echo ""
	@echo "$(BLUE)Available services:$(NC)"
	@echo "  • API Platform Swagger UI: $(YELLOW)http://localhost:8001/api$(NC)"
	@echo "  • Health Check:            $(YELLOW)http://localhost:8001/api/health$(NC)"
	@echo "  • Netflix Videos API:      $(YELLOW)http://localhost:8001/api/netflix_videos$(NC)"
	@echo ""
	@echo "$(BLUE)Useful commands:$(NC)"
	@echo "  $(GREEN)make test$(NC)       Run tests"
	@echo "  $(GREEN)make phpstan$(NC)    Run static analysis"
	@echo "  $(GREEN)make logs$(NC)       View container logs"
	@echo "  $(GREEN)make shell$(NC)      Open PHP container shell"
	@echo ""

install: ## Install Composer dependencies and git hooks
	@echo "$(BLUE)Installing dependencies...$(NC)"
	@./composer install --no-interaction --prefer-dist
	@echo "$(GREEN)✓ Dependencies installed$(NC)"

hooks: ## Install git hooks
	@bash .githooks/install-hooks.sh

chmod: ## Make shell scripts executable
	@echo "$(BLUE)Making shell scripts executable...$(NC)"
	@chmod +x php composer console test phpstan deptrac db 2>/dev/null || true
	@echo "$(GREEN)✓ Shell scripts are now executable$(NC)"

# Docker Management
up: ## Start Docker containers
	@echo "$(BLUE)Starting Docker containers...$(NC)"
	@docker-compose up -d --build
	@echo "$(GREEN)✓ Containers started$(NC)"

down: ## Stop Docker containers
	@echo "$(BLUE)Stopping containers...$(NC)"
	@docker-compose down
	@echo "$(GREEN)✓ Containers stopped$(NC)"

restart: ## Restart Docker containers
	@$(MAKE) down
	@$(MAKE) up

clean: ## Stop containers and remove volumes
	@echo "$(BLUE)Cleaning up containers and volumes...$(NC)"
	@docker-compose down -v 2>/dev/null || true
	@echo "$(GREEN)✓ Cleanup complete$(NC)"

logs: ## View container logs
	@docker-compose logs -f

# Database Management
db-create: ## Create database
	@echo "$(BLUE)Creating database...$(NC)"
	@./console doctrine:database:create --if-not-exists
	@echo "$(GREEN)✓ Database created$(NC)"

db-migrate: ## Run database migrations
	@echo "$(BLUE)Running migrations...$(NC)"
	@./console doctrine:migrations:migrate --no-interaction
	@echo "$(GREEN)✓ Migrations completed$(NC)"

db-reset: ## Drop and recreate database
	@echo "$(BLUE)Resetting database...$(NC)"
	@./console doctrine:database:drop --force --if-exists
	@$(MAKE) db-create
	@$(MAKE) db-migrate
	@echo "$(GREEN)✓ Database reset$(NC)"

db-test-setup: ## Set up test database
	@echo "$(BLUE)Setting up test database...$(NC)"
	@APP_ENV=test ./console doctrine:database:create --if-not-exists
	@APP_ENV=test ./console doctrine:migrations:migrate --no-interaction
	@echo "$(GREEN)✓ Test database ready$(NC)"

wait-for-db: ## Wait for database to be ready
	@echo "$(BLUE)Waiting for PostgreSQL...$(NC)"
	@max_attempts=30; \
	attempt=1; \
	while [ $$attempt -le $$max_attempts ]; do \
		if docker-compose exec -T db pg_isready -U app > /dev/null 2>&1; then \
			echo "$(GREEN)✓ PostgreSQL is ready$(NC)"; \
			exit 0; \
		fi; \
		printf "."; \
		sleep 1; \
		attempt=$$((attempt + 1)); \
	done; \
	echo "$(RED)✗ PostgreSQL failed to start$(NC)"; \
	exit 1

# Testing and Quality
test: ## Run PHPUnit tests
	@echo "$(BLUE)Running tests...$(NC)"
	@./test
	@echo "$(GREEN)✓ Tests passed$(NC)"

phpstan: ## Run PHPStan static analysis
	@echo "$(BLUE)Running PHPStan...$(NC)"
	@./phpstan analyse
	@echo "$(GREEN)✓ PHPStan passed$(NC)"

deptrac: ## Run Deptrac architecture validation
	@echo "$(BLUE)Running Deptrac...$(NC)"
	@./deptrac analyze
	@echo "$(GREEN)✓ Architecture validation passed$(NC)"

qa: ## Run all quality checks (tests, phpstan, deptrac)
	@echo "$(BLUE)Running quality checks...$(NC)"
	@$(MAKE) test
	@$(MAKE) phpstan
	@$(MAKE) deptrac
	@echo "$(GREEN)✓ All quality checks passed$(NC)"

# Cache Management
cache-clear: ## Clear application cache
	@echo "$(BLUE)Clearing cache...$(NC)"
	@./console cache:clear
	@./console cache:clear --env=test
	@echo "$(GREEN)✓ Cache cleared$(NC)"

# Development Helpers
shell: ## Open shell in PHP container
	@docker-compose exec php bash

composer: ## Run Composer command (usage: make composer CMD="require vendor/package")
	@./composer $(CMD)

console: ## Run Symfony console command (usage: make console CMD="debug:router")
	@./console $(CMD)

# Status
status: ## Show Docker containers status
	@docker-compose ps

ps: status ## Alias for status
