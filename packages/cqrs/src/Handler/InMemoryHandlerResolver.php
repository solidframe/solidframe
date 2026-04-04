<?php

declare(strict_types=1);

namespace SolidFrame\Cqrs\Handler;

use SolidFrame\Cqrs\Exception\HandlerNotFoundException;

final class InMemoryHandlerResolver implements HandlerResolverInterface
{
    /** @var array<string, callable> */
    private array $handlers = [];

    /**
     * @param class-string $messageClass
     */
    public function register(string $messageClass, callable $handler): self
    {
        $this->handlers[$messageClass] = $handler;

        return $this;
    }

    public function resolve(object $message): callable
    {
        return $this->handlers[$message::class]
            ?? throw HandlerNotFoundException::forMessage($message);
    }
}
