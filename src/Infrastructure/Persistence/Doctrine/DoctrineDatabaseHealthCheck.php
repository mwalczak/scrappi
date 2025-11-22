<?php

declare(strict_types=1);

namespace App\Infrastructure\Persistence\Doctrine;

use App\Domain\HealthCheck\DatabaseHealthCheckInterface;
use Doctrine\DBAL\Connection;
use Throwable;

/**
 * Doctrine implementation of database health check.
 *
 * Uses Doctrine DBAL Connection to verify database connectivity.
 */
readonly class DoctrineDatabaseHealthCheck implements DatabaseHealthCheckInterface
{
    public function __construct(
        private Connection $connection
    ) {
    }

    public function isHealthy(): bool
    {
        try {
            // Execute a simple query to verify database connectivity
            $this->connection->executeQuery('SELECT 1');
            return true;
        } catch (Throwable) {
            // Database is not accessible
            return false;
        }
    }
}
