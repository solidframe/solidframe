<?php

declare(strict_types=1);

namespace SolidFrame\Cqrs\Tests\Bus;

use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use SolidFrame\Core\Middleware\MiddlewareInterface;
use SolidFrame\Cqrs\Bus\QueryBus;
use SolidFrame\Cqrs\Handler\InMemoryHandlerResolver;

final class QueryBusTest extends TestCase
{
    #[Test]
    public function returnsHandlerResult(): void
    {
        $resolver = (new InMemoryHandlerResolver())
            ->register(StubQuery::class, static fn(): string => 'result');

        $bus = new QueryBus($resolver);

        self::assertSame('result', $bus->ask(new StubQuery()));
    }

    #[Test]
    public function passesQueryToHandler(): void
    {
        $resolver = (new InMemoryHandlerResolver())
            ->register(StubQuery::class, static fn(StubQuery $q): string => 'hello ' . $q->name);

        $bus = new QueryBus($resolver);

        self::assertSame('hello world', $bus->ask(new StubQuery('world')));
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
            ->register(StubQuery::class, static function () use (&$order): string {
                $order[] = 'handler';

                return 'result';
            });

        $bus = new QueryBus($resolver, [$middleware]);
        $result = $bus->ask(new StubQuery());

        self::assertSame(['middleware', 'handler'], $order);
        self::assertSame('result', $result);
    }

    #[Test]
    public function middlewareCanTransformResult(): void
    {
        $middleware = new class implements MiddlewareInterface {
            public function handle(object $message, callable $next): mixed
            {
                $result = $next($message);

                return strtoupper((string) $result);
            }
        };

        $resolver = (new InMemoryHandlerResolver())
            ->register(StubQuery::class, static fn(): string => 'hello');

        $bus = new QueryBus($resolver, [$middleware]);

        self::assertSame('HELLO', $bus->ask(new StubQuery()));
    }
}

final readonly class StubQuery
{
    public function __construct(
        public string $name = 'default',
    ) {}
}
