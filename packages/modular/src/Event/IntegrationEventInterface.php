<?php

declare(strict_types=1);

namespace SolidFrame\Modular\Event;

use SolidFrame\Core\Event\DomainEventInterface;

interface IntegrationEventInterface extends DomainEventInterface
{
    public function sourceModule(): string;
}
