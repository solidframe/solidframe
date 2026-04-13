<?php

declare(strict_types=1);

namespace App\Controller;

use App\Application\Command\ArchiveProject\ArchiveProject;
use App\Application\Command\CreateProject\CreateProject;
use App\Application\Query\GetProject\GetProject;
use App\Application\Query\ListProjects\ListProjects;
use App\Domain\Project\Project;
use App\Http\RequestValidator;
use SolidFrame\Core\Bus\CommandBusInterface;
use SolidFrame\Core\Bus\QueryBusInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Validator\Constraints as Assert;

#[Route('/api/projects')]
final readonly class ProjectController
{
    public function __construct(
        private CommandBusInterface $commandBus,
        private QueryBusInterface $queryBus,
        private RequestValidator $requestValidator,
    ) {}

    #[Route('', methods: ['GET'])]
    public function index(): JsonResponse
    {
        $projects = $this->queryBus->ask(new ListProjects());

        return new JsonResponse([
            'data' => array_map($this->toArray(...), $projects),
        ]);
    }

    #[Route('', methods: ['POST'])]
    public function store(Request $request): JsonResponse
    {
        $data = $this->requestValidator->validate($request, new Assert\Collection([
            'name' => [new Assert\NotBlank(), new Assert\Length(max: 100)],
            'description' => new Assert\Optional([new Assert\Type('string')]),
        ]));

        $projectId = \App\Domain\Project\ProjectId::generate()->value();

        $this->commandBus->dispatch(new CreateProject(
            projectId: $projectId,
            name: $data['name'],
            description: $data['description'] ?? null,
        ));

        $project = $this->queryBus->ask(new GetProject($projectId));

        return new JsonResponse(['data' => $this->toArray($project)], Response::HTTP_CREATED);
    }

    #[Route('/{id}', methods: ['GET'])]
    public function show(string $id): JsonResponse
    {
        $project = $this->queryBus->ask(new GetProject($id));

        return new JsonResponse(['data' => $this->toArray($project)]);
    }

    #[Route('/{id}/archive', methods: ['POST'])]
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
