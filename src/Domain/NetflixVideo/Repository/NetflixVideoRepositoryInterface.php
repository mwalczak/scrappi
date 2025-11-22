<?php

declare(strict_types=1);

namespace App\Domain\NetflixVideo\Repository;

use App\Domain\NetflixVideo\Entity\NetflixVideo;
use App\Domain\NetflixVideo\ValueObject\VideoId;

interface NetflixVideoRepositoryInterface
{
    public function save(NetflixVideo $video): void;

    public function findById(VideoId $id): ?NetflixVideo;

    /**
     * @return NetflixVideo[]
     */
    public function findAll(): array;

    /**
     * @return NetflixVideo[]
     */
    public function findByReleaseYear(int $year): array;

    public function delete(NetflixVideo $video): void;
}
