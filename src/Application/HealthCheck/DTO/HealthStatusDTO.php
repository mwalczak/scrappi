<?php

declare(strict_types=1);

namespace App\Application\HealthCheck\DTO;

readonly class HealthStatusDTO
{
    public function __construct(
        public string $status,
        public \DateTimeImmutable $timestamp
    ) {
    }
}
