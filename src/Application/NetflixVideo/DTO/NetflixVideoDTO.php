<?php

declare(strict_types=1);

namespace App\Application\NetflixVideo\DTO;

use App\Domain\NetflixVideo\Entity\NetflixVideo;
use DateTimeImmutable;

readonly class NetflixVideoDTO
{
    public function __construct(
        public string $id,
        public string $title,
        public string $description,
        public int $releaseYear,
        public ?float $imdbRating,
        public ?string $imdbId,
        public ?string $imdbUrl,
        public DateTimeImmutable $createdAt,
        public DateTimeImmutable $updatedAt
    ) {
    }

    public static function fromEntity(NetflixVideo $video): self
    {
        return new self(
            id: $video->id->value(),
            title: $video->title,
            description: $video->description,
            releaseYear: $video->releaseYear,
            imdbRating: $video->imdbRating?->value(),
            imdbId: $video->imdbId?->value(),
            imdbUrl: $video->imdbId?->toUrl(),
            createdAt: $video->createdAt,
            updatedAt: $video->updatedAt
        );
    }
}
