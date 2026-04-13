<?php

declare(strict_types=1);

namespace Tests\Unit\Domain;

use App\Domain\Project\Exception\InvalidProjectName;
use App\Domain\Project\ValueObject\ProjectName;
use App\Domain\Task\Exception\InvalidTaskDescription;
use App\Domain\Task\Exception\InvalidTaskTitle;
use App\Domain\Task\ValueObject\Assignee;
use App\Domain\Task\ValueObject\TaskDescription;
use App\Domain\Task\ValueObject\TaskTitle;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

final class ValueObjectTest extends TestCase
{
    #[Test]
    public function projectNameCannotBeEmpty(): void
    {
        $this->expectException(InvalidProjectName::class);
        ProjectName::from('');
    }

    #[Test]
    public function projectNameCannotExceed100Characters(): void
    {
        $this->expectException(InvalidProjectName::class);
        ProjectName::from(str_repeat('a', 101));
    }

    #[Test]
    public function validProjectName(): void
    {
        $name = ProjectName::from('My Project');
        self::assertSame('My Project', $name->value());
    }

    #[Test]
    public function projectNameTrimsWhitespace(): void
    {
        $name = ProjectName::from('  My Project  ');
        self::assertSame('My Project', $name->value());
    }

    #[Test]
    public function taskTitleCannotBeEmpty(): void
    {
        $this->expectException(InvalidTaskTitle::class);
        TaskTitle::from('');
    }

    #[Test]
    public function taskTitleCannotExceed255Characters(): void
    {
        $this->expectException(InvalidTaskTitle::class);
        TaskTitle::from(str_repeat('a', 256));
    }

    #[Test]
    public function validTaskTitle(): void
    {
        $title = TaskTitle::from('Write tests');
        self::assertSame('Write tests', $title->value());
    }

    #[Test]
    public function taskDescriptionCannotExceed1000Characters(): void
    {
        $this->expectException(InvalidTaskDescription::class);
        TaskDescription::from(str_repeat('a', 1001));
    }

    #[Test]
    public function validTaskDescription(): void
    {
        $desc = TaskDescription::from('Some description');
        self::assertSame('Some description', $desc->value());
    }

    #[Test]
    public function assigneeCannotBeEmpty(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        Assignee::from('');
    }

    #[Test]
    public function validAssignee(): void
    {
        $assignee = Assignee::from('Kadir');
        self::assertSame('Kadir', $assignee->value());
    }

    #[Test]
    public function valueObjectEquality(): void
    {
        $name1 = ProjectName::from('Project');
        $name2 = ProjectName::from('Project');
        $name3 = ProjectName::from('Other');

        self::assertTrue($name1->equals($name2));
        self::assertFalse($name1->equals($name3));
    }
}
