<?php

declare(strict_types=1);

namespace App\Tests\Unit\Domain\NetflixVideo\ValueObject;

use App\Domain\NetflixVideo\ValueObject\ImdbId;
use InvalidArgumentException;
use PHPUnit\Framework\TestCase;

class ImdbIdTest extends TestCase
{
    public function testFromStringWithValidSevenDigitId(): void
    {
        $imdbId = ImdbId::fromString('tt1234567');

        $this->assertEquals('tt1234567', $imdbId->value());
    }

    public function testFromStringWithValidEightDigitId(): void
    {
        $imdbId = ImdbId::fromString('tt12345678');

        $this->assertEquals('tt12345678', $imdbId->value());
    }

    public function testFromStringWithInvalidFormatThrowsException(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('IMDB ID must be in format ttXXXXXXX or ttXXXXXXXX');

        ImdbId::fromString('invalid');
    }

    public function testFromStringWithMissingPrefixThrowsException(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('IMDB ID must be in format ttXXXXXXX or ttXXXXXXXX');

        ImdbId::fromString('1234567');
    }

    public function testFromStringWithTooFewDigitsThrowsException(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('IMDB ID must be in format ttXXXXXXX or ttXXXXXXXX');

        ImdbId::fromString('tt123456');
    }

    public function testFromStringWithTooManyDigitsThrowsException(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('IMDB ID must be in format ttXXXXXXX or ttXXXXXXXX');

        ImdbId::fromString('tt123456789');
    }

    public function testFromStringWithNonNumericDigitsThrowsException(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('IMDB ID must be in format ttXXXXXXX or ttXXXXXXXX');

        ImdbId::fromString('tt12345ab');
    }

    public function testToUrlGeneratesCorrectUrl(): void
    {
        $imdbId = ImdbId::fromString('tt0111161');

        $this->assertEquals('https://www.imdb.com/title/tt0111161/', $imdbId->toUrl());
    }

    public function testToUrlWithEightDigitId(): void
    {
        $imdbId = ImdbId::fromString('tt12345678');

        $this->assertEquals('https://www.imdb.com/title/tt12345678/', $imdbId->toUrl());
    }

    public function testEqualsReturnsTrueForSameValue(): void
    {
        $imdbId1 = ImdbId::fromString('tt0111161');
        $imdbId2 = ImdbId::fromString('tt0111161');

        $this->assertTrue($imdbId1->equals($imdbId2));
    }

    public function testEqualsReturnsFalseForDifferentValue(): void
    {
        $imdbId1 = ImdbId::fromString('tt0111161');
        $imdbId2 = ImdbId::fromString('tt1234567');

        $this->assertFalse($imdbId1->equals($imdbId2));
    }

    public function testToStringReturnsValue(): void
    {
        $imdbId = ImdbId::fromString('tt0111161');

        $this->assertEquals('tt0111161', (string) $imdbId);
    }

    public function testRealWorldImdbIds(): void
    {
        // Test with real IMDB IDs from popular movies
        $shawshank = ImdbId::fromString('tt0111161'); // The Shawshank Redemption
        $darkKnight = ImdbId::fromString('tt0468569'); // The Dark Knight
        $inception = ImdbId::fromString('tt1375666'); // Inception

        $this->assertEquals('tt0111161', $shawshank->value());
        $this->assertEquals('tt0468569', $darkKnight->value());
        $this->assertEquals('tt1375666', $inception->value());
    }
}
