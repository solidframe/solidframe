<?php

declare(strict_types=1);

namespace SolidFrame\EventDriven\Bus;

use SolidFrame\Core\Bus\EventBusInterface;
use SolidFrame\Core\Middleware\MiddlewareInterface;
use SolidFrame\EventDriven\Listener\ListenerResolverInterface;

final readonly class EventBus implements EventBusInterface
{
    /** @param list<MiddlewareInterface> $middleware */
    public function __construct(private ListenerResolverInterface $resolver, private array $middleware = []) {}

    public function dispatch(object $event): void
    {
        $dispatchToListeners = function (object $event): void {
            foreach ($this->resolver->resolve($event) as $listener) {
                $listener($event);
            }
        };

        $chain = array_reduce(
            array_reverse($this->middleware),
            static fn(callable $next, MiddlewareInterface $mw): callable => static fn(object $msg): mixed => $mw->handle($msg, $next),
            $dispatchToListeners,
        );

        $chain($event);
    }
}
