<?php

declare(strict_types=1);

namespace App\Infrastructure\Persistence\Dbal;

use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Schema\Schema;

final readonly class SchemaManager
{
    public function __construct(private Connection $connection) {}

    public function createSchema(): void
    {
        $schema = new Schema();

        $orders = $schema->createTable('orders');
        $orders->addColumn('id', 'string', ['length' => 36]);
        $orders->addColumn('customer_email', 'string', ['length' => 255]);
        $orders->addColumn('total_amount', 'integer');
        $orders->addColumn('status', 'string', ['length' => 20, 'default' => 'pending']);
        $orders->addColumn('created_at', 'datetime_immutable');
        $orders->addColumn('updated_at', 'datetime_immutable');
        $orders->setPrimaryKey(['id']);
        $orders->addIndex(['status']);

        $orderItems = $schema->createTable('order_items');
        $orderItems->addColumn('id', 'integer', ['autoincrement' => true]);
        $orderItems->addColumn('order_id', 'string', ['length' => 36]);
        $orderItems->addColumn('product_id', 'string', ['length' => 36]);
        $orderItems->addColumn('quantity', 'integer');
        $orderItems->addColumn('unit_price', 'integer');
        $orderItems->setPrimaryKey(['id']);
        $orderItems->addForeignKeyConstraint('orders', ['order_id'], ['id'], ['onDelete' => 'CASCADE']);
        $orderItems->addIndex(['order_id']);

        $products = $schema->createTable('products');
        $products->addColumn('id', 'string', ['length' => 36]);
        $products->addColumn('name', 'string', ['length' => 255]);
        $products->addColumn('sku', 'string', ['length' => 100]);
        $products->addColumn('stock', 'integer', ['default' => 0]);
        $products->addColumn('price', 'integer');
        $products->addColumn('created_at', 'datetime_immutable');
        $products->addColumn('updated_at', 'datetime_immutable');
        $products->setPrimaryKey(['id']);
        $products->addUniqueIndex(['sku']);

        $payments = $schema->createTable('payments');
        $payments->addColumn('id', 'string', ['length' => 36]);
        $payments->addColumn('order_id', 'string', ['length' => 36]);
        $payments->addColumn('amount', 'integer');
        $payments->addColumn('method', 'string', ['length' => 50]);
        $payments->addColumn('status', 'string', ['length' => 20, 'default' => 'pending']);
        $payments->addColumn('created_at', 'datetime_immutable');
        $payments->addColumn('updated_at', 'datetime_immutable');
        $payments->setPrimaryKey(['id']);
        $payments->addIndex(['order_id']);

        $platform = $this->connection->getDatabasePlatform();

        foreach ($schema->toSql($platform) as $sql) {
            $this->connection->executeStatement($sql);
        }
    }
}
