<?php

declare(strict_types=1);

namespace Modules\Inventory\Domain\Event;

use SolidFrame\Modular\Event\AbstractIntegrationEvent;

final readonly class StockReservationFailed extends AbstractIntegrationEvent
{
    public function __construct(
        public readonly string $orderId,
        public readonly string $reason,
    ) {
        parent::__construct('inventory');
    }

    public function eventName(): string
    {
        return 'inventory.stock_reservation_failed';
    }
}
