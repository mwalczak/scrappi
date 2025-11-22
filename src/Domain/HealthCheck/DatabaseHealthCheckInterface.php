<?php

declare(strict_types=1);

namespace App\Domain\HealthCheck;

/**
 * Interface for checking database health status.
 *
 * This interface defines the contract for database health checks,
 * keeping the domain layer independent of any specific database implementation.
 */
interface DatabaseHealthCheckInterface
{
    /**
     * Check if the database is accessible and responding.
     *
     * @return bool True if database is healthy, false otherwise
     */
    public function isHealthy(): bool;
}
