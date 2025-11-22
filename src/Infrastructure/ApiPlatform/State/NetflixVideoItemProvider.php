<?php

declare(strict_types=1);

namespace App\Infrastructure\ApiPlatform\State;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProviderInterface;
use App\Infrastructure\ApiPlatform\Mapper\NetflixVideoMapper;
use App\Infrastructure\ApiPlatform\Resource\NetflixVideo;
use App\Application\NetflixVideo\Query\GetNetflixVideoQuery;
use App\Application\NetflixVideo\Query\GetNetflixVideoQueryHandler;

/**
 * State provider for single Netflix video item operations.
 *
 * Delegates to Application layer and maps DTO to API Resource.
 *
 * @implements ProviderInterface<NetflixVideo>
 */
readonly class NetflixVideoItemProvider implements ProviderInterface
{
    public function __construct(
        private GetNetflixVideoQueryHandler $queryHandler
    ) {
    }

    public function provide(Operation $operation, array $uriVariables = [], array $context = []): ?NetflixVideo
    {
        $dto = $this->queryHandler->__invoke(
            new GetNetflixVideoQuery($uriVariables['id'])
        );

        if (!$dto) {
            return null;
        }

        return NetflixVideoMapper::toResource($dto);
    }
}
