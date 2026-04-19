# SolidFrame CQRS

Command Query Responsibility Segregation: CommandBus, QueryBus, Handlers, and Middleware.

Commands produce side effects and return nothing. Queries return data and produce no side effects.

## Installation

```bash
composer require solidframe/cqrs
```

## Quick Start

### Define a Command and Handler

```php
use SolidFrame\Cqrs\Command;
use SolidFrame\Cqrs\CommandHandler;

final readonly class PlaceOrder implements Command
{
    public function __construct(
        public string $orderId,
        public string $customerId,
    ) {}
}

final readonly class PlaceOrderHandler implements CommandHandler
{
    public function __construct(private OrderRepository $orders) {}

    public function __invoke(PlaceOrder $command): void
    {
        $order = Order::place(
            new OrderId($command->orderId),
            new CustomerId($command->customerId),
        );

        $this->orders->save($order);
    }
}
```

### Define a Query and Handler

```php
use SolidFrame\Cqrs\Query;
use SolidFrame\Cqrs\QueryHandler;

final readonly class GetOrderById implements Query
{
    public function __construct(public string $orderId) {}
}

final readonly class GetOrderByIdHandler implements QueryHandler
{
    public function __construct(private OrderRepository $orders) {}

    public function __invoke(GetOrderById $query): ?OrderDto
    {
        $order = $this->orders->find(new OrderId($query->orderId));

        return $order ? OrderDto::fromEntity($order) : null;
    }
}
```

### Dispatch

```php
// Command — fire and forget
$commandBus->dispatch(new PlaceOrder(
    orderId: 'order-123',
    customerId: 'customer-456',
));

// Query — get result
$order = $queryBus->ask(new GetOrderById(orderId: 'order-123'));
```

## Standalone Usage

Without a framework bridge, wire the bus manually:

```php
use SolidFrame\Cqrs\Handler\InMemoryHandlerResolver;
use SolidFrame\Cqrs\Bus\CommandBus;
use SolidFrame\Cqrs\Bus\QueryBus;

$resolver = new InMemoryHandlerResolver();
$resolver->register(PlaceOrder::class, new PlaceOrderHandler($orders));

$commandBus = new CommandBus($resolver);
$commandBus->dispatch(new PlaceOrder('order-123', 'customer-456'));
```

## Middleware

Add cross-cutting concerns to bus processing.

```php
use SolidFrame\Core\Middleware\MiddlewareInterface;

final readonly class TransactionMiddleware implements MiddlewareInterface
{
    public function __construct(private Connection $connection) {}

    public function handle(object $message, callable $next): mixed
    {
        $this->connection->beginTransaction();

        try {
            $result = $next($message);
            $this->connection->commit();

            return $result;
        } catch (\Throwable $e) {
            $this->connection->rollBack();
            throw $e;
        }
    }
}

// With middleware
$commandBus = new CommandBus($resolver, [
    new TransactionMiddleware($connection),
    new LoggingMiddleware($logger),
]);
```

Middleware executes in registration order. Each middleware calls `$next($message)` to pass to the next one.

## Handler Resolution

Handlers are resolved by the message type passed to `__invoke()`. The convention:

- `PlaceOrder` command → `PlaceOrderHandler::__invoke(PlaceOrder $command)`
- `GetOrderById` query → `GetOrderByIdHandler::__invoke(GetOrderById $query)`

One handler per command/query. Multiple handlers for the same message type will throw an exception.

## API Reference

| Class / Interface | Purpose |
|---|---|
| `Command` | Marker interface for commands |
| `Query` | Marker interface for queries |
| `CommandHandler` | Marker interface for command handlers |
| `QueryHandler` | Marker interface for query handlers |
| `CommandBus` | Dispatches commands through middleware to handlers |
| `QueryBus` | Dispatches queries through middleware to handlers |
| `MessageBus` | Abstract base for both buses |
| `HandlerResolverInterface` | Contract for resolving handlers |
| `InMemoryHandlerResolver` | In-memory handler registry |
| `HandlerNotFoundException` | Thrown when no handler matches |

## Related Packages

- [solidframe/core](../core) — Bus interfaces, Middleware contract
- [solidframe/ddd](../ddd) — Entities and aggregates your handlers operate on
- [solidframe/event-driven](../event-driven) — Dispatch domain events after commands
- [solidframe/laravel](../laravel) — Auto-discovery, DI, `make:cqrs-command`, `make:query`
- [solidframe/symfony](../symfony) — Compiler pass, DI, same generators

## Contributing

This repository is a read-only split of the [solidframe/solidframe](https://github.com/solidframe/solidframe) monorepo, auto-synced on every push to `main`. Issues, pull requests, and discussions belong in the monorepo.
