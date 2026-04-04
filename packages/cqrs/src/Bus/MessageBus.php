<?php

declare(strict_types=1);

namespace SolidFrame\Cqrs\Bus;

use SolidFrame\Core\Middleware\MiddlewareInterface;
use SolidFrame\Cqrs\Handler\HandlerResolverInterface;

abstract class MessageBus
{
    /**
     * @param list<MiddlewareInterface> $middleware
     */
    public function __construct(private readonly HandlerResolverInterface $resolver, private readonly array $middleware = []) {}

    protected function handleMessage(object $message): mixed
    {
        $handler = $this->resolver->resolve($message);

        $chain = array_reduce(
            array_reverse($this->middleware),
            static fn(callable $next, MiddlewareInterface $mw): callable => static fn(object $msg): mixed => $mw->handle($msg, $next),
            $handler,
        );

        return $chain($message);
    }
}
