<?php

declare(strict_types=1);

namespace App\Application\Query\GetProject;

use App\Domain\Project\Exception\ProjectNotFoundException;
use App\Domain\Project\Port\ProjectRepository;
use App\Domain\Project\Project;
use App\Domain\Project\ProjectId;
use SolidFrame\Cqrs\QueryHandler;

final readonly class GetProjectHandler implements QueryHandler
{
    public function __construct(private ProjectRepository $projects)
    {
    }

    public function __invoke(GetProject $query): Project
    {
        return $this->projects->find(new ProjectId($query->projectId))
            ?? throw ProjectNotFoundException::forId($query->projectId);
    }
}
