<?php

declare(strict_types=1);

namespace SolidFrame\Cqrs\Tests\Handler;

use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use SolidFrame\Cqrs\Exception\HandlerNotFoundException;
use SolidFrame\Cqrs\Handler\InMemoryHandlerResolver;
use stdClass;

final class InMemoryHandlerResolverTest extends TestCase
{
    #[Test]
    public function resolvesRegisteredHandler(): void
    {
        $handler = static fn(): string => 'handled';

        $resolver = (new InMemoryHandlerResolver())
            ->register(stdClass::class, $handler);

        self::assertSame($handler, $resolver->resolve(new stdClass()));
    }

    #[Test]
    public function throwsWhenHandlerNotFound(): void
    {
        $this->expectException(HandlerNotFoundException::class);
        $this->expectExceptionMessage('stdClass');

        $resolver = new InMemoryHandlerResolver();
        $resolver->resolve(new stdClass());
    }

    #[Test]
    public function returnsItselfForFluentRegistration(): void
    {
        $resolver = new InMemoryHandlerResolver();

        $result = $resolver->register(stdClass::class, static fn(): null => null);

        self::assertSame($resolver, $result);
    }

    #[Test]
    public function lastRegistrationWins(): void
    {
        $first = static fn(): string => 'first';
        $second = static fn(): string => 'second';

        $resolver = (new InMemoryHandlerResolver())
            ->register(stdClass::class, $first)
            ->register(stdClass::class, $second);

        self::assertSame($second, $resolver->resolve(new stdClass()));
    }
}
