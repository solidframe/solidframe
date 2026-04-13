<?php

declare(strict_types=1);

namespace App\Application\Command\ArchiveProject;

use App\Domain\Project\Exception\ProjectNotFoundException;
use App\Domain\Project\Port\ProjectRepository;
use App\Domain\Project\ProjectId;
use SolidFrame\Cqrs\CommandHandler;

final readonly class ArchiveProjectHandler implements CommandHandler
{
    public function __construct(private ProjectRepository $projects)
    {
    }

    public function __invoke(ArchiveProject $command): void
    {
        $project = $this->projects->find(new ProjectId($command->projectId))
            ?? throw ProjectNotFoundException::forId($command->projectId);

        $project->archive();

        $this->projects->save($project);
    }
}
