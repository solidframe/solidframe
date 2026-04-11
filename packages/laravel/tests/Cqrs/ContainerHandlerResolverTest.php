<?php

declare(strict_types=1);

namespace SolidFrame\Laravel\Tests\Cqrs;

use Orchestra\Testbench\TestCase;
use PHPUnit\Framework\Attributes\Test;
use SolidFrame\Cqrs\Exception\HandlerNotFoundException;
use SolidFrame\Laravel\Cqrs\ContainerHandlerResolver;

final class ContainerHandlerResolverTest extends TestCase
{
    #[Test]
    public function resolvesHandlerFromContainer(): void
    {
        $resolver = new ContainerHandlerResolver(
            $this->app,
            [FakeCommand::class => FakeCommandHandler::class],
        );

        $handler = $resolver->resolve(new FakeCommand());

        self::assertInstanceOf(FakeCommandHandler::class, $handler);
    }

    #[Test]
    public function resolvedHandlerIsCallable(): void
    {
        $resolver = new ContainerHandlerResolver(
            $this->app,
            [FakeCommand::class => FakeCommandHandler::class],
        );

        $handler = $resolver->resolve(new FakeCommand());

        self::assertIsCallable($handler);
    }

    #[Test]
    public function handlerReceivesDependenciesFromContainer(): void
    {
        $this->app->singleton(FakeDependency::class);

        $resolver = new ContainerHandlerResolver(
            $this->app,
            [FakeCommand::class => FakeHandlerWithDependency::class],
        );

        $handler = $resolver->resolve(new FakeCommand());

        self::assertInstanceOf(FakeHandlerWithDependency::class, $handler);
        self::assertInstanceOf(FakeDependency::class, $handler->dependency);
    }

    #[Test]
    public function throwsWhenHandlerNotFound(): void
    {
        $resolver = new ContainerHandlerResolver($this->app);

        $this->expectException(HandlerNotFoundException::class);

        $resolver->resolve(new FakeCommand());
    }
}

final readonly class FakeCommand {}

final class FakeCommandHandler
{
    public function __invoke(FakeCommand $command): void {}
}

final class FakeDependency {}

final readonly class FakeHandlerWithDependency
{
    public function __construct(public FakeDependency $dependency) {}

    public function __invoke(FakeCommand $command): void {}
}
