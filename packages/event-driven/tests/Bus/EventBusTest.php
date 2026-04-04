<?php

declare(strict_types=1);

namespace SolidFrame\EventDriven\Tests\Bus;

use DateTimeImmutable;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use SolidFrame\Core\Event\DomainEventInterface;
use SolidFrame\Core\Middleware\MiddlewareInterface;
use SolidFrame\EventDriven\Bus\EventBus;
use SolidFrame\EventDriven\Listener\InMemoryListenerResolver;

final class EventBusTest extends TestCase
{
    #[Test]
    public function dispatchesEventToSingleListener(): void
    {
        $received = null;

        $resolver = (new InMemoryListenerResolver())
            ->listen(StubEvent::class, static function (StubEvent $event) use (&$received): void {
                $received = $event->eventName();
            });

        $bus = new EventBus($resolver);
        $bus->dispatch(new StubEvent('OrderPlaced'));

        self::assertSame('OrderPlaced', $received);
    }

    #[Test]
    public function dispatchesEventToMultipleListeners(): void
    {
        $order = [];

        $resolver = (new InMemoryListenerResolver())
            ->listen(StubEvent::class, static function () use (&$order): void {
                $order[] = 'listener1';
            })
            ->listen(StubEvent::class, static function () use (&$order): void {
                $order[] = 'listener2';
            })
            ->listen(StubEvent::class, static function () use (&$order): void {
                $order[] = 'listener3';
            });

        $bus = new EventBus($resolver);
        $bus->dispatch(new StubEvent('OrderPlaced'));

        self::assertSame(['listener1', 'listener2', 'listener3'], $order);
    }

    #[Test]
    public function doesNothingWhenNoListeners(): void
    {
        $resolver = new InMemoryListenerResolver();

        $bus = new EventBus($resolver);
        $bus->dispatch(new StubEvent('OrderPlaced'));

        $this->addToAssertionCount(1);
    }

    #[Test]
    public function executesMiddlewareBeforeListeners(): void
    {
        $order = [];

        $middleware = new class ($order) implements MiddlewareInterface {
            public function __construct(private array &$order) {}

            public function handle(object $message, callable $next): mixed
            {
                $this->order[] = 'middleware';

                return $next($message);
            }
        };

        $resolver = (new InMemoryListenerResolver())
            ->listen(StubEvent::class, static function () use (&$order): void {
                $order[] = 'listener';
            });

        $bus = new EventBus($resolver, [$middleware]);
        $bus->dispatch(new StubEvent('OrderPlaced'));

        self::assertSame(['middleware', 'listener'], $order);
    }

    #[Test]
    public function middlewareCanPreventDispatch(): void
    {
        $handled = false;

        $blocker = new class implements MiddlewareInterface {
            public function handle(object $message, callable $next): mixed
            {
                return null;
            }
        };

        $resolver = (new InMemoryListenerResolver())
            ->listen(StubEvent::class, static function () use (&$handled): void {
                $handled = true;
            });

        $bus = new EventBus($resolver, [$blocker]);
        $bus->dispatch(new StubEvent('OrderPlaced'));

        self::assertFalse($handled);
    }

    #[Test]
    public function middlewareWrapsAllListenersNotEach(): void
    {
        $order = [];

        $middleware = new class ($order) implements MiddlewareInterface {
            public function __construct(private array &$order) {}

            public function handle(object $message, callable $next): mixed
            {
                $this->order[] = 'before';
                $result = $next($message);
                $this->order[] = 'after';

                return $result;
            }
        };

        $resolver = (new InMemoryListenerResolver())
            ->listen(StubEvent::class, static function () use (&$order): void {
                $order[] = 'listener1';
            })
            ->listen(StubEvent::class, static function () use (&$order): void {
                $order[] = 'listener2';
            });

        $bus = new EventBus($resolver, [$middleware]);
        $bus->dispatch(new StubEvent('OrderPlaced'));

        self::assertSame(['before', 'listener1', 'listener2', 'after'], $order);
    }
}

final readonly class StubEvent implements DomainEventInterface
{
    public function __construct(private string $name) {}

    public function eventName(): string
    {
        return $this->name;
    }

    public function occurredAt(): DateTimeImmutable
    {
        return new DateTimeImmutable();
    }
}
