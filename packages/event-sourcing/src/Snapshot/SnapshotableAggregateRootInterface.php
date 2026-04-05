<?php

declare(strict_types=1);

namespace SolidFrame\EventSourcing\Snapshot;

use SolidFrame\Core\Event\DomainEventInterface;
use SolidFrame\Core\Identity\IdentityInterface;
use SolidFrame\EventSourcing\Aggregate\EventSourcedAggregateRootInterface;

interface SnapshotableAggregateRootInterface extends EventSourcedAggregateRootInterface
{
    public function createSnapshotState(): mixed;

    /** @param iterable<DomainEventInterface> $remainingEvents */
    public static function reconstituteFromSnapshot(
        IdentityInterface $id,
        int $version,
        mixed $state,
        iterable $remainingEvents,
    ): static;
}
