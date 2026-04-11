<?php

declare(strict_types=1);

namespace SolidFrame\Laravel\Console;

use Illuminate\Console\GeneratorCommand;
use Symfony\Component\Console\Input\InputOption;

final class MakeCommandHandlerCommand extends GeneratorCommand
{
    protected $name = 'make:command-handler';

    protected $description = 'Create a new CQRS Command Handler class';

    protected $type = 'Command Handler';

    protected function getStub(): string
    {
        return __DIR__ . '/../../stubs/command-handler.stub';
    }

    protected function getDefaultNamespace($rootNamespace): string
    {
        return $rootNamespace . '\\Application\\Command';
    }

    protected function buildClass($name): string
    {
        $stub = parent::buildClass($name);

        $commandClass = $this->option('command-class');

        if ($commandClass) {
            $shortName = class_basename($commandClass);

            $stub = str_replace(
                ['{{ command }}', 'use SolidFrame\Cqrs\CommandHandler;'],
                [$shortName, "use {$commandClass};\nuse SolidFrame\\Cqrs\\CommandHandler;"],
                $stub,
            );
        } else {
            $stub = str_replace('{{ command }}', 'object', $stub);
        }

        return $stub;
    }

    protected function getOptions(): array
    {
        return [
            ['command-class', null, InputOption::VALUE_REQUIRED, 'The FQCN of the command this handler handles'],
        ];
    }
}
