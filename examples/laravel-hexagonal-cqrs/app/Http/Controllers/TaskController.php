<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Application\Command\AssignTask\AssignTask;
use App\Application\Command\CompleteTask\CompleteTask;
use App\Application\Command\CreateTask\CreateTask;
use App\Application\Command\ReopenTask\ReopenTask;
use App\Application\Query\GetTask\GetTask;
use App\Application\Query\ListTasks\ListTasks;
use App\Domain\Task\Task;
use App\Http\Requests\AssignTaskRequest;
use App\Http\Requests\StoreTaskRequest;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Str;
use SolidFrame\Core\Bus\CommandBusInterface;
use SolidFrame\Core\Bus\QueryBusInterface;

final readonly class TaskController
{
    public function __construct(
        private CommandBusInterface $commandBus,
        private QueryBusInterface $queryBus,
    ) {
    }

    public function index(Request $request): JsonResponse
    {
        $tasks = $this->queryBus->ask(new ListTasks(
            projectId: $request->string('project')->toString() ?: null,
            status: $request->string('status')->toString() ?: null,
            assignee: $request->string('assignee')->toString() ?: null,
        ));

        return new JsonResponse([
            'data' => array_map($this->toArray(...), $tasks),
        ]);
    }

    public function store(StoreTaskRequest $request): JsonResponse
    {
        $taskId = Str::uuid()->toString();

        $this->commandBus->dispatch(new CreateTask(
            taskId: $taskId,
            projectId: $request->validated('project_id'),
            title: $request->validated('title'),
            description: $request->validated('description'),
            priority: $request->validated('priority', 'medium'),
        ));

        $task = $this->queryBus->ask(new GetTask($taskId));

        return new JsonResponse(['data' => $this->toArray($task)], Response::HTTP_CREATED);
    }

    public function show(string $id): JsonResponse
    {
        $task = $this->queryBus->ask(new GetTask($id));

        return new JsonResponse(['data' => $this->toArray($task)]);
    }

    public function assign(AssignTaskRequest $request, string $id): JsonResponse
    {
        $this->commandBus->dispatch(new AssignTask(
            taskId: $id,
            assignee: $request->validated('assignee'),
        ));

        $task = $this->queryBus->ask(new GetTask($id));

        return new JsonResponse(['data' => $this->toArray($task)]);
    }

    public function complete(string $id): JsonResponse
    {
        $this->commandBus->dispatch(new CompleteTask($id));

        $task = $this->queryBus->ask(new GetTask($id));

        return new JsonResponse(['data' => $this->toArray($task)]);
    }

    public function reopen(string $id): JsonResponse
    {
        $this->commandBus->dispatch(new ReopenTask($id));

        $task = $this->queryBus->ask(new GetTask($id));

        return new JsonResponse(['data' => $this->toArray($task)]);
    }

    /** @return array<string, mixed> */
    private function toArray(Task $task): array
    {
        return [
            'id' => $task->identity()->value(),
            'project_id' => $task->projectId()->value(),
            'title' => $task->title()->value(),
            'description' => $task->description()?->value(),
            'status' => $task->status()->value,
            'priority' => $task->priority()->value,
            'assignee' => $task->assignee()?->value(),
        ];
    }
}
