<?php

declare(strict_types=1);

namespace SolidFrame\Ddd\Aggregate;

use SolidFrame\Core\Event\DomainEventInterface;
use SolidFrame\Ddd\Entity\EntityInterface;

interface AggregateRootInterface extends EntityInterface
{
    /** @return list<DomainEventInterface> */
    public function releaseEvents(): array;
}
