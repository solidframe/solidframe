<?php

declare(strict_types=1);

namespace SolidFrame\Cqrs\Bus;

use SolidFrame\Core\Bus\CommandBusInterface;

final class CommandBus extends MessageBus implements CommandBusInterface
{
    public function dispatch(object $command): void
    {
        $this->handleMessage($command);
    }
}
