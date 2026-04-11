<?php

declare(strict_types=1);

namespace App\Domain\Book\ValueObject;

use App\Domain\Book\Exception\InvalidISBN;
use SolidFrame\Ddd\ValueObject\StringValueObject;

final readonly class ISBN extends StringValueObject
{
    private const ISBN_13_PATTERN = '/^\d{13}$/';

    public static function from(string $value): static
    {
        $cleaned = str_replace(['-', ' '], '', $value);

        (preg_match(self::ISBN_13_PATTERN, $cleaned) === 1)
            or throw InvalidISBN::malformed($value);

        self::validateCheckDigit($cleaned)
            or throw InvalidISBN::invalidCheckDigit($value);

        return parent::from($cleaned);
    }

    private static function validateCheckDigit(string $isbn): bool
    {
        $sum = 0;

        for ($i = 0; $i < 12; $i++) {
            $sum += (int) $isbn[$i] * ($i % 2 === 0 ? 1 : 3);
        }

        $checkDigit = (10 - ($sum % 10)) % 10;

        return $checkDigit === (int) $isbn[12];
    }
}
