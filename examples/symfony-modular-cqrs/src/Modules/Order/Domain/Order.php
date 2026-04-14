<?php

declare(strict_types=1);

namespace App\Modules\Order\Domain;

use App\Modules\Order\Domain\Event\OrderCancelled;
use App\Modules\Order\Domain\Event\OrderConfirmed;
use App\Modules\Order\Domain\Event\OrderCreated;
use App\Modules\Order\Domain\Exception\OrderAlreadyCancelledException;
use App\Modules\Order\Domain\Exception\OrderAlreadyConfirmedException;
use App\Modules\Order\Domain\ValueObject\CustomerEmail;
use App\Modules\Order\Domain\ValueObject\OrderItem;
use SolidFrame\Ddd\Aggregate\AbstractAggregateRoot;

final class Order extends AbstractAggregateRoot
{
    private OrderStatus $status;
    private CustomerEmail $customerEmail;
    /** @var list<OrderItem> */
    private array $items;
    private int $totalAmount;

    /** @param list<OrderItem> $items */
    public static function create(OrderId $id, CustomerEmail $customerEmail, array $items): self
    {
        $order = new self($id);
        $order->customerEmail = $customerEmail;
        $order->items = $items;
        $order->totalAmount = array_sum(array_map(fn (OrderItem $item) => $item->lineTotal(), $items));
        $order->status = OrderStatus::Pending;

        $order->recordThat(new OrderCreated(
            orderId: $id->value(),
            customerEmail: $customerEmail->value(),
            items: array_map(fn (OrderItem $item) => [
                'product_id' => $item->productId,
                'quantity' => $item->quantity,
                'unit_price' => $item->unitPrice,
            ], $items),
            totalAmount: $order->totalAmount,
        ));

        return $order;
    }

    /** @param array<int, OrderItem> $items */
    public static function reconstitute(
        OrderId $id,
        CustomerEmail $customerEmail,
        array $items,
        int $totalAmount,
        OrderStatus $status,
    ): self {
        $order = new self($id);
        $order->customerEmail = $customerEmail;
        $order->items = array_values($items);
        $order->totalAmount = $totalAmount;
        $order->status = $status;

        return $order;
    }

    public function markStockReserved(): void
    {
        $this->status = OrderStatus::StockReserved;
    }

    public function confirm(): void
    {
        if ($this->status === OrderStatus::Confirmed) {
            throw OrderAlreadyConfirmedException::forId($this->identity()->value());
        }

        if ($this->status === OrderStatus::Cancelled) {
            throw OrderAlreadyCancelledException::forId($this->identity()->value());
        }

        $this->status = OrderStatus::Confirmed;

        $this->recordThat(new OrderConfirmed(
            orderId: $this->identity()->value(),
        ));
    }

    public function cancel(): void
    {
        if ($this->status === OrderStatus::Cancelled) {
            throw OrderAlreadyCancelledException::forId($this->identity()->value());
        }

        if ($this->status === OrderStatus::Confirmed) {
            throw OrderAlreadyConfirmedException::forId($this->identity()->value());
        }

        $this->status = OrderStatus::Cancelled;

        $this->recordThat(new OrderCancelled(
            orderId: $this->identity()->value(),
        ));
    }

    public function status(): OrderStatus
    {
        return $this->status;
    }

    public function customerEmail(): CustomerEmail
    {
        return $this->customerEmail;
    }

    /** @return list<OrderItem> */
    public function items(): array
    {
        return $this->items;
    }

    public function totalAmount(): int
    {
        return $this->totalAmount;
    }
}
