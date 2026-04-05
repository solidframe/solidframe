<?php

declare(strict_types=1);

namespace SolidFrame\EventSourcing\Aggregate;

use ReflectionClass;
use SolidFrame\Core\Event\DomainEventInterface;
use SolidFrame\Core\Identity\IdentityInterface;
use SolidFrame\Ddd\Entity\EntityInterface;

abstract class AbstractEventSourcedAggregateRoot implements EventSourcedAggregateRootInterface
{
    /** @var list<DomainEventInterface> */
    private array $recordedEvents = [];

    protected int $aggregateRootVersion = 0;

    final protected function __construct(
        private readonly IdentityInterface $identity,
    ) {}

    public function identity(): IdentityInterface
    {
        return $this->identity;
    }

    public function equals(EntityInterface $other): bool
    {
        return $other instanceof static && $this->identity->equals($other->identity());
    }

    public function aggregateRootVersion(): int
    {
        return $this->aggregateRootVersion;
    }

    protected function recordThat(DomainEventInterface $event): void
    {
        $this->applyEvent($event);
        $this->recordedEvents[] = $event;
    }

    /** @return list<DomainEventInterface> */
    public function releaseEvents(): array
    {
        $events = $this->recordedEvents;
        $this->recordedEvents = [];

        return $events;
    }

    /** @param iterable<DomainEventInterface> $events */
    public static function reconstituteFromEvents(IdentityInterface $id, iterable $events): static
    {
        $aggregate = new static($id);

        foreach ($events as $event) {
            $aggregate->applyEvent($event);
        }

        return $aggregate;
    }

    protected function applyEvent(DomainEventInterface $event): void
    {
        $shortName = (new ReflectionClass($event))->getShortName();
        $method = 'apply' . $shortName;

        if (method_exists($this, $method)) {
            $this->$method($event);
        }

        $this->aggregateRootVersion++;
    }
}
