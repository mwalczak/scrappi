<?php

declare(strict_types=1);

namespace App\Application\NetflixVideo\Command;

use App\Domain\NetflixVideo\Entity\NetflixVideo;
use App\Domain\NetflixVideo\Repository\NetflixVideoRepositoryInterface;
use App\Domain\NetflixVideo\ValueObject\ImdbRating;
use App\Domain\NetflixVideo\ValueObject\VideoId;

final readonly class CreateNetflixVideoCommandHandler
{
    public function __construct(
        private NetflixVideoRepositoryInterface $repository
    ) {
    }

    public function __invoke(CreateNetflixVideoCommand $command): NetflixVideo
    {
        $video = new NetflixVideo(
            id: VideoId::generate(),
            title: $command->title,
            description: $command->description,
            releaseYear: $command->releaseYear,
            imdbRating: $command->imdbRating !== null
                ? ImdbRating::fromFloat($command->imdbRating)
                : null
        );

        $this->repository->save($video);

        return $video;
    }
}
