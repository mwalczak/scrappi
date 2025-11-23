<?php

declare(strict_types=1);

namespace App\Domain\NetflixVideo\ValueObject;

use InvalidArgumentException;

readonly class Country
{
    private function __construct(
        private string $code
    ) {
        if (strlen($code) !== 2) {
            throw new InvalidArgumentException('Country code must be 2 characters (ISO 3166-1 alpha-2)');
        }

        $code = strtoupper($code);
        if (!preg_match('/^[A-Z]{2}$/', $code)) {
            throw new InvalidArgumentException('Country code must contain only letters');
        }
    }

    public static function fromString(string $code): self
    {
        return new self($code);
    }

    public function code(): string
    {
        return $this->code;
    }

    public function equals(Country $other): bool
    {
        return $this->code === $other->code;
    }

    public function __toString(): string
    {
        return $this->code;
    }
}
