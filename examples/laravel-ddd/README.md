# SolidFrame Example: Library Management (Laravel + DDD)

A library management REST API built with Domain-Driven Design patterns using SolidFrame packages.

This is the simplest example in the SolidFrame series — pure DDD without CQRS, events, or modules.

## Packages Used

| Package | Purpose |
|---|---|
| `solidframe/core` | Identity, ApplicationServiceInterface, exceptions |
| `solidframe/ddd` | Entity, ValueObject, AggregateRoot, Specification |
| `solidframe/laravel` | ServiceProvider, Artisan generators |
| `solidframe/archtest` | Architecture tests (domain isolation, VO rules) |
| `solidframe/phpstan-rules` | Static analysis rules |

## Architecture

```
app/
├── Domain/Book/                  ← Pure PHP, no framework dependencies
│   ├── Book.php                  (AggregateRoot)
│   ├── BookId.php                (UuidIdentity)
│   ├── BookStatus.php            (Enum)
│   ├── BookRepository.php        (Interface)
│   ├── ValueObject/
│   │   ├── ISBN.php              (self-validating, check digit)
│   │   ├── Title.php             (max 255 chars)
│   │   └── Author.php            (max 255 chars)
│   ├── Specification/
│   │   ├── AvailableBookSpecification.php
│   │   └── BookByAuthorSpecification.php
│   └── Exception/
│       ├── BookNotFoundException.php
│       ├── BookAlreadyBorrowedException.php
│       ├── BookNotBorrowedException.php
│       ├── InvalidISBN.php
│       ├── InvalidTitle.php
│       └── InvalidAuthor.php
│
├── Application/Book/
│   └── BookService.php           ← Orchestration, specification composition
│
├── Infrastructure/Persistence/
│   └── Eloquent/
│       ├── BookModel.php         ← Eloquent model (persistence only)
│       └── EloquentBookRepository.php  ← Domain ↔ Eloquent mapping
│
└── Http/
    ├── Controllers/
    │   └── BookController.php    ← Thin, delegates to BookService
    └── Requests/
        ├── StoreBookRequest.php
        ├── UpdateBookRequest.php
        └── BorrowBookRequest.php
```

### Key Principles

- **Domain layer is pure PHP** — no Illuminate imports, no framework coupling
- **Eloquent Model ≠ Domain Entity** — separate classes with explicit mapping in the repository
- **Controller is thin** — only HTTP in/out, all logic in BookService
- **Self-validating Value Objects** — ISBN validates check digit, Title/Author validate length
- **Named Exception Constructors** — `BookNotFoundException::forId($id)`, not raw strings
- **Specification Pattern** — composable filtering: `$available->and($byAuthor)`

## API Endpoints

| Method | URL | Description |
|---|---|---|
| `POST` | `/api/books` | Add a book |
| `GET` | `/api/books` | List books (filter: `?author=...&available=true`) |
| `GET` | `/api/books/{id}` | Get book details |
| `PUT` | `/api/books/{id}` | Update book |
| `DELETE` | `/api/books/{id}` | Delete book |
| `POST` | `/api/books/{id}/borrow` | Borrow a book |
| `POST` | `/api/books/{id}/return` | Return a book |

### Example Requests

```bash
# Add a book
curl -X POST http://localhost:8000/api/books \
  -H "Content-Type: application/json" \
  -d '{"title": "Clean Architecture", "author": "Robert C. Martin", "isbn": "9780134494166"}'

# List available books by author
curl "http://localhost:8000/api/books?author=Robert+C.+Martin&available=true"

# Borrow a book
curl -X POST http://localhost:8000/api/books/{id}/borrow \
  -H "Content-Type: application/json" \
  -d '{"borrower": "Kadir"}'

# Return a book
curl -X POST http://localhost:8000/api/books/{id}/return
```

## Tests

```bash
# Unit tests (domain logic — no database, no framework)
php vendor/bin/phpunit tests/Unit

# Feature tests (API integration — uses SQLite)
php vendor/bin/phpunit tests/Feature

# Architecture tests (domain isolation, VO rules, DDD preset)
php vendor/bin/phpunit tests/Architecture

# All tests
php vendor/bin/phpunit
```

**33 tests, 66 assertions** covering:
- Domain: Book lifecycle, value object validation, specification composition
- API: CRUD, borrow/return, error handling, filtering
- Architecture: Domain isolation, value object immutability, DDD preset

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

## Scaffolding with Artisan

The `solidframe/laravel` bridge provides generator commands to scaffold DDD building blocks:

```bash
# Generate a Value Object
php artisan make:value-object ISBN
# → app/Domain/ISBN.php (final readonly, ValueObjectInterface)

# Generate an Entity
php artisan make:entity Book
# → app/Domain/Book.php (AbstractEntity)

# Generate an Aggregate Root
php artisan make:aggregate-root Order
# → app/Domain/Order.php (AbstractAggregateRoot with static create())

# Subdirectories are supported
php artisan make:value-object Book/ISBN
# → app/Domain/Book/ISBN.php
```

The generated files follow SolidFrame conventions and give you a working starting point. This project's domain classes were hand-written for full control, but you can use these generators to bootstrap your own projects quickly.

## What This Example Demonstrates

1. **Value Objects with self-validation** — ISBN check digit, Title/Author length
2. **Aggregate Root with business rules** — borrow/return state transitions
3. **Repository pattern** — domain interface, Eloquent implementation
4. **Specification pattern** — composable filtering with `and()`, `or()`, `not()`
5. **Named exception constructors** — clear, consistent error messages
6. **Domain ↔ Persistence separation** — Eloquent model maps to/from domain entity
7. **Architecture tests** — enforcing domain isolation at build time
8. **Application Service** — orchestration layer between HTTP and domain

## Next Examples

| Example | Adds |
|---|---|
| [laravel-hexagonal-cqrs](../laravel-hexagonal-cqrs) | CommandBus, QueryBus, EventBus |
| [laravel-modular-cqrs](../laravel-modular-cqrs) | Module isolation, Saga orchestration |
| [laravel-event-sourced](../laravel-event-sourced) | EventStore, Snapshots, full event sourcing |

## License

MIT
