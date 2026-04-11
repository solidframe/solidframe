<?php

declare(strict_types=1);

namespace SolidFrame\Laravel\Console;

use Illuminate\Console\GeneratorCommand;
use Symfony\Component\Console\Input\InputOption;

final class MakeEventCommand extends GeneratorCommand
{
    protected $name = 'make:domain-event';

    protected $description = 'Create a new Domain Event class';

    protected $type = 'Domain Event';

    protected function getStub(): string
    {
        return __DIR__ . '/../../stubs/event.stub';
    }

    protected function getDefaultNamespace($rootNamespace): string
    {
        return $rootNamespace . '\\Domain\\Event';
    }

    public function handle(): ?bool
    {
        $result = parent::handle();

        if ($result !== false && $this->option('listener')) {
            $this->call('make:event-listener', [
                'name' => $this->getNameInput() . 'Listener',
                '--event-class' => $this->qualifyClass($this->getNameInput()),
            ]);
        }

        return $result;
    }

    protected function getOptions(): array
    {
        return [
            ['listener', null, InputOption::VALUE_NONE, 'Also generate a corresponding EventListener'],
        ];
    }
}
