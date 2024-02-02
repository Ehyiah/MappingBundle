ENV?= dev
BRANCH?= dev

#
# DOCKER VAR
#
DOCKER_COMPOSE?= docker-compose
EXEC?= $(DOCKER_COMPOSE) exec

PHP?= $(EXEC) php
COMPOSER?= $(PHP) composer

ifeq ($(SKIP_DOCKER),true)
	PHP= php
	COMPOSER= composer
endif

help:  ## Display this help
	@awk 'BEGIN {FS = ":.*##"; printf "\nUsage:\n  make \033[36m\033[0m\n"} /^[a-zA-Z_-]+:.*?##/ { printf "  \033[36m%-15s\033[0m %s\n", $$1, $$2 } /^##@/ { printf "\n\033[1m%s\033[0m\n", substr($$0, 5) } ' $(MAKEFILE_LIST)

##@ Installation
install: build up vendor down ## Install new project with docker

update: vendor up ## update after checkout

##@ Docker
build: ## Build the images
	$(DOCKER_COMPOSE) build --no-cache --build-arg APP_USER_ID=$$(id -u) --build-arg APP_USER=$$(id -u -n)

up: ## Up the images
	$(DOCKER_COMPOSE) up -d --remove-orphans

down: ## Down the images
	$(DOCKER_COMPOSE) down

## don't forget this if you dont want makefile to get files with this name
.PHONY: build up down install vendor

##@ Composer
vendor: composer.lock ## Install composer dependency
	$(COMPOSER) install

##@ Utility

bash-php: ## Launch PHP bash
	$(PHP) bash

.PHONY: bash-php

##@ CI
ci: ## Launch csfixer and phpstan and javascript quality check
	$(COMPOSER) ci

fixer-php: ## Launch csfixer no dry
	$(COMPOSER) phpcsfixer

phpstan: ## Launch phpstan
	$(COMPOSER) phpstan

.PHONY: ci fixer-php phpstan
