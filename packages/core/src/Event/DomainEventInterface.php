<?php

declare(strict_types=1);

namespace SolidFrame\Core\Event;

use DateTimeImmutable;

interface DomainEventInterface
{
    public function eventName(): string;

    public function occurredAt(): DateTimeImmutable;
}
