<?php

declare(strict_types=1);

namespace SolidFrame\Laravel\Tests\Discovery;

use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use SolidFrame\Cqrs\CommandHandler;
use SolidFrame\Cqrs\QueryHandler;
use SolidFrame\EventDriven\EventListener;
use SolidFrame\Laravel\Discovery\HandlerDiscovery;
use SolidFrame\Laravel\Tests\Discovery\Fixtures\CreateOrderCommand;
use SolidFrame\Laravel\Tests\Discovery\Fixtures\CreateOrderHandler;
use SolidFrame\Laravel\Tests\Discovery\Fixtures\GetOrderHandler;
use SolidFrame\Laravel\Tests\Discovery\Fixtures\GetOrderQuery;
use SolidFrame\Laravel\Tests\Discovery\Fixtures\OrderCreatedEvent;
use SolidFrame\Laravel\Tests\Discovery\Fixtures\SendOrderConfirmationListener;
use SolidFrame\Laravel\Tests\Discovery\Fixtures\UpdateInventoryListener;

final class HandlerDiscoveryTest extends TestCase
{
    private string $fixturesPath;

    protected function setUp(): void
    {
        $this->fixturesPath = __DIR__ . '/Fixtures';
    }

    #[Test]
    public function discoversCommandHandlers(): void
    {
        $handlers = HandlerDiscovery::within([$this->fixturesPath], CommandHandler::class);

        self::assertArrayHasKey(CreateOrderCommand::class, $handlers);
        self::assertSame(CreateOrderHandler::class, $handlers[CreateOrderCommand::class]);
    }

    #[Test]
    public function discoversQueryHandlers(): void
    {
        $handlers = HandlerDiscovery::within([$this->fixturesPath], QueryHandler::class);

        self::assertArrayHasKey(GetOrderQuery::class, $handlers);
        self::assertSame(GetOrderHandler::class, $handlers[GetOrderQuery::class]);
    }

    #[Test]
    public function doesNotDiscoverClassesWithoutMarkerInterface(): void
    {
        $handlers = HandlerDiscovery::within([$this->fixturesPath], CommandHandler::class);

        // NotAHandler has __invoke(CreateOrderCommand) but doesn't implement CommandHandler
        self::assertCount(1, $handlers);
    }

    #[Test]
    public function discoversEventListeners(): void
    {
        $listeners = HandlerDiscovery::listeners([$this->fixturesPath], EventListener::class);

        self::assertArrayHasKey(OrderCreatedEvent::class, $listeners);
        self::assertCount(2, $listeners[OrderCreatedEvent::class]);
        self::assertContains(SendOrderConfirmationListener::class, $listeners[OrderCreatedEvent::class]);
        self::assertContains(UpdateInventoryListener::class, $listeners[OrderCreatedEvent::class]);
    }

    #[Test]
    public function returnsEmptyArrayForNonExistentDirectory(): void
    {
        $handlers = HandlerDiscovery::within(['/non/existent/path'], CommandHandler::class);

        self::assertSame([], $handlers);
    }

    #[Test]
    public function commandAndQueryHandlersDoNotMix(): void
    {
        $commandHandlers = HandlerDiscovery::within([$this->fixturesPath], CommandHandler::class);
        $queryHandlers = HandlerDiscovery::within([$this->fixturesPath], QueryHandler::class);

        self::assertArrayNotHasKey(GetOrderQuery::class, $commandHandlers);
        self::assertArrayNotHasKey(CreateOrderCommand::class, $queryHandlers);
    }
}
