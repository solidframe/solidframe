<?php

declare(strict_types=1);

namespace SolidFrame\EventSourcing\Tests\Aggregate;

use DateTimeImmutable;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use SolidFrame\Core\Event\DomainEventInterface;
use SolidFrame\Core\Identity\IdentityInterface;
use SolidFrame\EventSourcing\Aggregate\AbstractEventSourcedAggregateRoot;

final class AbstractEventSourcedAggregateRootTest extends TestCase
{
    #[Test]
    public function recordsEventAndAppliesIt(): void
    {
        $id = $this->createIdentity('id-1');
        $aggregate = TestAggregate::create($id, 'initial');

        self::assertSame('initial', $aggregate->name());
        self::assertSame(1, $aggregate->aggregateRootVersion());
    }

    #[Test]
    public function releasesRecordedEvents(): void
    {
        $id = $this->createIdentity('id-1');
        $aggregate = TestAggregate::create($id, 'test');

        $events = $aggregate->releaseEvents();

        self::assertCount(1, $events);
        self::assertInstanceOf(TestAggregateCreated::class, $events[0]);
    }

    #[Test]
    public function clearsEventsAfterRelease(): void
    {
        $id = $this->createIdentity('id-1');
        $aggregate = TestAggregate::create($id, 'test');
        $aggregate->releaseEvents();

        self::assertSame([], $aggregate->releaseEvents());
    }

    #[Test]
    public function tracksVersionAcrossMultipleEvents(): void
    {
        $id = $this->createIdentity('id-1');
        $aggregate = TestAggregate::create($id, 'original');
        $aggregate->rename('updated');

        self::assertSame(2, $aggregate->aggregateRootVersion());
        self::assertSame('updated', $aggregate->name());
    }

    #[Test]
    public function reconstitutesFromEvents(): void
    {
        $id = $this->createIdentity('id-1');
        $events = [
            new TestAggregateCreated('initial'),
            new TestAggregateRenamed('renamed'),
        ];

        $aggregate = TestAggregate::reconstituteFromEvents($id, $events);

        self::assertSame('renamed', $aggregate->name());
        self::assertSame(2, $aggregate->aggregateRootVersion());
        self::assertSame([], $aggregate->releaseEvents());
    }

    #[Test]
    public function reconstitutionDoesNotRecordEvents(): void
    {
        $id = $this->createIdentity('id-1');
        $events = [new TestAggregateCreated('test')];

        $aggregate = TestAggregate::reconstituteFromEvents($id, $events);

        self::assertSame([], $aggregate->releaseEvents());
    }

    #[Test]
    public function identityAndEquality(): void
    {
        $id = $this->createIdentity('id-1');
        $sameId = $this->createIdentity('id-1');
        $differentId = $this->createIdentity('id-2');

        $aggregate1 = TestAggregate::create($id, 'test');
        $aggregate2 = TestAggregate::create($sameId, 'test');
        $aggregate3 = TestAggregate::create($differentId, 'test');

        self::assertTrue($aggregate1->equals($aggregate2));
        self::assertFalse($aggregate1->equals($aggregate3));
        self::assertSame('id-1', $aggregate1->identity()->value());
    }

    #[Test]
    public function skipsApplyWhenMethodDoesNotExist(): void
    {
        $id = $this->createIdentity('id-1');
        $events = [
            new TestAggregateCreated('test'),
            new UnhandledEvent(),
        ];

        $aggregate = TestAggregate::reconstituteFromEvents($id, $events);

        self::assertSame('test', $aggregate->name());
        self::assertSame(2, $aggregate->aggregateRootVersion());
    }

    private function createIdentity(string $value): IdentityInterface
    {
        return new class ($value) implements IdentityInterface {
            public function __construct(private readonly string $value) {}

            public function value(): string
            {
                return $this->value;
            }

            public function equals(IdentityInterface $other): bool
            {
                return $this->value === $other->value();
            }

            public function __toString(): string
            {
                return $this->value;
            }
        };
    }
}

// Test fixtures

final readonly class TestAggregateCreated implements DomainEventInterface
{
    public function __construct(public string $name) {}

    public function eventName(): string
    {
        return 'test_aggregate.created';
    }

    public function occurredAt(): DateTimeImmutable
    {
        return new DateTimeImmutable();
    }
}

final readonly class TestAggregateRenamed implements DomainEventInterface
{
    public function __construct(public string $newName) {}

    public function eventName(): string
    {
        return 'test_aggregate.renamed';
    }

    public function occurredAt(): DateTimeImmutable
    {
        return new DateTimeImmutable();
    }
}

final class UnhandledEvent implements DomainEventInterface
{
    public function eventName(): string
    {
        return 'unhandled';
    }

    public function occurredAt(): DateTimeImmutable
    {
        return new DateTimeImmutable();
    }
}

final class TestAggregate extends AbstractEventSourcedAggregateRoot
{
    private string $name = '';

    public static function create(IdentityInterface $id, string $name): self
    {
        $aggregate = new self($id);
        $aggregate->recordThat(new TestAggregateCreated($name));

        return $aggregate;
    }

    public function rename(string $newName): void
    {
        $this->recordThat(new TestAggregateRenamed($newName));
    }

    public function name(): string
    {
        return $this->name;
    }

    protected function applyTestAggregateCreated(TestAggregateCreated $event): void
    {
        $this->name = $event->name;
    }

    protected function applyTestAggregateRenamed(TestAggregateRenamed $event): void
    {
        $this->name = $event->newName;
    }
}
