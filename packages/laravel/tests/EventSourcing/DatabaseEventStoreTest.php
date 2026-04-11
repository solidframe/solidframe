<?php

declare(strict_types=1);

namespace SolidFrame\Laravel\Tests\EventSourcing;

use DateTimeImmutable;
use Orchestra\Testbench\TestCase;
use PHPUnit\Framework\Attributes\Test;
use SolidFrame\Core\Event\DomainEventInterface;
use SolidFrame\Core\Identity\UuidIdentity;
use SolidFrame\EventSourcing\Exception\ConcurrencyException;
use SolidFrame\Laravel\EventSourcing\DatabaseEventStore;
use SolidFrame\Laravel\SolidFrameServiceProvider;

final class DatabaseEventStoreTest extends TestCase
{
    private DatabaseEventStore $store;

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

        $this->store = new DatabaseEventStore(
            $this->app->make(\Illuminate\Database\DatabaseManager::class),
        );
    }

    #[Test]
    public function persistsAndLoadsEvents(): void
    {
        $aggregateId = UuidIdentity::generate();
        $event = new TestEvent('order-created');

        $this->store->persist($aggregateId, 0, [$event]);

        $loaded = $this->store->load($aggregateId);

        self::assertCount(1, $loaded);
        self::assertInstanceOf(TestEvent::class, $loaded[0]);
        self::assertSame('order-created', $loaded[0]->name);
    }

    #[Test]
    public function loadsEventsInVersionOrder(): void
    {
        $aggregateId = UuidIdentity::generate();

        $this->store->persist($aggregateId, 0, [new TestEvent('first')]);
        $this->store->persist($aggregateId, 1, [new TestEvent('second')]);
        $this->store->persist($aggregateId, 2, [new TestEvent('third')]);

        $loaded = $this->store->load($aggregateId);

        self::assertCount(3, $loaded);
        self::assertSame('first', $loaded[0]->name);
        self::assertSame('second', $loaded[1]->name);
        self::assertSame('third', $loaded[2]->name);
    }

    #[Test]
    public function loadsEventsFromVersion(): void
    {
        $aggregateId = UuidIdentity::generate();

        $this->store->persist($aggregateId, 0, [
            new TestEvent('first'),
            new TestEvent('second'),
            new TestEvent('third'),
        ]);

        $loaded = $this->store->loadFromVersion($aggregateId, 1);

        self::assertCount(2, $loaded);
        self::assertSame('second', $loaded[0]->name);
        self::assertSame('third', $loaded[1]->name);
    }

    #[Test]
    public function throwsOnConcurrencyConflict(): void
    {
        $aggregateId = UuidIdentity::generate();

        $this->store->persist($aggregateId, 0, [new TestEvent('first')]);

        $this->expectException(ConcurrencyException::class);

        $this->store->persist($aggregateId, 0, [new TestEvent('conflict')]);
    }

    #[Test]
    public function returnsEmptyArrayForUnknownAggregate(): void
    {
        $aggregateId = UuidIdentity::generate();

        self::assertSame([], $this->store->load($aggregateId));
    }

    #[Test]
    public function preservesEventOccurredAt(): void
    {
        $aggregateId = UuidIdentity::generate();
        $occurredAt = new DateTimeImmutable('2026-01-15 10:30:00');
        $event = new TestEvent('test', $occurredAt);

        $this->store->persist($aggregateId, 0, [$event]);

        $loaded = $this->store->load($aggregateId);

        self::assertSame(
            $occurredAt->format('Y-m-d H:i:s'),
            $loaded[0]->occurredAt()->format('Y-m-d H:i:s'),
        );
    }
}

final readonly class TestEvent implements DomainEventInterface
{
    public DateTimeImmutable $occurredAt;

    public function __construct(
        public string $name,
        ?DateTimeImmutable $occurredAt = null,
    ) {
        $this->occurredAt = $occurredAt ?? new DateTimeImmutable();
    }

    public function eventName(): string
    {
        return $this->name;
    }

    public function occurredAt(): DateTimeImmutable
    {
        return $this->occurredAt;
    }
}
