<?php

declare(strict_types=1);

namespace App\Infrastructure\ApiPlatform\State;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProviderInterface;
use App\Infrastructure\ApiPlatform\Resource\NetflixVideo;
use App\Application\NetflixVideo\Query\GetNetflixVideoQuery;
use App\Application\NetflixVideo\Query\GetNetflixVideoQueryHandler;

/**
 * State provider for single Netflix video item operations.
 *
 * Delegates to Application layer and maps DTO to API Resource.
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

        return $this->dtoToResource($dto);
    }

    private function dtoToResource($dto): NetflixVideo
    {
        $resource = new NetflixVideo();
        $resource->id = $dto->id;
        $resource->title = $dto->title;
        $resource->description = $dto->description;
        $resource->releaseYear = $dto->releaseYear;
        $resource->imdbRating = $dto->imdbRating;
        $resource->createdAt = $dto->createdAt;
        $resource->updatedAt = $dto->updatedAt;

        return $resource;
    }
}
