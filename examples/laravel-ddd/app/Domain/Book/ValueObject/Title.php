<?php

declare(strict_types=1);

namespace App\Domain\Book\ValueObject;

use App\Domain\Book\Exception\InvalidTitle;
use SolidFrame\Ddd\ValueObject\StringValueObject;

final readonly class Title extends StringValueObject
{
    public static function from(string $value): static
    {
        ($value !== '') or throw InvalidTitle::empty();

        (mb_strlen($value) <= 255) or throw InvalidTitle::tooLong($value);

        return parent::from($value);
    }
}
