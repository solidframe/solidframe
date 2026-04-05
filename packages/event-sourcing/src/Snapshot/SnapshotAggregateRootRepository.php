<?php

declare(strict_types=1);

namespace SolidFrame\EventSourcing\Snapshot;

use SolidFrame\Core\Identity\IdentityInterface;
use SolidFrame\EventSourcing\Aggregate\EventSourcedAggregateRootInterface;
use SolidFrame\EventSourcing\Exception\AggregateNotFoundException;
use SolidFrame\EventSourcing\Repository\AggregateRootRepositoryInterface;
use SolidFrame\EventSourcing\Store\EventStoreInterface;

final readonly class SnapshotAggregateRootRepository implements AggregateRootRepositoryInterface
{
    /** @param class-string<SnapshotableAggregateRootInterface> $aggregateClass */
    public function __construct(
        private string $aggregateClass,
        private EventStoreInterface $eventStore,
        private SnapshotStoreInterface $snapshotStore,
    ) {}

    public function load(IdentityInterface $aggregateId): EventSourcedAggregateRootInterface
    {
        $snapshot = $this->snapshotStore->load($aggregateId);

        if ($snapshot !== null) {
            $remainingEvents = $this->eventStore->loadFromVersion($aggregateId, $snapshot->version);

            return ($this->aggregateClass)::reconstituteFromSnapshot(
                $aggregateId,
                $snapshot->version,
                $snapshot->state,
                $remainingEvents,
            );
        }

        $events = $this->eventStore->load($aggregateId);

        if ($events === []) {
            throw AggregateNotFoundException::forId($aggregateId->value());
        }

        return ($this->aggregateClass)::reconstituteFromEvents($aggregateId, $events);
    }

    public function save(EventSourcedAggregateRootInterface $aggregateRoot): void
    {
        $events = $aggregateRoot->releaseEvents();

        if ($events === []) {
            return;
        }

        $currentVersion = $aggregateRoot->aggregateRootVersion();
        $expectedVersion = $currentVersion - count($events);

        $this->eventStore->persist($aggregateRoot->identity(), $expectedVersion, $events);
    }
}
