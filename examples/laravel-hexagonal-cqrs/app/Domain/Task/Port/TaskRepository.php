<?php

declare(strict_types=1);

namespace App\Domain\Task\Port;

use App\Domain\Project\ProjectId;
use App\Domain\Task\Task;
use App\Domain\Task\TaskId;
use App\Domain\Task\TaskStatus;

interface TaskRepository
{
    public function find(TaskId $id): ?Task;

    public function save(Task $task): void;

    public function delete(TaskId $id): void;

    /** @return list<Task> */
    public function findByProject(ProjectId $projectId): array;

    /** @return list<Task> */
    public function findByStatus(TaskStatus $status): array;

    /** @return list<Task> */
    public function findByAssignee(string $assignee): array;

    /** @return list<Task> */
    public function all(): array;
}
