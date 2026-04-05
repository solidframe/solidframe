<?php

declare(strict_types=1);

namespace SolidFrame\EventSourcing\Tests\Repository;

use DateTimeImmutable;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use SolidFrame\Core\Event\DomainEventInterface;
use SolidFrame\Core\Identity\IdentityInterface;
use SolidFrame\EventSourcing\Aggregate\AbstractEventSourcedAggregateRoot;
use SolidFrame\EventSourcing\Exception\AggregateNotFoundException;
use SolidFrame\EventSourcing\Repository\AggregateRootRepository;
use SolidFrame\EventSourcing\Store\InMemoryEventStore;

final class AggregateRootRepositoryTest extends TestCase
{
    #[Test]
    public function savesAndLoadsAggregate(): void
    {
        $eventStore = new InMemoryEventStore();
        $repository = new AggregateRootRepository(RepositoryTestAggregate::class, $eventStore);

        $id = $this->createIdentity('agg-1');
        $aggregate = RepositoryTestAggregate::create($id, 'test');
        $repository->save($aggregate);

        $loaded = $repository->load($id);

        self::assertInstanceOf(RepositoryTestAggregate::class, $loaded);
        self::assertSame('test', $loaded->name());
        self::assertSame(1, $loaded->aggregateRootVersion());
    }

    #[Test]
    public function throwsWhenAggregateNotFound(): void
    {
        $eventStore = new InMemoryEventStore();
        $repository = new AggregateRootRepository(RepositoryTestAggregate::class, $eventStore);

        $this->expectException(AggregateNotFoundException::class);
        $repository->load($this->createIdentity('unknown'));
    }

    #[Test]
    public function skipsEmptyEventList(): void
    {
        $eventStore = new InMemoryEventStore();
        $repository = new AggregateRootRepository(RepositoryTestAggregate::class, $eventStore);

        $id = $this->createIdentity('agg-1');
        $aggregate = RepositoryTestAggregate::create($id, 'test');
        $repository->save($aggregate);

        // Save again with no new events
        $loaded = $repository->load($id);
        $repository->save($loaded);

        // Should still load fine
        $reloaded = $repository->load($id);
        self::assertSame('test', $reloaded->name());
    }

    #[Test]
    public function handlesMultipleEventsOnAggregate(): void
    {
        $eventStore = new InMemoryEventStore();
        $repository = new AggregateRootRepository(RepositoryTestAggregate::class, $eventStore);

        $id = $this->createIdentity('agg-1');
        $aggregate = RepositoryTestAggregate::create($id, 'original');
        $aggregate->rename('updated');
        $repository->save($aggregate);

        $loaded = $repository->load($id);

        self::assertSame('updated', $loaded->name());
        self::assertSame(2, $loaded->aggregateRootVersion());
    }

    #[Test]
    public function supportsIncrementalSaves(): void
    {
        $eventStore = new InMemoryEventStore();
        $repository = new AggregateRootRepository(RepositoryTestAggregate::class, $eventStore);

        $id = $this->createIdentity('agg-1');
        $aggregate = RepositoryTestAggregate::create($id, 'v1');
        $repository->save($aggregate);

        $loaded = $repository->load($id);
        $loaded->rename('v2');
        $repository->save($loaded);

        $reloaded = $repository->load($id);
        self::assertSame('v2', $reloaded->name());
        self::assertSame(2, $reloaded->aggregateRootVersion());
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

final readonly class RepositoryTestCreated implements DomainEventInterface
{
    public function __construct(public string $name) {}

    public function eventName(): string
    {
        return 'repository_test.created';
    }

    public function occurredAt(): DateTimeImmutable
    {
        return new DateTimeImmutable();
    }
}

final readonly class RepositoryTestRenamed implements DomainEventInterface
{
    public function __construct(public string $newName) {}

    public function eventName(): string
    {
        return 'repository_test.renamed';
    }

    public function occurredAt(): DateTimeImmutable
    {
        return new DateTimeImmutable();
    }
}

final class RepositoryTestAggregate extends AbstractEventSourcedAggregateRoot
{
    private string $name = '';

    public static function create(IdentityInterface $id, string $name): self
    {
        $aggregate = new self($id);
        $aggregate->recordThat(new RepositoryTestCreated($name));

        return $aggregate;
    }

    public function rename(string $newName): void
    {
        $this->recordThat(new RepositoryTestRenamed($newName));
    }

    public function name(): string
    {
        return $this->name;
    }

    protected function applyRepositoryTestCreated(RepositoryTestCreated $event): void
    {
        $this->name = $event->name;
    }

    protected function applyRepositoryTestRenamed(RepositoryTestRenamed $event): void
    {
        $this->name = $event->newName;
    }
}
