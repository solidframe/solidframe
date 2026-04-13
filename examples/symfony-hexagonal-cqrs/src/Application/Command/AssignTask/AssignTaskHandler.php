<?php

declare(strict_types=1);

namespace App\Application\Command\AssignTask;

use App\Domain\Task\Exception\TaskNotFoundException;
use App\Domain\Task\Port\TaskRepository;
use App\Domain\Task\TaskId;
use App\Domain\Task\ValueObject\Assignee;
use SolidFrame\Core\Bus\EventBusInterface;
use SolidFrame\Cqrs\CommandHandler;

final readonly class AssignTaskHandler implements CommandHandler
{
    public function __construct(
        private TaskRepository $tasks,
        private EventBusInterface $eventBus,
    ) {
    }

    public function __invoke(AssignTask $command): void
    {
        $task = $this->tasks->find(new TaskId($command->taskId))
            ?? throw TaskNotFoundException::forId($command->taskId);

        $task->assign(Assignee::from($command->assignee));

        $this->tasks->save($task);

        foreach ($task->releaseEvents() as $event) {
            $this->eventBus->dispatch($event);
        }
    }
}
