<?php

declare(strict_types=1);

namespace App\Domain\NetflixVideo\ValueObject;

use InvalidArgumentException;
use Ramsey\Uuid\Uuid;

readonly class VideoId
{
    private function __construct(
        private string $value
    ) {
    }

    public static function generate(): self
    {
        return new self(Uuid::uuid4()->toString());
    }

    public static function fromString(string $value): self
    {
        if (!Uuid::isValid($value)) {
            throw new InvalidArgumentException(
                sprintf('Invalid UUID format: %s', $value)
            );
        }
        return new self($value);
    }

    public function value(): string
    {
        return $this->value;
    }

    public function equals(VideoId $other): bool
    {
        return $this->value === $other->value;
    }

    public function __toString(): string
    {
        return $this->value;
    }
}
