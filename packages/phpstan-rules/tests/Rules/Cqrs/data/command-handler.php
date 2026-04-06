<?php

declare(strict_types=1);

namespace SolidFrame\PHPStanRules\Tests\Rules\Cqrs\Fixtures;

interface TestCommandHandler {}

final class ValidCommandHandler implements TestCommandHandler
{
    public function __invoke(object $command): void {}
}

final class InvalidCommandHandler implements TestCommandHandler
{
    public function __invoke(object $command): string
    {
        return 'result';
    }
}
