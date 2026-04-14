<?php

declare(strict_types=1);

namespace App\Tests\Unit\Order;

use App\Modules\Order\Domain\Event\OrderCancelled;
use App\Modules\Order\Domain\Event\OrderConfirmed;
use App\Modules\Order\Domain\Event\OrderCreated;
use App\Modules\Order\Domain\Exception\OrderAlreadyCancelledException;
use App\Modules\Order\Domain\Exception\OrderAlreadyConfirmedException;
use App\Modules\Order\Domain\Order;
use App\Modules\Order\Domain\OrderId;
use App\Modules\Order\Domain\OrderStatus;
use App\Modules\Order\Domain\ValueObject\CustomerEmail;
use App\Modules\Order\Domain\ValueObject\OrderItem;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

final class OrderTest extends TestCase
{
    #[Test]
    public function createOrder(): void
    {
        $order = Order::create(
            OrderId::generate(),
            CustomerEmail::from('test@example.com'),
            [new OrderItem('product-1', 2, 1000)],
        );

        self::assertSame(OrderStatus::Pending, $order->status());
        self::assertSame('test@example.com', $order->customerEmail()->value());
        self::assertCount(1, $order->items());
        self::assertSame(2000, $order->totalAmount());
    }

    #[Test]
    public function createOrderRecordsEvent(): void
    {
        $order = Order::create(
            OrderId::generate(),
            CustomerEmail::from('test@example.com'),
            [new OrderItem('product-1', 1, 500)],
        );

        $events = $order->releaseEvents();

        self::assertCount(1, $events);
        self::assertInstanceOf(OrderCreated::class, $events[0]);
        self::assertSame(500, $events[0]->totalAmount);
    }

    #[Test]
    public function confirmOrder(): void
    {
        $order = $this->createPendingOrder();
        $order->markStockReserved();
        $order->confirm();

        self::assertSame(OrderStatus::Confirmed, $order->status());

        $events = $order->releaseEvents();
        self::assertCount(1, $events);
        self::assertInstanceOf(OrderConfirmed::class, $events[0]);
    }

    #[Test]
    public function cancelOrder(): void
    {
        $order = $this->createPendingOrder();
        $order->cancel();

        self::assertSame(OrderStatus::Cancelled, $order->status());

        $events = $order->releaseEvents();
        self::assertCount(1, $events);
        self::assertInstanceOf(OrderCancelled::class, $events[0]);
    }

    #[Test]
    public function cannotConfirmAlreadyConfirmedOrder(): void
    {
        $order = $this->createPendingOrder();
        $order->confirm();
        $order->releaseEvents();

        $this->expectException(OrderAlreadyConfirmedException::class);
        $order->confirm();
    }

    #[Test]
    public function cannotConfirmCancelledOrder(): void
    {
        $order = $this->createPendingOrder();
        $order->cancel();

        $this->expectException(OrderAlreadyCancelledException::class);
        $order->confirm();
    }

    #[Test]
    public function cannotCancelAlreadyCancelledOrder(): void
    {
        $order = $this->createPendingOrder();
        $order->cancel();

        $this->expectException(OrderAlreadyCancelledException::class);
        $order->cancel();
    }

    #[Test]
    public function cannotCancelConfirmedOrder(): void
    {
        $order = $this->createPendingOrder();
        $order->confirm();

        $this->expectException(OrderAlreadyConfirmedException::class);
        $order->cancel();
    }

    #[Test]
    public function totalAmountCalculatesFromItems(): void
    {
        $order = Order::create(
            OrderId::generate(),
            CustomerEmail::from('test@example.com'),
            [
                new OrderItem('product-1', 2, 1000),
                new OrderItem('product-2', 3, 500),
            ],
        );

        self::assertSame(3500, $order->totalAmount());
    }

    #[Test]
    public function customerEmailValidation(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        CustomerEmail::from('not-an-email');
    }

    private function createPendingOrder(): Order
    {
        $order = Order::create(
            OrderId::generate(),
            CustomerEmail::from('test@example.com'),
            [new OrderItem('product-1', 1, 1000)],
        );
        $order->releaseEvents();

        return $order;
    }
}
