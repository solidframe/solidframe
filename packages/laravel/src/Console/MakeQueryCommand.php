<?php

declare(strict_types=1);

namespace SolidFrame\Laravel\Console;

use Illuminate\Console\GeneratorCommand;
use Symfony\Component\Console\Input\InputOption;

final class MakeQueryCommand extends GeneratorCommand
{
    protected $name = 'make:query';

    protected $description = 'Create a new CQRS Query class';

    protected $type = 'Query';

    protected function getStub(): string
    {
        return __DIR__ . '/../../stubs/query.stub';
    }

    protected function getDefaultNamespace($rootNamespace): string
    {
        return $rootNamespace . '\\Application\\Query';
    }

    public function handle(): ?bool
    {
        $result = parent::handle();

        if ($result !== false && $this->option('handler')) {
            $this->call('make:query-handler', [
                'name' => $this->getNameInput() . 'Handler',
                '--query-class' => $this->qualifyClass($this->getNameInput()),
            ]);
        }

        return $result;
    }

    protected function getOptions(): array
    {
        return [
            ['handler', null, InputOption::VALUE_NONE, 'Also generate the corresponding QueryHandler'],
        ];
    }
}
