<?php

declare(strict_types=1);

namespace App\Infrastructure\ApiPlatform\Mapper;

use App\Application\NetflixVideo\DTO\NetflixVideoDTO;
use App\Infrastructure\ApiPlatform\Resource\NetflixVideo;

/**
 * Maps NetflixVideo DTO from Application layer to API Resource.
 */
final class NetflixVideoMapper
{
    public static function toResource(NetflixVideoDTO $dto): NetflixVideo
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
