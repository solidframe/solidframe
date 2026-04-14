<?php

declare(strict_types=1);

namespace Tests\Unit\Inventory;

use Modules\Inventory\Domain\Exception\InsufficientStockException;
use Modules\Inventory\Domain\Product;
use Modules\Inventory\Domain\ProductId;
use Modules\Inventory\Domain\ValueObject\ProductName;
use Modules\Inventory\Domain\ValueObject\Sku;
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

        $this->assertSame('Laptop', $product->name()->value());
        $this->assertSame('LAP-001', $product->sku()->value());
        $this->assertSame(10, $product->stock());
        $this->assertSame(99900, $product->price());
    }

    #[Test]
    public function reserveStock(): void
    {
        $product = $this->createProductWithStock(10);
        $product->reserveStock(3);

        $this->assertSame(7, $product->stock());
    }

    #[Test]
    public function reserveAllStock(): void
    {
        $product = $this->createProductWithStock(5);
        $product->reserveStock(5);

        $this->assertSame(0, $product->stock());
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

        $this->assertSame(5, $product->stock());
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
