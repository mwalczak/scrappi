<?php

declare(strict_types=1);

namespace App\Application\NetflixVideo\Command;

final readonly class CreateNetflixVideoCommand
{
    public function __construct(
        public string $title,
        public string $description,
        public int $releaseYear,
        public ?float $imdbRating = null
    ) {
    }
}
