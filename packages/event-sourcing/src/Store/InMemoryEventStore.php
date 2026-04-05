<?php

declare(strict_types=1);

namespace SolidFrame\EventSourcing\Store;

use SolidFrame\Core\Event\DomainEventInterface;
use SolidFrame\Core\Identity\IdentityInterface;
use SolidFrame\EventSourcing\Exception\ConcurrencyException;

final class InMemoryEventStore implements EventStoreInterface
{
    /** @var array<string, list<DomainEventInterface>> */
    private array $streams = [];

    public function persist(IdentityInterface $aggregateId, int $expectedVersion, array $events): void
    {
        $id = $aggregateId->value();
        $currentVersion = count($this->streams[$id] ?? []);

        if ($currentVersion !== $expectedVersion) {
            throw ConcurrencyException::forAggregate($id, $expectedVersion, $currentVersion);
        }

        foreach ($events as $event) {
            $this->streams[$id][] = $event;
        }
    }

    public function load(IdentityInterface $aggregateId): array
    {
        return $this->streams[$aggregateId->value()] ?? [];
    }

    public function loadFromVersion(IdentityInterface $aggregateId, int $fromVersion): array
    {
        $events = $this->streams[$aggregateId->value()] ?? [];

        return array_slice($events, $fromVersion);
    }
}
