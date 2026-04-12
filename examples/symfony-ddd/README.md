# SolidFrame Example: Library Management (Symfony + DDD)

A library management REST API built with Domain-Driven Design patterns using SolidFrame packages.

This is the simplest example in the SolidFrame series — pure DDD without CQRS, events, or modules. It mirrors the [laravel-ddd](../laravel-ddd) example using Symfony instead.

## Packages Used

| Package | Purpose |
|---|---|
| `solidframe/core` | Identity, ApplicationServiceInterface, exceptions |
| `solidframe/ddd` | Entity, ValueObject, AggregateRoot, Specification |
| `solidframe/symfony` | Bundle, Console generators |
| `solidframe/archtest` | Architecture tests (domain isolation, VO rules) |
| `solidframe/phpstan-rules` | Static analysis rules |

## Architecture

```
src/
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
├── Infrastructure/
│   ├── Persistence/Dbal/
│   │   ├── DbalBookRepository.php      ← Domain ↔ DBAL mapping
│   │   ├── ConnectionFactory.php       ← DBAL 4 connection from DATABASE_URL
│   │   └── SchemaManager.php           ← Programmatic schema creation
│   └── Console/
│       └── CreateSchemaCommand.php     ← bin/console app:schema:create
│
├── Controller/
│   └── BookController.php        ← Thin, delegates to BookService
│
└── EventSubscriber/
    └── ExceptionSubscriber.php   ← Domain exceptions → JSON responses
```

### Key Principles

- **Domain layer is pure PHP** — no Symfony imports, no framework coupling
- **Doctrine DBAL (not ORM)** — explicit SQL queries, no magic; Domain Entity ≠ Persistence
- **Controller is thin** — only HTTP in/out, all logic in BookService
- **Self-validating Value Objects** — ISBN validates check digit, Title/Author validate length
- **Named Exception Constructors** — `BookNotFoundException::forId($id)`, not raw strings
- **Specification Pattern** — composable filtering: `$available->and($byAuthor)`

### Laravel vs Symfony Comparison

| Concern | Laravel | Symfony |
|---|---|---|
| Persistence | Eloquent Model + Repository | Doctrine DBAL + Repository |
| Validation | FormRequest | Symfony Validator Constraints |
| Exception handling | `bootstrap/app.php` renderable | EventSubscriber |
| DI binding | ServiceProvider | `services.yaml` alias |
| Schema | Migration file | SchemaManager + Console command |

The **domain layer is identical** — no changes needed between frameworks.

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
curl -X POST http://localhost:8001/api/books \
  -H "Content-Type: application/json" \
  -d '{"title": "Clean Architecture", "author": "Robert C. Martin", "isbn": "9780134494166"}'

# List available books by author
curl "http://localhost:8001/api/books?author=Robert+C.+Martin&available=true"

# Borrow a book
curl -X POST http://localhost:8001/api/books/{id}/borrow \
  -H "Content-Type: application/json" \
  -d '{"borrower": "Kadir"}'

# Return a book
curl -X POST http://localhost:8001/api/books/{id}/return
```

## Tests

```bash
# Unit tests (domain logic — no database, no framework)
php vendor/bin/phpunit --testsuite=Unit

# Functional tests (API integration — uses SQLite)
php vendor/bin/phpunit --testsuite=Functional

# Architecture tests (domain isolation, VO rules, DDD preset)
php vendor/bin/phpunit --testsuite=Architecture

# All tests
php vendor/bin/phpunit
```

**39 tests, 61 assertions** covering:
- Domain: Book lifecycle, value object validation, specification composition
- API: CRUD, borrow/return, error handling, filtering
- Architecture: Domain isolation, value object immutability, DDD preset

## Setup

```bash
# Install dependencies
composer install

# Create the database schema
php bin/console app:schema:create

# Start the server
php -S localhost:8001 -t public
```

Or with Docker:

```bash
docker compose up -d
docker compose run --rm composer install
docker compose exec app php bin/console app:schema:create
```

## Scaffolding with Console

The `solidframe/symfony` bridge provides generator commands to scaffold DDD building blocks:

```bash
# Generate a Value Object
php bin/console solidframe:make:value-object ISBN
# → src/Domain/ISBN.php (final readonly, ValueObjectInterface)

# Generate an Entity
php bin/console solidframe:make:entity Book
# → src/Domain/Book.php (AbstractEntity)

# Generate an Aggregate Root
php bin/console solidframe:make:aggregate-root Order
# → src/Domain/Order.php (AbstractAggregateRoot with static create())

# Subdirectories are supported
php bin/console solidframe:make:value-object Book/ISBN
# → src/Domain/Book/ISBN.php
```

## What This Example Demonstrates

1. **Value Objects with self-validation** — ISBN check digit, Title/Author length
2. **Aggregate Root with business rules** — borrow/return state transitions
3. **Repository pattern** — domain interface, DBAL implementation
4. **Specification pattern** — composable filtering with `and()`, `or()`, `not()`
5. **Named exception constructors** — clear, consistent error messages
6. **Domain ↔ Persistence separation** — DBAL maps to/from domain entity
7. **Architecture tests** — enforcing domain isolation at build time
8. **Application Service** — orchestration layer between HTTP and domain
9. **Framework portability** — identical domain layer across Laravel and Symfony

## Next Examples

| Example | Adds |
|---|---|
| [symfony-hexagonal-cqrs](../symfony-hexagonal-cqrs) | CommandBus, QueryBus, EventBus |
| [symfony-modular-cqrs](../symfony-modular-cqrs) | Module isolation, Saga orchestration |
| [symfony-event-sourced](../symfony-event-sourced) | EventStore, Snapshots, full event sourcing |

## License

MIT
