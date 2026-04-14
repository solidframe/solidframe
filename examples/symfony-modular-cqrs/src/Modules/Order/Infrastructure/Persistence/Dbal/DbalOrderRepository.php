<?php

declare(strict_types=1);

namespace App\Modules\Order\Infrastructure\Persistence\Dbal;

use App\Modules\Order\Domain\Exception\OrderNotFoundException;
use App\Modules\Order\Domain\Order;
use App\Modules\Order\Domain\OrderId;
use App\Modules\Order\Domain\OrderStatus;
use App\Modules\Order\Domain\Port\OrderRepository;
use App\Modules\Order\Domain\ValueObject\CustomerEmail;
use App\Modules\Order\Domain\ValueObject\OrderItem;
use Doctrine\DBAL\Connection;

final readonly class DbalOrderRepository implements OrderRepository
{
    public function __construct(private Connection $connection) {}

    public function find(OrderId $id): Order
    {
        $row = $this->connection->fetchAssociative(
            'SELECT * FROM orders WHERE id = ?',
            [$id->value()],
        );

        if ($row === false) {
            throw OrderNotFoundException::forId($id->value());
        }

        $items = $this->connection->fetchAllAssociative(
            'SELECT * FROM order_items WHERE order_id = ?',
            [$id->value()],
        );

        return $this->toDomain($row, $items);
    }

    public function save(Order $order): void
    {
        $now = new \DateTimeImmutable();

        $existing = $this->connection->fetchOne(
            'SELECT id FROM orders WHERE id = ?',
            [$order->identity()->value()],
        );

        if ($existing !== false) {
            $this->connection->update('orders', [
                'customer_email' => $order->customerEmail()->value(),
                'total_amount' => $order->totalAmount(),
                'status' => $order->status()->value,
                'updated_at' => $now->format('Y-m-d H:i:s'),
            ], ['id' => $order->identity()->value()]);
        } else {
            $this->connection->insert('orders', [
                'id' => $order->identity()->value(),
                'customer_email' => $order->customerEmail()->value(),
                'total_amount' => $order->totalAmount(),
                'status' => $order->status()->value,
                'created_at' => $now->format('Y-m-d H:i:s'),
                'updated_at' => $now->format('Y-m-d H:i:s'),
            ]);

            foreach ($order->items() as $item) {
                $this->connection->insert('order_items', [
                    'order_id' => $order->identity()->value(),
                    'product_id' => $item->productId,
                    'quantity' => $item->quantity,
                    'unit_price' => $item->unitPrice,
                ]);
            }
        }
    }

    public function all(): array
    {
        $rows = $this->connection->fetchAllAssociative('SELECT * FROM orders');

        return array_map(function (array $row) {
            $items = $this->connection->fetchAllAssociative(
                'SELECT * FROM order_items WHERE order_id = ?',
                [$row['id']],
            );

            return $this->toDomain($row, $items);
        }, $rows);
    }

    /**
     * @param array<string, mixed> $row
     * @param list<array<string, mixed>> $items
     */
    private function toDomain(array $row, array $items): Order
    {
        return Order::reconstitute(
            id: new OrderId((string) $row['id']),
            customerEmail: CustomerEmail::from((string) $row['customer_email']),
            items: array_map(fn (array $item) => new OrderItem(
                productId: (string) $item['product_id'],
                quantity: (int) $item['quantity'],
                unitPrice: (int) $item['unit_price'],
            ), $items),
            totalAmount: (int) $row['total_amount'],
            status: OrderStatus::from((string) $row['status']),
        );
    }
}
