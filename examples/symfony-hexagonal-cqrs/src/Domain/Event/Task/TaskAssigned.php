<?php

declare(strict_types=1);

namespace App\Domain\Event\Task;

use SolidFrame\Core\Event\DomainEventInterface;

final readonly class TaskAssigned implements DomainEventInterface
{
    public function __construct(
        public string $taskId,
        public string $assignee,
        private \DateTimeImmutable $occurredAt = new \DateTimeImmutable(),
    ) {
    }

    public function eventName(): string
    {
        return 'task.assigned';
    }

    public function occurredAt(): \DateTimeImmutable
    {
        return $this->occurredAt;
    }
}
