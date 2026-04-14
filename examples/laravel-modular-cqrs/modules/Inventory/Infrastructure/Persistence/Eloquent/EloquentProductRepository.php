<?php

declare(strict_types=1);

namespace Modules\Inventory\Infrastructure\Persistence\Eloquent;

use Modules\Inventory\Domain\Exception\ProductNotFoundException;
use Modules\Inventory\Domain\Port\ProductRepository;
use Modules\Inventory\Domain\Product;
use Modules\Inventory\Domain\ProductId;
use Modules\Inventory\Domain\ValueObject\ProductName;
use Modules\Inventory\Domain\ValueObject\Sku;

final class EloquentProductRepository implements ProductRepository
{
    public function find(ProductId $id): Product
    {
        $model = ProductModel::query()->find($id->value());

        if (!$model instanceof ProductModel) {
            throw ProductNotFoundException::forId($id->value());
        }

        return $this->toDomain($model);
    }

    public function save(Product $product): void
    {
        ProductModel::query()->updateOrCreate(
            ['id' => $product->identity()->value()],
            [
                'name' => $product->name()->value(),
                'sku' => $product->sku()->value(),
                'stock' => $product->stock(),
                'price' => $product->price(),
            ],
        );
    }

    public function all(): array
    {
        return array_values(
            ProductModel::query()->get()->map(fn (ProductModel $model) => $this->toDomain($model))->all(),
        );
    }

    private function toDomain(ProductModel $model): Product
    {
        return Product::reconstitute(
            id: new ProductId($model->id),
            name: ProductName::from($model->name),
            sku: Sku::from($model->sku),
            stock: (int) $model->stock,
            price: (int) $model->price,
        );
    }
}
