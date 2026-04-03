<?php

declare(strict_types=1);

namespace SolidFrame\Core\Bus;

interface CommandBusInterface
{
    public function dispatch(object $command): void;
}
