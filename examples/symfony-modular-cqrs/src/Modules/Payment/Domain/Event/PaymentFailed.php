<?php

declare(strict_types=1);

namespace App\Modules\Payment\Domain\Event;

use SolidFrame\Modular\Event\AbstractIntegrationEvent;

final readonly class PaymentFailed extends AbstractIntegrationEvent
{
    public function __construct(
        public readonly string $orderId,
        public readonly string $reason,
    ) {
        parent::__construct('payment');
    }

    public function eventName(): string
    {
        return 'payment.failed';
    }
}
