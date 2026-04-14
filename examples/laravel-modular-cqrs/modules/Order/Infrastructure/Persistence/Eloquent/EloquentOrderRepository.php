<?php

declare(strict_types=1);

namespace Modules\Order\Infrastructure\Persistence\Eloquent;

use Modules\Order\Domain\Exception\OrderNotFoundException;
use Modules\Order\Domain\Order;
use Modules\Order\Domain\OrderId;
use Modules\Order\Domain\OrderStatus;
use Modules\Order\Domain\Port\OrderRepository;
use Modules\Order\Domain\ValueObject\CustomerEmail;
use Modules\Order\Domain\ValueObject\OrderItem;

final class EloquentOrderRepository implements OrderRepository
{
    public function find(OrderId $id): Order
    {
        $model = OrderModel::query()->with('items')->find($id->value());

        if (!$model instanceof OrderModel) {
            throw OrderNotFoundException::forId($id->value());
        }

        return $this->toDomain($model);
    }

    public function save(Order $order): void
    {
        $model = OrderModel::query()->updateOrCreate(
            ['id' => $order->identity()->value()],
            [
                'customer_email' => $order->customerEmail()->value(),
                'total_amount' => $order->totalAmount(),
                'status' => $order->status()->value,
            ],
        );

        $model->items()->delete();

        foreach ($order->items() as $item) {
            $model->items()->create([
                'product_id' => $item->productId,
                'quantity' => $item->quantity,
                'unit_price' => $item->unitPrice,
            ]);
        }
    }

    public function all(): array
    {
        return array_values(
            OrderModel::query()->with('items')->get()->map(fn (OrderModel $model) => $this->toDomain($model))->all(),
        );
    }

    private function toDomain(OrderModel $model): Order
    {
        return Order::reconstitute(
            id: new OrderId($model->id),
            customerEmail: CustomerEmail::from($model->customer_email),
            items: $model->items->map(fn (OrderItemModel $item) => new OrderItem(
                productId: $item->product_id,
                quantity: (int) $item->quantity,
                unitPrice: (int) $item->unit_price,
            ))->values()->all(),
            totalAmount: (int) $model->total_amount,
            status: OrderStatus::from($model->status),
        );
    }
}
