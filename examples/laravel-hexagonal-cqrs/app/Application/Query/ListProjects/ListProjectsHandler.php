<?php

declare(strict_types=1);

namespace App\Application\Query\ListProjects;

use App\Domain\Project\Port\ProjectRepository;
use SolidFrame\Cqrs\QueryHandler;

final readonly class ListProjectsHandler implements QueryHandler
{
    public function __construct(private ProjectRepository $projects)
    {
    }

    /** @return list<\App\Domain\Project\Project> */
    public function __invoke(ListProjects $query): array
    {
        return $this->projects->all();
    }
}
