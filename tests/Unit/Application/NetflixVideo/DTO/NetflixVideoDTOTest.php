<?php

declare(strict_types=1);

namespace App\Tests\Unit\Application\NetflixVideo\DTO;

use App\Application\NetflixVideo\DTO\NetflixVideoDTO;
use App\Domain\NetflixVideo\Entity\NetflixVideo;
use App\Domain\NetflixVideo\ValueObject\ImdbId;
use App\Domain\NetflixVideo\ValueObject\ImdbRating;
use App\Domain\NetflixVideo\ValueObject\VideoId;
use DateTimeImmutable;
use PHPUnit\Framework\TestCase;

class NetflixVideoDTOTest extends TestCase
{
    public function testFromEntityWithAllFields(): void
    {
        $videoId = VideoId::generate();
        $imdbRating = ImdbRating::fromFloat(8.5);
        $imdbId = ImdbId::fromString('tt0111161');
        $createdAt = new DateTimeImmutable('2024-01-01 12:00:00');
        $updatedAt = new DateTimeImmutable('2024-01-02 12:00:00');

        $entity = new NetflixVideo(
            $videoId,
            'The Shawshank Redemption',
            'A great movie',
            1994,
            $imdbRating,
            $imdbId
        );

        // Use reflection to set timestamps for testing
        $reflection = new \ReflectionClass($entity);
        $createdAtProp = $reflection->getProperty('createdAt');
        $createdAtProp->setValue($entity, $createdAt);
        $updatedAtProp = $reflection->getProperty('updatedAt');
        $updatedAtProp->setValue($entity, $updatedAt);

        $dto = NetflixVideoDTO::fromEntity($entity);

        $this->assertEquals($videoId->value(), $dto->id);
        $this->assertEquals('The Shawshank Redemption', $dto->title);
        $this->assertEquals('A great movie', $dto->description);
        $this->assertEquals(1994, $dto->releaseYear);
        $this->assertEquals(8.5, $dto->imdbRating);
        $this->assertEquals('tt0111161', $dto->imdbId);
        $this->assertEquals('https://www.imdb.com/title/tt0111161/', $dto->imdbUrl);
        $this->assertEquals($createdAt, $dto->createdAt);
        $this->assertEquals($updatedAt, $dto->updatedAt);
    }

    public function testFromEntityWithNullImdbId(): void
    {
        $videoId = VideoId::generate();
        $imdbRating = ImdbRating::fromFloat(7.0);
        $createdAt = new DateTimeImmutable('2024-01-01 12:00:00');
        $updatedAt = new DateTimeImmutable('2024-01-02 12:00:00');

        $entity = new NetflixVideo(
            $videoId,
            'Some Movie',
            'A movie without IMDB ID',
            2024,
            $imdbRating,
            null
        );

        // Use reflection to set timestamps
        $reflection = new \ReflectionClass($entity);
        $createdAtProp = $reflection->getProperty('createdAt');
        $createdAtProp->setValue($entity, $createdAt);
        $updatedAtProp = $reflection->getProperty('updatedAt');
        $updatedAtProp->setValue($entity, $updatedAt);

        $dto = NetflixVideoDTO::fromEntity($entity);

        $this->assertEquals($videoId->value(), $dto->id);
        $this->assertEquals('Some Movie', $dto->title);
        $this->assertNull($dto->imdbId);
        $this->assertNull($dto->imdbUrl);
    }

    public function testFromEntityWithNullImdbRating(): void
    {
        $videoId = VideoId::generate();
        $imdbId = ImdbId::fromString('tt1234567');
        $createdAt = new DateTimeImmutable('2024-01-01 12:00:00');
        $updatedAt = new DateTimeImmutable('2024-01-02 12:00:00');

        $entity = new NetflixVideo(
            $videoId,
            'Movie Without Rating',
            'Description',
            2024,
            null,
            $imdbId
        );

        // Use reflection to set timestamps
        $reflection = new \ReflectionClass($entity);
        $createdAtProp = $reflection->getProperty('createdAt');
        $createdAtProp->setValue($entity, $createdAt);
        $updatedAtProp = $reflection->getProperty('updatedAt');
        $updatedAtProp->setValue($entity, $updatedAt);

        $dto = NetflixVideoDTO::fromEntity($entity);

        $this->assertNull($dto->imdbRating);
        $this->assertEquals('tt1234567', $dto->imdbId);
        $this->assertEquals('https://www.imdb.com/title/tt1234567/', $dto->imdbUrl);
    }

    public function testFromEntityWithAllNullOptionalFields(): void
    {
        $videoId = VideoId::generate();
        $createdAt = new DateTimeImmutable('2024-01-01 12:00:00');
        $updatedAt = new DateTimeImmutable('2024-01-02 12:00:00');

        $entity = new NetflixVideo(
            $videoId,
            'Minimal Movie',
            'Just the required fields',
            2024,
            null,
            null
        );

        // Use reflection to set timestamps
        $reflection = new \ReflectionClass($entity);
        $createdAtProp = $reflection->getProperty('createdAt');
        $createdAtProp->setValue($entity, $createdAt);
        $updatedAtProp = $reflection->getProperty('updatedAt');
        $updatedAtProp->setValue($entity, $updatedAt);

        $dto = NetflixVideoDTO::fromEntity($entity);

        $this->assertEquals($videoId->value(), $dto->id);
        $this->assertEquals('Minimal Movie', $dto->title);
        $this->assertNull($dto->imdbRating);
        $this->assertNull($dto->imdbId);
        $this->assertNull($dto->imdbUrl);
    }

    public function testImdbUrlIsGeneratedFromImdbId(): void
    {
        $videoId = VideoId::generate();
        $imdbId = ImdbId::fromString('tt0468569'); // The Dark Knight
        $createdAt = new DateTimeImmutable('2024-01-01 12:00:00');
        $updatedAt = new DateTimeImmutable('2024-01-02 12:00:00');

        $entity = new NetflixVideo(
            $videoId,
            'The Dark Knight',
            'Description',
            2008,
            null,
            $imdbId
        );

        // Use reflection to set timestamps
        $reflection = new \ReflectionClass($entity);
        $createdAtProp = $reflection->getProperty('createdAt');
        $createdAtProp->setValue($entity, $createdAt);
        $updatedAtProp = $reflection->getProperty('updatedAt');
        $updatedAtProp->setValue($entity, $updatedAt);

        $dto = NetflixVideoDTO::fromEntity($entity);

        // Verify imdbUrl is correctly generated from imdbId
        $this->assertEquals('https://www.imdb.com/title/tt0468569/', $dto->imdbUrl);
    }
}
