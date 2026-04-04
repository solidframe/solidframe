<?php

declare(strict_types=1);

namespace SolidFrame\Cqrs\Bus;

use SolidFrame\Core\Bus\QueryBusInterface;

final class QueryBus extends MessageBus implements QueryBusInterface
{
    public function ask(object $query): mixed
    {
        return $this->handleMessage($query);
    }
}
