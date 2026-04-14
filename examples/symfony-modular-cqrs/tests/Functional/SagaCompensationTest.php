<?php

declare(strict_types=1);

namespace App\Tests\Functional;

use App\Infrastructure\Persistence\Dbal\SchemaManager;
use Doctrine\DBAL\Connection;
use PHPUnit\Framework\Attributes\Test;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

final class SagaCompensationTest extends WebTestCase
{
    private KernelBrowser $client;

    protected function setUp(): void
    {
        $this->client = self::createClient();

        /** @var Connection $connection */
        $connection = self::getContainer()->get(Connection::class);

        $tables = $connection->createSchemaManager()->listTableNames();
        if (in_array('orders', $tables, true)) {
            $connection->executeStatement('DELETE FROM order_items');
            $connection->executeStatement('DELETE FROM payments');
            $connection->executeStatement('DELETE FROM orders');
            $connection->executeStatement('DELETE FROM products');
        } else {
            (new SchemaManager($connection))->createSchema();
        }
    }

    #[Test]
    public function orderFailsWhenInsufficientStock(): void
    {
        $productId = $this->createProduct(2);

        $orderId = $this->placeOrder($productId, 5);

        $this->client->request('GET', "/api/orders/{$orderId}");

        $data = $this->responseData();
        self::assertSame('cancelled', $data['status']);
    }

    #[Test]
    public function stockRestoredWhenOrderCancelledDueToInsufficientStock(): void
    {
        $productId = $this->createProduct(2);

        $this->placeOrder($productId, 5);

        $this->client->request('GET', "/api/products/{$productId}");

        $data = $this->responseData();
        self::assertSame(2, $data['stock']);
    }

    #[Test]
    public function happyPathSagaCompletesSuccessfully(): void
    {
        $productId = $this->createProduct(50);

        $this->client->request('POST', '/api/orders', [], [], ['CONTENT_TYPE' => 'application/json'], json_encode([
            'customer_email' => 'customer@example.com',
            'items' => [
                ['product_id' => $productId, 'quantity' => 3, 'unit_price' => 2990],
            ],
        ]));

        self::assertResponseStatusCodeSame(201);

        $data = $this->responseData();
        self::assertSame('confirmed', $data['status']);
        self::assertSame(8970, $data['total_amount']);

        $this->client->request('GET', "/api/products/{$productId}");
        $productData = $this->responseData();
        self::assertSame(47, $productData['stock']);
    }

    #[Test]
    public function multipleItemsOrderWithSufficientStock(): void
    {
        $laptopId = $this->createProduct(10, 'Laptop', 'LAP-001');
        $mouseId = $this->createProduct(50, 'Mouse', 'MOU-001');

        $this->client->request('POST', '/api/orders', [], [], ['CONTENT_TYPE' => 'application/json'], json_encode([
            'customer_email' => 'customer@example.com',
            'items' => [
                ['product_id' => $laptopId, 'quantity' => 1, 'unit_price' => 99900],
                ['product_id' => $mouseId, 'quantity' => 2, 'unit_price' => 2990],
            ],
        ]));

        $data = $this->responseData();
        self::assertSame('confirmed', $data['status']);
        self::assertSame(105880, $data['total_amount']);
    }

    private function createProduct(int $stock, string $name = 'Product', string $sku = 'PRD-001'): string
    {
        $this->client->request('POST', '/api/products', [], [], ['CONTENT_TYPE' => 'application/json'], json_encode([
            'name' => $name,
            'sku' => $sku,
            'stock' => $stock,
            'price' => 99900,
        ]));

        return $this->responseData()['id'];
    }

    private function placeOrder(string $productId, int $quantity): string
    {
        $this->client->request('POST', '/api/orders', [], [], ['CONTENT_TYPE' => 'application/json'], json_encode([
            'customer_email' => 'customer@example.com',
            'items' => [
                ['product_id' => $productId, 'quantity' => $quantity, 'unit_price' => 99900],
            ],
        ]));

        return $this->responseData()['id'];
    }

    /** @return array<string, mixed> */
    private function responseData(): array
    {
        $response = json_decode($this->client->getResponse()->getContent(), true);

        return $response['data'];
    }
}
