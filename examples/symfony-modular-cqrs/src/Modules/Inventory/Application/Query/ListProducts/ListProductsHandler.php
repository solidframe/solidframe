<?php

declare(strict_types=1);

namespace App\Modules\Inventory\Application\Query\ListProducts;

use App\Modules\Inventory\Domain\Port\ProductRepository;
use SolidFrame\Cqrs\QueryHandler;

final readonly class ListProductsHandler implements QueryHandler
{
    public function __construct(
        private ProductRepository $products,
    ) {
    }

    /** @return list<array<string, mixed>> */
    public function __invoke(ListProducts $query): array
    {
        return array_map(fn ($product) => [
            'id' => $product->identity()->value(),
            'name' => $product->name()->value(),
            'sku' => $product->sku()->value(),
            'stock' => $product->stock(),
            'price' => $product->price(),
        ], $this->products->all());
    }
}
