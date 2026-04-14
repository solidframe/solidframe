<?php

declare(strict_types=1);

namespace Modules\Payment\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Modules\Payment\Application\Query\GetPayment\GetPayment;
use SolidFrame\Core\Bus\QueryBusInterface;

final readonly class PaymentController
{
    public function __construct(
        private QueryBusInterface $queryBus,
    ) {
    }

    public function show(string $orderId): JsonResponse
    {
        $payment = $this->queryBus->ask(new GetPayment($orderId));

        return new JsonResponse(['data' => $payment]);
    }
}
