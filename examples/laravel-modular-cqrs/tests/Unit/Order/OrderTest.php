<?php

declare(strict_types=1);

namespace Tests\Unit\Order;

use Modules\Order\Domain\Event\OrderCancelled;
use Modules\Order\Domain\Event\OrderConfirmed;
use Modules\Order\Domain\Event\OrderCreated;
use Modules\Order\Domain\Exception\OrderAlreadyCancelledException;
use Modules\Order\Domain\Exception\OrderAlreadyConfirmedException;
use Modules\Order\Domain\Order;
use Modules\Order\Domain\OrderId;
use Modules\Order\Domain\OrderStatus;
use Modules\Order\Domain\ValueObject\CustomerEmail;
use Modules\Order\Domain\ValueObject\OrderItem;
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

        $this->assertSame(OrderStatus::Pending, $order->status());
        $this->assertSame('test@example.com', $order->customerEmail()->value());
        $this->assertCount(1, $order->items());
        $this->assertSame(2000, $order->totalAmount());
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

        $this->assertCount(1, $events);
        $this->assertInstanceOf(OrderCreated::class, $events[0]);
        $this->assertSame(500, $events[0]->totalAmount);
    }

    #[Test]
    public function confirmOrder(): void
    {
        $order = $this->createPendingOrder();
        $order->markStockReserved();
        $order->confirm();

        $this->assertSame(OrderStatus::Confirmed, $order->status());

        $events = $order->releaseEvents();
        $this->assertCount(1, $events);
        $this->assertInstanceOf(OrderConfirmed::class, $events[0]);
    }

    #[Test]
    public function cancelOrder(): void
    {
        $order = $this->createPendingOrder();
        $order->cancel();

        $this->assertSame(OrderStatus::Cancelled, $order->status());

        $events = $order->releaseEvents();
        $this->assertCount(1, $events);
        $this->assertInstanceOf(OrderCancelled::class, $events[0]);
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

        $this->assertSame(3500, $order->totalAmount());
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
