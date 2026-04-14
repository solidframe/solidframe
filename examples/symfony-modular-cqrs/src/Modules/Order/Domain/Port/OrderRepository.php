<?php

declare(strict_types=1);

namespace App\Modules\Order\Domain\Port;

use App\Modules\Order\Domain\Order;
use App\Modules\Order\Domain\OrderId;

interface OrderRepository
{
    public function find(OrderId $id): Order;

    public function save(Order $order): void;

    /** @return list<Order> */
    public function all(): array;
}
