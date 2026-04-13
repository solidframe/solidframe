<?php

declare(strict_types=1);

namespace App\Application\Command\CreateTask;

use App\Domain\Project\Exception\ProjectNotFoundException;
use App\Domain\Project\Port\ProjectRepository;
use App\Domain\Project\ProjectId;
use App\Domain\Task\Port\TaskRepository;
use App\Domain\Task\Priority;
use App\Domain\Task\Task;
use App\Domain\Task\TaskId;
use App\Domain\Task\ValueObject\TaskDescription;
use App\Domain\Task\ValueObject\TaskTitle;
use SolidFrame\Core\Bus\EventBusInterface;
use SolidFrame\Cqrs\CommandHandler;

final readonly class CreateTaskHandler implements CommandHandler
{
    public function __construct(
        private TaskRepository $tasks,
        private ProjectRepository $projects,
        private EventBusInterface $eventBus,
    ) {
    }

    public function __invoke(CreateTask $command): void
    {
        $this->projects->find(new ProjectId($command->projectId))
            ?? throw ProjectNotFoundException::forId($command->projectId);

        $task = Task::create(
            id: new TaskId($command->taskId),
            projectId: new ProjectId($command->projectId),
            title: TaskTitle::from($command->title),
            description: $command->description !== null ? TaskDescription::from($command->description) : null,
            priority: Priority::from($command->priority),
        );

        $this->tasks->save($task);

        foreach ($task->releaseEvents() as $event) {
            $this->eventBus->dispatch($event);
        }
    }
}
