<?php

declare(strict_types=1);

namespace App\Application\Command\CreateProject;

use App\Domain\Project\Port\ProjectRepository;
use App\Domain\Project\Project;
use App\Domain\Project\ProjectId;
use App\Domain\Project\ValueObject\ProjectName;
use SolidFrame\Cqrs\CommandHandler;

final readonly class CreateProjectHandler implements CommandHandler
{
    public function __construct(private ProjectRepository $projects)
    {
    }

    public function __invoke(CreateProject $command): void
    {
        $project = Project::create(
            id: new ProjectId($command->projectId),
            name: ProjectName::from($command->name),
            description: $command->description,
        );

        $this->projects->save($project);
    }
}
