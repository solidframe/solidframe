<?php

declare(strict_types=1);

namespace App\Application\Query\GetTask;

use App\Domain\Task\Exception\TaskNotFoundException;
use App\Domain\Task\Port\TaskRepository;
use App\Domain\Task\Task;
use App\Domain\Task\TaskId;
use SolidFrame\Cqrs\QueryHandler;

final readonly class GetTaskHandler implements QueryHandler
{
    public function __construct(private TaskRepository $tasks)
    {
    }

    public function __invoke(GetTask $query): Task
    {
        return $this->tasks->find(new TaskId($query->taskId))
            ?? throw TaskNotFoundException::forId($query->taskId);
    }
}
