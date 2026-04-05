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
        $event = new class ('billing') extends AbstractIntegrationEvent {
            public function eventName(): string
            {
                return 'order.placed';
            }
        };

        self::assertSame('billing', $event->sourceModule());
    }

    #[Test]
    public function generatesOccurredAtAutomatically(): void
    {
        $before = new DateTimeImmutable();

        $event = new class ('billing') extends AbstractIntegrationEvent {
            public function eventName(): string
            {
                return 'order.placed';
            }
        };

        self::assertGreaterThanOrEqual($before, $event->occurredAt());
    }

    #[Test]
    public function acceptsCustomOccurredAt(): void
    {
        $date = new DateTimeImmutable('2026-01-01');

        $event = new class ('billing', $date) extends AbstractIntegrationEvent {
            public function eventName(): string
            {
                return 'order.placed';
            }
        };

        self::assertSame($date, $event->occurredAt());
    }
}
