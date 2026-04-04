<?php

declare(strict_types=1);

namespace SolidFrame\EventDriven\Listener;

final class InMemoryListenerResolver implements ListenerResolverInterface
{
    /** @var array<string, list<callable>> */
    private array $listeners = [];

    /**
     * @param class-string $eventClass
     */
    public function listen(string $eventClass, callable $listener): self
    {
        $this->listeners[$eventClass][] = $listener;

        return $this;
    }

    /** @return list<callable> */
    public function resolve(object $event): array
    {
        return $this->listeners[$event::class] ?? [];
    }
}
