<?php

declare(strict_types=1);

namespace SolidFrame\EventSourcing\Repository;

use SolidFrame\Core\Identity\IdentityInterface;
use SolidFrame\EventSourcing\Aggregate\EventSourcedAggregateRootInterface;
use SolidFrame\EventSourcing\Exception\AggregateNotFoundException;
use SolidFrame\EventSourcing\Store\EventStoreInterface;

final readonly class AggregateRootRepository implements AggregateRootRepositoryInterface
{
    /** @param class-string<EventSourcedAggregateRootInterface> $aggregateClass */
    public function __construct(
        private string $aggregateClass,
        private EventStoreInterface $eventStore,
    ) {}

    public function load(IdentityInterface $aggregateId): EventSourcedAggregateRootInterface
    {
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
