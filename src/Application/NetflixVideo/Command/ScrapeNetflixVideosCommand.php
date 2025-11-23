<?php

declare(strict_types=1);

namespace App\Application\NetflixVideo\Command;

readonly class ScrapeNetflixVideosCommand
{
    public function __construct(
        public string $countryCode,
        public int $limit = 50
    ) {
    }
}
