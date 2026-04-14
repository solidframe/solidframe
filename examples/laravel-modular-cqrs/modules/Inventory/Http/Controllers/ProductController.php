<?php

declare(strict_types=1);

namespace Modules\Inventory\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Support\Str;
use Modules\Inventory\Application\Command\AddProduct\AddProduct;
use Modules\Inventory\Application\Query\GetProduct\GetProduct;
use Modules\Inventory\Application\Query\ListProducts\ListProducts;
use Modules\Inventory\Http\Requests\AddProductRequest;
use SolidFrame\Core\Bus\CommandBusInterface;
use SolidFrame\Core\Bus\QueryBusInterface;

final readonly class ProductController
{
    public function __construct(
        private CommandBusInterface $commandBus,
        private QueryBusInterface $queryBus,
    ) {
    }

    public function index(): JsonResponse
    {
        $products = $this->queryBus->ask(new ListProducts());

        return new JsonResponse(['data' => $products]);
    }

    public function store(AddProductRequest $request): JsonResponse
    {
        $productId = Str::uuid()->toString();

        $this->commandBus->dispatch(new AddProduct(
            productId: $productId,
            name: $request->validated('name'),
            sku: $request->validated('sku'),
            stock: (int) $request->validated('stock'),
            price: (int) $request->validated('price'),
        ));

        $product = $this->queryBus->ask(new GetProduct($productId));

        return new JsonResponse(['data' => $product], 201);
    }

    public function show(string $id): JsonResponse
    {
        $product = $this->queryBus->ask(new GetProduct($id));

        return new JsonResponse(['data' => $product]);
    }
}
