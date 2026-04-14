<?php

declare(strict_types=1);

namespace Modules\Inventory\Domain\Event;

use SolidFrame\Modular\Event\AbstractIntegrationEvent;

final readonly class StockReserved extends AbstractIntegrationEvent
{
    public function __construct(
        public readonly string $orderId,
        /** @var list<array{product_id: string, quantity: int}> */
        public readonly array $reservedItems,
    ) {
        parent::__construct('inventory');
    }

    public function eventName(): string
    {
        return 'inventory.stock_reserved';
    }
}
