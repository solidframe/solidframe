<?php

declare(strict_types=1);

namespace App\Domain\Project\Port;

use App\Domain\Project\Project;
use App\Domain\Project\ProjectId;

interface ProjectRepository
{
    public function find(ProjectId $id): ?Project;

    public function save(Project $project): void;

    /** @return list<Project> */
    public function all(): array;
}
