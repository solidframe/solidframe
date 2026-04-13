<?php

declare(strict_types=1);

namespace App\Infrastructure\Persistence\Eloquent;

use App\Domain\Project\Port\ProjectRepository;
use App\Domain\Project\Project;
use App\Domain\Project\ProjectId;
use App\Domain\Project\ProjectStatus;
use App\Domain\Project\ValueObject\ProjectName;

final readonly class EloquentProjectRepository implements ProjectRepository
{
    public function find(ProjectId $id): ?Project
    {
        $model = ProjectModel::find($id->value());

        return $model ? $this->toDomain($model) : null;
    }

    public function save(Project $project): void
    {
        ProjectModel::updateOrCreate(
            ['id' => $project->identity()->value()],
            [
                'name' => $project->name()->value(),
                'description' => $project->description(),
                'status' => $project->status()->value,
            ],
        );
    }

    /** @return list<Project> */
    public function all(): array
    {
        return ProjectModel::all()
            ->map(fn (ProjectModel $model) => $this->toDomain($model))
            ->values()
            ->all();
    }

    private function toDomain(ProjectModel $model): Project
    {
        return Project::reconstitute(
            id: new ProjectId($model->id),
            name: ProjectName::from($model->name),
            description: $model->description,
            status: ProjectStatus::from($model->status),
        );
    }
}
