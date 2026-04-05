<?php

declare(strict_types=1);

namespace SolidFrame\EventSourcing\Repository;

use SolidFrame\Core\Identity\IdentityInterface;
use SolidFrame\EventSourcing\Aggregate\EventSourcedAggregateRootInterface;

interface AggregateRootRepositoryInterface
{
    public function load(IdentityInterface $aggregateId): EventSourcedAggregateRootInterface;

    public function save(EventSourcedAggregateRootInterface $aggregateRoot): void;
}
