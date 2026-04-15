<?php

declare(strict_types=1);

namespace App\EventSubscriber;

use App\Domain\Account\Exception\AccountNotFoundException;
use App\Domain\Account\Exception\InsufficientBalanceException;
use App\Domain\Account\Exception\InvalidAmountException;
use App\Domain\Account\Exception\SelfTransferException;
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
            $exception instanceof AccountNotFoundException => new JsonResponse(
                ['error' => $exception->getMessage()],
                Response::HTTP_NOT_FOUND,
            ),
            $exception instanceof InsufficientBalanceException,
            $exception instanceof SelfTransferException => new JsonResponse(
                ['error' => $exception->getMessage()],
                Response::HTTP_CONFLICT,
            ),
            $exception instanceof InvalidAmountException => new JsonResponse(
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
