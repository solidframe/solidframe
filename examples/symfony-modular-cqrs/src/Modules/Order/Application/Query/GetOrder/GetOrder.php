<?php

declare(strict_types=1);

namespace App\Modules\Order\Application\Query\GetOrder;

use SolidFrame\Cqrs\Query;

final readonly class GetOrder implements Query
{
    public function __construct(
        public string $orderId,
    ) {
    }
}
