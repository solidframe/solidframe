<?php

declare(strict_types=1);

namespace Modules\Order\Domain\ValueObject;

use SolidFrame\Ddd\ValueObject\StringValueObject;

final readonly class CustomerEmail extends StringValueObject
{
    public static function from(string $value): static
    {
        if (!filter_var($value, FILTER_VALIDATE_EMAIL)) {
            throw new \InvalidArgumentException("Invalid email: {$value}");
        }

        return new static($value);
    }
}
