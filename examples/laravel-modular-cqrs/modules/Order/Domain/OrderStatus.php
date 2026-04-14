<?php

declare(strict_types=1);

namespace Modules\Order\Domain;

enum OrderStatus: string
{
    case Pending = 'pending';
    case StockReserved = 'stock_reserved';
    case Confirmed = 'confirmed';
    case Cancelled = 'cancelled';
}
