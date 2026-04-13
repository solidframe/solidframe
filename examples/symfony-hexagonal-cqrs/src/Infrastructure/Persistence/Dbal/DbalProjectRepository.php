<?php

declare(strict_types=1);

namespace App\Infrastructure\Persistence\Dbal;

use App\Domain\Project\Port\ProjectRepository;
use App\Domain\Project\Project;
use App\Domain\Project\ProjectId;
use App\Domain\Project\ProjectStatus;
use App\Domain\Project\ValueObject\ProjectName;
use Doctrine\DBAL\Connection;

final readonly class DbalProjectRepository implements ProjectRepository
{
    public function __construct(private Connection $connection) {}

    public function find(ProjectId $id): ?Project
    {
        $row = $this->connection->fetchAssociative(
            'SELECT * FROM projects WHERE id = ?',
            [$id->value()],
        );

        return $row !== false ? $this->toDomain($row) : null;
    }

    public function save(Project $project): void
    {
        $exists = $this->connection->fetchOne(
            'SELECT 1 FROM projects WHERE id = ?',
            [$project->identity()->value()],
        );

        $now = (new \DateTimeImmutable())->format('Y-m-d H:i:s');

        if ($exists !== false) {
            $this->connection->update('projects', [
                'name' => $project->name()->value(),
                'description' => $project->description(),
                'status' => $project->status()->value,
                'updated_at' => $now,
            ], ['id' => $project->identity()->value()]);
        } else {
            $this->connection->insert('projects', [
                'id' => $project->identity()->value(),
                'name' => $project->name()->value(),
                'description' => $project->description(),
                'status' => $project->status()->value,
                'created_at' => $now,
                'updated_at' => $now,
            ]);
        }
    }

    /** @return list<Project> */
    public function all(): array
    {
        $rows = $this->connection->fetchAllAssociative('SELECT * FROM projects');

        return array_map($this->toDomain(...), $rows);
    }

    /** @param array<string, mixed> $row */
    private function toDomain(array $row): Project
    {
        return Project::reconstitute(
            id: new ProjectId($row['id']),
            name: ProjectName::from($row['name']),
            description: $row['description'],
            status: ProjectStatus::from($row['status']),
        );
    }
}
