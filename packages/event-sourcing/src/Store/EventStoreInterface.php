<?php

declare(strict_types=1);

namespace SolidFrame\EventSourcing\Store;

use SolidFrame\Core\Event\DomainEventInterface;
use SolidFrame\Core\Identity\IdentityInterface;

interface EventStoreInterface
{
    /** @param list<DomainEventInterface> $events */
    public function persist(IdentityInterface $aggregateId, int $expectedVersion, array $events): void;

    /** @return list<DomainEventInterface> */
    public function load(IdentityInterface $aggregateId): array;

    /** @return list<DomainEventInterface> */
    public function loadFromVersion(IdentityInterface $aggregateId, int $fromVersion): array;
}
