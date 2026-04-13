<?php

declare(strict_types=1);

namespace App\Domain\Task\ValueObject;

use App\Domain\Task\Exception\InvalidTaskTitle;
use SolidFrame\Ddd\ValueObject\StringValueObject;

final readonly class TaskTitle extends StringValueObject
{
    public static function from(string $value): static
    {
        $value = trim($value);

        ($value !== '') or throw InvalidTaskTitle::empty();
        (mb_strlen($value) <= 255) or throw InvalidTaskTitle::tooLong($value);

        return parent::from($value);
    }
}
