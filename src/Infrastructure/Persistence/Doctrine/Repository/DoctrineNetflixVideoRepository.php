<?php

declare(strict_types=1);

namespace App\Infrastructure\Persistence\Doctrine\Repository;

use App\Domain\NetflixVideo\Entity\NetflixVideo;
use App\Domain\NetflixVideo\Repository\NetflixVideoRepositoryInterface;
use App\Domain\NetflixVideo\ValueObject\VideoId;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\EntityRepository;

class DoctrineNetflixVideoRepository implements NetflixVideoRepositoryInterface
{
    private EntityRepository $repository;

    public function __construct(
        private readonly EntityManagerInterface $entityManager
    ) {
        $this->repository = $entityManager->getRepository(NetflixVideo::class);
    }

    public function save(NetflixVideo $video): void
    {
        $this->entityManager->persist($video);
        $this->entityManager->flush();
    }

    public function findById(VideoId $id): ?NetflixVideo
    {
        return $this->repository->find($id->value()) ?: null;
    }

    public function findAll(): array
    {
        return $this->repository->findAll();
    }

    public function findByReleaseYear(int $year): array
    {
        return $this->repository->findBy(['releaseYear' => $year]);
    }

    public function delete(NetflixVideo $video): void
    {
        $this->entityManager->remove($video);
        $this->entityManager->flush();
    }
}
