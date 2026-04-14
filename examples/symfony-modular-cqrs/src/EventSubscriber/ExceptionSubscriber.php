<?php

declare(strict_types=1);

namespace App\EventSubscriber;

use App\Modules\Inventory\Domain\Exception\InsufficientStockException;
use App\Modules\Inventory\Domain\Exception\ProductNotFoundException;
use App\Modules\Order\Domain\Exception\OrderAlreadyCancelledException;
use App\Modules\Order\Domain\Exception\OrderAlreadyConfirmedException;
use App\Modules\Order\Domain\Exception\OrderNotFoundException;
use App\Modules\Payment\Domain\Exception\PaymentAlreadyChargedException;
use App\Modules\Payment\Domain\Exception\PaymentNotFoundException;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Event\ExceptionEvent;
use Symfony\Component\HttpKernel\Exception\UnprocessableEntityHttpException;
use Symfony\Component\HttpKernel\KernelEvents;

final class ExceptionSubscriber implements EventSubscriberInterface
{
    public static function getSubscribedEvents(): array
    {
        return [
            KernelEvents::EXCEPTION => 'onKernelException',
        ];
    }

    public function onKernelException(ExceptionEvent $event): void
    {
        $exception = $event->getThrowable();

        $response = match (true) {
            $exception instanceof OrderNotFoundException,
            $exception instanceof ProductNotFoundException,
            $exception instanceof PaymentNotFoundException => new JsonResponse(
                ['error' => $exception->getMessage()],
                Response::HTTP_NOT_FOUND,
            ),
            $exception instanceof OrderAlreadyConfirmedException,
            $exception instanceof OrderAlreadyCancelledException,
            $exception instanceof PaymentAlreadyChargedException => new JsonResponse(
                ['error' => $exception->getMessage()],
                Response::HTTP_CONFLICT,
            ),
            $exception instanceof InsufficientStockException => new JsonResponse(
                ['error' => $exception->getMessage()],
                Response::HTTP_UNPROCESSABLE_ENTITY,
            ),
            $exception instanceof UnprocessableEntityHttpException => new JsonResponse(
                json_decode($exception->getMessage(), true) ?? ['error' => $exception->getMessage()],
                Response::HTTP_UNPROCESSABLE_ENTITY,
            ),
            $exception instanceof \InvalidArgumentException => new JsonResponse(
                ['error' => $exception->getMessage()],
                Response::HTTP_UNPROCESSABLE_ENTITY,
            ),
            default => null,
        };

        if ($response !== null) {
            $event->setResponse($response);
        }
    }
}
