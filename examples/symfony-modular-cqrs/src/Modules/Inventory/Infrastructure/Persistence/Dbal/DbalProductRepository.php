<?php

declare(strict_types=1);

namespace App\Modules\Inventory\Infrastructure\Persistence\Dbal;

use App\Modules\Inventory\Domain\Exception\ProductNotFoundException;
use App\Modules\Inventory\Domain\Port\ProductRepository;
use App\Modules\Inventory\Domain\Product;
use App\Modules\Inventory\Domain\ProductId;
use App\Modules\Inventory\Domain\ValueObject\ProductName;
use App\Modules\Inventory\Domain\ValueObject\Sku;
use Doctrine\DBAL\Connection;

final readonly class DbalProductRepository implements ProductRepository
{
    public function __construct(private Connection $connection) {}

    public function find(ProductId $id): Product
    {
        $row = $this->connection->fetchAssociative(
            'SELECT * FROM products WHERE id = ?',
            [$id->value()],
        );

        if ($row === false) {
            throw ProductNotFoundException::forId($id->value());
        }

        return $this->toDomain($row);
    }

    public function save(Product $product): void
    {
        $now = new \DateTimeImmutable();

        $existing = $this->connection->fetchOne(
            'SELECT id FROM products WHERE id = ?',
            [$product->identity()->value()],
        );

        if ($existing !== false) {
            $this->connection->update('products', [
                'name' => $product->name()->value(),
                'sku' => $product->sku()->value(),
                'stock' => $product->stock(),
                'price' => $product->price(),
                'updated_at' => $now->format('Y-m-d H:i:s'),
            ], ['id' => $product->identity()->value()]);
        } else {
            $this->connection->insert('products', [
                'id' => $product->identity()->value(),
                'name' => $product->name()->value(),
                'sku' => $product->sku()->value(),
                'stock' => $product->stock(),
                'price' => $product->price(),
                'created_at' => $now->format('Y-m-d H:i:s'),
                'updated_at' => $now->format('Y-m-d H:i:s'),
            ]);
        }
    }

    public function all(): array
    {
        $rows = $this->connection->fetchAllAssociative('SELECT * FROM products');

        return array_map(fn (array $row) => $this->toDomain($row), $rows);
    }

    /**
     * @param array<string, mixed> $row
     */
    private function toDomain(array $row): Product
    {
        return Product::reconstitute(
            id: new ProductId((string) $row['id']),
            name: ProductName::from((string) $row['name']),
            sku: Sku::from((string) $row['sku']),
            stock: (int) $row['stock'],
            price: (int) $row['price'],
        );
    }
}
