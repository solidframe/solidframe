<?php

declare(strict_types=1);

namespace App\Tests\Unit\Payment;

use App\Modules\Payment\Domain\Exception\PaymentAlreadyChargedException;
use App\Modules\Payment\Domain\Payment;
use App\Modules\Payment\Domain\PaymentId;
use App\Modules\Payment\Domain\PaymentStatus;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

final class PaymentTest extends TestCase
{
    #[Test]
    public function createPayment(): void
    {
        $payment = Payment::create(
            PaymentId::generate(),
            'order-123',
            5000,
            'credit_card',
        );

        self::assertSame('order-123', $payment->orderId());
        self::assertSame(5000, $payment->amount());
        self::assertSame('credit_card', $payment->method());
        self::assertSame(PaymentStatus::Pending, $payment->status());
    }

    #[Test]
    public function chargePayment(): void
    {
        $payment = $this->createPendingPayment();
        $payment->charge();

        self::assertSame(PaymentStatus::Charged, $payment->status());
    }

    #[Test]
    public function cannotChargeAlreadyChargedPayment(): void
    {
        $payment = $this->createPendingPayment();
        $payment->charge();

        $this->expectException(PaymentAlreadyChargedException::class);
        $payment->charge();
    }

    #[Test]
    public function refundPayment(): void
    {
        $payment = $this->createPendingPayment();
        $payment->charge();
        $payment->refund();

        self::assertSame(PaymentStatus::Refunded, $payment->status());
    }

    #[Test]
    public function markPaymentFailed(): void
    {
        $payment = $this->createPendingPayment();
        $payment->markFailed();

        self::assertSame(PaymentStatus::Failed, $payment->status());
    }

    private function createPendingPayment(): Payment
    {
        return Payment::create(
            PaymentId::generate(),
            'order-123',
            5000,
            'credit_card',
        );
    }
}
