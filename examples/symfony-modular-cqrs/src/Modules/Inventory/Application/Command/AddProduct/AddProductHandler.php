<?php

declare(strict_types=1);

namespace App\Modules\Inventory\Application\Command\AddProduct;

use App\Modules\Inventory\Domain\Port\ProductRepository;
use App\Modules\Inventory\Domain\Product;
use App\Modules\Inventory\Domain\ProductId;
use App\Modules\Inventory\Domain\ValueObject\ProductName;
use App\Modules\Inventory\Domain\ValueObject\Sku;
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
