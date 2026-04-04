<?php

declare(strict_types=1);

namespace SolidFrame\Ddd\Tests\Aggregate;

use DateTimeImmutable;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use SolidFrame\Core\Event\DomainEventInterface;
use SolidFrame\Core\Identity\AbstractIdentity;
use SolidFrame\Ddd\Aggregate\AbstractAggregateRoot;

final class AbstractAggregateRootTest extends TestCase
{
    #[Test]
    public function releasesRecordedEvents(): void
    {
        $aggregate = $this->createAggregate('agg-1');
        $event = $this->createEvent('OrderPlaced');

        $aggregate->doSomething($event);

        $events = $aggregate->releaseEvents();

        self::assertCount(1, $events);
        self::assertSame($event, $events[0]);
    }

    #[Test]
    public function clearsEventsAfterRelease(): void
    {
        $aggregate = $this->createAggregate('agg-1');
        $aggregate->doSomething($this->createEvent('OrderPlaced'));

        $aggregate->releaseEvents();

        self::assertSame([], $aggregate->releaseEvents());
    }

    #[Test]
    public function recordsMultipleEvents(): void
    {
        $aggregate = $this->createAggregate('agg-1');
        $event1 = $this->createEvent('OrderPlaced');
        $event2 = $this->createEvent('OrderConfirmed');

        $aggregate->doSomething($event1);
        $aggregate->doSomething($event2);

        $events = $aggregate->releaseEvents();

        self::assertCount(2, $events);
        self::assertSame($event1, $events[0]);
        self::assertSame($event2, $events[1]);
    }

    #[Test]
    public function returnsEmptyArrayWhenNoEvents(): void
    {
        $aggregate = $this->createAggregate('agg-1');

        self::assertSame([], $aggregate->releaseEvents());
    }

    #[Test]
    public function isAnEntity(): void
    {
        $aggregate = $this->createAggregate('agg-1');

        self::assertSame('agg-1', $aggregate->identity()->value());
    }

    /**
     * @return AbstractAggregateRoot&object{doSomething: callable}
     */
    private function createAggregate(string $id): AbstractAggregateRoot
    {
        $identity = new class ($id) extends AbstractIdentity {};

        return new class ($identity) extends AbstractAggregateRoot {
            public function doSomething(DomainEventInterface $event): void
            {
                $this->recordThat($event);
            }
        };
    }

    private function createEvent(string $name): DomainEventInterface
    {
        return new class ($name) implements DomainEventInterface {
            public function __construct(private readonly string $name) {}

            public function eventName(): string
            {
                return $this->name;
            }

            public function occurredAt(): DateTimeImmutable
            {
                return new DateTimeImmutable();
            }
        };
    }
}
