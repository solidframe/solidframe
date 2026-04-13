<?php

declare(strict_types=1);

namespace App\Infrastructure\Persistence\Eloquent;

use App\Domain\Project\ProjectId;
use App\Domain\Task\Port\TaskRepository;
use App\Domain\Task\Priority;
use App\Domain\Task\Task;
use App\Domain\Task\TaskId;
use App\Domain\Task\TaskStatus;
use App\Domain\Task\ValueObject\Assignee;
use App\Domain\Task\ValueObject\TaskDescription;
use App\Domain\Task\ValueObject\TaskTitle;

final readonly class EloquentTaskRepository implements TaskRepository
{
    public function find(TaskId $id): ?Task
    {
        $model = TaskModel::find($id->value());

        return $model ? $this->toDomain($model) : null;
    }

    public function save(Task $task): void
    {
        TaskModel::updateOrCreate(
            ['id' => $task->identity()->value()],
            [
                'project_id' => $task->projectId()->value(),
                'title' => $task->title()->value(),
                'description' => $task->description()?->value(),
                'status' => $task->status()->value,
                'priority' => $task->priority()->value,
                'assignee' => $task->assignee()?->value(),
            ],
        );
    }

    public function delete(TaskId $id): void
    {
        TaskModel::where('id', $id->value())->delete();
    }

    /** @return list<Task> */
    public function findByProject(ProjectId $projectId): array
    {
        return TaskModel::where('project_id', $projectId->value())
            ->get()
            ->map(fn (TaskModel $model) => $this->toDomain($model))
            ->values()
            ->all();
    }

    /** @return list<Task> */
    public function findByStatus(TaskStatus $status): array
    {
        return TaskModel::where('status', $status->value)
            ->get()
            ->map(fn (TaskModel $model) => $this->toDomain($model))
            ->values()
            ->all();
    }

    /** @return list<Task> */
    public function findByAssignee(string $assignee): array
    {
        return TaskModel::where('assignee', $assignee)
            ->get()
            ->map(fn (TaskModel $model) => $this->toDomain($model))
            ->values()
            ->all();
    }

    /** @return list<Task> */
    public function all(): array
    {
        return TaskModel::all()
            ->map(fn (TaskModel $model) => $this->toDomain($model))
            ->values()
            ->all();
    }

    private function toDomain(TaskModel $model): Task
    {
        return Task::reconstitute(
            id: new TaskId($model->id),
            projectId: new ProjectId($model->project_id),
            title: TaskTitle::from($model->title),
            description: $model->description !== null ? TaskDescription::from($model->description) : null,
            priority: Priority::from($model->priority),
            status: TaskStatus::from($model->status),
            assignee: $model->assignee !== null ? Assignee::from($model->assignee) : null,
        );
    }
}
