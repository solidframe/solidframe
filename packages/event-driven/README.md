# SolidFrame Event-Driven

EventBus implementation with listener resolution and middleware support.

Dispatch domain events to one or more listeners. Decouple your application with event-driven communication.

## Installation

```bash
composer require solidframe/event-driven
```

## Quick Start

### Define an Event

```php
use SolidFrame\Core\Event\DomainEventInterface;

final readonly class OrderPlaced implements DomainEventInterface
{
    public function __construct(
        public string $orderId,
        public string $customerId,
        private DateTimeImmutable $occurredAt = new DateTimeImmutable(),
    ) {}

    public function eventName(): string
    {
        return 'order.placed';
    }

    public function occurredAt(): DateTimeImmutable
    {
        return $this->occurredAt;
    }
}
```

### Define Listeners

```php
use SolidFrame\EventDriven\EventListener;

final readonly class SendOrderConfirmation implements EventListener
{
    public function __construct(private Mailer $mailer) {}

    public function __invoke(OrderPlaced $event): void
    {
        $this->mailer->send($event->customerId, 'Your order has been placed.');
    }
}

final readonly class UpdateInventory implements EventListener
{
    public function __construct(private InventoryService $inventory) {}

    public function __invoke(OrderPlaced $event): void
    {
        $this->inventory->reserve($event->orderId);
    }
}
```

### Dispatch

```php
$eventBus->dispatch(new OrderPlaced(
    orderId: 'order-123',
    customerId: 'customer-456',
));
// Both SendOrderConfirmation and UpdateInventory will be called
```

## Standalone Usage

Without a framework bridge:

```php
use SolidFrame\EventDriven\Listener\InMemoryListenerResolver;
use SolidFrame\EventDriven\Bus\EventBus;

$resolver = new InMemoryListenerResolver();
$resolver->listen(OrderPlaced::class, new SendOrderConfirmation($mailer));
$resolver->listen(OrderPlaced::class, new UpdateInventory($inventory));

$eventBus = new EventBus($resolver);
$eventBus->dispatch(new OrderPlaced('order-123', 'customer-456'));
```

## Middleware

Add cross-cutting concerns to event processing.

```php
use SolidFrame\Core\Middleware\MiddlewareInterface;

final readonly class EventLoggingMiddleware implements MiddlewareInterface
{
    public function __construct(private LoggerInterface $logger) {}

    public function handle(object $message, callable $next): mixed
    {
        $this->logger->info('Event dispatched', [
            'event' => $message::class,
        ]);

        return $next($message);
    }
}

$eventBus = new EventBus($resolver, [
    new EventLoggingMiddleware($logger),
]);
```

## Key Differences from CQRS

| | Command/Query | Event |
|---|---|---|
| Handlers | Exactly one | Zero or more |
| Return value | Query returns data | None |
| Purpose | Execute action / fetch data | Notify what happened |

## API Reference

| Class / Interface | Purpose |
|---|---|
| `EventListener` | Marker interface for listeners |
| `ListenerResolverInterface` | Contract for resolving listeners per event |
| `InMemoryListenerResolver` | In-memory listener registry |
| `EventBus` | Dispatches events to all resolved listeners |

## Related Packages

- [solidframe/core](../core) — `DomainEventInterface`, `EventBusInterface`, Middleware
- [solidframe/ddd](../ddd) — AggregateRoot records events via `recordThat()`
- [solidframe/cqrs](../cqrs) — Dispatch events after command handling
- [solidframe/event-sourcing](../event-sourcing) — Persist events as source of truth
- [solidframe/laravel](../laravel) — Auto-discovery, DI, `make:domain-event`, `make:event-listener`
- [solidframe/symfony](../symfony) — Compiler pass, DI, same generators

## Contributing

This repository is a read-only split of the [solidframe/solidframe](https://github.com/solidframe/solidframe) monorepo, auto-synced on every push to `main`. Issues, pull requests, and discussions belong in the monorepo.
