<?php

declare(strict_types=1);

namespace App\Modules\Payment\Application\Query\GetPayment;

use App\Modules\Payment\Domain\Port\PaymentRepository;
use SolidFrame\Cqrs\QueryHandler;

final readonly class GetPaymentHandler implements QueryHandler
{
    public function __construct(
        private PaymentRepository $payments,
    ) {
    }

    /** @return array<string, mixed> */
    public function __invoke(GetPayment $query): array
    {
        $payment = $this->payments->findByOrderId($query->orderId);

        return [
            'id' => $payment->identity()->value(),
            'order_id' => $payment->orderId(),
            'amount' => $payment->amount(),
            'method' => $payment->method(),
            'status' => $payment->status()->value,
        ];
    }
}
