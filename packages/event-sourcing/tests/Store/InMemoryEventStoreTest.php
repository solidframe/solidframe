<?php

declare(strict_types=1);

namespace SolidFrame\EventSourcing\Tests\Store;

use DateTimeImmutable;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use SolidFrame\Core\Event\DomainEventInterface;
use SolidFrame\Core\Identity\IdentityInterface;
use SolidFrame\EventSourcing\Exception\ConcurrencyException;
use SolidFrame\EventSourcing\Store\InMemoryEventStore;

final class InMemoryEventStoreTest extends TestCase
{
    #[Test]
    public function persistsAndLoadsEvents(): void
    {
        $store = new InMemoryEventStore();
        $id = $this->createIdentity('agg-1');
        $event = $this->createEvent('order.placed');

        $store->persist($id, 0, [$event]);

        $loaded = $store->load($id);
        self::assertCount(1, $loaded);
        self::assertSame($event, $loaded[0]);
    }

    #[Test]
    public function appendsEventsToExistingStream(): void
    {
        $store = new InMemoryEventStore();
        $id = $this->createIdentity('agg-1');

        $store->persist($id, 0, [$this->createEvent('first')]);
        $store->persist($id, 1, [$this->createEvent('second')]);

        self::assertCount(2, $store->load($id));
    }

    #[Test]
    public function returnsEmptyArrayForUnknownAggregate(): void
    {
        $store = new InMemoryEventStore();

        self::assertSame([], $store->load($this->createIdentity('unknown')));
    }

    #[Test]
    public function throwsOnConcurrencyConflict(): void
    {
        $store = new InMemoryEventStore();
        $id = $this->createIdentity('agg-1');

        $store->persist($id, 0, [$this->createEvent('first')]);

        $this->expectException(ConcurrencyException::class);
        $store->persist($id, 0, [$this->createEvent('conflict')]);
    }

    #[Test]
    public function loadsEventsFromVersion(): void
    {
        $store = new InMemoryEventStore();
        $id = $this->createIdentity('agg-1');

        $store->persist($id, 0, [
            $this->createEvent('first'),
            $this->createEvent('second'),
            $this->createEvent('third'),
        ]);

        $events = $store->loadFromVersion($id, 2);
        self::assertCount(1, $events);
        self::assertSame('third', $events[0]->eventName());
    }

    #[Test]
    public function loadFromVersionReturnsEmptyForUnknownAggregate(): void
    {
        $store = new InMemoryEventStore();

        self::assertSame([], $store->loadFromVersion($this->createIdentity('unknown'), 0));
    }

    #[Test]
    public function persistsMultipleEventsAtOnce(): void
    {
        $store = new InMemoryEventStore();
        $id = $this->createIdentity('agg-1');

        $store->persist($id, 0, [
            $this->createEvent('first'),
            $this->createEvent('second'),
        ]);

        self::assertCount(2, $store->load($id));
    }

    #[Test]
    public function isolatesStreamsBetweenAggregates(): void
    {
        $store = new InMemoryEventStore();
        $id1 = $this->createIdentity('agg-1');
        $id2 = $this->createIdentity('agg-2');

        $store->persist($id1, 0, [$this->createEvent('event-1')]);
        $store->persist($id2, 0, [$this->createEvent('event-2')]);

        self::assertCount(1, $store->load($id1));
        self::assertCount(1, $store->load($id2));
        self::assertSame('event-1', $store->load($id1)[0]->eventName());
        self::assertSame('event-2', $store->load($id2)[0]->eventName());
    }

    private function createIdentity(string $value): IdentityInterface
    {
        return new class ($value) implements IdentityInterface {
            public function __construct(private readonly string $value) {}

            public function value(): string
            {
                return $this->value;
            }

            public function equals(IdentityInterface $other): bool
            {
                return $this->value === $other->value();
            }

            public function __toString(): string
            {
                return $this->value;
            }
        };
    }

    private function createEvent(string $name): DomainEventInterface
    {
        return new class ($name) implements DomainEventInterface {
            public function __construct(private readonly string $name) {}

            public function eventName(): string
            {
                return $this->name;
            }

            public function occurredAt(): DateTimeImmutable
            {
                return new DateTimeImmutable();
            }
        };
    }
}
