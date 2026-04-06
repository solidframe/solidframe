<?php

declare(strict_types=1);

namespace SolidFrame\PHPStanRules\Tests\Rules\EventSourcing\Fixtures;

abstract class TestAggregate
{
    protected function recordThat(object $event): void {}
}

final class OrderPlaced {}

final class OrderCancelled {}

final class ValidAggregate extends TestAggregate
{
    public function place(): void
    {
        $this->recordThat(new OrderPlaced());
    }

    protected function applyOrderPlaced(OrderPlaced $event): void {}
}

final class InvalidAggregate extends TestAggregate
{
    public function cancel(): void
    {
        $this->recordThat(new OrderCancelled());
    }
}
