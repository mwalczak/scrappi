<?php

declare(strict_types=1);

namespace App\Domain\NetflixVideo\ValueObject;

use InvalidArgumentException;

readonly class ImdbRating
{
    private function __construct(
        private float $value
    ) {
        if ($value < 0.0 || $value > 10.0) {
            throw new InvalidArgumentException('IMDB rating must be between 0.0 and 10.0');
        }
    }

    public static function fromFloat(float $value): self
    {
        return new self($value);
    }

    public function value(): float
    {
        return $this->value;
    }

    public function isHighRated(): bool
    {
        return $this->value >= 7.0;
    }

    public function equals(ImdbRating $other): bool
    {
        return abs($this->value - $other->value) < PHP_FLOAT_EPSILON;
    }
}
