<?php

declare(strict_types=1);

namespace SolidFrame\Laravel\Console;

use Illuminate\Console\GeneratorCommand;
use Symfony\Component\Console\Input\InputOption;

final class MakeEventListenerCommand extends GeneratorCommand
{
    protected $name = 'make:event-listener';

    protected $description = 'Create a new Event Listener class';

    protected $type = 'Event Listener';

    protected function getStub(): string
    {
        return __DIR__ . '/../../stubs/listener.stub';
    }

    protected function getDefaultNamespace($rootNamespace): string
    {
        return $rootNamespace . '\\Application\\Listener';
    }

    protected function buildClass($name): string
    {
        $stub = parent::buildClass($name);

        $eventClass = $this->option('event-class');

        if ($eventClass) {
            $shortName = class_basename($eventClass);

            $stub = str_replace(
                ['{{ event }}', 'use SolidFrame\EventDriven\EventListener;'],
                [$shortName, "use {$eventClass};\nuse SolidFrame\\EventDriven\\EventListener;"],
                $stub,
            );
        } else {
            $stub = str_replace('{{ event }}', 'object', $stub);
        }

        return $stub;
    }

    protected function getOptions(): array
    {
        return [
            ['event-class', null, InputOption::VALUE_REQUIRED, 'The FQCN of the event this listener handles'],
        ];
    }
}
