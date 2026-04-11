<?php

declare(strict_types=1);

namespace SolidFrame\Laravel\Console;

use Illuminate\Console\GeneratorCommand;

final class MakeAggregateRootCommand extends GeneratorCommand
{
    protected $name = 'make:aggregate-root';

    protected $description = 'Create a new Aggregate Root class';

    protected $type = 'Aggregate Root';

    protected function getStub(): string
    {
        return __DIR__ . '/../../stubs/aggregate-root.stub';
    }

    protected function getDefaultNamespace($rootNamespace): string
    {
        return $rootNamespace . '\\Domain';
    }
}
