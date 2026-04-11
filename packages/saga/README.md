# SolidFrame Saga

Saga / Process Manager building blocks: saga lifecycle, compensation, correlation, and persistence.

Orchestrate multi-step business processes with automatic compensation on failure.

## Installation

```bash
composer require solidframe/saga
```

## Quick Start

### Define a Saga

```php
use SolidFrame\Saga\Saga\AbstractSaga;

final class PlaceOrderSaga extends AbstractSaga
{
    public function handleOrderPlaced(OrderPlaced $event): void
    {
        // Correlate saga to order
        $this->associateWith('orderId', $event->orderId);

        // Step 1: Reserve inventory
        $this->reserveInventory($event);

        // Register compensation in case of failure
        $this->addCompensation(fn () => $this->releaseInventory($event->orderId));
    }

    public function handlePaymentCompleted(PaymentCompleted $event): void
    {
        // Step 2: Confirm order
        $this->confirmOrder($event->orderId);
        $this->complete();
    }

    public function handlePaymentFailed(PaymentFailed $event): void
    {
        // Triggers all compensations in reverse order
        $this->fail();
    }

    // ...
}
```

### Saga Lifecycle

```php
$saga = new PlaceOrderSaga();

$saga->status();      // SagaStatus::InProgress
$saga->isCompleted(); // false
$saga->isFailed();    // false

// After complete()
$saga->status();      // SagaStatus::Completed
$saga->isCompleted(); // true

// After fail() — compensations execute automatically
$saga->status();      // SagaStatus::Failed
$saga->isFailed();    // true
```

## Associations (Correlation)

Sagas are correlated to external entities via key-value associations:

```php
$saga->associateWith('orderId', 'order-123');
$saga->associateWith('customerId', 'customer-456');

$saga->associations();
// [Association(key: 'orderId', value: 'order-123'), ...]

$saga->removeAssociation('customerId');
```

Find sagas by association:

```php
$saga = $sagaStore->findByAssociation(
    PlaceOrderSaga::class,
    new Association('orderId', 'order-123'),
);
```

## Compensation

Compensations are registered during saga execution and run in **reverse order** on failure:

```php
// Step 1
$this->reserveInventory($orderId);
$this->addCompensation(fn () => $this->releaseInventory($orderId));

// Step 2
$this->chargePayment($orderId);
$this->addCompensation(fn () => $this->refundPayment($orderId));

// On failure: refundPayment() runs first, then releaseInventory()
$this->fail();
```

You can also trigger compensation manually:

```php
$saga->compensate();
```

## Persistence

### Saga Store

```php
use SolidFrame\Saga\Store\SagaStoreInterface;

// Save
$sagaStore->save($saga);

// Find by ID
$saga = $sagaStore->find('saga-id');

// Find by association
$saga = $sagaStore->findByAssociation(
    PlaceOrderSaga::class,
    new Association('orderId', 'order-123'),
);

// Delete
$sagaStore->delete('saga-id');
```

### In-Memory Store

For testing and prototyping:

```php
use SolidFrame\Saga\Store\InMemorySagaStore;

$store = new InMemorySagaStore();
```

## Saga Status

```php
use SolidFrame\Saga\State\SagaStatus;

SagaStatus::InProgress; // 'in_progress' — saga is executing
SagaStatus::Completed;  // 'completed'   — saga finished successfully
SagaStatus::Failed;     // 'failed'      — saga failed, compensations applied
```

## API Reference

| Class / Interface | Purpose |
|---|---|
| `SagaInterface` | Contract for saga objects |
| `AbstractSaga` | Base saga with lifecycle and compensation |
| `SagaStoreInterface` | Persistence contract |
| `InMemorySagaStore` | In-memory store for testing |
| `Association` | Key-value correlation object |
| `SagaStatus` | Enum: InProgress, Completed, Failed |
| `SagaNotFoundException` | Saga not found in store |

## Related Packages

- [solidframe/core](../core) — Base exception interface
- [solidframe/modular](../modular) — Sagas often orchestrate cross-module processes
- [solidframe/cqrs](../cqrs) — Saga handlers dispatch commands
- [solidframe/laravel](../laravel) — Database SagaStore, `make:saga`, `solidframe:saga:status`
- [solidframe/symfony](../symfony) — DBAL SagaStore, same generators
