# SolidFrame

Modern architectural patterns for PHP. Composable, framework-agnostic, built for PHP 8.2+.

Pick only what you need — from simple DDD building blocks to full event-sourced modular monoliths.

## Packages

### Architecture

| Package | Description | Install |
|---|---|---|
| [**core**](packages/core) | Identity, Bus interfaces, Pipeline, Middleware | `composer require solidframe/core` |
| [**ddd**](packages/ddd) | Entity, ValueObject, AggregateRoot, Specification | `composer require solidframe/ddd` |
| [**cqrs**](packages/cqrs) | CommandBus, QueryBus, Handlers, Middleware | `composer require solidframe/cqrs` |
| [**event-driven**](packages/event-driven) | EventBus, Listeners, multi-listener support | `composer require solidframe/event-driven` |
| [**event-sourcing**](packages/event-sourcing) | EventStore, Snapshots, event-sourced Aggregates | `composer require solidframe/event-sourcing` |
| [**modular**](packages/modular) | Module contracts, Integration Events, ACL, Registry | `composer require solidframe/modular` |
| [**saga**](packages/saga) | Saga lifecycle, Compensation, Correlation | `composer require solidframe/saga` |

### Tooling

| Package | Description | Install |
|---|---|---|
| [**archtest**](packages/archtest) | Architecture tests with fluent API (PHPUnit) | `composer require solidframe/archtest --dev` |
| [**phpstan-rules**](packages/phpstan-rules) | PHPStan rules for DDD, CQRS, Event Sourcing | `composer require solidframe/phpstan-rules --dev` |

### Framework Bridges

| Package | Description | Install |
|---|---|---|
| [**laravel**](packages/laravel) | ServiceProvider, Artisan commands, DB stores | `composer require solidframe/laravel` |
| [**symfony**](packages/symfony) | Bundle, Console commands, DBAL stores | `composer require solidframe/symfony` |

## Composable by Design

Install only the packages you need. Dependencies are handled automatically.

```bash
# Simple DDD with Laravel
composer require solidframe/ddd solidframe/laravel

# CQRS + Event-Driven with Symfony
composer require solidframe/cqrs solidframe/event-driven solidframe/symfony

# Full stack: Modular Monolith + DDD + CQRS + Event Sourcing
composer require solidframe/modular solidframe/ddd solidframe/cqrs \
    solidframe/event-sourcing solidframe/saga solidframe/laravel

# Tooling only
composer require solidframe/archtest solidframe/phpstan-rules --dev
```

## Quick Example

### Define a domain

```php
// Value Object
final readonly class Email extends StringValueObject
{
    public static function from(string $value): static
    {
        filter_var($value, FILTER_VALIDATE_EMAIL)
            or throw InvalidEmail::malformed($value);

        return parent::from($value);
    }
}

// Aggregate Root
final class User extends AbstractAggregateRoot
{
    private Email $email;

    public static function register(UserId $id, Email $email): self
    {
        $user = new self($id);
        $user->email = $email;
        $user->recordThat(new UserRegistered($id->value(), $email->value()));

        return $user;
    }
}

// Command + Handler
final readonly class RegisterUser implements Command
{
    public function __construct(
        public string $userId,
        public string $email,
    ) {}
}

final readonly class RegisterUserHandler implements CommandHandler
{
    public function __construct(
        private UserRepository $users,
        private EventBusInterface $events,
    ) {}

    public function __invoke(RegisterUser $command): void
    {
        $user = User::register(
            new UserId($command->userId),
            Email::from($command->email),
        );

        $this->users->save($user);

        foreach ($user->releaseEvents() as $event) {
            $this->events->dispatch($event);
        }
    }
}
```

### Wire it up

**Laravel** — zero config, just install:

```bash
composer require solidframe/ddd solidframe/cqrs solidframe/event-driven solidframe/laravel
```

```php
// Controller
final class UserController
{
    public function register(Request $request, CommandBusInterface $commandBus): Response
    {
        $commandBus->dispatch(new RegisterUser(
            userId: Str::uuid(),
            email: $request->input('email'),
        ));

        return response()->json(['status' => 'ok'], 201);
    }
}
```

**Symfony** — register the bundle:

```bash
composer require solidframe/ddd solidframe/cqrs solidframe/event-driven solidframe/symfony
```

```php
// Controller
final class UserController extends AbstractController
{
    public function register(Request $request, CommandBusInterface $commandBus): Response
    {
        $commandBus->dispatch(new RegisterUser(
            userId: Uuid::v4()->toRfc4122(),
            email: $request->request->get('email'),
        ));

        return new JsonResponse(['status' => 'ok'], 201);
    }
}
```

### Enforce architecture

```php
// tests/ArchitectureTest.php
final class ArchitectureTest extends TestCase
{
    #[Test]
    public function domainIsIsolated(): void
    {
        Arch::preset('ddd', [
            'domainDir' => __DIR__ . '/../src/Domain',
            'infrastructureDir' => __DIR__ . '/../src/Infrastructure',
        ])->assert();
    }

    #[Test]
    public function cqrsRulesHold(): void
    {
        Arch::preset('cqrs', [
            'commandDir' => __DIR__ . '/../src/Application/Command',
            'queryDir' => __DIR__ . '/../src/Application/Query',
        ])->assert();
    }
}
```

## Architecture

```
solidframe/core          ← everything depends on this
    ├── solidframe/ddd
    ├── solidframe/cqrs
    ├── solidframe/event-driven
    ├── solidframe/event-sourcing   (depends on ddd)
    ├── solidframe/modular
    ├── solidframe/saga
    ├── solidframe/archtest
    └── solidframe/phpstan-rules

solidframe/laravel       ← Laravel bridge (depends on core)
solidframe/symfony       ← Symfony bridge (depends on core)
```

## Examples

End-to-end example applications demonstrating SolidFrame patterns in real frameworks.
Each example is its own repository — clone, install, and run standalone.

### DDD — Library management

| Framework | Repository |
|---|---|
| Laravel | [solidframe/example-laravel-ddd](https://github.com/solidframe/example-laravel-ddd) |
| Symfony | [solidframe/example-symfony-ddd](https://github.com/solidframe/example-symfony-ddd) |

### Hexagonal + CQRS — Task management

| Framework | Repository |
|---|---|
| Laravel | [solidframe/example-laravel-hexagonal-cqrs](https://github.com/solidframe/example-laravel-hexagonal-cqrs) |
| Symfony | [solidframe/example-symfony-hexagonal-cqrs](https://github.com/solidframe/example-symfony-hexagonal-cqrs) |

### Modular Monolith + CQRS + Saga — E-commerce

| Framework | Repository |
|---|---|
| Laravel | [solidframe/example-laravel-modular-cqrs](https://github.com/solidframe/example-laravel-modular-cqrs) |
| Symfony | [solidframe/example-symfony-modular-cqrs](https://github.com/solidframe/example-symfony-modular-cqrs) |

### Event Sourcing + CQRS — Digital wallet

| Framework | Repository |
|---|---|
| Laravel | [solidframe/example-laravel-event-sourced](https://github.com/solidframe/example-laravel-event-sourced) |
| Symfony | [solidframe/example-symfony-event-sourced](https://github.com/solidframe/example-symfony-event-sourced) |

## Requirements

- PHP 8.2+
- Laravel 10/11/12/13 (for solidframe/laravel)
- Symfony 6.4/7.x/8.x (for solidframe/symfony)

## License

MIT
