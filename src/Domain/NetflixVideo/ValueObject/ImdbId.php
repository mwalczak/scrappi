<?php

declare(strict_types=1);

namespace App\Domain\NetflixVideo\ValueObject;

use InvalidArgumentException;

readonly class ImdbId
{
    private function __construct(
        private string $value
    ) {
        if (!preg_match('/^tt\d{7,8}$/', $value)) {
            throw new InvalidArgumentException('IMDB ID must be in format ttXXXXXXX or ttXXXXXXXX');
        }
    }

    public static function fromString(string $value): self
    {
        return new self($value);
    }

    public function value(): string
    {
        return $this->value;
    }

    public function equals(ImdbId $other): bool
    {
        return $this->value === $other->value;
    }

    public function toUrl(): string
    {
        return sprintf('https://www.imdb.com/title/%s/', $this->value);
    }

    public function __toString(): string
    {
        return $this->value;
    }
}
