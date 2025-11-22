# CLAUDE.md

This file provides guidance to Claude Code (claude.ai/code) when working with code in this repository.

## Technology Stack

- **PHP**: 8.5
- **Symfony**: 7.3
- **API Platform**: 4.0 (upgraded from 3.4)
- **Doctrine ORM**: 3.3+ with Doctrine Bundle 3.0+
- **PHPUnit**: 12.0+
- **PostgreSQL**: 15
- **Docker**: All development happens in containers

## Development Environment

All commands run inside Docker containers. Use Make commands for common tasks:

```bash
make help                    # Show all available commands
make setup                   # Complete environment setup
make up                      # Start containers
make down                    # Stop containers
make test                    # Run tests
make phpstan                 # Run static analysis
make deptrac                 # Validate architecture
```

## Common Commands

### Development Workflow
```bash
make setup                   # First-time setup (clean install)
make up                      # Start containers
make down                    # Stop containers
make restart                 # Restart containers
make clean                   # Stop and remove volumes
make logs                    # View container logs
make shell                   # Open PHP container shell
```

### Database Management
```bash
make db-create               # Create database
make db-migrate              # Run migrations
make db-reset                # Drop and recreate database
make db-test-setup           # Set up test database
```

### Testing & Quality
```bash
make test                    # Run all tests
make phpstan                 # Run PHPStan at level 9 (maximum strictness)
make deptrac                 # Validate architecture boundaries
make qa                      # Run all quality checks
```

### Cache & Maintenance
```bash
make cache-clear             # Clear all caches
make install                 # Install/update dependencies
```

### Helper Commands
```bash
make composer CMD="require pkg"     # Run Composer command
make console CMD="debug:router"     # Run Symfony console command
```

### Alternative: Helper Scripts
If you prefer scripts over Make:
```bash
./php [command]              # Run any command in PHP container
./composer [command]         # Run Composer commands
./console [command]          # Run Symfony console commands
./test [args]                # Run PHPUnit tests
./phpstan analyse            # Run static analysis
./deptrac analyze            # Validate architecture
```

### Accessing the application
- API Platform Swagger UI: http://localhost:8001/api
- OpenAPI spec: http://localhost:8001/api/docs.json
- Health check: http://localhost:8001/api/health
- Netflix Videos API: http://localhost:8001/api/netflix_videos

## Project Purpose

**Scrappi** is a media scraping platform that collects and serves information about content from various on-demand sources (VOD platforms, ebook stores, etc.).

**Data flow**:
- **Write side**: Console commands scrape external sources and replicate data to local database
- **Read side**: API Platform resources serve scraped data through public API endpoints

## Architecture

**CRITICAL**: This project follows **Hexagonal Architecture (Ports & Adapters)** with **Domain-Driven Design** principles. Read `docs/adr/001-hexagonal-architecture-with-ddd.md` for complete details.

### Core Principles

1. **Three-layer structure**: Domain (pure business logic) → Application (use cases) → Infrastructure (technical implementation)
2. **Framework-agnostic core**: Domain and Application layers have no Symfony/Doctrine dependencies
3. **Interface-driven**: All external dependencies defined as interfaces in Domain, implemented in Infrastructure
4. **Dependency Injection**: Constructor injection throughout, wired by Symfony DI container
5. **CQRS**: Commands (write) via console, Queries (read) via API Platform

### Layer Responsibilities

**Domain Layer** (`src/Domain/`):
- Pure PHP entities with business logic (NO Doctrine attributes)
- Repository interfaces (e.g., `MediaRepositoryInterface`)
- Value objects, domain services, domain events
- No framework dependencies whatsoever

**Application Layer** (`src/Application/`):
- Command/Query handlers orchestrating use cases
- DTOs for data transfer
- Framework-agnostic application services
- Depends only on Domain interfaces

**Infrastructure Layer** (`src/Infrastructure/`):
- Doctrine repositories implementing domain interfaces (e.g., `DoctrineMediaRepository implements MediaRepositoryInterface`)
- Doctrine entity mappings (ORM attributes live here, not in Domain)
- External service adapters (HTTP clients, scrapers)
- All Symfony/Doctrine-specific code

### Project Structure

```
src/
├── Domain/              # Pure business logic (framework-agnostic)
│   ├── Media/
│   │   ├── Entity/          # Domain entities (pure PHP, no Doctrine)
│   │   ├── ValueObject/     # Immutable domain values
│   │   ├── Repository/      # Repository interfaces
│   │   ├── Service/         # Domain services
│   │   └── Exception/       # Domain exceptions
│   └── Scraping/
│       └── Service/         # Scraping service interfaces
│
├── Application/         # Use cases and orchestration
│   ├── Media/
│   │   ├── Command/         # Write operations (ScrapeMediaSourceCommand)
│   │   ├── Query/           # Read operations (GetMediaListQuery)
│   │   └── DTO/             # Data transfer objects
│   └── Scraping/
│       └── Command/         # Scraping command handlers
│
├── Infrastructure/      # Technical implementation
│   ├── Persistence/
│   │   └── Doctrine/
│   │       ├── Entity/      # Doctrine entity mappings
│   │       ├── Repository/  # DoctrineMediaRepository implements MediaRepositoryInterface
│   │       └── Mapping/     # ORM XML/YAML mappings
│   ├── Scraping/
│   │   ├── Adapter/         # VodScraperAdapter, EbookScraperAdapter
│   │   └── Service/         # HTTP clients, external services
│   └── Console/
│       └── Command/         # Symfony console commands
│
├── ApiResource/         # API Platform resources (thin layer)
│   └── MediaResource.php    # Delegates to Application query handlers
│
└── Kernel.php
```

### CQRS Implementation

**Write Side** (Commands):
```bash
./console app:scrape:vod-source "https://example.com"
```
- Symfony console commands trigger scraping
- Execute Application Command Handlers
- Persist via Domain Repository interfaces (implemented by Infrastructure)

**Read Side** (Queries):
```
GET /api/media
```
- API Platform resources expose endpoints
- Delegate to Application Query Handlers
- Query handlers read from database
- Return DTOs to API layer

### API Platform State Pattern

API Platform resources are thin adapters that delegate to Application layer:

Example pattern:
```php
// src/ApiResource/MediaResource.php
#[ApiResource(
    provider: MediaStateProvider::class  // Calls GetMediaListQueryHandler
)]

// src/Infrastructure/ApiPlatform/State/MediaStateProvider.php
class MediaStateProvider implements ProviderInterface
{
    public function __construct(
        private GetMediaListQueryHandler $queryHandler
    ) {}

    public function provide(Operation $operation, array $uriVariables = [], array $context = []): array
    {
        // Use assertions for type safety (PHPStan level 9)
        if (isset($context['filters'])) {
            assert(is_array($context['filters']));
            // Process filters...
        }

        $dtos = $this->queryHandler->__invoke(new GetMediaListQuery());

        // Use mapper to convert DTOs to API Resources
        return array_map(
            MediaMapper::toResource(...),
            $dtos
        );
    }
}

// src/Infrastructure/ApiPlatform/Mapper/MediaMapper.php
final class MediaMapper
{
    public static function toResource(MediaDTO $dto): Media
    {
        $resource = new Media();
        $resource->id = $dto->id;
        $resource->title = $dto->title;
        return $resource;
    }
}
```

**Key patterns**:
- **Mapper classes**: Use static mappers to convert DTOs to API Resources (avoids code duplication)
- **Assertions**: Use `assert()` for runtime type checks (PHPStan understands these)
- **First-class callables**: Use `Mapper::method(...)` syntax for cleaner array_map calls
- **Type safety**: Add explicit type guards for mixed types at PHPStan level 9

### Dependency Injection Configuration

Configure interface bindings in `config/services.yaml`:

```yaml
services:
    # Bind Domain interfaces to Infrastructure implementations
    App\Domain\Media\Repository\MediaRepositoryInterface:
        class: App\Infrastructure\Persistence\Doctrine\Repository\DoctrineMediaRepository

    # Auto-wire Application handlers
    App\Application\:
        resource: '../src/Application'
        autowire: true
        autoconfigure: true
```

### Adding New Features

**IMPORTANT**: Always follow the dependency direction: Infrastructure → Application → Domain. Never let Domain depend on Application or Infrastructure.

#### Step-by-step workflow (outside-in approach):

**1. Domain Layer** - Define business concepts (framework-agnostic)

```php
// src/Domain/Media/Entity/Media.php
namespace App\Domain\Media\Entity;

class Media
{
    private MediaId $id;
    private string $title;
    private SourceType $sourceType;

    // Pure business logic, no framework dependencies
    public function __construct(MediaId $id, string $title, SourceType $sourceType)
    {
        $this->id = $id;
        $this->title = $title;
        $this->sourceType = $sourceType;
    }

    public function isAvailable(): bool
    {
        // Domain logic
    }
}

// src/Domain/Media/Repository/MediaRepositoryInterface.php
namespace App\Domain\Media\Repository;

interface MediaRepositoryInterface
{
    public function save(Media $media): void;
    public function findById(MediaId $id): ?Media;
    public function findBySourceType(SourceType $type): array;
}
```

**2. Application Layer** - Define use cases

```php
// src/Application/Media/Command/CreateMediaCommand.php
namespace App\Application\Media\Command;

readonly class CreateMediaCommand
{
    public function __construct(
        public string $title,
        public string $sourceType
    ) {}
}

// src/Application/Media/Command/CreateMediaHandler.php
namespace App\Application\Media\Command;

use App\Domain\Media\Repository\MediaRepositoryInterface;

class CreateMediaHandler
{
    public function __construct(
        private MediaRepositoryInterface $repository
    ) {}

    public function __invoke(CreateMediaCommand $command): void
    {
        $media = new Media(
            MediaId::generate(),
            $command->title,
            SourceType::from($command->sourceType)
        );

        $this->repository->save($media);
    }
}

// src/Application/Media/Query/GetMediaQuery.php
namespace App\Application\Media\Query;

readonly class GetMediaQuery
{
    public function __construct(
        public ?string $sourceType = null
    ) {}
}

// src/Application/Media/Query/GetMediaQueryHandler.php
namespace App\Application\Media\Query;

use App\Domain\Media\Repository\MediaRepositoryInterface;

class GetMediaQueryHandler
{
    public function __construct(
        private MediaRepositoryInterface $repository
    ) {}

    public function __invoke(GetMediaQuery $query): array
    {
        if ($query->sourceType) {
            return $this->repository->findBySourceType(
                SourceType::from($query->sourceType)
            );
        }

        return $this->repository->findAll();
    }
}
```

**3. Infrastructure Layer** - Implement technical details

```php
// src/Infrastructure/Persistence/Doctrine/Repository/DoctrineMediaRepository.php
namespace App\Infrastructure\Persistence\Doctrine\Repository;

use App\Domain\Media\Entity\Media;
use App\Domain\Media\Repository\MediaRepositoryInterface;
use Doctrine\ORM\EntityManagerInterface;

class DoctrineMediaRepository implements MediaRepositoryInterface
{
    public function __construct(
        private EntityManagerInterface $entityManager
    ) {}

    public function save(Media $media): void
    {
        // Map domain entity to Doctrine entity if needed
        $this->entityManager->persist($media);
        $this->entityManager->flush();
    }

    public function findById(MediaId $id): ?Media
    {
        return $this->entityManager
            ->getRepository(Media::class)
            ->find($id->value());
    }
}

// Configure Doctrine mapping (prefer XML/YAML to keep Domain clean)
// src/Infrastructure/Persistence/Doctrine/Mapping/Media.orm.xml
```

**4. Wire dependencies in config/services.yaml**

```yaml
services:
    # Bind repository interface to implementation
    App\Domain\Media\Repository\MediaRepositoryInterface:
        class: App\Infrastructure\Persistence\Doctrine\Repository\DoctrineMediaRepository
```

**5a. For Write Operations** - Add Console Command

```php
// src/Infrastructure/Console/Command/ScrapeMediaCommand.php
namespace App\Infrastructure\Console\Command;

use App\Application\Media\Command\CreateMediaCommand;
use App\Application\Media\Command\CreateMediaHandler;
use Symfony\Component\Console\Command\Command;

class ScrapeMediaCommand extends Command
{
    public function __construct(
        private CreateMediaHandler $handler
    ) {
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $this->handler->__invoke(new CreateMediaCommand(
            title: 'Movie Title',
            sourceType: 'vod'
        ));

        return Command::SUCCESS;
    }
}
```

**5b. For Read Operations** - Add API Platform Resource

```php
// src/ApiResource/Media.php
namespace App\ApiResource;

use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\GetCollection;

#[ApiResource(
    operations: [
        new GetCollection(
            uriTemplate: '/media',
            provider: 'App\Infrastructure\ApiPlatform\State\MediaStateProvider'
        )
    ]
)]
class Media
{
    public string $id;
    public string $title;
    public string $sourceType;
}

// src/Infrastructure/ApiPlatform/State/MediaStateProvider.php
namespace App\Infrastructure\ApiPlatform\State;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProviderInterface;
use App\Application\Media\Query\GetMediaQuery;
use App\Application\Media\Query\GetMediaQueryHandler;

class MediaStateProvider implements ProviderInterface
{
    public function __construct(
        private GetMediaQueryHandler $queryHandler
    ) {}

    public function provide(Operation $operation, array $uriVariables = [], array $context = []): array
    {
        $domainMedia = $this->queryHandler->__invoke(new GetMediaQuery());

        // Map domain entities to API resources (DTOs)
        return array_map(
            fn($media) => new \App\ApiResource\Media(
                id: $media->getId()->value(),
                title: $media->getTitle(),
                sourceType: $media->getSourceType()->value
            ),
            $domainMedia
        );
    }
}
```

#### Common Patterns

**Value Objects** (in Domain):
```php
// src/Domain/Media/ValueObject/MediaId.php
namespace App\Domain\Media\ValueObject;

readonly class MediaId
{
    private function __construct(
        private string $value
    ) {}

    public static function generate(): self
    {
        return new self(\Ramsey\Uuid\Uuid::uuid4()->toString());
    }

    public static function from(string $value): self
    {
        return new self($value);
    }

    public function value(): string
    {
        return $this->value;
    }
}
```

**Domain Events** (optional, for complex workflows):
```php
// src/Domain/Media/Event/MediaScrapedEvent.php
namespace App\Domain\Media\Event;

class MediaScrapedEvent
{
    public function __construct(
        public readonly MediaId $mediaId,
        public readonly \DateTimeImmutable $scrapedAt
    ) {}
}
```

### Architectural Rules (DO / DON'T)

**✅ DO:**
- Keep Domain entities pure PHP without framework attributes
- Define repository interfaces in Domain layer
- Implement repositories in Infrastructure layer
- Use constructor injection for all dependencies
- Create separate Command and Query handlers (CQRS)
- Use Value Objects for domain concepts (MediaId, Email, etc.)
- Map between Domain entities and API resources/DTOs in Infrastructure
- Use Doctrine XML/YAML mappings to keep Domain clean
- Keep API Platform State Providers thin - delegate to Application handlers
- Put all Symfony/Doctrine-specific code in Infrastructure

**❌ DON'T:**
- Add Doctrine attributes (`#[ORM\Entity]`) to Domain entities
- Use Doctrine repositories directly - always go through Domain interfaces
- Put business logic in API Platform State Providers
- Put business logic in Console Commands
- Let Domain layer depend on Application or Infrastructure
- Create "god objects" - keep entities focused
- Mix read and write operations in the same handler
- Directly expose Domain entities through API - use DTOs/API resources
- Use Symfony/Doctrine classes in Domain or Application layers

### Response Format

API Platform returns JSON-LD by default (semantic web format). This is configured in `config/packages/api_platform.yaml:4-10`.

Example response from `/api/health`:
```json
{
  "@context": "/api/contexts/HealthCheck",
  "@id": "/api/health",
  "@type": "HealthCheck",
  "status": "healthy",
  "timestamp": "2025-11-22T12:00:00+00:00"
}
```

### Configuration

Key configuration files:
- `config/packages/api_platform.yaml` - API Platform settings (formats, docs, defaults)
- `config/packages/doctrine.yaml` - Database and ORM configuration
- `config/packages/nelmio_cors.yaml` - CORS settings
- `config/routes.yaml` - Custom routes (API Platform routes are auto-generated)
- `phpunit.xml` - Test configuration

### Testing

Tests use Symfony's `WebTestCase` for functional testing. They create a test client that simulates HTTP requests.

All tests run in the test environment, which uses `.env.test` configuration and a separate test database.

The test suite is configured to be strict: fails on warnings, risky tests, and unexpected output.

**Test Base Classes**:
- **ApiTestCase** (`tests/Functional/Api/ApiTestCase.php`): Base class for API tests with helper methods
  - `getJsonResponse(KernelBrowser $client): array` - Parses and validates JSON responses
  - Eliminates duplicate JSON parsing code across test files

Example:
```php
// tests/Functional/Api/SomeApiTest.php
class SomeApiTest extends ApiTestCase
{
    public function testEndpoint(): void
    {
        $client = static::createClient();
        $client->request('GET', '/api/endpoint');

        $response = $this->getJsonResponse($client);
        // Use $response array directly...
    }
}
```

## Important Notes

### Architecture
- **ALWAYS** read `docs/adr/001-hexagonal-architecture-with-ddd.md` before making architectural decisions
- Follow the dependency rule: Infrastructure → Application → Domain (never reverse)
- Keep Domain layer completely framework-agnostic (no Symfony, no Doctrine attributes)
- Repository interfaces in Domain, implementations in Infrastructure
- CQRS: Commands for writes (console), Queries for reads (API)
- State Providers/Processors are thin adapters - delegate to Application layer

### Development
- All code changes are immediately reflected in containers (volume-mounted)
- The interactive Swagger UI at `/api` requires `symfony/twig-bundle` (already installed)
- Database connection string in `.env`: `postgresql://app:app@db:5432/app?serverVersion=15&charset=utf8`
- API Platform operations are stateless by default (configured in `api_platform.yaml:12`)
- Cache headers automatically include Vary headers for Content-Type, Authorization, and Origin

### Testing
- Tests should follow the same architecture (test Domain logic separately from Infrastructure)
- Use test doubles/mocks for Infrastructure when testing Application layer
- Integration tests should test the full stack through API Platform or Console commands
- Extend `ApiTestCase` for API endpoint tests to reuse helper methods

### Code Quality
- **PHPStan Level 9**: Maximum type safety - all code must pass without errors
- Use `assert()` statements for runtime type narrowing (PHPStan understands these)
- Add explicit type guards before casting mixed types (`is_string()`, `is_numeric()`, etc.)
- Use static mapper classes to eliminate code duplication in State Providers
- Run `./deptrac analyze` to ensure architecture boundaries are maintained

### Git Hooks (Automatic)
- **Pre-commit hook** automatically runs PHPStan and Deptrac before each commit
- Hooks are **automatically installed** via Composer (post-install/update scripts)
- All developers get the same hooks - no manual setup needed
- Located in `.githooks/` directory (version controlled)
- To reinstall manually: `make hooks` or `composer install-hooks`
- To bypass (not recommended): `git commit --no-verify`