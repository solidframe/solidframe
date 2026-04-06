<?php

declare(strict_types=1);

namespace SolidFrame\PHPStanRules\Tests\Rules\Ddd\Fixtures;

abstract class TestAggregateRoot
{
    protected function __construct() {}
}

final class Order extends TestAggregateRoot
{
    public static function create(): self
    {
        return new self();
    }
}

final class OrderService
{
    public function handle(): void
    {
        new Order();
    }
}
