<?php

declare(strict_types=1);

namespace App\Domain\Task\ValueObject;

use App\Domain\Task\Exception\InvalidTaskDescription;
use SolidFrame\Ddd\ValueObject\StringValueObject;

final readonly class TaskDescription extends StringValueObject
{
    public static function from(string $value): static
    {
        $value = trim($value);

        (mb_strlen($value) <= 1000) or throw InvalidTaskDescription::tooLong($value);

        return parent::from($value);
    }
}
