<?php

declare(strict_types=1);

namespace Tests\Feature\Inventory;

use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

final class ProductApiTest extends TestCase
{
    use RefreshDatabase;

    #[Test]
    public function addProduct(): void
    {
        $response = $this->postJson('/api/products', [
            'name' => 'Laptop',
            'sku' => 'LAP-001',
            'stock' => 10,
            'price' => 99900,
        ]);

        $response->assertStatus(201)
            ->assertJsonPath('data.name', 'Laptop')
            ->assertJsonPath('data.sku', 'LAP-001')
            ->assertJsonPath('data.stock', 10)
            ->assertJsonPath('data.price', 99900);
    }

    #[Test]
    public function addProductValidation(): void
    {
        $response = $this->postJson('/api/products', []);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['name', 'sku', 'stock', 'price']);
    }

    #[Test]
    public function listProducts(): void
    {
        $this->postJson('/api/products', ['name' => 'Laptop', 'sku' => 'LAP-001', 'stock' => 10, 'price' => 99900]);
        $this->postJson('/api/products', ['name' => 'Mouse', 'sku' => 'MOU-001', 'stock' => 50, 'price' => 2990]);

        $response = $this->getJson('/api/products');

        $response->assertStatus(200)
            ->assertJsonCount(2, 'data');
    }

    #[Test]
    public function showProduct(): void
    {
        $created = $this->postJson('/api/products', [
            'name' => 'Laptop',
            'sku' => 'LAP-001',
            'stock' => 10,
            'price' => 99900,
        ]);

        $id = $created->json('data.id');

        $response = $this->getJson("/api/products/{$id}");

        $response->assertStatus(200)
            ->assertJsonPath('data.name', 'Laptop');
    }
}
