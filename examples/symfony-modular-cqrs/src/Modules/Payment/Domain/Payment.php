<?php

declare(strict_types=1);

namespace App\Modules\Payment\Domain;

use App\Modules\Payment\Domain\Exception\PaymentAlreadyChargedException;
use SolidFrame\Ddd\Aggregate\AbstractAggregateRoot;

final class Payment extends AbstractAggregateRoot
{
    private string $orderId;
    private int $amount;
    private string $method;
    private PaymentStatus $status;

    public static function create(PaymentId $id, string $orderId, int $amount, string $method): self
    {
        $payment = new self($id);
        $payment->orderId = $orderId;
        $payment->amount = $amount;
        $payment->method = $method;
        $payment->status = PaymentStatus::Pending;

        return $payment;
    }

    public static function reconstitute(
        PaymentId $id,
        string $orderId,
        int $amount,
        string $method,
        PaymentStatus $status,
    ): self {
        $payment = new self($id);
        $payment->orderId = $orderId;
        $payment->amount = $amount;
        $payment->method = $method;
        $payment->status = $status;

        return $payment;
    }

    public function charge(): void
    {
        if ($this->status === PaymentStatus::Charged) {
            throw PaymentAlreadyChargedException::forId($this->identity()->value());
        }

        $this->status = PaymentStatus::Charged;
    }

    public function refund(): void
    {
        $this->status = PaymentStatus::Refunded;
    }

    public function markFailed(): void
    {
        $this->status = PaymentStatus::Failed;
    }

    public function orderId(): string
    {
        return $this->orderId;
    }

    public function amount(): int
    {
        return $this->amount;
    }

    public function method(): string
    {
        return $this->method;
    }

    public function status(): PaymentStatus
    {
        return $this->status;
    }
}
