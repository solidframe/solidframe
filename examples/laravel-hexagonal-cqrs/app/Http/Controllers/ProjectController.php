<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Application\Command\ArchiveProject\ArchiveProject;
use App\Application\Command\CreateProject\CreateProject;
use App\Application\Query\GetProject\GetProject;
use App\Application\Query\ListProjects\ListProjects;
use App\Domain\Project\Project;
use App\Http\Requests\StoreProjectRequest;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Response;
use Illuminate\Support\Str;
use SolidFrame\Core\Bus\CommandBusInterface;
use SolidFrame\Core\Bus\QueryBusInterface;

final readonly class ProjectController
{
    public function __construct(
        private CommandBusInterface $commandBus,
        private QueryBusInterface $queryBus,
    ) {
    }

    public function index(): JsonResponse
    {
        $projects = $this->queryBus->ask(new ListProjects());

        return new JsonResponse([
            'data' => array_map($this->toArray(...), $projects),
        ]);
    }

    public function store(StoreProjectRequest $request): JsonResponse
    {
        $projectId = Str::uuid()->toString();

        $this->commandBus->dispatch(new CreateProject(
            projectId: $projectId,
            name: $request->validated('name'),
            description: $request->validated('description'),
        ));

        $project = $this->queryBus->ask(new GetProject($projectId));

        return new JsonResponse(['data' => $this->toArray($project)], Response::HTTP_CREATED);
    }

    public function show(string $id): JsonResponse
    {
        $project = $this->queryBus->ask(new GetProject($id));

        return new JsonResponse(['data' => $this->toArray($project)]);
    }

    public function archive(string $id): JsonResponse
    {
        $this->commandBus->dispatch(new ArchiveProject($id));

        $project = $this->queryBus->ask(new GetProject($id));

        return new JsonResponse(['data' => $this->toArray($project)]);
    }

    /** @return array<string, mixed> */
    private function toArray(Project $project): array
    {
        return [
            'id' => $project->identity()->value(),
            'name' => $project->name()->value(),
            'description' => $project->description(),
            'status' => $project->status()->value,
        ];
    }
}
