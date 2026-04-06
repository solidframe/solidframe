<?php

declare(strict_types=1);

namespace SolidFrame\PHPStanRules\Tests\Rules\Cqrs\Fixtures;

interface TestCommand {}

final readonly class ValidCommand implements TestCommand
{
    public function __construct(public string $name) {}
}

class InvalidNonFinalCommand implements TestCommand
{
    public function __construct(public readonly string $name) {}
}
