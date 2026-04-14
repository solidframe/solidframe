<?php

declare(strict_types=1);

namespace App\Tests\Functional;

use App\Infrastructure\Persistence\Dbal\SchemaManager;
use Doctrine\DBAL\Connection;
use PHPUnit\Framework\Attributes\Test;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

final class ProductApiTest extends WebTestCase
{
    private KernelBrowser $client;

    protected function setUp(): void
    {
        $this->client = self::createClient();

        /** @var Connection $connection */
        $connection = self::getContainer()->get(Connection::class);

        $tables = $connection->createSchemaManager()->listTableNames();
        if (in_array('products', $tables, true)) {
            $connection->executeStatement('DELETE FROM order_items');
            $connection->executeStatement('DELETE FROM payments');
            $connection->executeStatement('DELETE FROM orders');
            $connection->executeStatement('DELETE FROM products');
        } else {
            (new SchemaManager($connection))->createSchema();
        }
    }

    #[Test]
    public function addProduct(): void
    {
        $this->client->request('POST', '/api/products', [], [], ['CONTENT_TYPE' => 'application/json'], json_encode([
            'name' => 'Laptop',
            'sku' => 'LAP-001',
            'stock' => 10,
            'price' => 99900,
        ]));

        self::assertResponseStatusCodeSame(201);

        $data = $this->responseData();
        self::assertSame('Laptop', $data['name']);
        self::assertSame('LAP-001', $data['sku']);
        self::assertSame(10, $data['stock']);
        self::assertSame(99900, $data['price']);
    }

    #[Test]
    public function addProductValidation(): void
    {
        $this->client->request('POST', '/api/products', [], [], ['CONTENT_TYPE' => 'application/json'], json_encode([]));

        self::assertResponseStatusCodeSame(422);
    }

    #[Test]
    public function listProducts(): void
    {
        $this->createProduct('Laptop', 'LAP-001');
        $this->createProduct('Mouse', 'MOU-001');

        $this->client->request('GET', '/api/products');

        self::assertResponseStatusCodeSame(200);

        $response = json_decode($this->client->getResponse()->getContent(), true);
        self::assertCount(2, $response['data']);
    }

    #[Test]
    public function showProduct(): void
    {
        $id = $this->createProduct('Laptop', 'LAP-001');

        $this->client->request('GET', "/api/products/{$id}");

        self::assertResponseStatusCodeSame(200);

        $data = $this->responseData();
        self::assertSame('Laptop', $data['name']);
    }

    private function createProduct(string $name, string $sku): string
    {
        $this->client->request('POST', '/api/products', [], [], ['CONTENT_TYPE' => 'application/json'], json_encode([
            'name' => $name,
            'sku' => $sku,
            'stock' => 10,
            'price' => 99900,
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
