<?php

declare(strict_types=1);

namespace App\Application\NetflixVideo\Query;

use App\Application\NetflixVideo\DTO\NetflixVideoDTO;
use App\Domain\NetflixVideo\Repository\NetflixVideoRepositoryInterface;
use App\Domain\NetflixVideo\ValueObject\VideoId;

readonly class GetNetflixVideoQueryHandler
{
    public function __construct(
        private NetflixVideoRepositoryInterface $repository
    ) {
    }

    public function __invoke(GetNetflixVideoQuery $query): ?NetflixVideoDTO
    {
        $video = $this->repository->findById(VideoId::fromString($query->id));

        return $video ? NetflixVideoDTO::fromEntity($video) : null;
    }
}
