<?php

declare(strict_types=1);

namespace SolidFrame\Core\Bus;

interface QueryBusInterface
{
    public function ask(object $query): mixed;
}
