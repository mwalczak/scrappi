<?php

declare(strict_types=1);

namespace App\Infrastructure\ApiPlatform\State;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProviderInterface;
use App\Infrastructure\ApiPlatform\Mapper\NetflixVideoMapper;
use App\Infrastructure\ApiPlatform\Resource\NetflixVideo;
use App\Application\NetflixVideo\Query\GetNetflixVideosQuery;
use App\Application\NetflixVideo\Query\GetNetflixVideosQueryHandler;

/**
 * State provider for Netflix video collection operations.
 *
 * Delegates to Application layer and maps DTOs to API Resources.
 *
 * @implements ProviderInterface<NetflixVideo>
 */
readonly class NetflixVideoCollectionProvider implements ProviderInterface
{
    public function __construct(
        private GetNetflixVideosQueryHandler $queryHandler
    ) {
    }

    /**
     * @return NetflixVideo[]
     */
    public function provide(Operation $operation, array $uriVariables = [], array $context = []): array
    {
        $releaseYear = null;

        if (isset($context['filters'])) {
            assert(is_array($context['filters']));

            if (isset($context['filters']['releaseYear'])) {
                $releaseYearValue = $context['filters']['releaseYear'];
                assert(is_numeric($releaseYearValue));

                $releaseYear = (int) $releaseYearValue;
            }
        }

        $dtos = $this->queryHandler->__invoke(
            new GetNetflixVideosQuery($releaseYear)
        );

        return array_map(
            NetflixVideoMapper::toResource(...),
            $dtos
        );
    }
}
