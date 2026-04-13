<?php

declare(strict_types=1);

namespace App\Infrastructure\Persistence\Dbal;

use App\Domain\Project\ProjectId;
use App\Domain\Task\Port\TaskRepository;
use App\Domain\Task\Priority;
use App\Domain\Task\Task;
use App\Domain\Task\TaskId;
use App\Domain\Task\TaskStatus;
use App\Domain\Task\ValueObject\Assignee;
use App\Domain\Task\ValueObject\TaskDescription;
use App\Domain\Task\ValueObject\TaskTitle;
use Doctrine\DBAL\Connection;

final readonly class DbalTaskRepository implements TaskRepository
{
    public function __construct(private Connection $connection) {}

    public function find(TaskId $id): ?Task
    {
        $row = $this->connection->fetchAssociative(
            'SELECT * FROM tasks WHERE id = ?',
            [$id->value()],
        );

        return $row !== false ? $this->toDomain($row) : null;
    }

    public function save(Task $task): void
    {
        $exists = $this->connection->fetchOne(
            'SELECT 1 FROM tasks WHERE id = ?',
            [$task->identity()->value()],
        );

        $now = (new \DateTimeImmutable())->format('Y-m-d H:i:s');

        if ($exists !== false) {
            $this->connection->update('tasks', [
                'project_id' => $task->projectId()->value(),
                'title' => $task->title()->value(),
                'description' => $task->description()?->value(),
                'status' => $task->status()->value,
                'priority' => $task->priority()->value,
                'assignee' => $task->assignee()?->value(),
                'updated_at' => $now,
            ], ['id' => $task->identity()->value()]);
        } else {
            $this->connection->insert('tasks', [
                'id' => $task->identity()->value(),
                'project_id' => $task->projectId()->value(),
                'title' => $task->title()->value(),
                'description' => $task->description()?->value(),
                'status' => $task->status()->value,
                'priority' => $task->priority()->value,
                'assignee' => $task->assignee()?->value(),
                'created_at' => $now,
                'updated_at' => $now,
            ]);
        }
    }

    public function delete(TaskId $id): void
    {
        $this->connection->delete('tasks', ['id' => $id->value()]);
    }

    /** @return list<Task> */
    public function findByProject(ProjectId $projectId): array
    {
        $rows = $this->connection->fetchAllAssociative(
            'SELECT * FROM tasks WHERE project_id = ?',
            [$projectId->value()],
        );

        return array_map($this->toDomain(...), $rows);
    }

    /** @return list<Task> */
    public function findByStatus(TaskStatus $status): array
    {
        $rows = $this->connection->fetchAllAssociative(
            'SELECT * FROM tasks WHERE status = ?',
            [$status->value],
        );

        return array_map($this->toDomain(...), $rows);
    }

    /** @return list<Task> */
    public function findByAssignee(string $assignee): array
    {
        $rows = $this->connection->fetchAllAssociative(
            'SELECT * FROM tasks WHERE assignee = ?',
            [$assignee],
        );

        return array_map($this->toDomain(...), $rows);
    }

    /** @return list<Task> */
    public function all(): array
    {
        $rows = $this->connection->fetchAllAssociative('SELECT * FROM tasks');

        return array_map($this->toDomain(...), $rows);
    }

    /** @param array<string, mixed> $row */
    private function toDomain(array $row): Task
    {
        return Task::reconstitute(
            id: new TaskId($row['id']),
            projectId: new ProjectId($row['project_id']),
            title: TaskTitle::from($row['title']),
            description: $row['description'] !== null ? TaskDescription::from($row['description']) : null,
            priority: Priority::from($row['priority']),
            status: TaskStatus::from($row['status']),
            assignee: $row['assignee'] !== null ? Assignee::from($row['assignee']) : null,
        );
    }
}
