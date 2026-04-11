<?php

declare(strict_types=1);

namespace SolidFrame\Laravel\Console;

use Illuminate\Console\GeneratorCommand;

final class MakeEntityCommand extends GeneratorCommand
{
    protected $name = 'make:entity';

    protected $description = 'Create a new Entity class';

    protected $type = 'Entity';

    protected function getStub(): string
    {
        return __DIR__ . '/../../stubs/entity.stub';
    }

    protected function getDefaultNamespace($rootNamespace): string
    {
        return $rootNamespace . '\\Domain';
    }
}
