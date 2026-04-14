<?php

declare(strict_types=1);

namespace Tests\Feature\Order;

use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

final class OrderApiTest extends TestCase
{
    use RefreshDatabase;

    private string $productId;

    protected function setUp(): void
    {
        parent::setUp();

        $response = $this->postJson('/api/products', [
            'name' => 'Laptop',
            'sku' => 'LAP-001',
            'stock' => 100,
            'price' => 99900,
        ]);
        $this->productId = $response->json('data.id');
    }

    #[Test]
    public function createOrder(): void
    {
        $response = $this->postJson('/api/orders', [
            'customer_email' => 'customer@example.com',
            'items' => [
                ['product_id' => $this->productId, 'quantity' => 2, 'unit_price' => 99900],
            ],
        ]);

        $response->assertStatus(201)
            ->assertJsonPath('data.customer_email', 'customer@example.com')
            ->assertJsonPath('data.total_amount', 199800)
            ->assertJsonPath('data.status', 'confirmed');
    }

    #[Test]
    public function createOrderValidation(): void
    {
        $response = $this->postJson('/api/orders', []);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['customer_email', 'items']);
    }

    #[Test]
    public function createOrderInvalidEmail(): void
    {
        $response = $this->postJson('/api/orders', [
            'customer_email' => 'not-an-email',
            'items' => [
                ['product_id' => $this->productId, 'quantity' => 1, 'unit_price' => 99900],
            ],
        ]);

        $response->assertStatus(422);
    }

    #[Test]
    public function listOrders(): void
    {
        $this->postJson('/api/orders', [
            'customer_email' => 'a@example.com',
            'items' => [['product_id' => $this->productId, 'quantity' => 1, 'unit_price' => 99900]],
        ]);
        $this->postJson('/api/orders', [
            'customer_email' => 'b@example.com',
            'items' => [['product_id' => $this->productId, 'quantity' => 1, 'unit_price' => 99900]],
        ]);

        $response = $this->getJson('/api/orders');

        $response->assertStatus(200)
            ->assertJsonCount(2, 'data');
    }

    #[Test]
    public function showOrder(): void
    {
        $created = $this->postJson('/api/orders', [
            'customer_email' => 'customer@example.com',
            'items' => [['product_id' => $this->productId, 'quantity' => 1, 'unit_price' => 99900]],
        ]);

        $id = $created->json('data.id');

        $response = $this->getJson("/api/orders/{$id}");

        $response->assertStatus(200)
            ->assertJsonPath('data.customer_email', 'customer@example.com');
    }

    #[Test]
    public function createOrderReducesStock(): void
    {
        $this->postJson('/api/orders', [
            'customer_email' => 'customer@example.com',
            'items' => [['product_id' => $this->productId, 'quantity' => 3, 'unit_price' => 99900]],
        ]);

        $product = $this->getJson("/api/products/{$this->productId}");
        $product->assertJsonPath('data.stock', 97);
    }

    #[Test]
    public function createOrderCreatesPayment(): void
    {
        $orderResponse = $this->postJson('/api/orders', [
            'customer_email' => 'customer@example.com',
            'items' => [['product_id' => $this->productId, 'quantity' => 1, 'unit_price' => 99900]],
        ]);

        $orderId = $orderResponse->json('data.id');

        $payment = $this->getJson("/api/payments/{$orderId}");
        $payment->assertStatus(200)
            ->assertJsonPath('data.amount', 99900)
            ->assertJsonPath('data.status', 'charged');
    }
}
