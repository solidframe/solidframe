<?php

declare(strict_types=1);

namespace App\Application\Query\ListTasks;

use App\Domain\Project\ProjectId;
use App\Domain\Task\Port\TaskRepository;
use App\Domain\Task\Task;
use App\Domain\Task\TaskStatus;
use SolidFrame\Cqrs\QueryHandler;

final readonly class ListTasksHandler implements QueryHandler
{
    public function __construct(private TaskRepository $tasks)
    {
    }

    /** @return list<Task> */
    public function __invoke(ListTasks $query): array
    {
        if ($query->projectId !== null) {
            return $this->tasks->findByProject(new ProjectId($query->projectId));
        }

        if ($query->status !== null) {
            return $this->tasks->findByStatus(TaskStatus::from($query->status));
        }

        if ($query->assignee !== null) {
            return $this->tasks->findByAssignee($query->assignee);
        }

        return $this->tasks->all();
    }
}
