<?php

declare(strict_types=1);

namespace SolidFrame\Modular\Event;

use DateTimeImmutable;

abstract class AbstractIntegrationEvent implements IntegrationEventInterface
{
    private readonly DateTimeImmutable $occurredAt;

    public function __construct(
        private readonly string $sourceModule,
        ?DateTimeImmutable $occurredAt = null,
    ) {
        $this->occurredAt = $occurredAt ?? new DateTimeImmutable();
    }

    public function sourceModule(): string
    {
        return $this->sourceModule;
    }

    public function occurredAt(): DateTimeImmutable
    {
        return $this->occurredAt;
    }
}
