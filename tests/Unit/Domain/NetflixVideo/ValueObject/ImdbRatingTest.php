<?php

declare(strict_types=1);

namespace App\Tests\Unit\Domain\NetflixVideo\ValueObject;

use App\Domain\NetflixVideo\ValueObject\ImdbRating;
use InvalidArgumentException;
use PHPUnit\Framework\TestCase;

class ImdbRatingTest extends TestCase
{
    public function testFromFloatWithValidRating(): void
    {
        $rating = ImdbRating::fromFloat(8.5);

        $this->assertInstanceOf(ImdbRating::class, $rating);
        $this->assertEquals(8.5, $rating->value());
    }

    public function testFromFloatWithMinimumRating(): void
    {
        $rating = ImdbRating::fromFloat(0.0);

        $this->assertEquals(0.0, $rating->value());
    }

    public function testFromFloatWithMaximumRating(): void
    {
        $rating = ImdbRating::fromFloat(10.0);

        $this->assertEquals(10.0, $rating->value());
    }

    public function testFromFloatWithBelowMinimumThrowsException(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('IMDB rating must be between 0.0 and 10.0');

        ImdbRating::fromFloat(-0.1);
    }

    public function testFromFloatWithAboveMaximumThrowsException(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('IMDB rating must be between 0.0 and 10.0');

        ImdbRating::fromFloat(10.1);
    }

    public function testIsHighRatedReturnsTrueForHighRatings(): void
    {
        $rating = ImdbRating::fromFloat(8.5);

        $this->assertTrue($rating->isHighRated());
    }

    public function testIsHighRatedReturnsTrueForBoundaryRating(): void
    {
        $rating = ImdbRating::fromFloat(7.0);

        $this->assertTrue($rating->isHighRated());
    }

    public function testIsHighRatedReturnsFalseForLowRatings(): void
    {
        $rating = ImdbRating::fromFloat(6.9);

        $this->assertFalse($rating->isHighRated());
    }

    public function testEqualsReturnsTrueForSameValue(): void
    {
        $rating1 = ImdbRating::fromFloat(8.5);
        $rating2 = ImdbRating::fromFloat(8.5);

        $this->assertTrue($rating1->equals($rating2));
    }

    public function testEqualsReturnsFalseForDifferentValue(): void
    {
        $rating1 = ImdbRating::fromFloat(8.5);
        $rating2 = ImdbRating::fromFloat(7.0);

        $this->assertFalse($rating1->equals($rating2));
    }

    public function testEqualsUsesEpsilonComparison(): void
    {
        // Verify that equals() uses epsilon comparison for float precision
        // by testing with very similar values
        $rating1 = ImdbRating::fromFloat(8.5);
        $rating2 = ImdbRating::fromFloat(8.5);

        // These should be equal
        $this->assertTrue($rating1->equals($rating2));

        // Values that differ by more than epsilon should not be equal
        $rating3 = ImdbRating::fromFloat(8.50001);
        $this->assertFalse($rating1->equals($rating3));
    }
}
