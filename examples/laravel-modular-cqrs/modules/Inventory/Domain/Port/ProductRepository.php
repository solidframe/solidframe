<?php

declare(strict_types=1);

namespace Modules\Inventory\Domain\Port;

use Modules\Inventory\Domain\Product;
use Modules\Inventory\Domain\ProductId;

interface ProductRepository
{
    public function find(ProductId $id): Product;

    public function save(Product $product): void;

    /** @return list<Product> */
    public function all(): array;
}
