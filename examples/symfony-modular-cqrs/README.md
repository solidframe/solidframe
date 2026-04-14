# SolidFrame Example: E-Commerce (Symfony + Modular CQRS + Saga)

An e-commerce order fulfillment REST API built with Modular Monolith, CQRS, and Saga orchestration patterns using SolidFrame packages.

This example builds on hexagonal CQRS patterns and adds **module isolation** (each bounded context is a self-contained module) and **saga-based distributed transaction coordination** (Order → Reserve Stock → Charge Payment → Confirm Order, with automatic compensation on failure).

## Packages Used

| Package | Purpose |
|---|---|
| `solidframe/core` | Identity, Bus interfaces (Command, Query, Event) |
| `solidframe/ddd` | Entity, ValueObject, AggregateRoot |
| `solidframe/cqrs` | CommandBus, QueryBus, Handlers |
| `solidframe/event-driven` | EventBus, Listeners |
| `solidframe/modular` | ModuleInterface, ModuleRegistry, IntegrationEvent |
| `solidframe/saga` | AbstractSaga, SagaStore, Association |
| `solidframe/symfony` | SolidFrameBundle, handler discovery, SagaStore |
| `solidframe/archtest` | Architecture tests (module isolation, DDD/CQRS presets) |
| `solidframe/phpstan-rules` | Static analysis rules (event immutability, etc.) |

## Architecture

```
src/Modules/
├── Order/                                   ← Orchestrator module
│   ├── Domain/
│   │   ├── Order.php                        (AggregateRoot)
│   │   ├── OrderId.php                      (UuidIdentity)
│   │   ├── OrderStatus.php                  (Enum: pending, stock_reserved, confirmed, cancelled)
│   │   ├── ValueObject/
│   │   │   ├── CustomerEmail.php            (self-validating)
│   │   │   └── OrderItem.php                (productId, quantity, unitPrice)
│   │   ├── Port/
│   │   │   └── OrderRepository.php          ← Driven Port (interface)
│   │   ├── Exception/
│   │   │   ├── OrderNotFoundException.php
│   │   │   ├── OrderAlreadyConfirmedException.php
│   │   │   └── OrderAlreadyCancelledException.php
│   │   └── Event/
│   │       ├── OrderCreated.php             (IntegrationEvent)
│   │       ├── OrderConfirmed.php
│   │       └── OrderCancelled.php
│   │
│   ├── Application/
│   │   ├── Command/
│   │   │   ├── CreateOrder/                 (Command + Handler)
│   │   │   ├── ConfirmOrder/
│   │   │   └── CancelOrder/
│   │   ├── Query/
│   │   │   ├── GetOrder/                    (Query + Handler)
│   │   │   └── ListOrders/
│   │   ├── Saga/
│   │   │   └── OrderFulfillmentSaga.php     ← Saga state machine
│   │   └── Listener/
│   │       ├── OrderCreatedStartSagaListener.php   → creates saga, dispatches ReserveStock
│   │       ├── StockReservedListener.php           → dispatches ChargePayment
│   │       ├── StockReservationFailedListener.php  → saga.fail() → compensate
│   │       ├── PaymentChargedListener.php          → dispatches ConfirmOrder, saga.complete()
│   │       └── PaymentFailedListener.php           → saga.fail() → compensate
│   │
│   └── Infrastructure/Persistence/Dbal/
│       └── DbalOrderRepository.php
│
├── Inventory/                               ← Stock management module
│   ├── Domain/
│   │   ├── Product.php                      (AggregateRoot)
│   │   ├── ProductId.php
│   │   ├── ValueObject/
│   │   │   ├── ProductName.php
│   │   │   └── Sku.php
│   │   ├── Port/ProductRepository.php
│   │   ├── Exception/
│   │   │   ├── ProductNotFoundException.php
│   │   │   └── InsufficientStockException.php
│   │   └── Event/
│   │       ├── StockReserved.php            (IntegrationEvent)
│   │       ├── StockReleased.php
│   │       └── StockReservationFailed.php
│   │
│   ├── Application/Command/
│   │   ├── AddProduct/
│   │   ├── ReserveStock/                    ← called by saga
│   │   └── ReleaseStock/                    ← compensation action
│   ├── Application/Query/
│   │   ├── GetProduct/
│   │   └── ListProducts/
│   │
│   └── Infrastructure/Persistence/Dbal/
│       └── DbalProductRepository.php
│
└── Payment/                                 ← Payment processing module
    ├── Domain/
    │   ├── Payment.php                      (AggregateRoot)
    │   ├── PaymentId.php
    │   ├── PaymentStatus.php                (Enum: pending, charged, refunded, failed)
    │   ├── Port/PaymentRepository.php
    │   ├── Exception/
    │   │   ├── PaymentNotFoundException.php
    │   │   └── PaymentAlreadyChargedException.php
    │   └── Event/
    │       ├── PaymentCharged.php           (IntegrationEvent)
    │       ├── PaymentFailed.php
    │       └── PaymentRefunded.php
    │
    ├── Application/Command/
    │   ├── ChargePayment/                   ← called by saga
    │   └── RefundPayment/                   ← compensation action
    ├── Application/Query/GetPayment/
    │
    └── Infrastructure/Persistence/Dbal/
        └── DbalPaymentRepository.php

src/Controller/
├── OrderController.php
├── ProductController.php
└── PaymentController.php
```

### Key Principles

- **Modular Monolith** — Each bounded context is a self-contained module with its own Domain, Application, and Infrastructure layers
- **Module isolation** — Modules communicate only via IntegrationEvents and Commands, never by importing each other's domain objects directly
- **Saga orchestration** — `OrderFulfillmentSaga` coordinates the multi-step order flow with automatic compensation on failure
- **CQRS** — Separate Command/Query buses with dedicated handlers per module
- **Integration Events** — Cross-module events (not domain events) carry `sourceModule()` metadata
- **SolidFrameBundle** — Handler auto-discovery, SagaStore registration, and module registry via CompilerPass

### Saga Flow

**Happy path:**
```
POST /api/orders
  → CreateOrderHandler         → OrderCreated event
    → OrderCreatedStartSagaListener
        creates saga, adds compensation: CancelOrder
        dispatches ReserveStock command
      → ReserveStockHandler    → StockReserved event
        → StockReservedListener
            adds compensation: ReleaseStock
            dispatches ChargePayment command
          → ChargePaymentHandler → PaymentCharged event
            → PaymentChargedListener
                saga.complete()
                dispatches ConfirmOrder → order status = confirmed
```

**Failure path (insufficient stock):**
```
  → ReserveStockHandler catches InsufficientStockException
    → StockReservationFailed event
      → StockReservationFailedListener
          saga.fail() → compensations run in reverse:
            1. CancelOrder → order status = cancelled
```

**Failure path (payment error):**
```
  → ChargePaymentHandler catches exception
    → PaymentFailed event
      → PaymentFailedListener
          saga.fail() → compensations run in reverse:
            1. ReleaseStock → stock restored
            2. CancelOrder → order status = cancelled
```

### How It Differs from symfony-hexagonal-cqrs

| Aspect | symfony-hexagonal-cqrs | symfony-modular-cqrs |
|---|---|---|
| Structure | Single `src/` with flat Domain | Separate `src/Modules/` per bounded context |
| Communication | Direct imports within app | IntegrationEvents across modules |
| Transaction | Single aggregate | Multi-step saga with compensation |
| Packages | core, ddd, cqrs, event-driven | + modular, saga |
| Domain Events | Simple events (logging) | IntegrationEvents driving saga flow |

## API Endpoints

### Products (Inventory module)

| Method | URL | Description |
|---|---|---|
| `POST` | `/api/products` | Add a product |
| `GET` | `/api/products` | List all products |
| `GET` | `/api/products/{id}` | Get product details |

### Orders (Order module)

| Method | URL | Description |
|---|---|---|
| `POST` | `/api/orders` | Create an order (triggers saga) |
| `GET` | `/api/orders` | List all orders |
| `GET` | `/api/orders/{id}` | Get order details |

### Payments (Payment module)

| Method | URL | Description |
|---|---|---|
| `GET` | `/api/payments/{orderId}` | Get payment for an order |

### Example Requests

```bash
# Add products to inventory
curl -X POST http://localhost:8004/api/products \
  -H "Content-Type: application/json" \
  -d '{"name": "Laptop", "sku": "LAP-001", "stock": 10, "price": 99900}'

curl -X POST http://localhost:8004/api/products \
  -H "Content-Type: application/json" \
  -d '{"name": "Mouse", "sku": "MOU-001", "stock": 50, "price": 2990}'

# Create an order (saga runs: reserve stock → charge payment → confirm)
curl -X POST http://localhost:8004/api/orders \
  -H "Content-Type: application/json" \
  -d '{
    "customer_email": "customer@example.com",
    "items": [
      {"product_id": "{laptop-id}", "quantity": 1, "unit_price": 99900},
      {"product_id": "{mouse-id}", "quantity": 2, "unit_price": 2990}
    ]
  }'

# Check order status (should be "confirmed")
curl http://localhost:8004/api/orders/{id}

# Check payment was created
curl http://localhost:8004/api/payments/{order-id}

# Check stock was reduced
curl http://localhost:8004/api/products/{laptop-id}
```

## Tests

```bash
# Unit tests (domain logic — no database, no framework)
php vendor/bin/phpunit tests/Unit

# Functional tests (API + saga integration — uses SQLite)
php vendor/bin/phpunit tests/Functional

# Architecture tests (module isolation, DDD/CQRS presets)
php vendor/bin/phpunit tests/Architecture

# All tests
php vendor/bin/phpunit
```

**58 tests, 96 assertions** covering:
- Domain: Order lifecycle, Product stock reservation/release, Payment charge/refund, value object validation
- Saga: State machine (start, complete, fail), compensation execution in reverse order
- API: Product CRUD, Order creation with full saga flow, payment creation
- Saga compensation: Insufficient stock cancels order, stock is restored after failure
- Architecture: Per-module domain isolation, cross-module dependency rules, DDD + CQRS presets for all 3 modules

## Setup

```bash
# Install dependencies
composer install

# Create database schema
php bin/console app:schema:create

# Start the server
php -S localhost:8004 -t public
```

Or with Docker:

```bash
docker compose up -d
docker compose run --rm composer install
docker compose exec app php bin/console app:schema:create
```

## What This Example Demonstrates

1. **Modular Monolith** — Each bounded context is a self-contained module with its own layers
2. **SolidFrameBundle** — Auto-discovers handlers, registers SagaStore, wires buses via CompilerPass
3. **Integration Events** — Cross-module communication without direct domain coupling
4. **Saga orchestration** — Multi-step transaction with automatic compensation on failure
5. **Compensation pattern** — Reverse-order undo of completed steps when a later step fails
6. **CQRS per module** — Each module has its own Command/Query handlers discovered automatically
7. **Architecture tests** — Per-module DDD/CQRS presets + cross-module dependency enforcement
8. **PHPStan level 8** — Full static analysis with solidframe/phpstan-rules, zero errors, zero ignores

## Next Examples

| Example | Adds |
|---|---|
| [symfony-event-sourced](../symfony-event-sourced) | EventStore, Snapshots, full event sourcing |

## License

MIT
