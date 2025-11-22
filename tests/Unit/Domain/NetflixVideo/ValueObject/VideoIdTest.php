<?php

declare(strict_types=1);

namespace App\Tests\Unit\Domain\NetflixVideo\ValueObject;

use App\Domain\NetflixVideo\ValueObject\VideoId;
use InvalidArgumentException;
use PHPUnit\Framework\TestCase;

class VideoIdTest extends TestCase
{
    public function testGenerateCreatesValidVideoId(): void
    {
        $videoId = VideoId::generate();

        $this->assertMatchesRegularExpression(
            '/^[0-9a-f]{8}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{12}$/i',
            $videoId->value()
        );
    }

    public function testFromStringWithValidUuid(): void
    {
        $uuid = '550e8400-e29b-41d4-a716-446655440000';
        $videoId = VideoId::fromString($uuid);

        $this->assertEquals($uuid, $videoId->value());
    }

    public function testFromStringWithInvalidUuidThrowsException(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Invalid UUID format: invalid-uuid');

        VideoId::fromString('invalid-uuid');
    }

    public function testFromStringWithEmptyStringThrowsException(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Invalid UUID format: ');

        VideoId::fromString('');
    }

    public function testEqualsReturnsTrueForSameValue(): void
    {
        $uuid = '550e8400-e29b-41d4-a716-446655440000';
        $videoId1 = VideoId::fromString($uuid);
        $videoId2 = VideoId::fromString($uuid);

        $this->assertTrue($videoId1->equals($videoId2));
    }

    public function testEqualsReturnsFalseForDifferentValue(): void
    {
        $videoId1 = VideoId::fromString('550e8400-e29b-41d4-a716-446655440000');
        $videoId2 = VideoId::fromString('6ba7b810-9dad-11d1-80b4-00c04fd430c8');

        $this->assertFalse($videoId1->equals($videoId2));
    }

    public function testToStringReturnsUuidValue(): void
    {
        $uuid = '550e8400-e29b-41d4-a716-446655440000';
        $videoId = VideoId::fromString($uuid);

        $this->assertEquals($uuid, (string) $videoId);
    }
}
