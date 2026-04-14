<?php

declare(strict_types=1);

namespace Modules\Order\Application\Query\ListOrders;

use Modules\Order\Domain\Port\OrderRepository;
use SolidFrame\Cqrs\QueryHandler;

final readonly class ListOrdersHandler implements QueryHandler
{
    public function __construct(
        private OrderRepository $orders,
    ) {
    }

    /** @return list<array<string, mixed>> */
    public function __invoke(ListOrders $query): array
    {
        return array_map(fn ($order) => [
            'id' => $order->identity()->value(),
            'customer_email' => $order->customerEmail()->value(),
            'total_amount' => $order->totalAmount(),
            'status' => $order->status()->value,
        ], $this->orders->all());
    }
}
