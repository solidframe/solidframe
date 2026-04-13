<?php

declare(strict_types=1);

namespace App\EventSubscriber;

use App\Domain\Project\Exception\ProjectAlreadyArchivedException;
use App\Domain\Project\Exception\ProjectNotFoundException;
use App\Domain\Task\Exception\TaskAlreadyCompletedException;
use App\Domain\Task\Exception\TaskNotFoundException;
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
            $exception instanceof ProjectNotFoundException,
            $exception instanceof TaskNotFoundException => new JsonResponse(
                ['error' => $exception->getMessage()],
                Response::HTTP_NOT_FOUND,
            ),
            $exception instanceof ProjectAlreadyArchivedException,
            $exception instanceof TaskAlreadyCompletedException => new JsonResponse(
                ['error' => $exception->getMessage()],
                Response::HTTP_CONFLICT,
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
