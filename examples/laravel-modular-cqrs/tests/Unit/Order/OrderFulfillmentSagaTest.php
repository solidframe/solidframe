<?php

declare(strict_types=1);

namespace Tests\Unit\Order;

use Modules\Order\Application\Saga\OrderFulfillmentSaga;
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

        $this->assertSame('order-123', $saga->orderId());
        $this->assertSame(5000, $saga->totalAmount());
        $this->assertSame(SagaStatus::InProgress, $saga->status());
        $this->assertFalse($saga->isCompleted());
        $this->assertFalse($saga->isFailed());
    }

    #[Test]
    public function completeSaga(): void
    {
        $saga = new OrderFulfillmentSaga();
        $saga->start('order-123', 5000);
        $saga->markCompleted();

        $this->assertTrue($saga->isCompleted());
        $this->assertSame(SagaStatus::Completed, $saga->status());
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

        $this->assertTrue($saga->isFailed());
        $this->assertTrue($compensated);
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

        $this->assertSame(['second', 'first'], $order);
    }

    #[Test]
    public function markStockReserved(): void
    {
        $saga = new OrderFulfillmentSaga();
        $saga->start('order-123', 5000);
        $saga->markStockReserved([
            ['product_id' => 'p-1', 'quantity' => 2],
        ]);

        $this->assertCount(1, $saga->reservedItems());
    }
}
