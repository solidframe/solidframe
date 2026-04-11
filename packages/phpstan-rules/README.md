# SolidFrame PHPStan Rules

PHPStan rules for DDD, CQRS, and Event Sourcing architectural enforcement.

Static analysis catches architectural violations before tests even run.

## Installation

```bash
composer require solidframe/phpstan-rules --dev
```

The rules are auto-registered via PHPStan's extension mechanism. No manual configuration needed.

## Rules

### CQRS Rules

#### Command Handler Must Return Void

Command handlers perform side effects and must not return values.

```php
// OK
final readonly class PlaceOrderHandler implements CommandHandler
{
    public function __invoke(PlaceOrder $command): void { /* ... */ }
}

// ERROR: Command handler must return void
final readonly class PlaceOrderHandler implements CommandHandler
{
    public function __invoke(PlaceOrder $command): Order { /* ... */ }
}
```

#### Query Handler Must Not Return Void

Query handlers must return data.

```php
// OK
final readonly class GetOrderHandler implements QueryHandler
{
    public function __invoke(GetOrder $query): OrderDto { /* ... */ }
}

// ERROR: Query handler must return a value
final readonly class GetOrderHandler implements QueryHandler
{
    public function __invoke(GetOrder $query): void { /* ... */ }
}
```

#### Handler Must Be Invokable

Handlers must implement `__invoke()` and have only one public method (besides `__construct`).

```php
// OK
final readonly class PlaceOrderHandler implements CommandHandler
{
    public function __invoke(PlaceOrder $command): void { /* ... */ }
}

// ERROR: Handler must implement __invoke()
final readonly class PlaceOrderHandler implements CommandHandler
{
    public function handle(PlaceOrder $command): void { /* ... */ }
}

// ERROR: Handler must have only one public method
final readonly class PlaceOrderHandler implements CommandHandler
{
    public function __invoke(PlaceOrder $command): void { /* ... */ }
    public function anotherMethod(): void { /* ... */ }
}
```

#### Messages Must Be Final Readonly

Commands and Queries must be declared as `final readonly`.

```php
// OK
final readonly class PlaceOrder implements Command {}

// ERROR: Command must be final
class PlaceOrder implements Command {}

// ERROR: Command must be readonly
final class PlaceOrder implements Command {}
```

#### Messages Must Not Extend

Commands and Queries must not extend other classes. Use composition.

```php
// OK
final readonly class PlaceOrder implements Command
{
    public function __construct(public string $orderId) {}
}

// ERROR: Commands/Queries must not extend other classes
final readonly class PlaceOrder extends BaseCommand implements Command {}
```

### DDD Rules

#### Value Object Must Be Readonly

Value objects are immutable. The class must be declared as `readonly`.

```php
// OK
final readonly class Email extends StringValueObject {}

// ERROR: Value object must be readonly
final class Email extends StringValueObject {}
```

#### No Direct Aggregate Construction

Aggregate roots must be created via static factory methods, not `new`.

```php
// OK
$order = Order::place($orderId, $customerId);

// ERROR: Use a static factory method instead of new
$order = new Order($orderId);
```

Construction inside the aggregate class itself is allowed.

### Event Sourcing Rules

#### Events Must Be Final Readonly

Domain events are immutable data structures.

```php
// OK
final readonly class OrderPlaced implements DomainEventInterface {}

// ERROR: Event must be final and readonly
class OrderPlaced implements DomainEventInterface {}
```

#### Apply Method Must Exist

For every event recorded via `recordThat()`, a corresponding `apply{EventName}()` method must exist.

```php
// OK
final class Order extends AbstractEventSourcedAggregateRoot
{
    public function place(): void
    {
        $this->recordThat(new OrderPlaced(/* ... */));
    }

    protected function applyOrderPlaced(OrderPlaced $event): void
    {
        // apply state change
    }
}

// ERROR: Missing method applyOrderPlaced
final class Order extends AbstractEventSourcedAggregateRoot
{
    public function place(): void
    {
        $this->recordThat(new OrderPlaced(/* ... */));
    }
    // no applyOrderPlaced method!
}
```

## Configuration

Rules work out of the box with SolidFrame interfaces. To use with custom interfaces, override in your `phpstan.neon`:

```neon
parameters:
    solidframe:
        commandHandlerInterface: App\Cqrs\CommandHandler
        queryHandlerInterface: App\Cqrs\QueryHandler
        commandInterface: App\Cqrs\Command
        queryInterface: App\Cqrs\Query
        eventInterface: App\Event\DomainEvent
        valueObjectInterface: App\Ddd\ValueObject
        aggregateRootClass: App\Ddd\AggregateRoot
```

## Rule Summary

| Rule | ID | Area |
|---|---|---|
| Command handler returns void | `solidframe.commandHandlerMustReturnVoid` | CQRS |
| Query handler returns data | `solidframe.queryHandlerMustNotReturnVoid` | CQRS |
| Handler is invokable | `solidframe.handlerMustBeInvokable` | CQRS |
| Handler has single public method | `solidframe.handlerSinglePublicMethod` | CQRS |
| Command is final | `solidframe.commandMustBeFinal` | CQRS |
| Command is readonly | `solidframe.commandMustBeReadonly` | CQRS |
| Query is final | `solidframe.queryMustBeFinal` | CQRS |
| Query is readonly | `solidframe.queryMustBeReadonly` | CQRS |
| Message has no parent class | `solidframe.messageMustNotExtend` | CQRS |
| Value object is readonly | `solidframe.valueObjectMustBeReadonly` | DDD |
| No direct aggregate construction | `solidframe.noDirectAggregateConstruction` | DDD |
| Event is final | `solidframe.eventMustBeFinal` | Event Sourcing |
| Event is readonly | `solidframe.eventMustBeReadonly` | Event Sourcing |
| Apply method exists for recorded events | `solidframe.applyMethodMustExist` | Event Sourcing |

## Complementary: ArchTest vs PHPStan Rules

| Concern | ArchTest | PHPStan Rules |
|---|---|---|
| Namespace dependencies | `doesNotDependOn()` | — |
| Class structure (final, readonly) | `areFinal()`, `areReadonly()` | Message/VO/Event rules |
| Handler return types | — | Command void, Query non-void |
| Handler conventions | — | Invokable, single public method |
| Event apply methods | — | `applyMethodMustExist` |
| Aggregate construction | — | `noDirectAggregateConstruction` |
| Module isolation | Modular preset | — |

Use both for comprehensive architectural enforcement.

## Related Packages

- [solidframe/core](../core) — DomainEventInterface
- [solidframe/cqrs](../cqrs) — Command, Query, Handler interfaces
- [solidframe/ddd](../ddd) — ValueObjectInterface
- [solidframe/event-sourcing](../event-sourcing) — AbstractEventSourcedAggregateRoot
- [solidframe/archtest](../archtest) — Complementary PHPUnit-based architecture tests
