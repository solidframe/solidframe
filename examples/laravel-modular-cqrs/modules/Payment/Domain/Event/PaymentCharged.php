<?php

declare(strict_types=1);

namespace Modules\Payment\Domain\Event;

use SolidFrame\Modular\Event\AbstractIntegrationEvent;

final readonly class PaymentCharged extends AbstractIntegrationEvent
{
    public function __construct(
        public readonly string $orderId,
        public readonly string $paymentId,
        public readonly int $amount,
    ) {
        parent::__construct('payment');
    }

    public function eventName(): string
    {
        return 'payment.charged';
    }
}
