<?php

declare(strict_types=1);

namespace App\Modules\Payment\Application\Command\ChargePayment;

use SolidFrame\Cqrs\Command;

final readonly class ChargePayment implements Command
{
    public function __construct(
        public string $orderId,
        public int $amount,
        public string $method,
    ) {
    }
}
