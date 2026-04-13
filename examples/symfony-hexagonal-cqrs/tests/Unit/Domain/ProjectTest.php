<?php

declare(strict_types=1);

namespace App\Tests\Unit\Domain;

use App\Domain\Project\Exception\ProjectAlreadyArchivedException;
use App\Domain\Project\Project;
use App\Domain\Project\ProjectId;
use App\Domain\Project\ProjectStatus;
use App\Domain\Project\ValueObject\ProjectName;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

final class ProjectTest extends TestCase
{
    #[Test]
    public function createProject(): void
    {
        $project = Project::create(
            id: ProjectId::generate(),
            name: ProjectName::from('My Project'),
            description: 'A test project',
        );

        self::assertSame('My Project', $project->name()->value());
        self::assertSame('A test project', $project->description());
        self::assertSame(ProjectStatus::Active, $project->status());
    }

    #[Test]
    public function archiveProject(): void
    {
        $project = Project::create(
            id: ProjectId::generate(),
            name: ProjectName::from('My Project'),
        );

        $project->archive();

        self::assertSame(ProjectStatus::Archived, $project->status());
    }

    #[Test]
    public function cannotArchiveAlreadyArchivedProject(): void
    {
        $project = Project::create(
            id: ProjectId::generate(),
            name: ProjectName::from('My Project'),
        );

        $project->archive();

        $this->expectException(ProjectAlreadyArchivedException::class);
        $project->archive();
    }
}
