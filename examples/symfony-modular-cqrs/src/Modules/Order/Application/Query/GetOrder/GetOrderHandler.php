<?php

declare(strict_types=1);

namespace App\Modules\Order\Application\Query\GetOrder;

use App\Modules\Order\Domain\OrderId;
use App\Modules\Order\Domain\Port\OrderRepository;
use SolidFrame\Cqrs\QueryHandler;

final readonly class GetOrderHandler implements QueryHandler
{
    public function __construct(
        private OrderRepository $orders,
    ) {
    }

    /** @return array<string, mixed> */
    public function __invoke(GetOrder $query): array
    {
        $order = $this->orders->find(new OrderId($query->orderId));

        return [
            'id' => $order->identity()->value(),
            'customer_email' => $order->customerEmail()->value(),
            'items' => array_map(fn ($item) => [
                'product_id' => $item->productId,
                'quantity' => $item->quantity,
                'unit_price' => $item->unitPrice,
                'line_total' => $item->lineTotal(),
            ], $order->items()),
            'total_amount' => $order->totalAmount(),
            'status' => $order->status()->value,
        ];
    }
}
