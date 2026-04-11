<?php

declare(strict_types=1);

namespace SolidFrame\Laravel\Console;

use Illuminate\Console\GeneratorCommand;

final class MakeValueObjectCommand extends GeneratorCommand
{
    protected $name = 'make:value-object';

    protected $description = 'Create a new Value Object class';

    protected $type = 'Value Object';

    protected function getStub(): string
    {
        return __DIR__ . '/../../stubs/value-object.stub';
    }

    protected function getDefaultNamespace($rootNamespace): string
    {
        return $rootNamespace . '\\Domain';
    }
}
