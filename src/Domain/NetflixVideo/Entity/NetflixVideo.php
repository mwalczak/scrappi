<?php

declare(strict_types=1);

namespace App\Domain\NetflixVideo\Entity;

use App\Domain\NetflixVideo\ValueObject\ImdbId;
use App\Domain\NetflixVideo\ValueObject\ImdbRating;
use App\Domain\NetflixVideo\ValueObject\VideoId;
use DateTimeImmutable;

class NetflixVideo
{
    public VideoId $id {
        get {
            return $this->id;
        }
    }
    public string $title {
        get {
            return $this->title;
        }
    }
    public string $description {
        get {
            return $this->description;
        }
    }
    public int $releaseYear {
        get {
            return $this->releaseYear;
        }
    }
    public ?ImdbRating $imdbRating {
        get {
            return $this->imdbRating;
        }
    }
    public ?ImdbId $imdbId {
        get {
            return $this->imdbId;
        }
    }
    public DateTimeImmutable $createdAt {
        get {
            return $this->createdAt;
        }
    }
    public DateTimeImmutable $updatedAt {
        get {
            return $this->updatedAt;
        }
    }

    public function __construct(
        VideoId $id,
        string $title,
        string $description,
        int $releaseYear,
        ?ImdbRating $imdbRating = null,
        ?ImdbId $imdbId = null
    ) {
        $this->id = $id;
        $this->title = $title;
        $this->description = $description;
        $this->releaseYear = $releaseYear;
        $this->imdbRating = $imdbRating;
        $this->imdbId = $imdbId;
        $this->createdAt = new DateTimeImmutable();
        $this->updatedAt = new DateTimeImmutable();
    }

    public function updateRating(ImdbRating $rating): void
    {
        $this->imdbRating = $rating;
        $this->updatedAt = new DateTimeImmutable();
    }

    public function updateImdbId(ImdbId $imdbId): void
    {
        $this->imdbId = $imdbId;
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
