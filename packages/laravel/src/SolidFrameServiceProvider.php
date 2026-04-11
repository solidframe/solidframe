<?php

declare(strict_types=1);

namespace SolidFrame\Laravel;

use Illuminate\Support\ServiceProvider;
use SolidFrame\Laravel\Cqrs\ContainerHandlerResolver;
use SolidFrame\Laravel\Discovery\HandlerDiscovery;
use SolidFrame\Laravel\EventDriven\ContainerListenerResolver;
use SolidFrame\Laravel\Modular\ModuleDiscovery;
use SolidFrame\Laravel\Modular\ModuleServiceProvider;

final class SolidFrameServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->mergeConfigFrom(__DIR__ . '/../config/solidframe.php', 'solidframe');

        $this->registerCqrs();
        $this->registerEventDriven();
        $this->registerEventSourcing();
        $this->registerModular();
        $this->registerSaga();
    }

    public function boot(): void
    {
        if ($this->app->runningInConsole()) {
            $this->publishes([
                __DIR__ . '/../config/solidframe.php' => config_path('solidframe.php'),
            ], 'solidframe-config');

            $this->publishes([
                __DIR__ . '/../database/migrations' => database_path('migrations'),
            ], 'solidframe-migrations');

            $this->registerCommands();
        }

        $this->bootModules();
    }

    private function registerCommands(): void
    {
        $commands = [];

        // DDD commands (always available — solidframe/core is required)
        $commands[] = Console\MakeEntityCommand::class;
        $commands[] = Console\MakeValueObjectCommand::class;
        $commands[] = Console\MakeAggregateRootCommand::class;

        // CQRS commands
        if (class_exists(\SolidFrame\Cqrs\Bus\CommandBus::class)) {
            $commands[] = Console\MakeCommandCommand::class;
            $commands[] = Console\MakeCommandHandlerCommand::class;
            $commands[] = Console\MakeQueryCommand::class;
            $commands[] = Console\MakeQueryHandlerCommand::class;
        }

        // Event-Driven commands
        if (class_exists(\SolidFrame\EventDriven\Bus\EventBus::class)) {
            $commands[] = Console\MakeEventCommand::class;
            $commands[] = Console\MakeEventListenerCommand::class;
        }

        // Saga commands
        if (class_exists(\SolidFrame\Saga\Store\InMemorySagaStore::class)) {
            $commands[] = Console\MakeSagaCommand::class;
            $commands[] = Console\SagaStatusCommand::class;
        }

        // Modular commands
        if (class_exists(\SolidFrame\Modular\Registry\InMemoryModuleRegistry::class)) {
            $commands[] = Console\MakeModuleCommand::class;
            $commands[] = Console\ModuleListCommand::class;
        }

        $this->commands($commands);
    }

    private function registerCqrs(): void
    {
        if (! class_exists(\SolidFrame\Cqrs\Bus\CommandBus::class)) {
            return;
        }

        $this->app->singleton(
            \SolidFrame\Core\Bus\CommandBusInterface::class,
            function (): \SolidFrame\Cqrs\Bus\CommandBus {
                $handlers = $this->discoverHandlers(\SolidFrame\Cqrs\CommandHandler::class);

                return new \SolidFrame\Cqrs\Bus\CommandBus(
                    new ContainerHandlerResolver($this->app, $handlers),
                    $this->resolveMiddleware(config('solidframe.cqrs.command_bus.middleware', [])),
                );
            },
        );

        $this->app->singleton(
            \SolidFrame\Core\Bus\QueryBusInterface::class,
            function (): \SolidFrame\Cqrs\Bus\QueryBus {
                $handlers = $this->discoverHandlers(\SolidFrame\Cqrs\QueryHandler::class);

                return new \SolidFrame\Cqrs\Bus\QueryBus(
                    new ContainerHandlerResolver($this->app, $handlers),
                    $this->resolveMiddleware(config('solidframe.cqrs.query_bus.middleware', [])),
                );
            },
        );
    }

    private function registerEventDriven(): void
    {
        if (! class_exists(\SolidFrame\EventDriven\Bus\EventBus::class)) {
            return;
        }

        $this->app->singleton(
            \SolidFrame\Core\Bus\EventBusInterface::class,
            function (): \SolidFrame\EventDriven\Bus\EventBus {
                $listeners = $this->discoverListeners();

                return new \SolidFrame\EventDriven\Bus\EventBus(
                    new ContainerListenerResolver($this->app, $listeners),
                    $this->resolveMiddleware(config('solidframe.event_driven.event_bus.middleware', [])),
                );
            },
        );
    }

    private function registerEventSourcing(): void
    {
        if (! class_exists(\SolidFrame\EventSourcing\Store\InMemoryEventStore::class)) {
            return;
        }

        $this->app->singleton(
            \SolidFrame\EventSourcing\Store\EventStoreInterface::class,
            function (): \SolidFrame\Laravel\EventSourcing\DatabaseEventStore|\SolidFrame\EventSourcing\Store\InMemoryEventStore {
                $driver = config('solidframe.event_sourcing.event_store.driver', 'database');

                if ($driver === 'database') {
                    return new \SolidFrame\Laravel\EventSourcing\DatabaseEventStore(
                        $this->app->make(\Illuminate\Database\DatabaseManager::class),
                        config('solidframe.event_sourcing.event_store.table', 'event_store'),
                        config('solidframe.event_sourcing.event_store.connection'),
                    );
                }

                return new \SolidFrame\EventSourcing\Store\InMemoryEventStore();
            },
        );

        $this->app->singleton(
            \SolidFrame\EventSourcing\Snapshot\SnapshotStoreInterface::class,
            function (): \SolidFrame\Laravel\EventSourcing\DatabaseSnapshotStore|\SolidFrame\EventSourcing\Snapshot\InMemorySnapshotStore {
                $driver = config('solidframe.event_sourcing.snapshot_store.driver', 'database');

                if ($driver === 'database') {
                    return new \SolidFrame\Laravel\EventSourcing\DatabaseSnapshotStore(
                        $this->app->make(\Illuminate\Database\DatabaseManager::class),
                        config('solidframe.event_sourcing.snapshot_store.table', 'snapshots'),
                        config('solidframe.event_sourcing.snapshot_store.connection'),
                    );
                }

                return new \SolidFrame\EventSourcing\Snapshot\InMemorySnapshotStore();
            },
        );
    }

    private function registerModular(): void
    {
        if (! class_exists(\SolidFrame\Modular\Registry\InMemoryModuleRegistry::class)) {
            return;
        }

        $this->app->singleton(
            \SolidFrame\Modular\Registry\ModuleRegistryInterface::class,
            \SolidFrame\Modular\Registry\InMemoryModuleRegistry::class,
        );
    }

    private function registerSaga(): void
    {
        if (! class_exists(\SolidFrame\Saga\Store\InMemorySagaStore::class)) {
            return;
        }

        $this->app->singleton(
            \SolidFrame\Saga\Store\SagaStoreInterface::class,
            function (): \SolidFrame\Laravel\Saga\DatabaseSagaStore|\SolidFrame\Saga\Store\InMemorySagaStore {
                $driver = config('solidframe.saga.store.driver', 'database');

                if ($driver === 'database') {
                    return new \SolidFrame\Laravel\Saga\DatabaseSagaStore(
                        $this->app->make(\Illuminate\Database\DatabaseManager::class),
                        config('solidframe.saga.store.table', 'sagas'),
                        config('solidframe.saga.store.connection'),
                    );
                }

                return new \SolidFrame\Saga\Store\InMemorySagaStore();
            },
        );
    }

    /**
     * @param class-string $markerInterface
     * @return array<class-string, class-string>
     */
    private function discoverHandlers(string $markerInterface): array
    {
        if (! config('solidframe.discovery.enabled', true)) {
            return [];
        }

        $paths = $this->discoveryPaths();

        if ($paths === []) {
            return [];
        }

        return HandlerDiscovery::within($paths, $markerInterface);
    }

    /**
     * @return array<class-string, list<class-string>>
     */
    private function discoverListeners(): array
    {
        if (! config('solidframe.discovery.enabled', true)) {
            return [];
        }

        if (! class_exists(\SolidFrame\EventDriven\EventListener::class)) {
            return [];
        }

        $paths = $this->discoveryPaths();

        if ($paths === []) {
            return [];
        }

        return HandlerDiscovery::listeners($paths, \SolidFrame\EventDriven\EventListener::class);
    }

    /**
     * @return list<string>
     */
    private function discoveryPaths(): array
    {
        $basePath = $this->app->basePath();
        $configPaths = config('solidframe.discovery.paths', ['app']);

        return array_filter(
            array_map(
                static fn(string $path): string => $basePath . '/' . $path,
                $configPaths,
            ),
            is_dir(...),
        );
    }

    private function bootModules(): void
    {
        if (! class_exists(\SolidFrame\Modular\Registry\InMemoryModuleRegistry::class)) {
            return;
        }

        if (! config('solidframe.modular.auto_discovery', true)) {
            return;
        }

        $modulesPath = $this->app->basePath(config('solidframe.modular.path', 'modules'));
        $namespace = $this->app->getNamespace() . 'Modules';

        $providerClasses = ModuleDiscovery::within($modulesPath, rtrim($namespace, '\\'));

        if ($providerClasses === []) {
            return;
        }

        $registry = $this->app->make(\SolidFrame\Modular\Registry\ModuleRegistryInterface::class);

        // First pass: register all modules in the registry
        $providers = [];
        foreach ($providerClasses as $providerClass) {
            /** @var ModuleServiceProvider $provider */
            $provider = $this->app->register($providerClass);
            $providers[] = $provider;
            $registry->register($provider->module());
        }

        // Boot order is handled by Laravel's own provider boot sequence
        // The registry tracks dependencies for validation and listing
    }

    /**
     * @param list<class-string> $middlewareClasses
     * @return list<\SolidFrame\Core\Middleware\MiddlewareInterface>
     */
    private function resolveMiddleware(array $middlewareClasses): array
    {
        return array_map(
            fn(string $class) => $this->app->make($class),
            $middlewareClasses,
        );
    }
}
