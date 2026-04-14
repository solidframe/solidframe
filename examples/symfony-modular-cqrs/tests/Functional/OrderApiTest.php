<?php

declare(strict_types=1);

namespace App\Tests\Functional;

use App\Infrastructure\Persistence\Dbal\SchemaManager;
use Doctrine\DBAL\Connection;
use PHPUnit\Framework\Attributes\Test;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

final class OrderApiTest extends WebTestCase
{
    private KernelBrowser $client;
    private string $productId;

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

        $this->productId = $this->createProduct();
    }

    #[Test]
    public function createOrder(): void
    {
        $this->client->request('POST', '/api/orders', [], [], ['CONTENT_TYPE' => 'application/json'], json_encode([
            'customer_email' => 'customer@example.com',
            'items' => [
                ['product_id' => $this->productId, 'quantity' => 2, 'unit_price' => 99900],
            ],
        ]));

        self::assertResponseStatusCodeSame(201);

        $data = $this->responseData();
        self::assertSame('customer@example.com', $data['customer_email']);
        self::assertSame(199800, $data['total_amount']);
        self::assertSame('confirmed', $data['status']);
    }

    #[Test]
    public function createOrderValidation(): void
    {
        $this->client->request('POST', '/api/orders', [], [], ['CONTENT_TYPE' => 'application/json'], json_encode([]));

        self::assertResponseStatusCodeSame(422);
    }

    #[Test]
    public function createOrderInvalidEmail(): void
    {
        $this->client->request('POST', '/api/orders', [], [], ['CONTENT_TYPE' => 'application/json'], json_encode([
            'customer_email' => 'not-an-email',
            'items' => [
                ['product_id' => $this->productId, 'quantity' => 1, 'unit_price' => 99900],
            ],
        ]));

        self::assertResponseStatusCodeSame(422);
    }

    #[Test]
    public function listOrders(): void
    {
        $this->placeOrder('a@example.com');
        $this->placeOrder('b@example.com');

        $this->client->request('GET', '/api/orders');

        self::assertResponseStatusCodeSame(200);

        $response = json_decode($this->client->getResponse()->getContent(), true);
        self::assertCount(2, $response['data']);
    }

    #[Test]
    public function showOrder(): void
    {
        $orderId = $this->placeOrder('customer@example.com');

        $this->client->request('GET', "/api/orders/{$orderId}");

        self::assertResponseStatusCodeSame(200);

        $data = $this->responseData();
        self::assertSame('customer@example.com', $data['customer_email']);
    }

    #[Test]
    public function createOrderReducesStock(): void
    {
        $this->placeOrder('customer@example.com', 3);

        $this->client->request('GET', "/api/products/{$this->productId}");

        $data = $this->responseData();
        self::assertSame(97, $data['stock']);
    }

    #[Test]
    public function createOrderCreatesPayment(): void
    {
        $orderId = $this->placeOrder('customer@example.com');

        $this->client->request('GET', "/api/payments/{$orderId}");

        self::assertResponseStatusCodeSame(200);

        $data = $this->responseData();
        self::assertSame(99900, $data['amount']);
        self::assertSame('charged', $data['status']);
    }

    private function createProduct(): string
    {
        $this->client->request('POST', '/api/products', [], [], ['CONTENT_TYPE' => 'application/json'], json_encode([
            'name' => 'Laptop',
            'sku' => 'LAP-001',
            'stock' => 100,
            'price' => 99900,
        ]));

        return $this->responseData()['id'];
    }

    private function placeOrder(string $email, int $quantity = 1): string
    {
        $this->client->request('POST', '/api/orders', [], [], ['CONTENT_TYPE' => 'application/json'], json_encode([
            'customer_email' => $email,
            'items' => [
                ['product_id' => $this->productId, 'quantity' => $quantity, 'unit_price' => 99900],
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
