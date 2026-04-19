# SolidFrame DDD

Domain-Driven Design building blocks: Entity, ValueObject, AggregateRoot, and Specification pattern.

Framework-agnostic. Use with any PHP 8.2+ project.

## Installation

```bash
composer require solidframe/ddd
```

## Components

### Entity

Entities have identity and are compared by identity, not by value.

```php
use SolidFrame\Ddd\Entity\AbstractEntity;
use SolidFrame\Core\Identity\UuidIdentity;

final readonly class UserId extends UuidIdentity {}

final class User extends AbstractEntity
{
    private string $name;

    public function __construct(UserId $id, string $name)
    {
        parent::__construct($id);
        $this->name = $name;
    }

    public function rename(string $name): void
    {
        $this->name = $name;
    }

    public function name(): string
    {
        return $this->name;
    }
}

$user = new User(UserId::generate(), 'Kadir');
$user->identity(); // UserId instance
$user->equals($otherUser); // compares by identity
```

### Aggregate Root

Aggregate roots are entities that record domain events.

```php
use SolidFrame\Ddd\Aggregate\AbstractAggregateRoot;

final class Order extends AbstractAggregateRoot
{
    private OrderStatus $status;

    public static function place(OrderId $id, CustomerId $customerId): self
    {
        $order = new self($id);
        $order->status = OrderStatus::Placed;
        $order->recordThat(new OrderPlaced($id, $customerId));

        return $order;
    }

    public function cancel(): void
    {
        $this->status = OrderStatus::Cancelled;
        $this->recordThat(new OrderCancelled($this->identity()));
    }
}

$order = Order::place(OrderId::generate(), $customerId);
$events = $order->releaseEvents(); // [OrderPlaced]
```

### Value Object

Immutable objects compared by value, not identity.

```php
use SolidFrame\Ddd\ValueObject\StringValueObject;
use SolidFrame\Ddd\ValueObject\IntValueObject;
use SolidFrame\Ddd\ValueObject\BoolValueObject;

// String-based
final readonly class Email extends StringValueObject
{
    public static function from(string $value): static
    {
        filter_var($value, FILTER_VALIDATE_EMAIL) or throw new InvalidEmail($value);

        return parent::from($value);
    }
}

$email = Email::from('kadir@example.com');
$email->value();   // 'kadir@example.com'
$email->equals(Email::from('kadir@example.com')); // true
(string) $email;   // 'kadir@example.com'

// Integer-based
final readonly class Age extends IntValueObject
{
    public static function from(int $value): static
    {
        ($value >= 0) or throw InvalidAge::negative($value);

        return parent::from($value);
    }
}

// Boolean-based
final readonly class IsActive extends BoolValueObject {}

$active = IsActive::from(true);
$active->value(); // true
```

For composite value objects, implement `ValueObjectInterface` directly:

```php
use SolidFrame\Ddd\ValueObject\ValueObjectInterface;

final readonly class Money implements ValueObjectInterface
{
    private function __construct(
        public int $amount,
        public string $currency,
    ) {}

    public static function from(int $amount, string $currency): self
    {
        return new self($amount, $currency);
    }

    public function add(self $other): self
    {
        ($this->currency === $other->currency)
            or throw InvalidMoney::currencyMismatch($this->currency, $other->currency);

        return new self($this->amount + $other->amount, $this->currency);
    }

    public function equals(ValueObjectInterface $other): bool
    {
        return $other instanceof self
            && $this->amount === $other->amount
            && $this->currency === $other->currency;
    }

    public function __toString(): string
    {
        return sprintf('%d %s', $this->amount, $this->currency);
    }
}
```

### Specification

Composable business rules using the Specification pattern.

```php
use SolidFrame\Ddd\Specification\AbstractSpecification;

final class IsAdult extends AbstractSpecification
{
    public function isSatisfiedBy(mixed $candidate): bool
    {
        return $candidate->age() >= 18;
    }
}

final class HasVerifiedEmail extends AbstractSpecification
{
    public function isSatisfiedBy(mixed $candidate): bool
    {
        return $candidate->isEmailVerified();
    }
}

// Compose specifications
$canPurchase = (new IsAdult())->and(new HasVerifiedEmail());
$canPurchase->isSatisfiedBy($user); // true/false

// Negate
$isMinor = (new IsAdult())->not();

// OR
$canAccess = (new IsAdult())->or(new HasParentalConsent());
```

## API Reference

| Class / Interface | Purpose |
|---|---|
| `EntityInterface` | Contract for entities with identity |
| `AbstractEntity` | Base entity with identity and equality |
| `AggregateRootInterface` | Entity that records domain events |
| `AbstractAggregateRoot` | Base aggregate with `recordThat()` / `releaseEvents()` |
| `ValueObjectInterface` | Contract for immutable value objects |
| `StringValueObject` | Base for string value objects |
| `IntValueObject` | Base for integer value objects |
| `BoolValueObject` | Base for boolean value objects |
| `SpecificationInterface` | Composable business rule contract |
| `AbstractSpecification` | Base specification with `and()` / `or()` / `not()` |
| `AndSpecification` | Combines two specs with AND |
| `OrSpecification` | Combines two specs with OR |
| `NotSpecification` | Negates a spec |

## Related Packages

- [solidframe/core](../core) — Identity, DomainEventInterface, Bus interfaces
- [solidframe/cqrs](../cqrs) — Command/Query bus for use cases
- [solidframe/event-sourcing](../event-sourcing) — Event-sourced aggregates
- [solidframe/archtest](../archtest) — Enforce DDD rules in tests
- [solidframe/laravel](../laravel) — `make:entity`, `make:value-object`, `make:aggregate-root`
- [solidframe/symfony](../symfony) — Same generators for Symfony

## Contributing

This repository is a read-only split of the [solidframe/solidframe](https://github.com/solidframe/solidframe) monorepo, auto-synced on every push to `main`. Issues, pull requests, and discussions belong in the monorepo.
