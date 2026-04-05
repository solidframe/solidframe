<?php

declare(strict_types=1);

namespace SolidFrame\EventSourcing\Snapshot;

use SolidFrame\Core\Identity\IdentityInterface;

interface SnapshotStoreInterface
{
    public function save(Snapshot $snapshot): void;

    public function load(IdentityInterface $aggregateId): ?Snapshot;
}
