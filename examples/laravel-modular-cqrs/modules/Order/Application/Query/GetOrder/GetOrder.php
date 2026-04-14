<?php

declare(strict_types=1);

namespace Modules\Order\Application\Query\GetOrder;

use SolidFrame\Cqrs\Query;

final readonly class GetOrder implements Query
{
    public function __construct(
        public string $orderId,
    ) {
    }
}
