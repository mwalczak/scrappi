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

- **API Platform Interactive Docs (Swagger UI)**: http://localhost:8000/api
- **OpenAPI Specification (JSON)**: http://localhost:8000/api/docs.json
- **Health Check Endpoint**: http://localhost:8000/api/health

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
├── php                        # Helper script: run commands in PHP container
├── composer                   # Helper script: run Composer commands
├── console                    # Helper script: run Symfony console
├── test                       # Helper script: run PHPUnit tests
├── config/                    # Configuration files
│   ├── packages/             # Package configurations
│   │   ├── api_platform.yaml
│   │   ├── doctrine.yaml
│   │   ├── framework.yaml
│   │   └── test/
│   │       └── framework.yaml
│   ├── routes/               # Route configurations
│   │   └── api_platform.yaml
│   ├── bundles.php          # Registered bundles
│   ├── routes.yaml          # Application routes
│   ├── services.yaml        # Service definitions
│   └── bootstrap.php        # Bootstrap configuration
├── public/                    # Public directory
│   └── index.php            # Application entry point
├── src/                       # Application code
│   ├── ApiResource/         # API Platform resources
│   ├── Controller/          # Controllers
│   ├── Entity/              # Doctrine entities
│   ├── Repository/          # Doctrine repositories
│   ├── State/               # API Platform state providers/processors
│   └── Kernel.php          # Application kernel
├── tests/                     # Test files
│   └── Controller/          # Controller tests
├── var/                       # Generated files (cache, logs)
├── vendor/                    # Composer dependencies
├── docker-compose.yml        # Docker Compose configuration
├── Dockerfile               # PHP container definition
├── composer.json            # PHP dependencies
├── phpunit.xml              # PHPUnit configuration
├── .env                     # Environment variables
└── .env.test                # Test environment variables
```

## Technology Stack

- **PHP**: 8.4+
- **Symfony**: 7.3
- **API Platform**: 4.0
- **Doctrine ORM**: 3.3+
- **Doctrine Bundle**: 3.0+
- **PHPUnit**: 12.0+
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
APP_ENV=dev                          # Application environment (dev, prod, test)
APP_SECRET=changeme...               # Secret key for Symfony
DATABASE_URL=postgresql://...        # PostgreSQL connection string
DEFAULT_URI=http://localhost         # Default application URI
CORS_ALLOW_ORIGIN=^https?://...     # CORS allowed origins regex
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
1. Create entity in `src/Entity/` or API Resource in `src/ApiResource/` with API Platform attributes
2. Create a state provider in `src/State/` if needed for custom data handling
3. The resource will automatically be available at `/api/{resource}`
4. API documentation is auto-generated and visible at `/api`

**Note:** The interactive Swagger UI at `/api` requires `symfony/twig-bundle`. Install it with:
```bash
./composer require symfony/twig-bundle
```
docker-compose exec php php bin/console cache:clear
docker-compose exec php php bin/console cache:clear --env=test
```
The `/api/health` endpoint is implemented using API Platform's state provider pattern and returns:
## Next Steps

- Add your first API resource using `src/Entity/` or `src/ApiResource/`
**Response Format** (JSON-LD):
```json
{
  "status": "healthy",
  "timestamp": "2025-10-29T12:00:00+00:00"
}
```

- Configure CORS settings in `config/packages/nelmio_cors.yaml`
- Add authentication/authorization
- Set up CI/CD pipelines
- `testHealthCheckReturnsJsonContent()`: Verifies correct Content-Type header (application/ld+json)
- `testHealthCheckAppearsInApiDocumentation()`: Verifies endpoint is documented in OpenAPI spec

## License
**Features:**
- ✅ Fully documented in API Platform Swagger UI at `/api`
- ✅ Auto-generated OpenAPI documentation
- ✅ Follows API Platform conventions
- ✅ Returns JSON-LD format (semantic web standard)


Proprietary


