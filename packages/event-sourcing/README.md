# SolidFrame Event Sourcing

Event Sourcing building blocks: EventStore, Snapshot, and event-sourced AggregateRoot.

Store every state change as a domain event. Rebuild aggregate state by replaying events. Optimize with snapshots.

## Installation

```bash
composer require solidframe/event-sourcing
```

## Quick Start

### Define an Event-Sourced Aggregate

```php
use SolidFrame\EventSourcing\Aggregate\AbstractEventSourcedAggregateRoot;
use SolidFrame\Core\Event\DomainEventInterface;

final class BankAccount extends AbstractEventSourcedAggregateRoot
{
    private int $balance = 0;

    public static function open(AccountId $id, int $initialDeposit): self
    {
        $account = new self($id);
        $account->recordThat(new AccountOpened($id->value(), $initialDeposit));

        return $account;
    }

    public function deposit(int $amount): void
    {
        $this->recordThat(new MoneyDeposited($this->identity()->value(), $amount));
    }

    public function withdraw(int $amount): void
    {
        ($this->balance >= $amount) or throw InsufficientFunds::forAccount($this->identity()->value());

        $this->recordThat(new MoneyWithdrawn($this->identity()->value(), $amount));
    }

    // Event apply methods — called automatically during reconstitution
    protected function applyAccountOpened(AccountOpened $event): void
    {
        $this->balance = $event->initialDeposit;
    }

    protected function applyMoneyDeposited(MoneyDeposited $event): void
    {
        $this->balance += $event->amount;
    }

    protected function applyMoneyWithdrawn(MoneyWithdrawn $event): void
    {
        $this->balance -= $event->amount;
    }
}
```

### Persist and Load

```php
use SolidFrame\EventSourcing\Repository\AggregateRootRepository;

$repository = new AggregateRootRepository(BankAccount::class, $eventStore);

// Save
$account = BankAccount::open(AccountId::generate(), 1000);
$account->deposit(500);
$repository->save($account);

// Load — replays all events to rebuild state
$account = $repository->load($accountId);
```

## Event Store

The `EventStoreInterface` defines how events are stored and loaded.

```php
use SolidFrame\EventSourcing\Store\EventStoreInterface;

// Persist events with optimistic concurrency control
$eventStore->persist($aggregateId, expectedVersion: 0, events: [
    new AccountOpened($aggregateId->value(), 1000),
]);

// Load all events
$events = $eventStore->load($aggregateId);

// Load from a specific version
$events = $eventStore->loadFromVersion($aggregateId, fromVersion: 5);
```

### Concurrency Control

The event store uses optimistic locking. If another process has written events since you loaded, a `ConcurrencyException` is thrown:

```php
use SolidFrame\EventSourcing\Exception\ConcurrencyException;

try {
    $eventStore->persist($aggregateId, expectedVersion: 3, events: $newEvents);
} catch (ConcurrencyException $e) {
    // Version conflict — reload and retry
}
```

### In-Memory Store

For testing and prototyping:

```php
use SolidFrame\EventSourcing\Store\InMemoryEventStore;

$eventStore = new InMemoryEventStore();
```

## Snapshots

Snapshots optimize loading for aggregates with many events.

### Make an Aggregate Snapshotable

```php
use SolidFrame\EventSourcing\Snapshot\SnapshotableAggregateRootInterface;

final class BankAccount extends AbstractEventSourcedAggregateRoot
    implements SnapshotableAggregateRootInterface
{
    private int $balance = 0;

    public function createSnapshotState(): mixed
    {
        return ['balance' => $this->balance];
    }

    public static function reconstituteFromSnapshot(
        IdentityInterface $id,
        int $version,
        mixed $state,
        iterable $remainingEvents,
    ): static {
        $account = new self($id);
        $account->balance = $state['balance'];
        $account->aggregateRootVersion = $version;

        foreach ($remainingEvents as $event) {
            $account->applyEvent($event);
        }

        return $account;
    }

    // ... rest of the aggregate
}
```

### Snapshot Repository

```php
use SolidFrame\EventSourcing\Snapshot\SnapshotAggregateRootRepository;
use SolidFrame\EventSourcing\Snapshot\Snapshot;

$repository = new SnapshotAggregateRootRepository(
    BankAccount::class,
    $eventStore,
    $snapshotStore,
);

// Load: uses snapshot + remaining events (faster than full replay)
$account = $repository->load($accountId);

// Save a snapshot manually
$snapshotStore->save(new Snapshot(
    aggregateId: $accountId->value(),
    aggregateType: BankAccount::class,
    version: $account->aggregateRootVersion(),
    state: $account->createSnapshotState(),
));
```

## Event Apply Convention

When replaying events, the aggregate calls `apply{EventShortName}()` automatically:

| Event Class | Apply Method |
|---|---|
| `OrderPlaced` | `applyOrderPlaced()` |
| `MoneyDeposited` | `applyMoneyDeposited()` |
| `AccountOpened` | `applyAccountOpened()` |

These methods are `protected` and must not be called directly.

## API Reference

| Class / Interface | Purpose |
|---|---|
| `EventSourcedAggregateRootInterface` | Contract for event-sourced aggregates |
| `AbstractEventSourcedAggregateRoot` | Base aggregate with `recordThat()` and replay |
| `EventStoreInterface` | Event persistence contract |
| `InMemoryEventStore` | In-memory event store |
| `AggregateRootRepositoryInterface` | Aggregate persistence contract |
| `AggregateRootRepository` | Standard repository (full replay) |
| `SnapshotableAggregateRootInterface` | Contract for snapshotable aggregates |
| `Snapshot` | Snapshot value object |
| `SnapshotStoreInterface` | Snapshot persistence contract |
| `InMemorySnapshotStore` | In-memory snapshot store |
| `SnapshotAggregateRootRepository` | Repository with snapshot optimization |
| `AggregateNotFoundException` | Aggregate not found in event store |
| `ConcurrencyException` | Version conflict during persist |

## Related Packages

- [solidframe/core](../core) — DomainEventInterface, Identity
- [solidframe/ddd](../ddd) — Entity, AggregateRoot base classes
- [solidframe/cqrs](../cqrs) — Command/Query handlers that use event-sourced aggregates
- [solidframe/laravel](../laravel) — Database EventStore/SnapshotStore, migrations
- [solidframe/symfony](../symfony) — DBAL EventStore/SnapshotStore, schema SQL
