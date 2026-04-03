<?php

declare(strict_types=1);

namespace SolidFrame\Core\Bus;

interface EventBusInterface
{
    public function dispatch(object $event): void;
}
