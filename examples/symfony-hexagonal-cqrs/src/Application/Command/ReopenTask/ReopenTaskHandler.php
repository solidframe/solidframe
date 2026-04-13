<?php

declare(strict_types=1);

namespace App\Application\Command\ReopenTask;

use App\Domain\Task\Exception\TaskNotFoundException;
use App\Domain\Task\Port\TaskRepository;
use App\Domain\Task\TaskId;
use SolidFrame\Cqrs\CommandHandler;

final readonly class ReopenTaskHandler implements CommandHandler
{
    public function __construct(private TaskRepository $tasks)
    {
    }

    public function __invoke(ReopenTask $command): void
    {
        $task = $this->tasks->find(new TaskId($command->taskId))
            ?? throw TaskNotFoundException::forId($command->taskId);

        $task->reopen();

        $this->tasks->save($task);
    }
}
