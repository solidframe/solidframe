<?php

declare(strict_types=1);

namespace Modules\Payment\Infrastructure\Persistence\Eloquent;

use Modules\Payment\Domain\Exception\PaymentNotFoundException;
use Modules\Payment\Domain\Payment;
use Modules\Payment\Domain\PaymentId;
use Modules\Payment\Domain\PaymentStatus;
use Modules\Payment\Domain\Port\PaymentRepository;

final class EloquentPaymentRepository implements PaymentRepository
{
    public function find(PaymentId $id): Payment
    {
        $model = PaymentModel::query()->find($id->value());

        if (!$model instanceof PaymentModel) {
            throw PaymentNotFoundException::forId($id->value());
        }

        return $this->toDomain($model);
    }

    public function findByOrderId(string $orderId): Payment
    {
        $model = PaymentModel::query()->where('order_id', $orderId)->first();

        if (!$model instanceof PaymentModel) {
            throw PaymentNotFoundException::forOrderId($orderId);
        }

        return $this->toDomain($model);
    }

    public function save(Payment $payment): void
    {
        PaymentModel::query()->updateOrCreate(
            ['id' => $payment->identity()->value()],
            [
                'order_id' => $payment->orderId(),
                'amount' => $payment->amount(),
                'method' => $payment->method(),
                'status' => $payment->status()->value,
            ],
        );
    }

    private function toDomain(PaymentModel $model): Payment
    {
        return Payment::reconstitute(
            id: new PaymentId($model->id),
            orderId: $model->order_id,
            amount: (int) $model->amount,
            method: $model->method,
            status: PaymentStatus::from($model->status),
        );
    }
}
