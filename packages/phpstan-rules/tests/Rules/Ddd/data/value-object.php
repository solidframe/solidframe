<?php

declare(strict_types=1);

namespace SolidFrame\PHPStanRules\Tests\Rules\Ddd\Fixtures;

interface TestValueObject {}

final readonly class ValidValueObject implements TestValueObject
{
    public function __construct(public string $value) {}
}

final class InvalidNonReadonlyValueObject implements TestValueObject
{
    public string $mutableValue = '';

    public function __construct(public readonly string $value) {}
}
