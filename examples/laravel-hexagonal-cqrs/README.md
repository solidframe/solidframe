# SolidFrame Example: Task Management (Laravel + Hexagonal CQRS)

A task/project management REST API built with Hexagonal Architecture, CQRS, and Event-Driven patterns using SolidFrame packages.

This example builds on DDD patterns and adds **CommandBus/QueryBus separation** and **domain events** — the controller never touches a service or repository directly.

## Packages Used

| Package | Purpose |
|---|---|
| `solidframe/core` | Identity, Bus interfaces (Command, Query, Event) |
| `solidframe/ddd` | Entity, ValueObject, AggregateRoot |
| `solidframe/cqrs` | CommandBus, QueryBus, Handlers |
| `solidframe/event-driven` | EventBus, Listeners |
| `solidframe/laravel` | ServiceProvider, Artisan generators |
| `solidframe/archtest` | Architecture tests (hexagonal isolation, CQRS preset) |
| `solidframe/phpstan-rules` | Static analysis rules |

## Architecture

```
app/
├── Domain/                              ← Pure PHP, no framework dependencies
│   ├── Project/
│   │   ├── Project.php                  (AggregateRoot)
│   │   ├── ProjectId.php                (UuidIdentity)
│   │   ├── ProjectStatus.php            (Enum: active, archived)
│   │   ├── ValueObject/
│   │   │   └── ProjectName.php          (max 100 chars)
│   │   ├── Port/
│   │   │   └── ProjectRepository.php    ← Driven Port (interface)
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
│   │   │   └── TaskRepository.php       ← Driven Port (interface)
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
│   ├── Command/                         ← Write side (CQRS)
│   │   ├── CreateProject/               (Command + Handler)
│   │   ├── ArchiveProject/
│   │   ├── CreateTask/
│   │   ├── AssignTask/
│   │   ├── CompleteTask/
│   │   └── ReopenTask/
│   ├── Query/                           ← Read side (CQRS)
│   │   ├── GetProject/                  (Query + Handler)
│   │   ├── ListProjects/
│   │   ├── GetTask/
│   │   └── ListTasks/
│   └── Listener/Task/
│       ├── TaskCreatedListener.php      → logs event
│       ├── TaskCompletedListener.php
│       └── TaskAssignedListener.php
│
├── Infrastructure/Persistence/Eloquent/
│   ├── ProjectModel.php                 ← Adapter (Eloquent)
│   ├── TaskModel.php
│   ├── EloquentProjectRepository.php
│   └── EloquentTaskRepository.php
│
└── Http/
    ├── Controllers/
    │   ├── ProjectController.php        ← dispatches to CommandBus/QueryBus
    │   └── TaskController.php
    └── Requests/
        ├── StoreProjectRequest.php
        ├── StoreTaskRequest.php
        └── AssignTaskRequest.php
```

### Key Principles

- **Hexagonal Architecture** — Domain defines Ports (interfaces), Infrastructure provides Adapters
- **CQRS** — Commands (write) and Queries (read) are separate objects with separate handlers
- **Event-Driven** — Domain events are dispatched via EventBus after state changes
- **No Application Service** — Controller dispatches directly to CommandBus/QueryBus (unlike the DDD example)
- **Domain layer is pure PHP** — no Illuminate imports, no framework coupling
- **Application layer doesn't know Infrastructure** — handlers depend on Port interfaces, not Eloquent

### How It Differs from laravel-ddd

| Aspect | laravel-ddd | laravel-hexagonal-cqrs |
|---|---|---|
| Orchestration | `BookService` (Application Service) | `CommandHandler` + `QueryHandler` |
| Controller calls | `$this->bookService->addBook()` | `$commandBus->dispatch(new CreateTask(...))` |
| Read/Write | Same service for both | Separate Command and Query paths |
| Domain Events | None | TaskCreated, TaskCompleted, TaskAssigned |
| Repository location | `Domain/Book/BookRepository` | `Domain/Task/Port/TaskRepository` (Hexagonal Port) |
| Aggregates | 1 (Book) | 2 (Project + Task) |

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
curl -X POST http://localhost:8002/api/projects \
  -H "Content-Type: application/json" \
  -d '{"name": "SolidFrame v2", "description": "Next major release"}'

# Create a task
curl -X POST http://localhost:8002/api/tasks \
  -H "Content-Type: application/json" \
  -d '{"project_id": "{id}", "title": "Add projection support", "priority": "high"}'

# Assign a task
curl -X POST http://localhost:8002/api/tasks/{id}/assign \
  -H "Content-Type: application/json" \
  -d '{"assignee": "Kadir"}'

# Complete a task
curl -X POST http://localhost:8002/api/tasks/{id}/complete

# List open tasks by assignee
curl "http://localhost:8002/api/tasks?assignee=Kadir&status=open"
```

## Tests

```bash
# Unit tests (domain logic — no database, no framework)
php vendor/bin/phpunit tests/Unit

# Feature tests (API integration — uses SQLite)
php vendor/bin/phpunit tests/Feature

# Architecture tests (hexagonal isolation, CQRS preset)
php vendor/bin/phpunit tests/Architecture

# All tests
php vendor/bin/phpunit
```

**51 tests, 101 assertions** covering:
- Domain: Project lifecycle, Task lifecycle (create, assign, complete, reopen), domain events, VO validation
- API: CRUD, filtering by project/status/assignee, error handling
- Architecture: Domain isolation, hexagonal rules, application isolation, VO immutability, DDD + CQRS presets

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

The `solidframe/laravel` bridge provides generator commands used to scaffold this project:

```bash
# Aggregate Roots
php artisan make:aggregate-root Project/Project
php artisan make:aggregate-root Task/Task

# Value Objects
php artisan make:value-object Task/ValueObject/TaskTitle

# CQRS Commands (--handler generates the handler too)
php artisan make:cqrs-command CreateTask/CreateTask --handler

# CQRS Queries
php artisan make:query GetTask/GetTask --handler

# Domain Events (--listener generates the listener too)
php artisan make:domain-event Task/TaskCreated --listener
```

## What This Example Demonstrates

1. **Hexagonal Architecture** — Ports in Domain, Adapters in Infrastructure
2. **CQRS** — Separate Command/Query buses with dedicated handlers
3. **Event-Driven** — Domain events dispatched after aggregate state changes
4. **Domain event recording** — `recordThat()` in aggregate, `releaseEvents()` in handler
5. **Thin controllers** — only dispatch to bus, never touch domain directly
6. **Two aggregates** — Project and Task with cross-reference via ProjectId
7. **Architecture tests** — DDD preset + CQRS preset enforcing layering at build time

## Next Examples

| Example | Adds |
|---|---|
| [laravel-modular-cqrs](../laravel-modular-cqrs) | Module isolation, Saga orchestration |
| [laravel-event-sourced](../laravel-event-sourced) | EventStore, Snapshots, full event sourcing |

## License

MIT
