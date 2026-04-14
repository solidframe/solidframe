<?php

declare(strict_types=1);

namespace Modules\Payment\Domain\Event;

use SolidFrame\Modular\Event\AbstractIntegrationEvent;

final readonly class PaymentRefunded extends AbstractIntegrationEvent
{
    public function __construct(
        public readonly string $orderId,
        public readonly string $paymentId,
    ) {
        parent::__construct('payment');
    }

    public function eventName(): string
    {
        return 'payment.refunded';
    }
}
