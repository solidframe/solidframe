<?php

declare(strict_types=1);

namespace SolidFrame\EventDriven\Tests\Listener;

use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use SolidFrame\EventDriven\Listener\InMemoryListenerResolver;
use stdClass;

final class InMemoryListenerResolverTest extends TestCase
{
    #[Test]
    public function resolvesRegisteredListeners(): void
    {
        $listener = static fn(): null => null;

        $resolver = (new InMemoryListenerResolver())
            ->listen(stdClass::class, $listener);

        $listeners = $resolver->resolve(new stdClass());

        self::assertCount(1, $listeners);
        self::assertSame($listener, $listeners[0]);
    }

    #[Test]
    public function resolvesMultipleListenersForSameEvent(): void
    {
        $first = static fn(): null => null;
        $second = static fn(): null => null;

        $resolver = (new InMemoryListenerResolver())
            ->listen(stdClass::class, $first)
            ->listen(stdClass::class, $second);

        $listeners = $resolver->resolve(new stdClass());

        self::assertCount(2, $listeners);
        self::assertSame($first, $listeners[0]);
        self::assertSame($second, $listeners[1]);
    }

    #[Test]
    public function returnsEmptyArrayWhenNoListeners(): void
    {
        $resolver = new InMemoryListenerResolver();

        self::assertSame([], $resolver->resolve(new stdClass()));
    }

    #[Test]
    public function returnsItselfForFluentRegistration(): void
    {
        $resolver = new InMemoryListenerResolver();

        $result = $resolver->listen(stdClass::class, static fn(): null => null);

        self::assertSame($resolver, $result);
    }
}
