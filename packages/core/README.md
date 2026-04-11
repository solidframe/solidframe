# SolidFrame Core

Core interfaces, base classes, Pipeline, and Middleware for the SolidFrame ecosystem.

All SolidFrame packages depend on this package. It provides the shared contracts that keep the ecosystem consistent and composable.

## Installation

```bash
composer require solidframe/core
```

## Components

### Identity

Type-safe identity objects for your entities.

```php
use SolidFrame\Core\Identity\AbstractIdentity;
use SolidFrame\Core\Identity\UuidIdentity;

// Simple identity
final readonly class UserId extends AbstractIdentity {}

$id = new UserId('user-123');
$id->value();              // 'user-123'
$id->equals(new UserId('user-123')); // true

// UUID identity with auto-generation
final readonly class OrderId extends UuidIdentity {}

$id = OrderId::generate(); // random UUIDv4
```

### Bus Interfaces

Minimal bus contracts for CQRS and event-driven architectures.

```php
use SolidFrame\Core\Bus\CommandBusInterface;
use SolidFrame\Core\Bus\QueryBusInterface;
use SolidFrame\Core\Bus\EventBusInterface;

// Command — side effect, no return value
$commandBus->dispatch($command); // void

// Query — returns data, no side effect
$result = $queryBus->ask($query); // mixed

// Event — notification, no return value
$eventBus->dispatch($event); // void
```

### Domain Events

Base contract for all domain events.

```php
use SolidFrame\Core\Event\DomainEventInterface;

final readonly class OrderPlaced implements DomainEventInterface
{
    public function __construct(
        private string $orderId,
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

### Pipeline

Immutable, composable data processing pipeline.

```php
use SolidFrame\Core\Pipeline\Pipeline;

$pipeline = new Pipeline();

$result = $pipeline
    ->pipe(fn (string $payload) => strtoupper($payload))
    ->pipe(fn (string $payload) => trim($payload))
    ->process('  hello world  ');

// 'HELLO WORLD'
```

Implement `StageInterface` for reusable stages:

```php
use SolidFrame\Core\Pipeline\StageInterface;

final readonly class FormatPrice implements StageInterface
{
    public function __invoke(mixed $payload): mixed
    {
        return number_format($payload / 100, 2) . ' TL';
    }
}

$pipeline = (new Pipeline())->pipe(new FormatPrice());
$pipeline->process(1500); // '15.00 TL'
```

### Middleware

Middleware contract for bus implementations.

```php
use SolidFrame\Core\Middleware\MiddlewareInterface;

final readonly class LoggingMiddleware implements MiddlewareInterface
{
    public function __construct(private LoggerInterface $logger) {}

    public function handle(object $message, callable $next): mixed
    {
        $this->logger->info('Handling: ' . $message::class);
        $result = $next($message);
        $this->logger->info('Handled: ' . $message::class);

        return $result;
    }
}
```

### Exceptions

Named constructor pattern for clear, consistent error messages.

```php
use SolidFrame\Core\Exception\EntityNotFoundException;

throw EntityNotFoundException::forId('order-123');
// "Entity with id "order-123" was not found."

throw EntityNotFoundException::forClassAndId(Order::class, 'order-123');
// "Entity App\Domain\Order with id "order-123" was not found."
```

All exceptions implement `SolidFrameException` marker interface for catch-all handling.

### Application Service

Marker interface for use case handlers.

```php
use SolidFrame\Core\Service\ApplicationServiceInterface;

final readonly class PlaceOrderService implements ApplicationServiceInterface
{
    public function __invoke(PlaceOrderCommand $command): void
    {
        // ...
    }
}
```

## API Reference

| Class / Interface | Purpose |
|---|---|
| `IdentityInterface` | Contract for identity objects |
| `AbstractIdentity` | Base identity with equality |
| `UuidIdentity` | UUID-based identity with `generate()` |
| `DomainEventInterface` | Contract for domain events |
| `CommandBusInterface` | Command dispatch contract |
| `QueryBusInterface` | Query dispatch contract |
| `EventBusInterface` | Event dispatch contract |
| `MiddlewareInterface` | Middleware chain contract |
| `PipelineInterface` | Pipeline contract |
| `Pipeline` | Immutable pipeline implementation |
| `StageInterface` | Callable pipeline stage contract |
| `ApplicationServiceInterface` | Marker for application services |
| `SolidFrameException` | Base exception marker |
| `EntityNotFoundException` | Entity lookup failure |
| `InvalidArgumentException` | Validation failure |

## Related Packages

- [solidframe/ddd](../ddd) — Entity, ValueObject, AggregateRoot, Specification
- [solidframe/cqrs](../cqrs) — CommandBus, QueryBus, Handlers
- [solidframe/event-driven](../event-driven) — EventBus, Listeners
- [solidframe/laravel](../laravel) — Laravel integration
- [solidframe/symfony](../symfony) — Symfony integration
