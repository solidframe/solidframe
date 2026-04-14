<?php

declare(strict_types=1);

namespace Tests\Feature\Order;

use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

final class SagaCompensationTest extends TestCase
{
    use RefreshDatabase;

    #[Test]
    public function orderFailsWhenInsufficientStock(): void
    {
        $product = $this->postJson('/api/products', [
            'name' => 'Laptop',
            'sku' => 'LAP-001',
            'stock' => 2,
            'price' => 99900,
        ]);
        $productId = $product->json('data.id');

        $response = $this->postJson('/api/orders', [
            'customer_email' => 'customer@example.com',
            'items' => [['product_id' => $productId, 'quantity' => 5, 'unit_price' => 99900]],
        ]);

        $orderId = $response->json('data.id');

        $order = $this->getJson("/api/orders/{$orderId}");
        $order->assertJsonPath('data.status', 'cancelled');
    }

    #[Test]
    public function stockRestoredWhenOrderCancelledDueToInsufficientStock(): void
    {
        $product = $this->postJson('/api/products', [
            'name' => 'Laptop',
            'sku' => 'LAP-001',
            'stock' => 2,
            'price' => 99900,
        ]);
        $productId = $product->json('data.id');

        $this->postJson('/api/orders', [
            'customer_email' => 'customer@example.com',
            'items' => [['product_id' => $productId, 'quantity' => 5, 'unit_price' => 99900]],
        ]);

        $productAfter = $this->getJson("/api/products/{$productId}");
        $productAfter->assertJsonPath('data.stock', 2);
    }

    #[Test]
    public function happyPathSagaCompletesSuccessfully(): void
    {
        $product = $this->postJson('/api/products', [
            'name' => 'Mouse',
            'sku' => 'MOU-001',
            'stock' => 50,
            'price' => 2990,
        ]);
        $productId = $product->json('data.id');

        $order = $this->postJson('/api/orders', [
            'customer_email' => 'customer@example.com',
            'items' => [['product_id' => $productId, 'quantity' => 3, 'unit_price' => 2990]],
        ]);

        $order->assertStatus(201)
            ->assertJsonPath('data.status', 'confirmed')
            ->assertJsonPath('data.total_amount', 8970);

        $productAfter = $this->getJson("/api/products/{$productId}");
        $productAfter->assertJsonPath('data.stock', 47);
    }

    #[Test]
    public function multipleItemsOrderWithSufficientStock(): void
    {
        $laptop = $this->postJson('/api/products', [
            'name' => 'Laptop', 'sku' => 'LAP-001', 'stock' => 10, 'price' => 99900,
        ]);
        $mouse = $this->postJson('/api/products', [
            'name' => 'Mouse', 'sku' => 'MOU-001', 'stock' => 50, 'price' => 2990,
        ]);

        $order = $this->postJson('/api/orders', [
            'customer_email' => 'customer@example.com',
            'items' => [
                ['product_id' => $laptop->json('data.id'), 'quantity' => 1, 'unit_price' => 99900],
                ['product_id' => $mouse->json('data.id'), 'quantity' => 2, 'unit_price' => 2990],
            ],
        ]);

        $order->assertJsonPath('data.status', 'confirmed')
            ->assertJsonPath('data.total_amount', 105880);
    }
}
