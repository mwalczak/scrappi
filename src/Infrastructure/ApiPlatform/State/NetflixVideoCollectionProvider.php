<?php

declare(strict_types=1);

namespace App\Infrastructure\ApiPlatform\State;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProviderInterface;
use App\Infrastructure\ApiPlatform\Resource\NetflixVideo;
use App\Application\NetflixVideo\Query\GetNetflixVideosQuery;
use App\Application\NetflixVideo\Query\GetNetflixVideosQueryHandler;

/**
 * State provider for Netflix video collection operations.
 *
 * Delegates to Application layer and maps DTOs to API Resources.
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
        $releaseYear = $context['filters']['releaseYear'] ?? null;

        $dtos = $this->queryHandler->__invoke(
            new GetNetflixVideosQuery($releaseYear)
        );

        return array_map(
            fn($dto) => $this->dtoToResource($dto),
            $dtos
        );
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
