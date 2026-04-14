<?php

declare(strict_types=1);

namespace App\Modules\Inventory\Application\Command\AddProduct;

use SolidFrame\Cqrs\Command;

final readonly class AddProduct implements Command
{
    public function __construct(
        public string $productId,
        public string $name,
        public string $sku,
        public int $stock,
        public int $price,
    ) {
    }
}
