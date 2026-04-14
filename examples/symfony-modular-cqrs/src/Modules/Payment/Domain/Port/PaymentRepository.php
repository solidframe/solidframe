<?php

declare(strict_types=1);

namespace App\Modules\Payment\Domain\Port;

use App\Modules\Payment\Domain\Payment;
use App\Modules\Payment\Domain\PaymentId;

interface PaymentRepository
{
    public function find(PaymentId $id): Payment;

    public function findByOrderId(string $orderId): Payment;

    public function save(Payment $payment): void;
}
