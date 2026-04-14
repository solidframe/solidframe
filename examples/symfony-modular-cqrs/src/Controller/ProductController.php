<?php

declare(strict_types=1);

namespace App\Controller;

use App\Http\RequestValidator;
use App\Modules\Inventory\Application\Command\AddProduct\AddProduct;
use App\Modules\Inventory\Application\Query\GetProduct\GetProduct;
use App\Modules\Inventory\Application\Query\ListProducts\ListProducts;
use App\Modules\Inventory\Domain\ProductId;
use SolidFrame\Core\Bus\CommandBusInterface;
use SolidFrame\Core\Bus\QueryBusInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Validator\Constraints as Assert;

#[Route('/api/products')]
final readonly class ProductController
{
    public function __construct(
        private CommandBusInterface $commandBus,
        private QueryBusInterface $queryBus,
        private RequestValidator $requestValidator,
    ) {}

    #[Route('', methods: ['GET'])]
    public function index(): JsonResponse
    {
        $products = $this->queryBus->ask(new ListProducts());

        return new JsonResponse(['data' => $products]);
    }

    #[Route('', methods: ['POST'])]
    public function store(Request $request): JsonResponse
    {
        $data = $this->requestValidator->validate($request, new Assert\Collection([
            'name' => [new Assert\NotBlank(), new Assert\Length(min: 1, max: 255)],
            'sku' => [new Assert\NotBlank(), new Assert\Length(min: 1, max: 100)],
            'stock' => [new Assert\NotBlank(), new Assert\Type('integer'), new Assert\PositiveOrZero()],
            'price' => [new Assert\NotBlank(), new Assert\Type('integer'), new Assert\Positive()],
        ]));

        $productId = ProductId::generate()->value();

        $this->commandBus->dispatch(new AddProduct(
            productId: $productId,
            name: $data['name'],
            sku: $data['sku'],
            stock: (int) $data['stock'],
            price: (int) $data['price'],
        ));

        $product = $this->queryBus->ask(new GetProduct($productId));

        return new JsonResponse(['data' => $product], Response::HTTP_CREATED);
    }

    #[Route('/{id}', methods: ['GET'])]
    public function show(string $id): JsonResponse
    {
        $product = $this->queryBus->ask(new GetProduct($id));

        return new JsonResponse(['data' => $product]);
    }
}
