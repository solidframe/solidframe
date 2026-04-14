<?php

declare(strict_types=1);

namespace Modules\Order\Domain\Event;

use SolidFrame\Modular\Event\AbstractIntegrationEvent;

final readonly class OrderCancelled extends AbstractIntegrationEvent
{
    public function __construct(
        public readonly string $orderId,
    ) {
        parent::__construct('order');
    }

    public function eventName(): string
    {
        return 'order.cancelled';
    }
}
