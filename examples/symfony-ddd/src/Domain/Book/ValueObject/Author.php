<?php

declare(strict_types=1);

namespace App\Domain\Book\ValueObject;

use App\Domain\Book\Exception\InvalidAuthor;
use SolidFrame\Ddd\ValueObject\StringValueObject;

final readonly class Author extends StringValueObject
{
    public static function from(string $value): static
    {
        ($value !== '') or throw InvalidAuthor::empty();

        (mb_strlen($value) <= 255) or throw InvalidAuthor::tooLong($value);

        return parent::from($value);
    }
}
