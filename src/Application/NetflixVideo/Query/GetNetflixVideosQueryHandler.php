<?php

declare(strict_types=1);

namespace App\Application\NetflixVideo\Query;

use App\Application\NetflixVideo\DTO\NetflixVideoDTO;
use App\Domain\NetflixVideo\Repository\NetflixVideoRepositoryInterface;

readonly class GetNetflixVideosQueryHandler
{
    public function __construct(
        private NetflixVideoRepositoryInterface $repository
    ) {
    }

    /**
     * @return NetflixVideoDTO[]
     */
    public function __invoke(GetNetflixVideosQuery $query): array
    {
        return ($query->releaseYear !== null
            ? $this->repository->findByReleaseYear($query->releaseYear)
            : $this->repository->findAll())
            |> (fn($videos) => array_map(NetflixVideoDTO::fromEntity(...), $videos));
    }
}
