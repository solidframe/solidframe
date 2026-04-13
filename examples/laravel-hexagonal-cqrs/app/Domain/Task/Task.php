<?php

declare(strict_types=1);

namespace App\Domain\Task;

use App\Domain\Event\Task\TaskAssigned;
use App\Domain\Event\Task\TaskCompleted;
use App\Domain\Event\Task\TaskCreated;
use App\Domain\Project\ProjectId;
use App\Domain\Task\Exception\TaskAlreadyCompletedException;
use App\Domain\Task\ValueObject\Assignee;
use App\Domain\Task\ValueObject\TaskDescription;
use App\Domain\Task\ValueObject\TaskTitle;
use SolidFrame\Ddd\Aggregate\AbstractAggregateRoot;

final class Task extends AbstractAggregateRoot
{
    private TaskTitle $title;
    private ?TaskDescription $description;
    private TaskStatus $status;
    private Priority $priority;
    private ?Assignee $assignee;
    private ProjectId $projectId;

    private function __construct(
        TaskId $id,
        ProjectId $projectId,
        TaskTitle $title,
        ?TaskDescription $description,
        Priority $priority,
    ) {
        parent::__construct($id);
        $this->projectId = $projectId;
        $this->title = $title;
        $this->description = $description;
        $this->priority = $priority;
        $this->status = TaskStatus::Open;
        $this->assignee = null;
    }

    public static function create(
        TaskId $id,
        ProjectId $projectId,
        TaskTitle $title,
        ?TaskDescription $description = null,
        Priority $priority = Priority::Medium,
    ): self {
        $task = new self($id, $projectId, $title, $description, $priority);

        $task->recordThat(new TaskCreated(
            taskId: $id->value(),
            projectId: $projectId->value(),
            title: $title->value(),
        ));

        return $task;
    }

    public static function reconstitute(
        TaskId $id,
        ProjectId $projectId,
        TaskTitle $title,
        ?TaskDescription $description,
        Priority $priority,
        TaskStatus $status,
        ?Assignee $assignee,
    ): self {
        $task = new self($id, $projectId, $title, $description, $priority);
        $task->status = $status;
        $task->assignee = $assignee;

        return $task;
    }

    public function assign(Assignee $assignee): void
    {
        ($this->status !== TaskStatus::Completed)
            or throw TaskAlreadyCompletedException::forId($this->identity()->value());

        $this->assignee = $assignee;
        $this->status = TaskStatus::InProgress;

        $this->recordThat(new TaskAssigned(
            taskId: $this->identity()->value(),
            assignee: $assignee->value(),
        ));
    }

    public function complete(): void
    {
        ($this->status !== TaskStatus::Completed)
            or throw TaskAlreadyCompletedException::forId($this->identity()->value());

        $this->status = TaskStatus::Completed;

        $this->recordThat(new TaskCompleted(
            taskId: $this->identity()->value(),
            projectId: $this->projectId->value(),
        ));
    }

    public function reopen(): void
    {
        $this->status = TaskStatus::Open;
        $this->assignee = null;
    }

    public function title(): TaskTitle
    {
        return $this->title;
    }

    public function description(): ?TaskDescription
    {
        return $this->description;
    }

    public function status(): TaskStatus
    {
        return $this->status;
    }

    public function priority(): Priority
    {
        return $this->priority;
    }

    public function assignee(): ?Assignee
    {
        return $this->assignee;
    }

    public function projectId(): ProjectId
    {
        return $this->projectId;
    }
}
