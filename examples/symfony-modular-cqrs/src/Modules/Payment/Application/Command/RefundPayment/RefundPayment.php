<?php

declare(strict_types=1);

namespace App\Modules\Payment\Application\Command\RefundPayment;

use SolidFrame\Cqrs\Command;

final readonly class RefundPayment implements Command
{
    public function __construct(
        public string $orderId,
    ) {
    }
}
