<?php

declare(strict_types=1);

namespace Modules\Order\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Support\Str;
use Modules\Order\Application\Command\CreateOrder\CreateOrder;
use Modules\Order\Application\Query\GetOrder\GetOrder;
use Modules\Order\Application\Query\ListOrders\ListOrders;
use Modules\Order\Http\Requests\CreateOrderRequest;
use SolidFrame\Core\Bus\CommandBusInterface;
use SolidFrame\Core\Bus\QueryBusInterface;

final readonly class OrderController
{
    public function __construct(
        private CommandBusInterface $commandBus,
        private QueryBusInterface $queryBus,
    ) {
    }

    public function index(): JsonResponse
    {
        $orders = $this->queryBus->ask(new ListOrders());

        return new JsonResponse(['data' => $orders]);
    }

    public function store(CreateOrderRequest $request): JsonResponse
    {
        $orderId = Str::uuid()->toString();

        $this->commandBus->dispatch(new CreateOrder(
            orderId: $orderId,
            customerEmail: $request->validated('customer_email'),
            items: $request->validated('items'),
        ));

        $order = $this->queryBus->ask(new GetOrder($orderId));

        return new JsonResponse(['data' => $order], 201);
    }

    public function show(string $id): JsonResponse
    {
        $order = $this->queryBus->ask(new GetOrder($id));

        return new JsonResponse(['data' => $order]);
    }
}
