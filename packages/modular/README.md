# SolidFrame Modular

Modular monolith building blocks: module contracts, integration events, Anti-Corruption Layer, and module registry.

Build isolated modules that communicate through contracts, not concrete dependencies.

## Installation

```bash
composer require solidframe/modular
```

## Quick Start

### Define a Module

```php
use SolidFrame\Modular\Module\AbstractModule;

final class OrderModule extends AbstractModule
{
    public function __construct()
    {
        parent::__construct(
            name: 'order',
            dependsOn: ['inventory', 'payment'],
        );
    }
}
```

### Register Modules

```php
use SolidFrame\Modular\Registry\InMemoryModuleRegistry;

$registry = new InMemoryModuleRegistry();
$registry->register(new OrderModule());
$registry->register(new InventoryModule());
$registry->register(new PaymentModule());

// List all modules
$modules = $registry->all();

// Get by name
$order = $registry->get('order');
$order->dependsOn(); // ['inventory', 'payment']

// Topological sort — respects dependency order
$sorted = $registry->dependencyOrder();
// [InventoryModule, PaymentModule, OrderModule]
```

Circular dependencies are detected automatically:

```php
use SolidFrame\Modular\Exception\CircularDependencyException;

// A depends on B, B depends on A → throws CircularDependencyException
```

## Module Contracts

Define what a module exposes to others via `ModuleContractInterface`:

```php
use SolidFrame\Modular\Contract\ModuleContractInterface;

interface InventoryContractInterface extends ModuleContractInterface
{
    public function reserve(string $productId, int $quantity): void;
    public function checkAvailability(string $productId): int;
}
```

Other modules depend on the contract, never on the implementation:

```php
final readonly class PlaceOrderHandler implements CommandHandler
{
    public function __construct(
        private InventoryContractInterface $inventory,
    ) {}

    public function __invoke(PlaceOrder $command): void
    {
        $this->inventory->reserve($command->productId, $command->quantity);
        // ...
    }
}
```

## Integration Events

Modules communicate asynchronously via integration events:

```php
use SolidFrame\Modular\Event\AbstractIntegrationEvent;

final readonly class OrderPlacedIntegration extends AbstractIntegrationEvent
{
    public function __construct(
        public string $orderId,
        public string $productId,
        public int $quantity,
    ) {
        parent::__construct(sourceModule: 'order');
    }

    public function eventName(): string
    {
        return 'order.placed';
    }
}

// In another module's listener:
final readonly class ReserveInventoryOnOrderPlaced implements EventListener
{
    public function __invoke(OrderPlacedIntegration $event): void
    {
        $event->sourceModule(); // 'order'
        $event->occurredAt();   // DateTimeImmutable
        // reserve inventory...
    }
}
```

## Anti-Corruption Layer

Translate between module boundaries with `TranslatorInterface`:

```php
use SolidFrame\Modular\AntiCorruption\TranslatorInterface;

/** @implements TranslatorInterface<ExternalOrder, DomainOrder> */
final readonly class OrderTranslator implements TranslatorInterface
{
    public function translate(object $source): object
    {
        return new DomainOrder(
            id: new OrderId($source->getId()),
            total: Money::from($source->getTotal(), $source->getCurrency()),
        );
    }
}
```

## API Reference

| Class / Interface | Purpose |
|---|---|
| `ModuleInterface` | Contract for module definition |
| `AbstractModule` | Base module with name and dependencies |
| `ModuleRegistryInterface` | Module registration and lookup |
| `InMemoryModuleRegistry` | In-memory registry with topological sort |
| `ModuleContractInterface` | Marker for module public APIs |
| `IntegrationEventInterface` | Cross-module event contract |
| `AbstractIntegrationEvent` | Base integration event |
| `TranslatorInterface` | Anti-Corruption Layer translator |
| `ModuleNotFoundException` | Module not found in registry |
| `CircularDependencyException` | Circular module dependency detected |

## Related Packages

- [solidframe/core](../core) — DomainEventInterface, Bus interfaces
- [solidframe/event-driven](../event-driven) — EventBus for integration events
- [solidframe/cqrs](../cqrs) — Command/Query handling within modules
- [solidframe/archtest](../archtest) — Enforce module isolation rules
- [solidframe/laravel](../laravel) — ModuleServiceProvider, auto-discovery, `make:module`, `solidframe:module:list`
- [solidframe/symfony](../symfony) — Module discovery, `make:module`, `solidframe:module:list`
