<?php

declare(strict_types=1);

namespace SolidFrame\EventSourcing\Tests\Snapshot;

use DateTimeImmutable;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use SolidFrame\Core\Event\DomainEventInterface;
use SolidFrame\Core\Identity\IdentityInterface;
use SolidFrame\EventSourcing\Aggregate\AbstractEventSourcedAggregateRoot;
use SolidFrame\EventSourcing\Exception\AggregateNotFoundException;
use SolidFrame\EventSourcing\Snapshot\InMemorySnapshotStore;
use SolidFrame\EventSourcing\Snapshot\Snapshot;
use SolidFrame\EventSourcing\Snapshot\SnapshotableAggregateRootInterface;
use SolidFrame\EventSourcing\Snapshot\SnapshotAggregateRootRepository;
use SolidFrame\EventSourcing\Store\InMemoryEventStore;

final class SnapshotAggregateRootRepositoryTest extends TestCase
{
    #[Test]
    public function loadsFromSnapshotWithRemainingEvents(): void
    {
        $eventStore = new InMemoryEventStore();
        $snapshotStore = new InMemorySnapshotStore();
        $repository = new SnapshotAggregateRootRepository(
            SnapshotTestAggregate::class,
            $eventStore,
            $snapshotStore,
        );

        $id = $this->createIdentity('agg-1');

        // Persist 3 events directly
        $eventStore->persist($id, 0, [
            new SnapshotTestCreated('original'),
            new SnapshotTestRenamed('v2'),
            new SnapshotTestRenamed('v3'),
        ]);

        // Save snapshot at version 2
        $snapshotStore->save(new Snapshot(
            aggregateId: 'agg-1',
            aggregateType: SnapshotTestAggregate::class,
            version: 2,
            state: ['name' => 'v2'],
        ));

        $loaded = $repository->load($id);

        self::assertInstanceOf(SnapshotTestAggregate::class, $loaded);
        self::assertSame('v3', $loaded->name());
        self::assertSame(3, $loaded->aggregateRootVersion());
    }

    #[Test]
    public function fallsBackToFullReconstitutionWithoutSnapshot(): void
    {
        $eventStore = new InMemoryEventStore();
        $snapshotStore = new InMemorySnapshotStore();
        $repository = new SnapshotAggregateRootRepository(
            SnapshotTestAggregate::class,
            $eventStore,
            $snapshotStore,
        );

        $id = $this->createIdentity('agg-1');
        $eventStore->persist($id, 0, [new SnapshotTestCreated('test')]);

        $loaded = $repository->load($id);

        self::assertSame('test', $loaded->name());
        self::assertSame(1, $loaded->aggregateRootVersion());
    }

    #[Test]
    public function throwsWhenNoSnapshotAndNoEvents(): void
    {
        $eventStore = new InMemoryEventStore();
        $snapshotStore = new InMemorySnapshotStore();
        $repository = new SnapshotAggregateRootRepository(
            SnapshotTestAggregate::class,
            $eventStore,
            $snapshotStore,
        );

        $this->expectException(AggregateNotFoundException::class);
        $repository->load($this->createIdentity('unknown'));
    }

    #[Test]
    public function savesEventsToStore(): void
    {
        $eventStore = new InMemoryEventStore();
        $snapshotStore = new InMemorySnapshotStore();
        $repository = new SnapshotAggregateRootRepository(
            SnapshotTestAggregate::class,
            $eventStore,
            $snapshotStore,
        );

        $id = $this->createIdentity('agg-1');
        $aggregate = SnapshotTestAggregate::create($id, 'test');
        $repository->save($aggregate);

        self::assertCount(1, $eventStore->load($id));
    }

    #[Test]
    public function loadsFromSnapshotWithNoRemainingEvents(): void
    {
        $eventStore = new InMemoryEventStore();
        $snapshotStore = new InMemorySnapshotStore();
        $repository = new SnapshotAggregateRootRepository(
            SnapshotTestAggregate::class,
            $eventStore,
            $snapshotStore,
        );

        $id = $this->createIdentity('agg-1');

        $eventStore->persist($id, 0, [new SnapshotTestCreated('snapped')]);

        $snapshotStore->save(new Snapshot(
            aggregateId: 'agg-1',
            aggregateType: SnapshotTestAggregate::class,
            version: 1,
            state: ['name' => 'snapped'],
        ));

        $loaded = $repository->load($id);

        self::assertSame('snapped', $loaded->name());
        self::assertSame(1, $loaded->aggregateRootVersion());
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
}

// Test fixtures

final readonly class SnapshotTestCreated implements DomainEventInterface
{
    public function __construct(public string $name) {}

    public function eventName(): string
    {
        return 'snapshot_test.created';
    }

    public function occurredAt(): DateTimeImmutable
    {
        return new DateTimeImmutable();
    }
}

final readonly class SnapshotTestRenamed implements DomainEventInterface
{
    public function __construct(public string $newName) {}

    public function eventName(): string
    {
        return 'snapshot_test.renamed';
    }

    public function occurredAt(): DateTimeImmutable
    {
        return new DateTimeImmutable();
    }
}

final class SnapshotTestAggregate extends AbstractEventSourcedAggregateRoot implements SnapshotableAggregateRootInterface
{
    private string $name = '';

    public static function create(IdentityInterface $id, string $name): self
    {
        $aggregate = new self($id);
        $aggregate->recordThat(new SnapshotTestCreated($name));

        return $aggregate;
    }

    public function rename(string $newName): void
    {
        $this->recordThat(new SnapshotTestRenamed($newName));
    }

    public function name(): string
    {
        return $this->name;
    }

    public function createSnapshotState(): mixed
    {
        return ['name' => $this->name];
    }

    /** @param iterable<DomainEventInterface> $remainingEvents */
    public static function reconstituteFromSnapshot(
        IdentityInterface $id,
        int $version,
        mixed $state,
        iterable $remainingEvents,
    ): static {
        $aggregate = new static($id);
        $aggregate->name = $state['name'];
        $aggregate->aggregateRootVersion = $version;

        foreach ($remainingEvents as $event) {
            $aggregate->applyEvent($event);
        }

        return $aggregate;
    }

    protected function applySnapshotTestCreated(SnapshotTestCreated $event): void
    {
        $this->name = $event->name;
    }

    protected function applySnapshotTestRenamed(SnapshotTestRenamed $event): void
    {
        $this->name = $event->newName;
    }
}
