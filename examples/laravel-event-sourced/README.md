# SolidFrame Example: Digital Wallet (Laravel + Event Sourcing + CQRS)

A digital wallet REST API built with Event Sourcing, CQRS, and Snapshot support using SolidFrame packages.

This example builds on CQRS patterns and adds **Event Sourcing** (state derived from events, not mutable rows), **Projections** (denormalized read models updated by event listeners), **Snapshot** support (performance optimization for long event streams), and **Temporal Queries** (replay events to a specific date to see past state).

## Packages Used

| Package | Purpose |
|---|---|
| `solidframe/core` | Identity, Bus interfaces (Command, Query) |
| `solidframe/ddd` | Entity, ValueObject, AggregateRoot |
| `solidframe/cqrs` | CommandBus, QueryBus, Handlers |
| `solidframe/event-driven` | DomainEventInterface |
| `solidframe/event-sourcing` | EventStore, Snapshot, AggregateRootRepository |
| `solidframe/laravel` | DatabaseEventStore, DatabaseSnapshotStore, handler discovery |
| `solidframe/archtest` | Architecture tests (DDD/CQRS presets, layer boundaries) |
| `solidframe/phpstan-rules` | Static analysis rules |

## Architecture

```
app/
├── Domain/
│   └── Account/
│       ├── Account.php                          (Event-sourced AggregateRoot + Snapshotable)
│       ├── AccountId.php                        (UuidIdentity)
│       ├── Currency.php                         (Enum: TRY, USD, EUR)
│       ├── ValueObject/
│       │   ├── AccountHolderName.php            (self-validating)
│       │   └── Money.php                        (amount + currency, arithmetic)
│       ├── Port/
│       │   └── AccountRepository.php            ← Driven Port (interface)
│       ├── Exception/
│       │   ├── AccountNotFoundException.php
│       │   ├── InsufficientBalanceException.php
│       │   ├── InvalidAmountException.php
│       │   └── SelfTransferException.php
│       └── Event/
│           ├── AccountOpened.php                 (DomainEvent)
│           ├── MoneyDeposited.php
│           ├── MoneyWithdrawn.php
│           ├── TransferSent.php
│           └── TransferReceived.php
│
├── Application/
│   ├── Command/
│   │   ├── OpenAccount.php + Handler            → creates aggregate, persists events
│   │   ├── DepositMoney.php + Handler           → loads aggregate from events, deposits
│   │   ├── WithdrawMoney.php + Handler          → balance check, then withdraw
│   │   └── TransferMoney.php + Handler          → debit source, credit target
│   └── Query/
│       ├── ListAccounts.php + Handler           → reads from projection table
│       ├── GetAccount.php + Handler             → reads from projection table
│       ├── GetTransactions.php + Handler        → reads from projection table
│       └── GetBalanceAt.php + Handler           ← Temporal Query (replays events to date)
│
├── Infrastructure/
│   ├── Persistence/
│   │   └── EventSourcedAccountRepository.php    (EventStore + Projection dispatch)
│   └── Projection/
│       ├── AccountBalanceProjection.php          → account_balances table (read model)
│       └── TransactionHistoryProjection.php      → account_transactions table (read model)
│
├── Http/
│   ├── Controllers/AccountController.php
│   └── Requests/
│       ├── OpenAccountRequest.php
│       ├── DepositRequest.php
│       ├── WithdrawRequest.php
│       └── TransferRequest.php
│
├── Console/Commands/
│   └── RebuildProjectionsCommand.php            ← solidframe:projection:rebuild
│
└── Providers/
    └── AppServiceProvider.php                   (DI: EventStore, SnapshotStore, Repository)
```

### Key Principles

- **Event Sourcing** — Account state is derived entirely from domain events. No mutable rows — the event stream is the single source of truth
- **CQRS** — Write side persists events to the EventStore; read side queries denormalized projection tables. Completely separate models
- **Projections** — Event listeners (`AccountBalanceProjection`, `TransactionHistoryProjection`) maintain read-optimized tables updated on every event
- **Temporal Query** — `GetBalanceAt` replays events up to a given date to reconstruct past state. This is a killer feature of event sourcing
- **Projection Rebuild** — `solidframe:projection:rebuild` replays all events from the store to rebuild read models from scratch
- **Snapshot support** — Account implements `SnapshotableAggregateRootInterface` to avoid replaying long event streams on every load

### Event Sourcing Flow

**Write side (Command):**
```
POST /api/accounts/{id}/deposit  { amount: 5000 }
  → DepositMoneyHandler
    → AccountRepository.load(id)
      → EventStore.load(id)             ← loads all events
      → Account::reconstituteFromEvents  ← replays events to rebuild state
    → account.deposit(5000)
      → recordThat(MoneyDeposited)       ← records new event + applies to state
    → AccountRepository.save(account)
      → EventStore.persist(events)       ← appends to event stream
      → AccountBalanceProjection.onMoneyDeposited()   ← updates read model
      → TransactionHistoryProjection.onMoneyDeposited()
```

**Read side (Query):**
```
GET /api/accounts/{id}
  → GetAccountHandler
    → DB::table('account_balances')     ← reads from projection (fast, no replay)
```

**Temporal Query:**
```
GET /api/accounts/{id}/balance-at?date=2026-04-10
  → GetBalanceAtHandler
    → EventStore.load(id)               ← loads all events
    → filter events where occurredAt <= date
    → Account::reconstituteFromEvents   ← replays filtered events
    → return balance at that point in time
```

### How It Differs from laravel-hexagonal-cqrs

| Aspect | laravel-hexagonal-cqrs | laravel-event-sourced |
|---|---|---|
| State storage | Eloquent models (mutable rows) | Event stream (append-only) |
| Read model | Same as write model | Separate projection tables |
| History | Lost on update | Full audit trail in EventStore |
| Past state | Not available | Temporal queries via event replay |
| Packages | core, ddd, cqrs, event-driven | + event-sourcing |
| Aggregate | `AbstractAggregateRoot` | `AbstractEventSourcedAggregateRoot` + `SnapshotableAggregateRootInterface` |
| Persistence | Eloquent Repository (CRUD) | EventStore.persist() + Projections |

## API Endpoints

| Method | URL | Description |
|---|---|---|
| `POST` | `/api/accounts` | Open a new account |
| `GET` | `/api/accounts` | List all accounts |
| `GET` | `/api/accounts/{id}` | Get account details |
| `POST` | `/api/accounts/{id}/deposit` | Deposit money |
| `POST` | `/api/accounts/{id}/withdraw` | Withdraw money |
| `POST` | `/api/accounts/{id}/transfer` | Transfer to another account |
| `GET` | `/api/accounts/{id}/transactions` | Transaction history |
| `GET` | `/api/accounts/{id}/balance-at?date=YYYY-MM-DD` | Balance at a specific date |

### Example Requests

```bash
# Open an account with initial balance
curl -X POST http://localhost:8004/api/accounts \
  -H "Content-Type: application/json" \
  -d '{"holder_name": "Kadir Posul", "currency": "TRY", "initial_balance": 50000}'

# Open a second account
curl -X POST http://localhost:8004/api/accounts \
  -H "Content-Type: application/json" \
  -d '{"holder_name": "Ali Veli", "currency": "TRY"}'

# Deposit money
curl -X POST http://localhost:8004/api/accounts/{id}/deposit \
  -H "Content-Type: application/json" \
  -d '{"amount": 10000, "description": "Salary"}'

# Withdraw money
curl -X POST http://localhost:8004/api/accounts/{id}/withdraw \
  -H "Content-Type: application/json" \
  -d '{"amount": 5000, "description": "ATM"}'

# Transfer between accounts
curl -X POST http://localhost:8004/api/accounts/{source-id}/transfer \
  -H "Content-Type: application/json" \
  -d '{"target_account_id": "{target-id}", "amount": 15000, "description": "Rent"}'

# View transaction history
curl http://localhost:8004/api/accounts/{id}/transactions

# Check balance at a past date (temporal query)
curl http://localhost:8004/api/accounts/{id}/balance-at?date=2026-04-10

# Rebuild projections from event store
php artisan solidframe:projection:rebuild
```

## Tests

```bash
# Unit tests (domain logic — no database, no framework)
php vendor/bin/phpunit tests/Unit

# Feature tests (API integration — uses SQLite)
php vendor/bin/phpunit tests/Feature

# Architecture tests (layer boundaries, DDD/CQRS presets)
php vendor/bin/phpunit tests/Architecture

# All tests
php vendor/bin/phpunit
```

**56 tests, 108 assertions** covering:
- Domain: Account lifecycle (open, deposit, withdraw, transfer), event recording, reconstitution from events, snapshot create/restore, version tracking
- Value objects: Money arithmetic (add, subtract, currency mismatch), AccountHolderName validation
- API: Account CRUD, deposit/withdraw/transfer flows, transaction history, temporal query, validation errors, multi-operation sequences
- Architecture: Domain/Application layer isolation from Infrastructure/HTTP/Laravel, DDD + CQRS presets, value object/event immutability, exception contracts

## Setup

```bash
# Install dependencies
composer install

# Create database and run migrations
php artisan migrate

# Start the server
php artisan serve
```

Or with Docker:

```bash
docker compose up -d
docker compose run --rm composer composer install
docker compose exec app php artisan migrate
```

## What This Example Demonstrates

1. **Event Sourcing** — State derived from domain events, not mutable database rows
2. **EventStore** — Append-only event persistence with optimistic concurrency control
3. **Projections** — Denormalized read models updated by event listeners
4. **Temporal Query** — Replay events to a specific date to see past account balance
5. **Projection Rebuild** — Artisan command to replay all events and rebuild read models from scratch
6. **Snapshot support** — `SnapshotableAggregateRootInterface` for performance with long event streams
7. **CQRS separation** — Write side uses EventStore, read side uses projection tables
8. **PHPStan level 8** — Full static analysis with solidframe/phpstan-rules, zero errors

## Next Examples

| Example | Adds |
|---|---|
| [symfony-event-sourced](../symfony-event-sourced) | Same scenario implemented with Symfony |

## License

MIT
