<?php

declare(strict_types=1);

namespace SolidFrame\Laravel\Tests\EventSourcing;

use Orchestra\Testbench\TestCase;
use PHPUnit\Framework\Attributes\Test;
use SolidFrame\Core\Identity\UuidIdentity;
use SolidFrame\EventSourcing\Snapshot\Snapshot;
use SolidFrame\Laravel\EventSourcing\DatabaseSnapshotStore;
use SolidFrame\Laravel\SolidFrameServiceProvider;

final class DatabaseSnapshotStoreTest extends TestCase
{
    private DatabaseSnapshotStore $store;

    protected function getPackageProviders($app): array
    {
        return [SolidFrameServiceProvider::class];
    }

    protected function defineEnvironment($app): void
    {
        $app['config']->set('database.default', 'testing');
        $app['config']->set('database.connections.testing', [
            'driver' => 'sqlite',
            'database' => ':memory:',
        ]);
    }

    protected function setUp(): void
    {
        parent::setUp();

        $this->loadMigrationsFrom(__DIR__ . '/../../database/migrations');

        $this->store = new DatabaseSnapshotStore(
            $this->app->make(\Illuminate\Database\DatabaseManager::class),
        );
    }

    #[Test]
    public function savesAndLoadsSnapshot(): void
    {
        $aggregateId = UuidIdentity::generate();

        $snapshot = new Snapshot(
            aggregateId: $aggregateId->value(),
            aggregateType: 'App\\Domain\\Order',
            version: 5,
            state: ['status' => 'confirmed', 'total' => 1500],
        );

        $this->store->save($snapshot);

        $loaded = $this->store->load($aggregateId);

        self::assertNotNull($loaded);
        self::assertSame($aggregateId->value(), $loaded->aggregateId);
        self::assertSame('App\\Domain\\Order', $loaded->aggregateType);
        self::assertSame(5, $loaded->version);
        self::assertSame(['status' => 'confirmed', 'total' => 1500], $loaded->state);
    }

    #[Test]
    public function returnsNullForUnknownAggregate(): void
    {
        $aggregateId = UuidIdentity::generate();

        self::assertNull($this->store->load($aggregateId));
    }

    #[Test]
    public function updatesExistingSnapshot(): void
    {
        $aggregateId = UuidIdentity::generate();

        $this->store->save(new Snapshot($aggregateId->value(), 'Order', 5, ['v' => 1]));
        $this->store->save(new Snapshot($aggregateId->value(), 'Order', 10, ['v' => 2]));

        $loaded = $this->store->load($aggregateId);

        self::assertSame(10, $loaded->version);
        self::assertSame(['v' => 2], $loaded->state);
    }
}
