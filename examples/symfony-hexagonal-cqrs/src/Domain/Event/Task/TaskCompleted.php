<?php

declare(strict_types=1);

namespace App\Domain\Event\Task;

use SolidFrame\Core\Event\DomainEventInterface;

final readonly class TaskCompleted implements DomainEventInterface
{
    public function __construct(
        public string $taskId,
        public string $projectId,
        private \DateTimeImmutable $occurredAt = new \DateTimeImmutable(),
    ) {
    }

    public function eventName(): string
    {
        return 'task.completed';
    }

    public function occurredAt(): \DateTimeImmutable
    {
        return $this->occurredAt;
    }
}
