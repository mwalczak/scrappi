<?php

declare(strict_types=1);

namespace App\Infrastructure\Persistence\Doctrine\Type;

use App\Domain\NetflixVideo\ValueObject\ImdbRating;
use Doctrine\DBAL\Platforms\AbstractPlatform;
use Doctrine\DBAL\Types\Type;

class ImdbRatingType extends Type
{
    public const NAME = 'imdb_rating';

    public function getSQLDeclaration(array $column, AbstractPlatform $platform): string
    {
        return $platform->getFloatDeclarationSQL($column);
    }

    public function convertToPHPValue(mixed $value, AbstractPlatform $platform): ?ImdbRating
    {
        if ($value === null) {
            return null;
        }

        return ImdbRating::fromFloat((float) $value);
    }

    public function convertToDatabaseValue(mixed $value, AbstractPlatform $platform): ?float
    {
        if ($value === null) {
            return null;
        }

        if (!$value instanceof ImdbRating) {
            throw new \InvalidArgumentException('Expected ImdbRating instance');
        }

        return $value->value();
    }

    public function getName(): string
    {
        return self::NAME;
    }

    public function requiresSQLCommentHint(AbstractPlatform $platform): bool
    {
        return true;
    }
}
