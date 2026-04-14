<?php

declare(strict_types=1);

namespace Modules\Inventory\Application\Query\GetProduct;

use SolidFrame\Cqrs\Query;

final readonly class GetProduct implements Query
{
    public function __construct(
        public string $productId,
    ) {
    }
}
