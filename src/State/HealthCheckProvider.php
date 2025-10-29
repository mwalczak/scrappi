<?php
namespace App\State;
use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProviderInterface;
use App\ApiResource\HealthCheck;
class HealthCheckProvider implements ProviderInterface
{
    public function provide(Operation $operation, array $uriVariables = [], array $context = []): HealthCheck
    {
        $healthCheck = new HealthCheck();
        $healthCheck->status = 'healthy';
        $healthCheck->timestamp = (new \DateTime())->format(\DateTimeInterface::ATOM);
        return $healthCheck;
    }
}
