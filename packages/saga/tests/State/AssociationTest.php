<?php

declare(strict_types=1);

namespace SolidFrame\Saga\Tests\State;

use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use SolidFrame\Saga\State\Association;

final class AssociationTest extends TestCase
{
    #[Test]
    public function storesKeyAndValue(): void
    {
        $association = new Association('orderId', 'order-123');

        self::assertSame('orderId', $association->key);
        self::assertSame('order-123', $association->value);
    }

    #[Test]
    public function equalityWhenSameKeyAndValue(): void
    {
        $a = new Association('orderId', 'order-123');
        $b = new Association('orderId', 'order-123');

        self::assertTrue($a->equals($b));
    }

    #[Test]
    public function notEqualWhenDifferentKey(): void
    {
        $a = new Association('orderId', 'order-123');
        $b = new Association('paymentId', 'order-123');

        self::assertFalse($a->equals($b));
    }

    #[Test]
    public function notEqualWhenDifferentValue(): void
    {
        $a = new Association('orderId', 'order-123');
        $b = new Association('orderId', 'order-456');

        self::assertFalse($a->equals($b));
    }
}
