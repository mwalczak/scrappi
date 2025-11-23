<?php

declare(strict_types=1);

namespace App\Tests\Factory;

use App\Domain\NetflixVideo\Entity\NetflixVideo;
use App\Domain\NetflixVideo\ValueObject\ImdbId;
use App\Domain\NetflixVideo\ValueObject\ImdbRating;
use App\Domain\NetflixVideo\ValueObject\VideoId;
use Zenstruck\Foundry\Persistence\PersistentObjectFactory;

/**
 * @extends PersistentObjectFactory<NetflixVideo>
 */
final class NetflixVideoFactory extends PersistentObjectFactory
{
    public static function class(): string
    {
        return NetflixVideo::class;
    }

    /**
     * @return array<string, mixed>
     */
    protected function defaults(): array
    {
        return [
            'id' => VideoId::generate(),
            'title' => self::faker()->words(3, true),
            'description' => self::faker()->paragraph(),
            'releaseYear' => self::faker()->numberBetween(2000, 2024),
            'imdbRating' => ImdbRating::fromFloat(
                self::faker()->randomFloat(1, 1.0, 10.0)
            ),
            'imdbId' => ImdbId::fromString(
                'tt' . str_pad((string) self::faker()->numberBetween(1000000, 9999999), 7, '0', STR_PAD_LEFT)
            ),
        ];
    }

    protected function initialize(): static
    {
        return $this;
    }

    /**
     * @param array<string, mixed> $attributes
     */
    protected static function instantiate(array $attributes): NetflixVideo
    {
        // Type assertions for PHPStan Level 9
        assert(isset($attributes['id']) && $attributes['id'] instanceof VideoId);
        assert(isset($attributes['title']) && is_string($attributes['title']));
        assert(isset($attributes['description']) && is_string($attributes['description']));
        assert(isset($attributes['releaseYear']) && is_int($attributes['releaseYear']));

        $imdbRating = $attributes['imdbRating'] ?? null;
        assert($imdbRating === null || $imdbRating instanceof ImdbRating);

        $imdbId = $attributes['imdbId'] ?? null;
        assert($imdbId === null || $imdbId instanceof ImdbId);

        return new NetflixVideo(
            $attributes['id'],
            $attributes['title'],
            $attributes['description'],
            $attributes['releaseYear'],
            $imdbRating,
            $imdbId
        );
    }

    /**
     * Create a Netflix video with specific title
     */
    public function withTitle(string $title): static
    {
        return $this->with(['title' => $title]);
    }

    /**
     * Create a Netflix video with specific release year
     */
    public function withReleaseYear(int $year): static
    {
        return $this->with(['releaseYear' => $year]);
    }

    /**
     * Create a Netflix video with specific IMDB rating
     */
    public function withRating(float $rating): static
    {
        return $this->with(['imdbRating' => ImdbRating::fromFloat($rating)]);
    }

    /**
     * Create a Netflix video without rating
     */
    public function withoutRating(): static
    {
        return $this->with(['imdbRating' => null]);
    }

    /**
     * Create a high-rated video (>= 8.0)
     */
    public function highRated(): static
    {
        return $this->with([
            'imdbRating' => ImdbRating::fromFloat(
                self::faker()->randomFloat(1, 8.0, 10.0)
            ),
        ]);
    }

    /**
     * Create a Netflix video with specific IMDB ID
     */
    public function withImdbId(string $imdbId): static
    {
        return $this->with(['imdbId' => ImdbId::fromString($imdbId)]);
    }

    /**
     * Create a Netflix video without IMDB ID
     */
    public function withoutImdbId(): static
    {
        return $this->with(['imdbId' => null]);
    }
}
