<?php

declare(strict_types=1);

namespace App\Tests\Unit\Order;

use App\Modules\Order\Application\Saga\OrderFulfillmentSaga;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use SolidFrame\Saga\State\SagaStatus;

final class OrderFulfillmentSagaTest extends TestCase
{
    #[Test]
    public function startSaga(): void
    {
        $saga = new OrderFulfillmentSaga();
        $saga->start('order-123', 5000);

        self::assertSame('order-123', $saga->orderId());
        self::assertSame(5000, $saga->totalAmount());
        self::assertSame(SagaStatus::InProgress, $saga->status());
        self::assertFalse($saga->isCompleted());
        self::assertFalse($saga->isFailed());
    }

    #[Test]
    public function completeSaga(): void
    {
        $saga = new OrderFulfillmentSaga();
        $saga->start('order-123', 5000);
        $saga->markCompleted();

        self::assertTrue($saga->isCompleted());
        self::assertSame(SagaStatus::Completed, $saga->status());
    }

    #[Test]
    public function failSagaRunsCompensations(): void
    {
        $compensated = false;

        $saga = new OrderFulfillmentSaga();
        $saga->start('order-123', 5000);
        $saga->registerCompensation(function () use (&$compensated): void {
            $compensated = true;
        });

        $saga->markFailed();

        self::assertTrue($saga->isFailed());
        self::assertTrue($compensated);
    }

    #[Test]
    public function compensationsRunInReverseOrder(): void
    {
        $order = [];

        $saga = new OrderFulfillmentSaga();
        $saga->start('order-123', 5000);
        $saga->registerCompensation(function () use (&$order): void {
            $order[] = 'first';
        });
        $saga->registerCompensation(function () use (&$order): void {
            $order[] = 'second';
        });

        $saga->markFailed();

        self::assertSame(['second', 'first'], $order);
    }

    #[Test]
    public function markStockReserved(): void
    {
        $saga = new OrderFulfillmentSaga();
        $saga->start('order-123', 5000);
        $saga->markStockReserved([
            ['product_id' => 'p-1', 'quantity' => 2],
        ]);

        self::assertCount(1, $saga->reservedItems());
    }
}
