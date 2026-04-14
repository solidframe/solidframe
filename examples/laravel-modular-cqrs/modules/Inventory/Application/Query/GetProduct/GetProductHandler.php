<?php

declare(strict_types=1);

namespace Modules\Inventory\Application\Query\GetProduct;

use Modules\Inventory\Domain\Port\ProductRepository;
use Modules\Inventory\Domain\ProductId;
use SolidFrame\Cqrs\QueryHandler;

final readonly class GetProductHandler implements QueryHandler
{
    public function __construct(
        private ProductRepository $products,
    ) {
    }

    /** @return array<string, mixed> */
    public function __invoke(GetProduct $query): array
    {
        $product = $this->products->find(new ProductId($query->productId));

        return [
            'id' => $product->identity()->value(),
            'name' => $product->name()->value(),
            'sku' => $product->sku()->value(),
            'stock' => $product->stock(),
            'price' => $product->price(),
        ];
    }
}
