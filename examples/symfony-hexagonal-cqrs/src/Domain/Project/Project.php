<?php

declare(strict_types=1);

namespace App\Domain\Project;

use App\Domain\Project\Exception\ProjectAlreadyArchivedException;
use App\Domain\Project\ValueObject\ProjectName;
use SolidFrame\Ddd\Aggregate\AbstractAggregateRoot;

final class Project extends AbstractAggregateRoot
{
    private ProjectName $name;
    private ?string $description;
    private ProjectStatus $status;

    private function __construct(
        ProjectId $id,
        ProjectName $name,
        ?string $description,
    ) {
        parent::__construct($id);
        $this->name = $name;
        $this->description = $description;
        $this->status = ProjectStatus::Active;
    }

    public static function create(ProjectId $id, ProjectName $name, ?string $description = null): self
    {
        return new self($id, $name, $description);
    }

    public static function reconstitute(
        ProjectId $id,
        ProjectName $name,
        ?string $description,
        ProjectStatus $status,
    ): self {
        $project = new self($id, $name, $description);
        $project->status = $status;

        return $project;
    }

    public function archive(): void
    {
        ($this->status === ProjectStatus::Active)
            or throw ProjectAlreadyArchivedException::forId($this->identity()->value());

        $this->status = ProjectStatus::Archived;
    }

    public function name(): ProjectName
    {
        return $this->name;
    }

    public function description(): ?string
    {
        return $this->description;
    }

    public function status(): ProjectStatus
    {
        return $this->status;
    }
}
