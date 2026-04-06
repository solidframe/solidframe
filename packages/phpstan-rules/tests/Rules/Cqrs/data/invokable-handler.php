<?php

declare(strict_types=1);

namespace SolidFrame\PHPStanRules\Tests\Rules\Cqrs\Fixtures;

interface TestHandler {}

final class ValidInvokableHandler implements TestHandler
{
    public function __construct() {}

    public function __invoke(): void {}
}

final class InvalidMultiMethodHandler implements TestHandler
{
    public function __invoke(): void {}

    public function extra(): void {}
}

final class InvalidNonInvokableHandler implements TestHandler
{
    public function handle(): void {}
}
