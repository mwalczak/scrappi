<?php

declare(strict_types=1);

namespace App\Infrastructure\ApiPlatform\State;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProviderInterface;
use App\Infrastructure\ApiPlatform\Resource\HealthCheck;
use App\Application\HealthCheck\Query\GetHealthStatusQuery;
use App\Application\HealthCheck\Query\GetHealthStatusQueryHandler;

/**
 * @implements ProviderInterface<HealthCheck>
 */
readonly class HealthCheckProvider implements ProviderInterface
{
    public function __construct(
        private GetHealthStatusQueryHandler $queryHandler
    ) {
    }

    public function provide(Operation $operation, array $uriVariables = [], array $context = []): HealthCheck
    {
        // Delegate to Application layer
        $dto = $this->queryHandler->__invoke(new GetHealthStatusQuery());

        // Map DTO to API Resource
        $healthCheck = new HealthCheck();
        $healthCheck->status = $dto->status;
        $healthCheck->timestamp = $dto->timestamp->format(\DateTimeInterface::ATOM);

        return $healthCheck;
    }
}
