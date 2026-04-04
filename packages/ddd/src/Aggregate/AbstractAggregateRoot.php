<?php

declare(strict_types=1);

namespace SolidFrame\Ddd\Aggregate;

use SolidFrame\Core\Event\DomainEventInterface;
use SolidFrame\Ddd\Entity\AbstractEntity;

abstract class AbstractAggregateRoot extends AbstractEntity implements AggregateRootInterface
{
    /** @var list<DomainEventInterface> */
    private array $recordedEvents = [];

    protected function recordThat(DomainEventInterface $event): void
    {
        $this->recordedEvents[] = $event;
    }

    /** @return list<DomainEventInterface> */
    public function releaseEvents(): array
    {
        $events = $this->recordedEvents;
        $this->recordedEvents = [];

        return $events;
    }
}
