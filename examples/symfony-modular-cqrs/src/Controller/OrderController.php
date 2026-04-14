<?php

declare(strict_types=1);

namespace App\Controller;

use App\Http\RequestValidator;
use App\Modules\Order\Application\Command\CreateOrder\CreateOrder;
use App\Modules\Order\Application\Query\GetOrder\GetOrder;
use App\Modules\Order\Application\Query\ListOrders\ListOrders;
use App\Modules\Order\Domain\OrderId;
use SolidFrame\Core\Bus\CommandBusInterface;
use SolidFrame\Core\Bus\QueryBusInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Validator\Constraints as Assert;

#[Route('/api/orders')]
final readonly class OrderController
{
    public function __construct(
        private CommandBusInterface $commandBus,
        private QueryBusInterface $queryBus,
        private RequestValidator $requestValidator,
    ) {}

    #[Route('', methods: ['GET'])]
    public function index(): JsonResponse
    {
        $orders = $this->queryBus->ask(new ListOrders());

        return new JsonResponse(['data' => $orders]);
    }

    #[Route('', methods: ['POST'])]
    public function store(Request $request): JsonResponse
    {
        $data = $this->requestValidator->validate($request, new Assert\Collection([
            'customer_email' => [new Assert\NotBlank(), new Assert\Email()],
            'items' => new Assert\Required([
                new Assert\Type('array'),
                new Assert\Count(min: 1),
                new Assert\All([
                    new Assert\Collection([
                        'product_id' => [new Assert\NotBlank(), new Assert\Uuid()],
                        'quantity' => [new Assert\NotBlank(), new Assert\Type('integer'), new Assert\Positive()],
                        'unit_price' => [new Assert\NotBlank(), new Assert\Type('integer'), new Assert\Positive()],
                    ]),
                ]),
            ]),
        ]));

        $orderId = OrderId::generate()->value();

        $this->commandBus->dispatch(new CreateOrder(
            orderId: $orderId,
            customerEmail: $data['customer_email'],
            items: $data['items'],
        ));

        $order = $this->queryBus->ask(new GetOrder($orderId));

        return new JsonResponse(['data' => $order], Response::HTTP_CREATED);
    }

    #[Route('/{id}', methods: ['GET'])]
    public function show(string $id): JsonResponse
    {
        $order = $this->queryBus->ask(new GetOrder($id));

        return new JsonResponse(['data' => $order]);
    }
}
