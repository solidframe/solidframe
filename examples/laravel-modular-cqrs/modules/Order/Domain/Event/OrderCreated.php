<?php

declare(strict_types=1);

namespace Modules\Order\Domain\Event;

use SolidFrame\Modular\Event\AbstractIntegrationEvent;

final readonly class OrderCreated extends AbstractIntegrationEvent
{
    public function __construct(
        public readonly string $orderId,
        public readonly string $customerEmail,
        /** @var list<array{product_id: string, quantity: int, unit_price: int}> */
        public readonly array $items,
        public readonly int $totalAmount,
    ) {
        parent::__construct('order');
    }

    public function eventName(): string
    {
        return 'order.created';
    }
}
