<?php

declare(strict_types=1);

namespace SolidFrame\PHPStanRules\Tests\Rules\Cqrs\Fixtures;

interface TestQueryHandler {}

final class ValidQueryHandler implements TestQueryHandler
{
    public function __invoke(object $query): mixed
    {
        return [];
    }
}

final class InvalidQueryHandler implements TestQueryHandler
{
    public function __invoke(object $query): void {}
}
