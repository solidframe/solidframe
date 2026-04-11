<?php

declare(strict_types=1);

namespace SolidFrame\Laravel\Console;

use Illuminate\Console\GeneratorCommand;
use Symfony\Component\Console\Input\InputOption;

final class MakeCommandCommand extends GeneratorCommand
{
    protected $name = 'make:cqrs-command';

    protected $description = 'Create a new CQRS Command class';

    protected $type = 'Command';

    protected function getStub(): string
    {
        return __DIR__ . '/../../stubs/command.stub';
    }

    protected function getDefaultNamespace($rootNamespace): string
    {
        return $rootNamespace . '\\Application\\Command';
    }

    public function handle(): ?bool
    {
        $result = parent::handle();

        if ($result !== false && $this->option('handler')) {
            $this->call('make:command-handler', [
                'name' => $this->getNameInput() . 'Handler',
                '--command-class' => $this->qualifyClass($this->getNameInput()),
            ]);
        }

        return $result;
    }

    protected function getOptions(): array
    {
        return [
            ['handler', null, InputOption::VALUE_NONE, 'Also generate the corresponding CommandHandler'],
        ];
    }
}
