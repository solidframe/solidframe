<?php

declare(strict_types=1);

namespace SolidFrame\EventSourcing\Aggregate;

use SolidFrame\Core\Event\DomainEventInterface;
use SolidFrame\Core\Identity\IdentityInterface;
use SolidFrame\Ddd\Aggregate\AggregateRootInterface;

interface EventSourcedAggregateRootInterface extends AggregateRootInterface
{
    public function aggregateRootVersion(): int;

    /** @param iterable<DomainEventInterface> $events */
    public static function reconstituteFromEvents(IdentityInterface $id, iterable $events): static;
}
