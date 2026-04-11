<?php

declare(strict_types=1);

namespace SolidFrame\Laravel\Modular;

use Illuminate\Support\ServiceProvider;
use ReflectionClass;
use SolidFrame\Modular\Module\ModuleInterface;

abstract class ModuleServiceProvider extends ServiceProvider
{
    abstract public function module(): ModuleInterface;

    /**
     * Base path of the module directory.
     * Override this if your module lives in a non-standard location.
     */
    public function modulePath(): string
    {
        $reflection = new ReflectionClass(static::class);

        return dirname($reflection->getFileName());
    }

    public function boot(): void
    {
        $this->bootRoutes();
        $this->bootMigrations();
        $this->bootConfig();
    }

    protected function bootRoutes(): void
    {
        $routesPath = $this->modulePath() . '/routes.php';

        if (file_exists($routesPath)) {
            $this->loadRoutesFrom($routesPath);
        }
    }

    protected function bootMigrations(): void
    {
        $migrationsPath = $this->modulePath() . '/Database/Migrations';

        if (is_dir($migrationsPath)) {
            $this->loadMigrationsFrom($migrationsPath);
        }
    }

    protected function bootConfig(): void
    {
        $configPath = $this->modulePath() . '/config.php';
        $moduleName = $this->module()->name();

        if (file_exists($configPath)) {
            $this->mergeConfigFrom($configPath, "modules.{$moduleName}");
        }
    }
}
