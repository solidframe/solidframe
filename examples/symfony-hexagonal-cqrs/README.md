# SolidFrame Example: Task Management (Symfony + Hexagonal CQRS)

A task/project management REST API built with Hexagonal Architecture, CQRS, and Event-Driven patterns using SolidFrame packages.

Symfony 8.0 counterpart of [laravel-hexagonal-cqrs](../laravel-hexagonal-cqrs) with identical domain and application layers.

## Packages Used

| Package | Purpose |
|---|---|
| `solidframe/core` | Identity, Bus interfaces (Command, Query, Event) |
| `solidframe/ddd` | Entity, ValueObject, AggregateRoot |
| `solidframe/cqrs` | CommandBus, QueryBus, Handlers |
| `solidframe/event-driven` | EventBus, Listeners |
| `solidframe/symfony` | Bundle, handler discovery, Console commands |
| `solidframe/archtest` | Architecture tests (hexagonal isolation, CQRS preset) |
| `solidframe/phpstan-rules` | Static analysis rules |

## Architecture

```
src/
├── Domain/                              <- Pure PHP, no framework dependencies
│   ├── Project/
│   │   ├── Project.php                  (AggregateRoot)
│   │   ├── ProjectId.php                (UuidIdentity)
│   │   ├── ProjectStatus.php            (Enum: active, archived)
│   │   ├── ValueObject/
│   │   │   └── ProjectName.php          (max 100 chars)
│   │   ├── Port/
│   │   │   └── ProjectRepository.php    <- Driven Port (interface)
│   │   └── Exception/
│   │       ├── ProjectNotFoundException.php
│   │       ├── ProjectAlreadyArchivedException.php
│   │       └── InvalidProjectName.php
│   │
│   ├── Task/
│   │   ├── Task.php                     (AggregateRoot)
│   │   ├── TaskId.php                   (UuidIdentity)
│   │   ├── TaskStatus.php               (Enum: open, in_progress, completed)
│   │   ├── Priority.php                 (Enum: low, medium, high, critical)
│   │   ├── ValueObject/
│   │   │   ├── TaskTitle.php            (max 255 chars)
│   │   │   ├── TaskDescription.php      (max 1000 chars)
│   │   │   └── Assignee.php
│   │   ├── Port/
│   │   │   └── TaskRepository.php       <- Driven Port (interface)
│   │   └── Exception/
│   │       ├── TaskNotFoundException.php
│   │       ├── TaskAlreadyCompletedException.php
│   │       ├── InvalidTaskTitle.php
│   │       └── InvalidTaskDescription.php
│   │
│   └── Event/Task/
│       ├── TaskCreated.php              (DomainEvent)
│       ├── TaskCompleted.php
│       └── TaskAssigned.php
│
├── Application/
│   ├── Command/                         <- Write side (CQRS)
│   │   ├── CreateProject/               (Command + Handler)
│   │   ├── ArchiveProject/
│   │   ├── CreateTask/
│   │   ├── AssignTask/
│   │   ├── CompleteTask/
│   │   └── ReopenTask/
│   ├── Query/                           <- Read side (CQRS)
│   │   ├── GetProject/                  (Query + Handler)
│   │   ├── ListProjects/
│   │   ├── GetTask/
│   │   └── ListTasks/
│   └── Listener/Task/
│       ├── TaskCreatedListener.php      -> logs event via PSR Logger
│       ├── TaskCompletedListener.php
│       └── TaskAssignedListener.php
│
├── Infrastructure/
│   ├── Persistence/Dbal/
│   │   ├── ConnectionFactory.php        <- DATABASE_URL parser
│   │   ├── SchemaManager.php            <- Schema creation (projects + tasks)
│   │   ├── DbalProjectRepository.php    <- Adapter (Doctrine DBAL)
│   │   └── DbalTaskRepository.php
│   └── Console/
│       └── CreateSchemaCommand.php      <- app:schema:create
│
├── Controller/
│   ├── ProjectController.php            <- dispatches to CommandBus/QueryBus
│   └── TaskController.php
│
├── Http/
│   └── RequestValidator.php             <- JSON parse + Symfony Validator
│
└── EventSubscriber/
    └── ExceptionSubscriber.php          <- Domain exceptions -> HTTP responses
```

### Key Principles

- **Hexagonal Architecture** -- Domain defines Ports (interfaces), Infrastructure provides Adapters
- **CQRS** -- Commands (write) and Queries (read) are separate objects with separate handlers
- **Event-Driven** -- Domain events dispatched via EventBus after state changes
- **Domain and Application layers are identical to the Laravel version** -- pure PHP, framework-agnostic
- **Autowiring** -- Symfony injects all dependencies automatically via constructor type-hints
- **Handler auto-discovery** -- SolidFrameBundle discovers CommandHandler, QueryHandler, EventListener via CompilerPass

### Symfony vs Laravel Differences

| Aspect | Symfony | Laravel |
|---|---|---|
| Database | Doctrine DBAL (raw SQL) | Eloquent ORM |
| Validation | Symfony Validator + RequestValidator service | Form Request classes |
| Routing | PHP Attributes (`#[Route]`) | `routes/api.php` |
| DI config | `services.yaml` (autowiring) | `AppServiceProvider` |
| Exception handling | `ExceptionSubscriber` (kernel event) | Exception handler |
| Schema | `SchemaManager` + console command | Migration files |
| Logging | PSR `LoggerInterface` | `Log` facade |

## API Endpoints

### Projects

| Method | URL | Description |
|---|---|---|
| `POST` | `/api/projects` | Create a project |
| `GET` | `/api/projects` | List all projects |
| `GET` | `/api/projects/{id}` | Get project details |
| `POST` | `/api/projects/{id}/archive` | Archive a project |

### Tasks

| Method | URL | Description |
|---|---|---|
| `POST` | `/api/tasks` | Create a task |
| `GET` | `/api/tasks` | List tasks (filter: `?project=...&status=...&assignee=...`) |
| `GET` | `/api/tasks/{id}` | Get task details |
| `POST` | `/api/tasks/{id}/assign` | Assign a task |
| `POST` | `/api/tasks/{id}/complete` | Complete a task |
| `POST` | `/api/tasks/{id}/reopen` | Reopen a task |

### Example Requests

```bash
# Create a project
curl -X POST http://localhost:8003/api/projects \
  -H "Content-Type: application/json" \
  -d '{"name": "SolidFrame v2", "description": "Next major release"}'

# Create a task
curl -X POST http://localhost:8003/api/tasks \
  -H "Content-Type: application/json" \
  -d '{"project_id": "{id}", "title": "Add projection support", "priority": "high"}'

# Assign a task
curl -X POST http://localhost:8003/api/tasks/{id}/assign \
  -H "Content-Type: application/json" \
  -d '{"assignee": "Kadir"}'

# Complete a task
curl -X POST http://localhost:8003/api/tasks/{id}/complete

# List open tasks by assignee
curl "http://localhost:8003/api/tasks?assignee=Kadir&status=open"
```

## Tests

```bash
# Unit tests (domain logic -- no database, no framework)
php vendor/bin/phpunit tests/Unit

# Functional tests (API integration -- uses SQLite)
php vendor/bin/phpunit tests/Functional

# Architecture tests (hexagonal isolation, CQRS preset)
php vendor/bin/phpunit tests/Architecture

# All tests
php vendor/bin/phpunit
```

**51 tests, 96 assertions** covering:
- Domain: Project lifecycle, Task lifecycle (create, assign, complete, reopen), domain events, VO validation
- API: CRUD, filtering by project/status/assignee, error handling
- Architecture: Domain isolation, hexagonal rules, application isolation, VO immutability, DDD + CQRS presets

## Setup

```bash
# Install dependencies
composer install

# Create database schema
php bin/console app:schema:create

# Start the server
php -S localhost:8000 -t public
```

Or with Docker:

```bash
docker compose up -d
docker compose run --rm composer composer install
docker compose exec app php bin/console app:schema:create
```

## What This Example Demonstrates

1. **Hexagonal Architecture** -- Ports in Domain, Adapters in Infrastructure
2. **CQRS** -- Separate Command/Query buses with dedicated handlers
3. **Event-Driven** -- Domain events dispatched after aggregate state changes
4. **Handler auto-discovery** -- SolidFrameBundle CompilerPass finds handlers automatically
5. **Thin controllers** -- only dispatch to bus, never touch domain directly
6. **RequestValidator service** -- reusable JSON validation extracted from controllers
7. **ExceptionSubscriber** -- centralized domain exception to HTTP response mapping
8. **Architecture tests** -- DDD preset + CQRS preset enforcing layering at build time

## Next Examples

| Example | Adds |
|---|---|
| [symfony-modular-cqrs](../symfony-modular-cqrs) | Module isolation, Saga orchestration |
| [symfony-event-sourced](../symfony-event-sourced) | EventStore, Snapshots, full event sourcing |

## License

MIT
