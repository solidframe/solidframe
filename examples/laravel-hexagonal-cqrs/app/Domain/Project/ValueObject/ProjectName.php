<?php

declare(strict_types=1);

namespace App\Domain\Project\ValueObject;

use App\Domain\Project\Exception\InvalidProjectName;
use SolidFrame\Ddd\ValueObject\StringValueObject;

final readonly class ProjectName extends StringValueObject
{
    public static function from(string $value): static
    {
        $value = trim($value);

        ($value !== '') or throw InvalidProjectName::empty();
        (mb_strlen($value) <= 100) or throw InvalidProjectName::tooLong($value);

        return parent::from($value);
    }
}
