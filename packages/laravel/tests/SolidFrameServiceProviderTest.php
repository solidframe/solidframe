<?php

declare(strict_types=1);

namespace SolidFrame\Laravel\Tests;

use Orchestra\Testbench\TestCase;
use PHPUnit\Framework\Attributes\Test;
use SolidFrame\Core\Bus\CommandBusInterface;
use SolidFrame\Core\Bus\EventBusInterface;
use SolidFrame\Core\Bus\QueryBusInterface;
use SolidFrame\Cqrs\Bus\CommandBus;
use SolidFrame\Cqrs\Bus\QueryBus;
use SolidFrame\EventDriven\Bus\EventBus;
use SolidFrame\EventSourcing\Snapshot\SnapshotStoreInterface;
use SolidFrame\EventSourcing\Store\EventStoreInterface;
use SolidFrame\Laravel\SolidFrameServiceProvider;
use SolidFrame\Modular\Registry\ModuleRegistryInterface;
use SolidFrame\Saga\Store\SagaStoreInterface;

final class SolidFrameServiceProviderTest extends TestCase
{
    protected function getPackageProviders($app): array
    {
        return [SolidFrameServiceProvider::class];
    }

    // -- Config --

    #[Test]
    public function registersConfig(): void
    {
        $config = $this->app['config']->get('solidframe');

        self::assertIsArray($config);
        self::assertArrayHasKey('discovery', $config);
        self::assertArrayHasKey('cqrs', $config);
        self::assertArrayHasKey('event_driven', $config);
        self::assertArrayHasKey('event_sourcing', $config);
        self::assertArrayHasKey('saga', $config);
        self::assertArrayHasKey('modular', $config);
    }

    #[Test]
    public function discoveryIsEnabledByDefault(): void
    {
        self::assertTrue($this->app['config']->get('solidframe.discovery.enabled'));
    }

    #[Test]
    public function defaultDiscoveryPathIsApp(): void
    {
        self::assertSame(['app'], $this->app['config']->get('solidframe.discovery.paths'));
    }

    #[Test]
    public function configCanBeOverridden(): void
    {
        $this->app['config']->set('solidframe.discovery.enabled', false);

        self::assertFalse($this->app['config']->get('solidframe.discovery.enabled'));
    }

    // -- CQRS Bindings --

    #[Test]
    public function bindsCommandBusInterface(): void
    {
        $bus = $this->app->make(CommandBusInterface::class);

        self::assertInstanceOf(CommandBus::class, $bus);
    }

    #[Test]
    public function bindsQueryBusInterface(): void
    {
        $bus = $this->app->make(QueryBusInterface::class);

        self::assertInstanceOf(QueryBus::class, $bus);
    }

    #[Test]
    public function commandBusIsSingleton(): void
    {
        $bus1 = $this->app->make(CommandBusInterface::class);
        $bus2 = $this->app->make(CommandBusInterface::class);

        self::assertSame($bus1, $bus2);
    }

    // -- Event-Driven Bindings --

    #[Test]
    public function bindsEventBusInterface(): void
    {
        $bus = $this->app->make(EventBusInterface::class);

        self::assertInstanceOf(EventBus::class, $bus);
    }

    // -- Event Sourcing Bindings --

    #[Test]
    public function bindsEventStoreInterface(): void
    {
        $store = $this->app->make(EventStoreInterface::class);

        self::assertInstanceOf(\SolidFrame\Laravel\EventSourcing\DatabaseEventStore::class, $store);
    }

    #[Test]
    public function bindsSnapshotStoreInterface(): void
    {
        $store = $this->app->make(SnapshotStoreInterface::class);

        self::assertInstanceOf(\SolidFrame\Laravel\EventSourcing\DatabaseSnapshotStore::class, $store);
    }

    #[Test]
    public function bindsInMemoryEventStoreWhenConfigured(): void
    {
        $this->app['config']->set('solidframe.event_sourcing.event_store.driver', 'in_memory');
        $this->app->forgetInstance(EventStoreInterface::class);

        $store = $this->app->make(EventStoreInterface::class);

        self::assertInstanceOf(\SolidFrame\EventSourcing\Store\InMemoryEventStore::class, $store);
    }

    // -- Modular Bindings --

    #[Test]
    public function bindsModuleRegistryInterface(): void
    {
        $registry = $this->app->make(ModuleRegistryInterface::class);

        self::assertInstanceOf(\SolidFrame\Modular\Registry\InMemoryModuleRegistry::class, $registry);
    }

    // -- Saga Bindings --

    #[Test]
    public function bindsSagaStoreInterface(): void
    {
        $store = $this->app->make(SagaStoreInterface::class);

        self::assertInstanceOf(\SolidFrame\Laravel\Saga\DatabaseSagaStore::class, $store);
    }

    #[Test]
    public function bindsInMemorySagaStoreWhenConfigured(): void
    {
        $this->app['config']->set('solidframe.saga.store.driver', 'in_memory');
        $this->app->forgetInstance(SagaStoreInterface::class);

        $store = $this->app->make(SagaStoreInterface::class);

        self::assertInstanceOf(\SolidFrame\Saga\Store\InMemorySagaStore::class, $store);
    }
}
