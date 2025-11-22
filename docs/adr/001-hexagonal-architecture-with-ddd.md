# ADR-001: Hexagonal Architecture with Domain-Driven Design

**Status**: Accepted
**Date**: 2025-11-22
**Context**: Architecture foundation for a media scraping platform

## Decision

We will implement a **Hexagonal Architecture** (Ports & Adapters) combined with **Domain-Driven Design** principles, maintaining a pragmatic and framework-agnostic core.

## Architecture Layers

### 1. Domain Layer (`src/Domain/`)
**Purpose**: Pure business logic, framework-agnostic

**Contains**:
- **Entities**: Domain models representing business concepts (e.g., `Media`, `Source`, `ScrapingJob`)
- **Value Objects**: Immutable objects representing domain concepts (e.g., `MediaId`, `SourceUrl`)
- **Repository Interfaces**: Contracts for data persistence (e.g., `MediaRepositoryInterface`)
- **Domain Services**: Business logic that doesn't belong to a single entity
- **Domain Events**: Events representing state changes in the domain
- **Exceptions**: Domain-specific exceptions

**Rules**:
- No framework dependencies (no Symfony, no Doctrine attributes)
- No infrastructure concerns (no database, no HTTP)
- Pure PHP with business logic only
- All external dependencies defined as interfaces

**Example**:
```php
// src/Domain/Media/Entity/Media.php
namespace App\Domain\Media\Entity;

class Media
{
    private MediaId $id;
    private string $title;
    private SourceType $sourceType;
    // Pure domain logic, no Doctrine attributes
}

// src/Domain/Media/Repository/MediaRepositoryInterface.php
namespace App\Domain\Media\Repository;

interface MediaRepositoryInterface
{
    public function save(Media $media): void;
    public function findById(MediaId $id): ?Media;
}
```

### 2. Application Layer (`src/Application/`)
**Purpose**: Orchestrates domain logic, implements use cases

**Contains**:
- **Command Handlers**: Execute write operations (e.g., `ScrapeMediaSourceHandler`)
- **Query Handlers**: Execute read operations (e.g., `GetMediaListQueryHandler`)
- **DTOs**: Data Transfer Objects for input/output
- **Application Services**: Coordinate multiple domain services
- **Commands**: Represent write intentions (e.g., `ScrapeMediaSourceCommand`)
- **Queries**: Represent read intentions (e.g., `GetMediaListQuery`)

**Rules**:
- Depends on Domain layer interfaces
- No infrastructure dependencies (database, HTTP, framework specifics)
- Orchestrates domain logic without implementing it
- Framework-agnostic where possible

**Example**:
```php
// src/Application/Media/Command/ScrapeMediaSourceCommand.php
namespace App\Application\Media\Command;

readonly class ScrapeMediaSourceCommand
{
    public function __construct(
        public string $sourceUrl,
        public string $sourceType
    ) {}
}

// src/Application/Media/Command/ScrapeMediaSourceHandler.php
namespace App\Application\Media\Command;

use App\Domain\Media\Repository\MediaRepositoryInterface;

class ScrapeMediaSourceHandler
{
    public function __construct(
        private MediaRepositoryInterface $mediaRepository,
        private ScraperServiceInterface $scraperService
    ) {}

    public function __invoke(ScrapeMediaSourceCommand $command): void
    {
        // Orchestrate scraping logic using domain services
    }
}
```

### 3. Infrastructure Layer (`src/Infrastructure/`)
**Purpose**: Technical implementation, framework integration

**Contains**:
- **Doctrine Repositories**: Implement domain repository interfaces (e.g., `DoctrineMediaRepository implements MediaRepositoryInterface`)
- **Doctrine Entities/Mappings**: ORM-specific entity mappings
- **External Service Adapters**: HTTP clients, scrapers, third-party APIs
- **Persistence Configuration**: Database migrations, mappings
- **Framework Integration**: Symfony-specific implementations

**Rules**:
- Implements Domain interfaces
- Contains all framework-specific code (Doctrine attributes, Symfony services)
- Adapts external services to domain interfaces
- No business logic

**Example**:
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

// src/Infrastructure/Persistence/Doctrine/Entity/MediaEntity.php
namespace App\Infrastructure\Persistence\Doctrine\Entity;

use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
#[ORM\Table(name: 'media')]
class MediaEntity
{
    #[ORM\Id]
    #[ORM\Column(type: 'string')]
    private string $id;

    #[ORM\Column(type: 'string')]
    private string $title;

    // Doctrine-specific entity with ORM attributes
}
```

## CQRS Pattern

### Write Side (Commands)
**Mechanism**: Symfony Console Commands

- Console commands trigger scraping operations
- Execute Application Command Handlers
- Write to database through Domain Repositories
- Handle scraping jobs, data ingestion, updates

**Example**:
```bash
./console app:scrape:vod-source "https://example.com/vod"
./console app:scrape:ebook-source "https://example.com/ebooks"
```

### Read Side (Queries)
**Mechanism**: API Platform Resources with Query Handlers

- API Platform resources expose public endpoints
- Execute Application Query Handlers
- Read from database through optimized queries
- Serve data to API consumers

**Example**:
```php
// API resource delegates to query handler
// GET /api/media -> GetMediaListQueryHandler
```

## Dependency Injection

- All dependencies injected through constructors
- Interfaces defined in Domain, implementations in Infrastructure
- Symfony DI container wires everything together
- Service configuration in `config/services.yaml`

## Directory Structure

```
src/
├── Domain/
│   ├── Media/
│   │   ├── Entity/
│   │   │   └── Media.php
│   │   ├── ValueObject/
│   │   │   ├── MediaId.php
│   │   │   └── SourceType.php
│   │   ├── Repository/
│   │   │   └── MediaRepositoryInterface.php
│   │   ├── Service/
│   │   │   └── MediaValidationService.php
│   │   └── Exception/
│   │       └── InvalidMediaException.php
│   └── Scraping/
│       └── Service/
│           └── ScraperServiceInterface.php
│
├── Application/
│   ├── Media/
│   │   ├── Command/
│   │   │   ├── ScrapeMediaSourceCommand.php
│   │   │   └── ScrapeMediaSourceHandler.php
│   │   ├── Query/
│   │   │   ├── GetMediaListQuery.php
│   │   │   └── GetMediaListQueryHandler.php
│   │   └── DTO/
│   │       └── MediaDTO.php
│   └── Scraping/
│       └── Command/
│           └── ProcessScrapingJobHandler.php
│
├── Infrastructure/
│   ├── Persistence/
│   │   └── Doctrine/
│   │       ├── Entity/
│   │       │   └── MediaEntity.php
│   │       ├── Repository/
│   │       │   └── DoctrineMediaRepository.php
│   │       └── Mapping/
│   │           └── Media.orm.xml
│   ├── Scraping/
│   │   ├── Adapter/
│   │   │   ├── VodScraperAdapter.php
│   │   │   └── EbookScraperAdapter.php
│   │   └── Service/
│   │       └── HttpScraperService.php
│   └── Console/
│       └── Command/
│           └── ScrapeMediaSourceCommand.php
│
├── ApiResource/
│   └── MediaResource.php (API Platform)
│
└── Kernel.php
```

## Benefits

1. **Testability**: Domain logic testable without framework
2. **Framework Independence**: Can swap Symfony/Doctrine if needed
3. **Clear Boundaries**: Each layer has well-defined responsibilities
4. **Maintainability**: Business logic isolated from technical details
5. **CQRS Optimization**: Separate models for read/write operations
6. **Scalability**: Easy to optimize read/write paths independently

## Consequences

### Positive
- Clean separation of concerns
- Domain logic free from infrastructure
- Easy to test business logic
- Clear dependency direction (inward toward domain)

### Negative
- More boilerplate code (interfaces, DTOs, adapters)
- Learning curve for team members unfamiliar with DDD
- Initial setup more complex than traditional MVC

## Implementation Notes

- Start with simple domain models, evolve as complexity grows
- Use Doctrine XML/YAML mapping to keep domain entities clean
- Consider repository pattern with specification pattern for complex queries
- Use domain events for cross-aggregate communication
- Keep API Platform resources thin—delegate to query handlers

### Type Safety & Code Quality

**PHPStan Level 9** (maximum strictness) is enforced across the codebase:

```php
// Use assertions for runtime type checking
public function provide(Operation $operation, array $uriVariables = [], array $context = []): array
{
    assert(isset($uriVariables['id']), 'Missing id parameter');
    $idValue = $uriVariables['id'];
    assert(is_string($idValue) || is_numeric($idValue), 'Invalid id type');

    $id = (string) $idValue;
    // PHPStan now knows $id is string...
}
```

**Benefits**:
- Catches type errors at development time
- Eliminates runtime type bugs
- Improves IDE autocomplete and refactoring
- Documents expected types explicitly

### Mapper Pattern for State Providers

Eliminate code duplication by creating static mapper classes:

```php
// src/Infrastructure/ApiPlatform/Mapper/MediaMapper.php
final class MediaMapper
{
    public static function toResource(MediaDTO $dto): Media
    {
        $resource = new Media();
        $resource->id = $dto->id;
        $resource->title = $dto->title;
        $resource->sourceType = $dto->sourceType;
        return $resource;
    }
}

// Usage in State Providers
class MediaCollectionProvider implements ProviderInterface
{
    public function provide(...): array
    {
        $dtos = $this->queryHandler->__invoke(new GetMediaListQuery());

        // Use first-class callable syntax
        return array_map(
            MediaMapper::toResource(...),
            $dtos
        );
    }
}
```

**Benefits**:
- Single source of truth for DTO → Resource mapping
- Easier to maintain and test
- Reusable across multiple State Providers
- Cleaner code with first-class callable syntax