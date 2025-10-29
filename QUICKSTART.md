# Quick Reference Guide

## Start/Stop Application

```bash
# Start
docker-compose up -d --build

# Stop
docker-compose down

# Stop and remove volumes
docker-compose down -v

# View logs
docker-compose logs -f php

# Check status
docker-compose ps
```

## Helper Scripts

```bash
# Run any command in PHP container
./php [command]

# Interactive shell
./php bash

# Composer
./composer install
./composer update
./composer require package/name

# Symfony Console
./console cache:clear
./console debug:router
./console doctrine:database:create
./console doctrine:migrations:migrate

# Tests
./test
./test --filter TestName
```

## Common Tasks

### Run Tests
```bash
./test
```

### Clear Cache
```bash
./console cache:clear
```

### View Routes
```bash
./console debug:router
```

### Install New Package
```bash
./composer require symfony/mailer
```

### Create Database
```bash
./console doctrine:database:create
```

### Run Migrations
```bash
./console doctrine:migrations:migrate
```

### View Container Logs
```bash
docker-compose logs -f php
```

## Endpoints

- Health Check: http://localhost:8000/health
- API Docs: http://localhost:8000/api

## Troubleshooting

### Clear all caches
```bash
./console cache:clear
./console cache:clear --env=test
```

### Rebuild containers
```bash
docker-compose down -v
docker-compose up -d --build
./composer install
```

### Fix permissions
```bash
./php chown -R www-data:www-data /var/www/html
```

