<?php

declare(strict_types=1);

namespace App\Domain\NetflixVideo\Entity;

use App\Domain\NetflixVideo\ValueObject\ImdbRating;
use App\Domain\NetflixVideo\ValueObject\VideoId;
use DateTimeImmutable;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
#[ORM\Table(name: 'netflix_videos')]
#[ORM\Index(name: 'idx_release_year', columns: ['release_year'])]
class NetflixVideo
{
    #[ORM\Id]
    #[ORM\Column(type: 'string', length: 36)]
    private string $id;

    #[ORM\Column(type: 'string', length: 255)]
    private string $title;

    #[ORM\Column(type: 'text')]
    private string $description;

    #[ORM\Column(name: 'release_year', type: 'integer')]
    private int $releaseYear;

    #[ORM\Column(name: 'imdb_rating', type: 'float', nullable: true)]
    private ?float $imdbRating;

    #[ORM\Column(name: 'created_at', type: 'datetime_immutable')]
    private DateTimeImmutable $createdAt;

    #[ORM\Column(name: 'updated_at', type: 'datetime_immutable')]
    private DateTimeImmutable $updatedAt;

    public function __construct(
        VideoId $id,
        string $title,
        string $description,
        int $releaseYear,
        ?ImdbRating $imdbRating = null
    ) {
        $this->id = $id->value();
        $this->title = $title;
        $this->description = $description;
        $this->releaseYear = $releaseYear;
        $this->imdbRating = $imdbRating?->value();
        $this->createdAt = new DateTimeImmutable();
        $this->updatedAt = new DateTimeImmutable();
    }

    public function getId(): VideoId
    {
        return VideoId::fromString($this->id);
    }

    public function getTitle(): string
    {
        return $this->title;
    }

    public function getDescription(): string
    {
        return $this->description;
    }

    public function getReleaseYear(): int
    {
        return $this->releaseYear;
    }

    public function getImdbRating(): ?ImdbRating
    {
        return $this->imdbRating !== null ? ImdbRating::fromFloat($this->imdbRating) : null;
    }

    public function getCreatedAt(): DateTimeImmutable
    {
        return $this->createdAt;
    }

    public function getUpdatedAt(): DateTimeImmutable
    {
        return $this->updatedAt;
    }

    public function updateRating(ImdbRating $rating): void
    {
        $this->imdbRating = $rating->value();
        $this->updatedAt = new DateTimeImmutable();
    }

    public function updateDetails(string $title, string $description, int $releaseYear): void
    {
        $this->title = $title;
        $this->description = $description;
        $this->releaseYear = $releaseYear;
        $this->updatedAt = new DateTimeImmutable();
    }

    public function isRecentRelease(): bool
    {
        return $this->releaseYear >= (int)date('Y') - 2;
    }
}
