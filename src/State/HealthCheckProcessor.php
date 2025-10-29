<?php

namespace App\State;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProcessorInterface;
use App\ApiResource\HealthCheck;

class HealthCheckProcessor implements ProcessorInterface
{
    public function process(mixed $data, Operation $operation, array $uriVariables = [], array $context = []): HealthCheck
    {
        $healthCheck = new HealthCheck();
        $healthCheck->status = 'healthy';
        $healthCheck->timestamp = (new \DateTime())->format(\DateTimeInterface::ATOM);

        return $healthCheck;
    }
}

