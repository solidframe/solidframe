<?php

declare(strict_types=1);

namespace App\Modules\Payment\Application\Command\RefundPayment;

use App\Modules\Payment\Domain\Event\PaymentRefunded;
use App\Modules\Payment\Domain\Port\PaymentRepository;
use SolidFrame\Core\Bus\EventBusInterface;
use SolidFrame\Cqrs\CommandHandler;

final readonly class RefundPaymentHandler implements CommandHandler
{
    public function __construct(
        private PaymentRepository $payments,
        private EventBusInterface $eventBus,
    ) {
    }

    public function __invoke(RefundPayment $command): void
    {
        $payment = $this->payments->findByOrderId($command->orderId);

        $payment->refund();

        $this->payments->save($payment);

        $this->eventBus->dispatch(new PaymentRefunded(
            orderId: $command->orderId,
            paymentId: $payment->identity()->value(),
        ));
    }
}
