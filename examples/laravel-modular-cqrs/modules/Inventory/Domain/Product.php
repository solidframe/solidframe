<?php

declare(strict_types=1);

namespace Modules\Inventory\Domain;

use Modules\Inventory\Domain\Exception\InsufficientStockException;
use Modules\Inventory\Domain\ValueObject\ProductName;
use Modules\Inventory\Domain\ValueObject\Sku;
use SolidFrame\Ddd\Aggregate\AbstractAggregateRoot;

final class Product extends AbstractAggregateRoot
{
    private ProductName $name;
    private Sku $sku;
    private int $stock;
    private int $price;

    public static function create(ProductId $id, ProductName $name, Sku $sku, int $stock, int $price): self
    {
        $product = new self($id);
        $product->name = $name;
        $product->sku = $sku;
        $product->stock = $stock;
        $product->price = $price;

        return $product;
    }

    public static function reconstitute(
        ProductId $id,
        ProductName $name,
        Sku $sku,
        int $stock,
        int $price,
    ): self {
        return self::create($id, $name, $sku, $stock, $price);
    }

    public function reserveStock(int $quantity): void
    {
        if ($this->stock < $quantity) {
            throw InsufficientStockException::forProduct($this->identity()->value(), $quantity, $this->stock);
        }

        $this->stock -= $quantity;
    }

    public function releaseStock(int $quantity): void
    {
        $this->stock += $quantity;
    }

    public function name(): ProductName
    {
        return $this->name;
    }

    public function sku(): Sku
    {
        return $this->sku;
    }

    public function stock(): int
    {
        return $this->stock;
    }

    public function price(): int
    {
        return $this->price;
    }
}
