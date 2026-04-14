<?php

declare(strict_types=1);

namespace App\Tests\Unit\Inventory;

use App\Modules\Inventory\Domain\Exception\InsufficientStockException;
use App\Modules\Inventory\Domain\Product;
use App\Modules\Inventory\Domain\ProductId;
use App\Modules\Inventory\Domain\ValueObject\ProductName;
use App\Modules\Inventory\Domain\ValueObject\Sku;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

final class ProductTest extends TestCase
{
    #[Test]
    public function createProduct(): void
    {
        $product = Product::create(
            ProductId::generate(),
            ProductName::from('Laptop'),
            Sku::from('LAP-001'),
            10,
            99900,
        );

        self::assertSame('Laptop', $product->name()->value());
        self::assertSame('LAP-001', $product->sku()->value());
        self::assertSame(10, $product->stock());
        self::assertSame(99900, $product->price());
    }

    #[Test]
    public function reserveStock(): void
    {
        $product = $this->createProductWithStock(10);
        $product->reserveStock(3);

        self::assertSame(7, $product->stock());
    }

    #[Test]
    public function reserveAllStock(): void
    {
        $product = $this->createProductWithStock(5);
        $product->reserveStock(5);

        self::assertSame(0, $product->stock());
    }

    #[Test]
    public function insufficientStock(): void
    {
        $product = $this->createProductWithStock(3);

        $this->expectException(InsufficientStockException::class);
        $product->reserveStock(5);
    }

    #[Test]
    public function releaseStock(): void
    {
        $product = $this->createProductWithStock(5);
        $product->reserveStock(3);
        $product->releaseStock(3);

        self::assertSame(5, $product->stock());
    }

    private function createProductWithStock(int $stock): Product
    {
        return Product::create(
            ProductId::generate(),
            ProductName::from('Test Product'),
            Sku::from('TEST-001'),
            $stock,
            1000,
        );
    }
}
