<?php

declare(strict_types=1);

namespace SolidFrame\Laravel\EventDriven;

use Illuminate\Contracts\Container\Container;
use SolidFrame\EventDriven\Listener\ListenerResolverInterface;

final readonly class ContainerListenerResolver implements ListenerResolverInterface
{
    /**
     * @param array<class-string, list<class-string>> $listeners event => listener classes mapping
     */
    public function __construct(private Container $container, private array $listeners = []) {}

    /** @return list<callable> */
    public function resolve(object $event): array
    {
        $listenerClasses = $this->listeners[$event::class] ?? [];

        return array_map(
            fn(string $class): callable => $this->container->make($class),
            $listenerClasses,
        );
    }
}
