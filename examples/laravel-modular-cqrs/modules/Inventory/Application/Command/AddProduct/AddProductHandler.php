<?php

declare(strict_types=1);

namespace Modules\Inventory\Application\Command\AddProduct;

use Modules\Inventory\Domain\Port\ProductRepository;
use Modules\Inventory\Domain\Product;
use Modules\Inventory\Domain\ProductId;
use Modules\Inventory\Domain\ValueObject\ProductName;
use Modules\Inventory\Domain\ValueObject\Sku;
use SolidFrame\Cqrs\CommandHandler;

final readonly class AddProductHandler implements CommandHandler
{
    public function __construct(
        private ProductRepository $products,
    ) {
    }

    public function __invoke(AddProduct $command): void
    {
        $id = new ProductId($command->productId);

        $product = Product::create(
            $id,
            ProductName::from($command->name),
            Sku::from($command->sku),
            $command->stock,
            $command->price,
        );

        $this->products->save($product);
    }
}
