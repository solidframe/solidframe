<?php

declare(strict_types=1);

namespace App\Modules\Inventory\Domain\Event;

use SolidFrame\Modular\Event\AbstractIntegrationEvent;

final readonly class StockReleased extends AbstractIntegrationEvent
{
    public function __construct(
        public readonly string $orderId,
    ) {
        parent::__construct('inventory');
    }

    public function eventName(): string
    {
        return 'inventory.stock_released';
    }
}
