# SolidFrame ArchTest

Architecture testing with fluent API: enforce DDD, CQRS, event-driven, and modular rules as PHPUnit tests.

Write architectural constraints as tests. Break a rule, break the build.

## Installation

```bash
composer require solidframe/archtest --dev
```

## Quick Start

### Fluent Assertions

```php
use SolidFrame\Archtest\Arch;
use PHPUnit\Framework\TestCase;
use PHPUnit\Framework\Attributes\Test;

final class ArchitectureTest extends TestCase
{
    #[Test]
    public function valueObjectsAreFinalAndReadonly(): void
    {
        Arch::assertThat(__DIR__ . '/../src/Domain/ValueObject')
            ->areFinal()
            ->areReadonly();
    }

    #[Test]
    public function domainDoesNotDependOnInfrastructure(): void
    {
        Arch::assertThat(__DIR__ . '/../src/Domain')
            ->doesNotDependOn('App\Infrastructure');
    }

    #[Test]
    public function handlersHaveCorrectSuffix(): void
    {
        Arch::assertThat(__DIR__ . '/../src/Application/Handler')
            ->haveSuffix('Handler');
    }
}
```

### Presets

Built-in presets enforce common architectural patterns with zero configuration:

```php
#[Test]
public function dddRules(): void
{
    Arch::preset('ddd', [
        'domainDir' => __DIR__ . '/../src/Domain',
        'infrastructureDir' => __DIR__ . '/../src/Infrastructure',
        'applicationDir' => __DIR__ . '/../src/Application',
    ])->assert();
}

#[Test]
public function cqrsRules(): void
{
    Arch::preset('cqrs', [
        'commandDir' => __DIR__ . '/../src/Application/Command',
        'queryDir' => __DIR__ . '/../src/Application/Query',
        'handlerDir' => __DIR__ . '/../src/Application/Handler',
    ])->assert();
}

#[Test]
public function eventDrivenRules(): void
{
    Arch::preset('event-driven', [
        'eventDir' => __DIR__ . '/../src/Domain/Event',
    ])->assert();
}

#[Test]
public function modularRules(): void
{
    Arch::preset('modular', [
        'modulesDir' => __DIR__ . '/../modules',
        'contractSubNamespace' => 'Contract', // default
    ])->assert();
}
```

## Available Assertions

### Structural

```php
Arch::assertThat($dir)
    ->areFinal()       // all classes must be final
    ->areReadonly()     // all classes must be readonly
    ->areAbstract()    // all classes must be abstract
    ->areInterfaces()  // all must be interfaces
    ->areEnums();      // all must be enums
```

### Naming

```php
Arch::assertThat($dir)
    ->haveSuffix('Handler')     // class name ends with Handler
    ->havePrefix('Abstract');   // class name starts with Abstract
```

### Inheritance

```php
Arch::assertThat($dir)
    ->implement(DomainEventInterface::class)   // must implement interface
    ->extend(AbstractEntity::class);           // must extend class
```

### Dependencies

```php
Arch::assertThat($dir)
    ->doesNotDependOn('App\Infrastructure')   // no imports from namespace
    ->onlyDependsOn([                         // whitelist allowed namespaces
        'App\Domain',
        'SolidFrame\Core',
        'SolidFrame\Ddd',
    ]);
```

## Preset Rules

### DDD Preset

| Rule | Description |
|---|---|
| Domain isolation | Domain must not depend on Infrastructure or Application |
| ValueObject final | All ValueObject classes must be final |
| ValueObject readonly | All ValueObject classes must be readonly |

### CQRS Preset

| Rule | Description |
|---|---|
| Command immutability | Commands must be final readonly |
| Query immutability | Queries must be final readonly |
| Handler pairing | Each Command/Query must have a matching Handler (optional) |

### Event-Driven Preset

| Rule | Description |
|---|---|
| Event immutability | Events must be final readonly |
| Event interface | Events must implement DomainEventInterface |

### Modular Preset

| Rule | Description |
|---|---|
| Module isolation | Cross-module dependencies only allowed through Contract namespace |

## Custom Presets

Implement `PresetInterface` for your own rules:

```php
use SolidFrame\Archtest\Preset\PresetInterface;

final readonly class MyCustomPreset implements PresetInterface
{
    public function __construct(private string $srcDir) {}

    public function evaluate(): array
    {
        $violations = [];

        // your validation logic...
        // return array of violation message strings

        return $violations;
    }
}

// Usage
Arch::presetFrom(new MyCustomPreset(__DIR__ . '/../src'))->assert();
```

## API Reference

| Class / Interface | Purpose |
|---|---|
| `Arch` | Main entry point: `assertThat()` and `preset()` |
| `ArchExpectation` | Fluent assertion builder |
| `PresetInterface` | Contract for custom presets |
| `PresetResult` | Wraps preset with `assert()` method |
| `DddPreset` | DDD rules (domain isolation, VO immutability) |
| `CqrsPreset` | CQRS rules (message immutability, handler pairing) |
| `EventDrivenPreset` | Event rules (immutability, interface) |
| `ModularPreset` | Module isolation rules |
| `ClassInfo` | Metadata about a PHP class |
| `ClassFinder` | Discovers classes in directories |
| `DependencyParser` | Extracts use-statement dependencies |
| `ArchViolationException` | Thrown on rule violations |

## Related Packages

- [solidframe/core](../core) — DomainEventInterface checked by presets
- [solidframe/ddd](../ddd) — ValueObject, Entity conventions enforced
- [solidframe/cqrs](../cqrs) — Command/Query immutability enforced
- [solidframe/phpstan-rules](../phpstan-rules) — Complementary static analysis rules
