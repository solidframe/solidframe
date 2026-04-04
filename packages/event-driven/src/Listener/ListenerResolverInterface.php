<?php

declare(strict_types=1);

namespace SolidFrame\EventDriven\Listener;

interface ListenerResolverInterface
{
    /** @return list<callable> */
    public function resolve(object $event): array;
}
