<?php

declare(strict_types=1);

namespace App\Modules\Payment\Application\Query\GetPayment;

use SolidFrame\Cqrs\Query;

final readonly class GetPayment implements Query
{
    public function __construct(
        public string $orderId,
    ) {
    }
}
