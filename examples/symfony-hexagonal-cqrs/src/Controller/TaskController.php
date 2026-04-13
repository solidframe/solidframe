<?php

declare(strict_types=1);

namespace App\Controller;

use App\Application\Command\AssignTask\AssignTask;
use App\Application\Command\CompleteTask\CompleteTask;
use App\Application\Command\CreateTask\CreateTask;
use App\Application\Command\ReopenTask\ReopenTask;
use App\Application\Query\GetTask\GetTask;
use App\Application\Query\ListTasks\ListTasks;
use App\Domain\Task\Task;
use App\Http\RequestValidator;
use SolidFrame\Core\Bus\CommandBusInterface;
use SolidFrame\Core\Bus\QueryBusInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Validator\Constraints as Assert;

#[Route('/api/tasks')]
final readonly class TaskController
{
    public function __construct(
        private CommandBusInterface $commandBus,
        private QueryBusInterface $queryBus,
        private RequestValidator $requestValidator,
    ) {}

    #[Route('', methods: ['GET'])]
    public function index(Request $request): JsonResponse
    {
        $tasks = $this->queryBus->ask(new ListTasks(
            projectId: $request->query->getString('project') ?: null,
            status: $request->query->getString('status') ?: null,
            assignee: $request->query->getString('assignee') ?: null,
        ));

        return new JsonResponse([
            'data' => array_map($this->toArray(...), $tasks),
        ]);
    }

    #[Route('', methods: ['POST'])]
    public function store(Request $request): JsonResponse
    {
        $data = $this->requestValidator->validate($request, new Assert\Collection([
            'project_id' => [new Assert\NotBlank(), new Assert\Uuid()],
            'title' => [new Assert\NotBlank(), new Assert\Length(max: 255)],
            'description' => new Assert\Optional([new Assert\Type('string'), new Assert\Length(max: 1000)]),
            'priority' => new Assert\Optional([new Assert\Choice(choices: ['low', 'medium', 'high', 'critical'])]),
        ]));

        $taskId = \App\Domain\Task\TaskId::generate()->value();

        $this->commandBus->dispatch(new CreateTask(
            taskId: $taskId,
            projectId: $data['project_id'],
            title: $data['title'],
            description: $data['description'] ?? null,
            priority: $data['priority'] ?? 'medium',
        ));

        $task = $this->queryBus->ask(new GetTask($taskId));

        return new JsonResponse(['data' => $this->toArray($task)], Response::HTTP_CREATED);
    }

    #[Route('/{id}', methods: ['GET'])]
    public function show(string $id): JsonResponse
    {
        $task = $this->queryBus->ask(new GetTask($id));

        return new JsonResponse(['data' => $this->toArray($task)]);
    }

    #[Route('/{id}/assign', methods: ['POST'])]
    public function assign(Request $request, string $id): JsonResponse
    {
        $data = $this->requestValidator->validate($request, new Assert\Collection([
            'assignee' => [new Assert\NotBlank(), new Assert\Length(max: 255)],
        ]));

        $this->commandBus->dispatch(new AssignTask(
            taskId: $id,
            assignee: $data['assignee'],
        ));

        $task = $this->queryBus->ask(new GetTask($id));

        return new JsonResponse(['data' => $this->toArray($task)]);
    }

    #[Route('/{id}/complete', methods: ['POST'])]
    public function complete(string $id): JsonResponse
    {
        $this->commandBus->dispatch(new CompleteTask($id));

        $task = $this->queryBus->ask(new GetTask($id));

        return new JsonResponse(['data' => $this->toArray($task)]);
    }

    #[Route('/{id}/reopen', methods: ['POST'])]
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
