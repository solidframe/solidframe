<?php

declare(strict_types=1);

namespace SolidFrame\Cqrs\Tests\Bus;

use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use SolidFrame\Core\Middleware\MiddlewareInterface;
use SolidFrame\Cqrs\Bus\CommandBus;
use SolidFrame\Cqrs\Handler\InMemoryHandlerResolver;

final class CommandBusTest extends TestCase
{
    #[Test]
    public function dispatchesCommandToHandler(): void
    {
        $handled = false;

        $resolver = (new InMemoryHandlerResolver())
            ->register(StubCommand::class, static function () use (&$handled): void {
                $handled = true;
            });

        $bus = new CommandBus($resolver);
        $bus->dispatch(new StubCommand());

        self::assertTrue($handled);
    }

    #[Test]
    public function passesCommandToHandler(): void
    {
        $received = null;

        $resolver = (new InMemoryHandlerResolver())
            ->register(StubCommand::class, static function (StubCommand $cmd) use (&$received): void {
                $received = $cmd->name;
            });

        $bus = new CommandBus($resolver);
        $bus->dispatch(new StubCommand('create'));

        self::assertSame('create', $received);
    }

    #[Test]
    public function executesMiddlewareBeforeHandler(): void
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

        $resolver = (new InMemoryHandlerResolver())
            ->register(StubCommand::class, static function () use (&$order): void {
                $order[] = 'handler';
            });

        $bus = new CommandBus($resolver, [$middleware]);
        $bus->dispatch(new StubCommand());

        self::assertSame(['middleware', 'handler'], $order);
    }

    #[Test]
    public function executesMultipleMiddlewareInOrder(): void
    {
        $order = [];

        $first = new class ($order, 'first') implements MiddlewareInterface {
            public function __construct(private array &$order, private readonly string $name) {}

            public function handle(object $message, callable $next): mixed
            {
                $this->order[] = $this->name;

                return $next($message);
            }
        };

        $second = new class ($order, 'second') implements MiddlewareInterface {
            public function __construct(private array &$order, private readonly string $name) {}

            public function handle(object $message, callable $next): mixed
            {
                $this->order[] = $this->name;

                return $next($message);
            }
        };

        $resolver = (new InMemoryHandlerResolver())
            ->register(StubCommand::class, static function () use (&$order): void {
                $order[] = 'handler';
            });

        $bus = new CommandBus($resolver, [$first, $second]);
        $bus->dispatch(new StubCommand());

        self::assertSame(['first', 'second', 'handler'], $order);
    }

    #[Test]
    public function middlewareCanShortCircuit(): void
    {
        $handled = false;

        $blocker = new class implements MiddlewareInterface {
            public function handle(object $message, callable $next): mixed
            {
                return null;
            }
        };

        $resolver = (new InMemoryHandlerResolver())
            ->register(StubCommand::class, static function () use (&$handled): void {
                $handled = true;
            });

        $bus = new CommandBus($resolver, [$blocker]);
        $bus->dispatch(new StubCommand());

        self::assertFalse($handled);
    }
}

final readonly class StubCommand
{
    public function __construct(
        public string $name = 'default',
    ) {}
}
