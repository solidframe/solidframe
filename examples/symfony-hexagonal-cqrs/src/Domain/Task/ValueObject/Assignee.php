<?php

declare(strict_types=1);

namespace App\Domain\Task\ValueObject;

use SolidFrame\Ddd\ValueObject\StringValueObject;

final readonly class Assignee extends StringValueObject
{
    public static function from(string $value): static
    {
        $value = trim($value);

        ($value !== '') or throw new \InvalidArgumentException('Assignee name cannot be empty.');

        return parent::from($value);
    }
}
