<?php

declare(strict_types=1);

namespace SolidFrame\EventSourcing\Snapshot;

use SolidFrame\Core\Identity\IdentityInterface;

final class InMemorySnapshotStore implements SnapshotStoreInterface
{
    /** @var array<string, Snapshot> */
    private array $snapshots = [];

    public function save(Snapshot $snapshot): void
    {
        $this->snapshots[$snapshot->aggregateId] = $snapshot;
    }

    public function load(IdentityInterface $aggregateId): ?Snapshot
    {
        return $this->snapshots[$aggregateId->value()] ?? null;
    }
}
