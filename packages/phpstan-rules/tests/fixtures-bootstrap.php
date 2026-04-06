<?php

declare(strict_types=1);

// CQRS fixtures

namespace SolidFrame\PHPStanRules\Tests\Rules\Cqrs\Fixtures {
    interface TestCommandHandler {}
    interface TestQueryHandler {}
    interface TestHandler {}
    interface TestCommand {}
    interface TestQuery {}
    interface TestMessage {}
}

// DDD fixtures

namespace SolidFrame\PHPStanRules\Tests\Rules\Ddd\Fixtures {
    interface TestValueObject {}
    abstract class TestAggregateRoot
    {
        protected function __construct() {}
    }
}

// EventSourcing fixtures

namespace SolidFrame\PHPStanRules\Tests\Rules\EventSourcing\Fixtures {
    interface TestDomainEvent {}
    abstract class TestAggregate
    {
        protected function recordThat(object $event): void {}
    }
}
