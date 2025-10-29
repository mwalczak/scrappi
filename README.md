# Symfony API Platform Application

A simple PHP API application using Symfony 7.3 and API Platform 3.4 with a health-check endpoint.

## Requirements

- Docker
- Docker Compose

No local PHP installation is required!

## Getting Started

### 1. Start the Application

```bash
docker-compose up -d --build
```

This will:
- Build the PHP container with PHP 8.4 and all dependencies
- Start PostgreSQL 15 database
- Install Composer dependencies automatically
- Start the Symfony development server on port 8000

### 2. Install/Update Dependencies (if needed)

After the containers are running, you can manage dependencies:

```bash
# Install dependencies
docker-compose exec php composer install

# Update dependencies
docker-compose exec php composer update
```

### 3. Access the Application

- **API Platform Documentation**: http://localhost:8000/api
- **Health Check Endpoint**: http://localhost:8000/api/health

The health check endpoint returns:
```json
{
  "status": "healthy",
  "timestamp": "2025-10-29T12:00:00+00:00"
}
```

## Helper Scripts

To avoid typing `docker-compose exec php` repeatedly, the project includes convenient helper scripts:

### Available Scripts

```bash
# Run any command in PHP container
./php [command]
./php bash                    # Open interactive shell

# Run Composer commands
./composer install
./composer require symfony/mailer
./composer update

# Run Symfony console commands
./console cache:clear
./console debug:router
./console doctrine:database:create

# Run PHPUnit tests
./test
./test --filter HealthCheck
./test tests/Controller/HealthCheckControllerTest.php
```

## Running Tests

Run PHPUnit tests using the helper script:

```bash
./test
```

Or the traditional way:

```bash
docker-compose exec php ./vendor/bin/phpunit
```


Expected output:
```
PHPUnit 11.5.42 by Sebastian Bergmann and contributors.

Runtime:       PHP 8.4.14
Configuration: /var/www/html/phpunit.xml

..                                                                  2 / 2 (100%)

Time: 00:00.869, Memory: 40.50 MB

OK (2 tests, 7 assertions)
```

## Stopping the Application

```bash
# Stop containers
docker-compose down

# Stop and remove volumes
docker-compose down -v
```

## Project Structure

```
.
├── bin/                       # Console scripts
│   ├── console               # Symfony console
│   └── phpunit              # PHPUnit runner
├── config/                    # Configuration files
│   ├── packages/             # Package configurations
│   │   ├── api_platform.yaml
│   │   ├── doctrine.yaml
│   │   ├── framework.yaml
### Helper Scripts (Recommended)

The project includes convenient helper scripts in the root directory:

```bash
# Run commands in PHP container
./php bash                              # Interactive shell
./php [any-command]                     # Run any command

# Composer commands
./composer install                      # Install dependencies
./composer update                       # Update dependencies
./composer require symfony/mailer       # Add new package

# Symfony console commands
./console cache:clear                   # Clear cache
./console debug:router                  # List all routes
./console debug:container               # List all services
./console doctrine:database:create      # Create database
./console doctrine:migrations:migrate   # Run migrations

# Run tests
./test                                  # Run all tests
./test --filter HealthCheck             # Run specific test
```

### Docker Commands (Direct)
│   │       └── framework.yaml
│   ├── routes/               # Route configurations
│   │   └── api_platform.yaml
│   ├── bundles.php          # Registered bundles
│   ├── routes.yaml          # Application routes
│   ├── services.yaml        # Service definitions
│   └── bootstrap.php        # Bootstrap configuration
├── public/                    # Public directory
│   └── index.php            # Application entry point

# Check container status
docker-compose ps
├── src/                       # Application code
│   ├── Controller/           # Controllers
### Traditional Commands (Without Helper Scripts)
│   └── Kernel.php           # Application kernel
# Composer
│   ├── Controller/
├── docker-compose.yml         # Docker Compose configuration
├── Dockerfile                # PHP container definition
# Console

# View logs
docker-compose logs -f php
# Tests
docker-compose exec php ./vendor/bin/phpunit
docker-compose exec php composer update

# Require new package
docker-compose exec php composer require package/name
```

### Symfony Console Commands
```bash
# Clear cache
docker-compose exec php php bin/console cache:clear

# List all routes
docker-compose exec php php bin/console debug:router

# List all services
docker-compose exec php php bin/console debug:container
```

### Database Commands
```bash
# Create database
docker-compose exec php php bin/console doctrine:database:create

# Run migrations
docker-compose exec php php bin/console doctrine:migrations:migrate

# Create new migration
docker-compose exec php php bin/console doctrine:migrations:generate
```

## Technology Stack

- **PHP**: 8.2
- **Symfony**: 7.0
- **API Platform**: 3.3
- **Doctrine ORM**: 2.20
- **PHPUnit**: 10.5
- **PostgreSQL**: 15
- **Docker**: For containerization

## Development

All code in the current directory is mounted into the container, so changes are reflected immediately without rebuilding.

### Adding New Endpoints

1. Create a new controller in `src/Controller/`
2. Add routes in `config/routes.yaml` or use route attributes
3. Write tests in `tests/Controller/`

### Adding API Resources

1. Create entity in `src/Entity/` with API Platform attributes
2. The resource will automatically be available at `/api/{resource}`
3. API documentation is auto-generated

## Health Check Endpoint

The `/health` endpoint is a simple health check that returns:
- Status: "healthy"
- Current timestamp in ISO 8601 format

This endpoint is useful for:
- Container health checks
- Load balancer health monitoring
- Service availability verification
- Kubernetes readiness/liveness probes

## Testing

The project includes comprehensive PHPUnit tests for the health check endpoint:

- `testHealthCheckReturnsSuccessfulResponse()`: Verifies 200 status code and response structure
- `testHealthCheckReturnsJsonContent()`: Verifies correct Content-Type header

All tests use Symfony's WebTestCase for full functional testing.

## Environment Variables

Key environment variables in `.env`:

```env
APP_ENV=dev
APP_SECRET=changeme_this_is_a_secret_key_for_symfony
DATABASE_URL="postgresql://app:app@db:5432/app?serverVersion=15&charset=utf8"
```

For testing, see `.env.test` which overrides settings for the test environment.

## Troubleshooting

### Container won't start
```bash
docker-compose down -v
docker-compose up -d --build
```

### Permission issues
```bash
docker-compose exec php chown -R www-data:www-data /var/www/html
```

### Clear all caches
```bash
docker-compose exec php php bin/console cache:clear
docker-compose exec php php bin/console cache:clear --env=test
```

## Next Steps

- Add your first API resource using `src/Entity/` or `src/ApiResource/`
- Configure CORS settings in `config/packages/nelmio_cors.yaml`
- Add authentication/authorization
- Set up CI/CD pipelines
- Add more comprehensive tests

## License

Proprietary


