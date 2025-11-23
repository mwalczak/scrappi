<?php

declare(strict_types=1);

namespace App\Domain\NetflixVideo\Service;

use App\Domain\NetflixVideo\Entity\NetflixVideo;
use App\Domain\NetflixVideo\ValueObject\Country;

interface NetflixScraperInterface
{
    /**
     * @return array<NetflixVideo>
     */
    public function scrapeNetflixReleases(Country $country, int $limit = 50): array;
}
