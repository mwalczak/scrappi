<?php

declare(strict_types=1);

namespace App\Application\HealthCheck\Query;

use App\Application\HealthCheck\DTO\HealthStatusDTO;
use App\Domain\HealthCheck\DatabaseHealthCheckInterface;
use DateTimeImmutable;

readonly class GetHealthStatusQueryHandler
{
    public function __construct(
        private DatabaseHealthCheckInterface $databaseHealthCheck
    ) {
    }

    public function __invoke(GetHealthStatusQuery $query): HealthStatusDTO
    {
        $status = $this->databaseHealthCheck->isHealthy() ? 'healthy' : 'unhealthy';

        return new HealthStatusDTO(
            status: $status,
            timestamp: new DateTimeImmutable()
        );
    }
}
