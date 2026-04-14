<?php

declare(strict_types=1);

namespace App\Controller;

use App\Modules\Payment\Application\Query\GetPayment\GetPayment;
use SolidFrame\Core\Bus\QueryBusInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Attribute\Route;

final readonly class PaymentController
{
    public function __construct(
        private QueryBusInterface $queryBus,
    ) {}

    #[Route('/api/payments/{orderId}', methods: ['GET'])]
    public function show(string $orderId): JsonResponse
    {
        $payment = $this->queryBus->ask(new GetPayment($orderId));

        return new JsonResponse(['data' => $payment]);
    }
}
