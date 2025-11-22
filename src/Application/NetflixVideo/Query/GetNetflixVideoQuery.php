<?php

declare(strict_types=1);

namespace App\Application\NetflixVideo\Query;

readonly class GetNetflixVideoQuery
{
    public function __construct(
        public string $id
    ) {
    }
}
