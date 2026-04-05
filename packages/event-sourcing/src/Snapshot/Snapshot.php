<?php

declare(strict_types=1);

namespace SolidFrame\EventSourcing\Snapshot;

final readonly class Snapshot
{
    public function __construct(
        public string $aggregateId,
        public string $aggregateType,
        public int $version,
        public mixed $state,
    ) {}
}
