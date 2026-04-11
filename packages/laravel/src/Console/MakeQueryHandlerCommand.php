<?php

declare(strict_types=1);

namespace SolidFrame\Laravel\Console;

use Illuminate\Console\GeneratorCommand;
use Symfony\Component\Console\Input\InputOption;

final class MakeQueryHandlerCommand extends GeneratorCommand
{
    protected $name = 'make:query-handler';

    protected $description = 'Create a new CQRS Query Handler class';

    protected $type = 'Query Handler';

    protected function getStub(): string
    {
        return __DIR__ . '/../../stubs/query-handler.stub';
    }

    protected function getDefaultNamespace($rootNamespace): string
    {
        return $rootNamespace . '\\Application\\Query';
    }

    protected function buildClass($name): string
    {
        $stub = parent::buildClass($name);

        $queryClass = $this->option('query-class');

        if ($queryClass) {
            $shortName = class_basename($queryClass);

            $stub = str_replace(
                ['{{ query }}', 'use SolidFrame\Cqrs\QueryHandler;'],
                [$shortName, "use {$queryClass};\nuse SolidFrame\\Cqrs\\QueryHandler;"],
                $stub,
            );
        } else {
            $stub = str_replace('{{ query }}', 'object', $stub);
        }

        return $stub;
    }

    protected function getOptions(): array
    {
        return [
            ['query-class', null, InputOption::VALUE_REQUIRED, 'The FQCN of the query this handler handles'],
        ];
    }
}
