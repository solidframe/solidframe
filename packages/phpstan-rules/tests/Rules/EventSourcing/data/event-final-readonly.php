<?php

declare(strict_types=1);

namespace SolidFrame\PHPStanRules\Tests\Rules\EventSourcing\Fixtures;

interface TestDomainEvent {}

final readonly class ValidEvent implements TestDomainEvent
{
    public function __construct(public string $id) {}
}

class InvalidNonFinalEvent implements TestDomainEvent
{
    public function __construct(public readonly string $id) {}
}
