<?php

declare(strict_types=1);

namespace Tests\Unit\Payment;

use Modules\Payment\Domain\Exception\PaymentAlreadyChargedException;
use Modules\Payment\Domain\Payment;
use Modules\Payment\Domain\PaymentId;
use Modules\Payment\Domain\PaymentStatus;
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

        $this->assertSame('order-123', $payment->orderId());
        $this->assertSame(5000, $payment->amount());
        $this->assertSame('credit_card', $payment->method());
        $this->assertSame(PaymentStatus::Pending, $payment->status());
    }

    #[Test]
    public function chargePayment(): void
    {
        $payment = $this->createPendingPayment();
        $payment->charge();

        $this->assertSame(PaymentStatus::Charged, $payment->status());
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

        $this->assertSame(PaymentStatus::Refunded, $payment->status());
    }

    #[Test]
    public function markPaymentFailed(): void
    {
        $payment = $this->createPendingPayment();
        $payment->markFailed();

        $this->assertSame(PaymentStatus::Failed, $payment->status());
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
