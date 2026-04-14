<?php

declare(strict_types=1);

namespace SolidFrame\Modular\Tests\Event;

use DateTimeImmutable;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use SolidFrame\Modular\Event\AbstractIntegrationEvent;

final class AbstractIntegrationEventTest extends TestCase
{
    #[Test]
    public function returnsSourceModule(): void
    {
        $event = new TestIntegrationEvent('billing');

        self::assertSame('billing', $event->sourceModule());
    }

    #[Test]
    public function generatesOccurredAtAutomatically(): void
    {
        $before = new DateTimeImmutable();

        $event = new TestIntegrationEvent('billing');

        self::assertGreaterThanOrEqual($before, $event->occurredAt());
    }

    #[Test]
    public function acceptsCustomOccurredAt(): void
    {
        $date = new DateTimeImmutable('2026-01-01');

        $event = new TestIntegrationEvent('billing', $date);

        self::assertSame($date, $event->occurredAt());
    }
}

/** @internal */
final readonly class TestIntegrationEvent extends AbstractIntegrationEvent
{
    public function eventName(): string
    {
        return 'order.placed';
    }
}
