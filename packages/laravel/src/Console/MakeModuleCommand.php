<?php

declare(strict_types=1);

namespace SolidFrame\Laravel\Console;

use Illuminate\Console\Command;
use Illuminate\Filesystem\Filesystem;

final class MakeModuleCommand extends Command
{
    protected $signature = 'make:module {name : The name of the module}';

    protected $description = 'Create a new module with ServiceProvider and Module class';

    public function handle(Filesystem $files): int
    {
        $name = $this->argument('name');
        $modulesPath = $this->laravel->basePath(config('solidframe.modular.path', 'modules'));
        $modulePath = $modulesPath . '/' . $name;

        if ($files->isDirectory($modulePath)) {
            $this->error("Module [{$name}] already exists.");

            return self::FAILURE;
        }

        $namespace = $this->laravel->getNamespace() . 'Modules\\' . $name;
        $moduleName = strtolower($name);

        // Create directory structure
        $files->makeDirectory($modulePath . '/Database/Migrations', 0o755, true);

        // Generate ServiceProvider
        $providerStub = $files->get(__DIR__ . '/../../stubs/module-service-provider.stub');
        $providerContent = str_replace(
            ['{{ namespace }}', '{{ class }}'],
            [$namespace, $name],
            $providerStub,
        );
        $files->put($modulePath . '/' . $name . 'ServiceProvider.php', $providerContent);

        // Generate Module class
        $moduleStub = $files->get(__DIR__ . '/../../stubs/module-class.stub');
        $moduleContent = str_replace(
            ['{{ namespace }}', '{{ class }}', '{{ moduleName }}'],
            [$namespace, $name, $moduleName],
            $moduleStub,
        );
        $files->put($modulePath . '/' . $name . 'Module.php', $moduleContent);

        $this->components->info("Module [{$name}] created successfully.");
        $this->components->bulletList([
            $modulePath . '/' . $name . 'ServiceProvider.php',
            $modulePath . '/' . $name . 'Module.php',
            $modulePath . '/Database/Migrations/',
        ]);

        return self::SUCCESS;
    }
}
