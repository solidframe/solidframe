<?php

declare(strict_types=1);

namespace App\Modules\Inventory\Domain\Port;

use App\Modules\Inventory\Domain\Product;
use App\Modules\Inventory\Domain\ProductId;

interface ProductRepository
{
    public function find(ProductId $id): Product;

    public function save(Product $product): void;

    /** @return list<Product> */
    public function all(): array;
}
