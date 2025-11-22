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
        public DateTimeImmutable $createdAt,
        public DateTimeImmutable $updatedAt
    ) {
    }

    public static function fromEntity(NetflixVideo $video): self
    {
        return new self(
            id: $video->getId()->value(),
            title: $video->getTitle(),
            description: $video->getDescription(),
            releaseYear: $video->getReleaseYear(),
            imdbRating: $video->getImdbRating()?->value(),
            createdAt: $video->getCreatedAt(),
            updatedAt: $video->getUpdatedAt()
        );
    }
}
