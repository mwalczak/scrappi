<?php

declare(strict_types=1);

namespace App\Infrastructure\ApiPlatform\Resource;

use ApiPlatform\Metadata\ApiProperty;
use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\GetCollection;
use DateTimeImmutable;

#[ApiResource(
    shortName: 'NetflixVideo',
    operations: [
        new GetCollection(
            uriTemplate: '/netflix-videos',
            description: 'Retrieves the collection of Netflix videos',
            provider: 'App\Infrastructure\ApiPlatform\State\NetflixVideoCollectionProvider'
        ),
        new Get(
            uriTemplate: '/netflix-videos/{id}',
            description: 'Retrieves a Netflix video resource',
            provider: 'App\Infrastructure\ApiPlatform\State\NetflixVideoItemProvider'
        )
    ]
)]
class NetflixVideo
{
    #[ApiProperty(identifier: true)]
    public string $id;

    #[ApiProperty(description: 'The title of the video')]
    public string $title;

    #[ApiProperty(description: 'The description of the video')]
    public string $description;

    #[ApiProperty(description: 'The release year of the video')]
    public int $releaseYear;

    #[ApiProperty(description: 'The IMDB rating (0.0-10.0)', example: 8.5)]
    public ?float $imdbRating = null;

    #[ApiProperty(description: 'The IMDB ID (e.g., tt1234567)', example: 'tt0111161')]
    public ?string $imdbId = null;

    #[ApiProperty(description: 'The IMDB URL', example: 'https://www.imdb.com/title/tt0111161/')]
    public ?string $imdbUrl = null;

    #[ApiProperty(description: 'The date and time when the video was created', readable: true, writable: false)]
    public DateTimeImmutable $createdAt;

    #[ApiProperty(description: 'The date and time when the video was last updated', readable: true, writable: false)]
    public DateTimeImmutable $updatedAt;
}
