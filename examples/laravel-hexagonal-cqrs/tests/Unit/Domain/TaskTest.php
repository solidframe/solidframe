<?php

declare(strict_types=1);

namespace Tests\Unit\Domain;

use App\Domain\Event\Task\TaskAssigned;
use App\Domain\Event\Task\TaskCompleted;
use App\Domain\Event\Task\TaskCreated;
use App\Domain\Project\ProjectId;
use App\Domain\Task\Exception\TaskAlreadyCompletedException;
use App\Domain\Task\Priority;
use App\Domain\Task\Task;
use App\Domain\Task\TaskId;
use App\Domain\Task\TaskStatus;
use App\Domain\Task\ValueObject\Assignee;
use App\Domain\Task\ValueObject\TaskDescription;
use App\Domain\Task\ValueObject\TaskTitle;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

final class TaskTest extends TestCase
{
    #[Test]
    public function createRecordsTaskCreatedEvent(): void
    {
        $taskId = TaskId::generate();
        $projectId = ProjectId::generate();

        $task = Task::create(
            id: $taskId,
            projectId: $projectId,
            title: TaskTitle::from('Write tests'),
        );

        self::assertSame(TaskStatus::Open, $task->status());
        self::assertSame(Priority::Medium, $task->priority());
        self::assertNull($task->assignee());

        $events = $task->releaseEvents();
        self::assertCount(1, $events);
        self::assertInstanceOf(TaskCreated::class, $events[0]);
        self::assertSame($taskId->value(), $events[0]->taskId);
    }

    #[Test]
    public function createWithAllParameters(): void
    {
        $task = Task::create(
            id: TaskId::generate(),
            projectId: ProjectId::generate(),
            title: TaskTitle::from('Write tests'),
            description: TaskDescription::from('All unit tests'),
            priority: Priority::High,
        );

        self::assertSame('Write tests', $task->title()->value());
        self::assertSame('All unit tests', $task->description()->value());
        self::assertSame(Priority::High, $task->priority());
    }

    #[Test]
    public function assignChangesStatusToInProgress(): void
    {
        $task = $this->createTask();
        $task->releaseEvents();

        $task->assign(Assignee::from('Kadir'));

        self::assertSame(TaskStatus::InProgress, $task->status());
        self::assertSame('Kadir', $task->assignee()->value());

        $events = $task->releaseEvents();
        self::assertCount(1, $events);
        self::assertInstanceOf(TaskAssigned::class, $events[0]);
        self::assertSame('Kadir', $events[0]->assignee);
    }

    #[Test]
    public function completeRecordsTaskCompletedEvent(): void
    {
        $task = $this->createTask();
        $task->releaseEvents();

        $task->complete();

        self::assertSame(TaskStatus::Completed, $task->status());

        $events = $task->releaseEvents();
        self::assertCount(1, $events);
        self::assertInstanceOf(TaskCompleted::class, $events[0]);
    }

    #[Test]
    public function cannotAssignCompletedTask(): void
    {
        $task = $this->createTask();
        $task->complete();

        $this->expectException(TaskAlreadyCompletedException::class);
        $task->assign(Assignee::from('Kadir'));
    }

    #[Test]
    public function cannotCompleteAlreadyCompletedTask(): void
    {
        $task = $this->createTask();
        $task->complete();

        $this->expectException(TaskAlreadyCompletedException::class);
        $task->complete();
    }

    #[Test]
    public function reopenResetsStatusAndAssignee(): void
    {
        $task = $this->createTask();
        $task->assign(Assignee::from('Kadir'));
        $task->complete();
        $task->releaseEvents();

        $task->reopen();

        self::assertSame(TaskStatus::Open, $task->status());
        self::assertNull($task->assignee());
    }

    private function createTask(): Task
    {
        return Task::create(
            id: TaskId::generate(),
            projectId: ProjectId::generate(),
            title: TaskTitle::from('Write tests'),
        );
    }
}
