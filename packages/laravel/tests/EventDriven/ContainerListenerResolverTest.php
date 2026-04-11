<?php

declare(strict_types=1);

namespace SolidFrame\Laravel\Tests\EventDriven;

use Orchestra\Testbench\TestCase;
use PHPUnit\Framework\Attributes\Test;
use SolidFrame\Laravel\EventDriven\ContainerListenerResolver;

final class ContainerListenerResolverTest extends TestCase
{
    #[Test]
    public function resolvesListenersFromContainer(): void
    {
        $resolver = new ContainerListenerResolver(
            $this->app,
            [FakeEvent::class => [FakeEventListenerA::class, FakeEventListenerB::class]],
        );

        $listeners = $resolver->resolve(new FakeEvent());

        self::assertCount(2, $listeners);
        self::assertInstanceOf(FakeEventListenerA::class, $listeners[0]);
        self::assertInstanceOf(FakeEventListenerB::class, $listeners[1]);
    }

    #[Test]
    public function returnsEmptyArrayWhenNoListenersRegistered(): void
    {
        $resolver = new ContainerListenerResolver($this->app);

        $listeners = $resolver->resolve(new FakeEvent());

        self::assertSame([], $listeners);
    }

    #[Test]
    public function allResolvedListenersAreCallable(): void
    {
        $resolver = new ContainerListenerResolver(
            $this->app,
            [FakeEvent::class => [FakeEventListenerA::class]],
        );

        $listeners = $resolver->resolve(new FakeEvent());

        foreach ($listeners as $listener) {
            self::assertIsCallable($listener);
        }
    }
}

final readonly class FakeEvent {}

final class FakeEventListenerA
{
    /** @param FakeEvent $event */
    public function __invoke(FakeEvent $event): void {}
}

final class FakeEventListenerB
{
    /** @param FakeEvent $event */
    public function __invoke(FakeEvent $event): void {}
}
