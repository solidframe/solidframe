<?php

declare(strict_types=1);

namespace App\Modules\Payment\Application\Command\ChargePayment;

use App\Modules\Payment\Domain\Event\PaymentCharged;
use App\Modules\Payment\Domain\Event\PaymentFailed;
use App\Modules\Payment\Domain\Payment;
use App\Modules\Payment\Domain\PaymentId;
use App\Modules\Payment\Domain\Port\PaymentRepository;
use SolidFrame\Core\Bus\EventBusInterface;
use SolidFrame\Cqrs\CommandHandler;

final readonly class ChargePaymentHandler implements CommandHandler
{
    public function __construct(
        private PaymentRepository $payments,
        private EventBusInterface $eventBus,
    ) {
    }

    public function __invoke(ChargePayment $command): void
    {
        $id = PaymentId::generate();

        $payment = Payment::create($id, $command->orderId, $command->amount, $command->method);

        try {
            $payment->charge();
            $this->payments->save($payment);

            $this->eventBus->dispatch(new PaymentCharged(
                orderId: $command->orderId,
                paymentId: $id->value(),
                amount: $command->amount,
            ));
        } catch (\Throwable $e) {
            $payment->markFailed();
            $this->payments->save($payment);

            $this->eventBus->dispatch(new PaymentFailed(
                orderId: $command->orderId,
                reason: $e->getMessage(),
            ));
        }
    }
}
