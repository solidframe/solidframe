<?php

declare(strict_types=1);

namespace App\Tests\Functional;

use App\Infrastructure\Persistence\Dbal\SchemaManager;
use Doctrine\DBAL\Connection;
use PHPUnit\Framework\Attributes\Test;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

final class TaskApiTest extends WebTestCase
{
    private KernelBrowser $client;
    private string $projectId;

    protected function setUp(): void
    {
        $this->client = self::createClient();

        /** @var Connection $connection */
        $connection = self::getContainer()->get(Connection::class);

        $tables = $connection->createSchemaManager()->listTableNames();
        if (in_array('tasks', $tables, true)) {
            $connection->executeStatement('DELETE FROM tasks');
            $connection->executeStatement('DELETE FROM projects');
        } else {
            (new SchemaManager($connection))->createSchema();
        }

        $this->projectId = $this->createProject('Test Project');
    }

    #[Test]
    public function createsATask(): void
    {
        $this->client->request('POST', '/api/tasks', [], [], ['CONTENT_TYPE' => 'application/json'], json_encode([
            'project_id' => $this->projectId,
            'title' => 'Write unit tests',
            'description' => 'Cover all domain logic',
            'priority' => 'high',
        ]));

        self::assertResponseStatusCodeSame(201);

        $data = $this->responseData();
        self::assertSame('Write unit tests', $data['title']);
        self::assertSame('Cover all domain logic', $data['description']);
        self::assertSame('high', $data['priority']);
        self::assertSame('open', $data['status']);
        self::assertNull($data['assignee']);
    }

    #[Test]
    public function createsTaskWithDefaults(): void
    {
        $this->client->request('POST', '/api/tasks', [], [], ['CONTENT_TYPE' => 'application/json'], json_encode([
            'project_id' => $this->projectId,
            'title' => 'Simple task',
        ]));

        self::assertResponseStatusCodeSame(201);

        $data = $this->responseData();
        self::assertSame('medium', $data['priority']);
        self::assertNull($data['description']);
    }

    #[Test]
    public function validatesRequiredFields(): void
    {
        $this->client->request('POST', '/api/tasks', [], [], ['CONTENT_TYPE' => 'application/json'], json_encode([]));

        self::assertResponseStatusCodeSame(422);
    }

    #[Test]
    public function rejectsTaskForNonexistentProject(): void
    {
        $this->client->request('POST', '/api/tasks', [], [], ['CONTENT_TYPE' => 'application/json'], json_encode([
            'project_id' => 'a1b2c3d4-e5f6-7890-abcd-ef1234567890',
            'title' => 'Orphan task',
        ]));

        self::assertResponseStatusCodeSame(404);
    }

    #[Test]
    public function listsTasks(): void
    {
        $this->createTask('Task A');
        $this->createTask('Task B');

        $this->client->request('GET', '/api/tasks');

        self::assertResponseStatusCodeSame(200);

        $json = $this->responseJson();
        self::assertCount(2, $json['data']);
    }

    #[Test]
    public function listsTasksFilterByProject(): void
    {
        $otherId = $this->createProject('Other Project');

        $this->createTask('Task A', $this->projectId);
        $this->createTask('Task B', $otherId);

        $this->client->request('GET', "/api/tasks?project={$this->projectId}");

        self::assertResponseStatusCodeSame(200);

        $json = $this->responseJson();
        self::assertCount(1, $json['data']);
        self::assertSame('Task A', $json['data'][0]['title']);
    }

    #[Test]
    public function showsTask(): void
    {
        $id = $this->createTask('My Task');

        $this->client->request('GET', "/api/tasks/{$id}");

        self::assertResponseStatusCodeSame(200);

        $data = $this->responseData();
        self::assertSame('My Task', $data['title']);
    }

    #[Test]
    public function assignsTask(): void
    {
        $id = $this->createTask('Assign me');

        $this->client->request('POST', "/api/tasks/{$id}/assign", [], [], ['CONTENT_TYPE' => 'application/json'], json_encode([
            'assignee' => 'Kadir',
        ]));

        self::assertResponseStatusCodeSame(200);

        $data = $this->responseData();
        self::assertSame('Kadir', $data['assignee']);
        self::assertSame('in_progress', $data['status']);
    }

    #[Test]
    public function completesTask(): void
    {
        $id = $this->createTask('Complete me');

        $this->client->request('POST', "/api/tasks/{$id}/complete");

        self::assertResponseStatusCodeSame(200);

        $data = $this->responseData();
        self::assertSame('completed', $data['status']);
    }

    #[Test]
    public function cannotCompleteAlreadyCompletedTask(): void
    {
        $id = $this->createTask('Done task');

        $this->client->request('POST', "/api/tasks/{$id}/complete");
        $this->client->request('POST', "/api/tasks/{$id}/complete");

        self::assertResponseStatusCodeSame(409);
    }

    #[Test]
    public function reopensTask(): void
    {
        $id = $this->createTask('Reopen me');

        $this->client->request('POST', "/api/tasks/{$id}/assign", [], [], ['CONTENT_TYPE' => 'application/json'], json_encode([
            'assignee' => 'Kadir',
        ]));
        $this->client->request('POST', "/api/tasks/{$id}/complete");
        $this->client->request('POST', "/api/tasks/{$id}/reopen");

        self::assertResponseStatusCodeSame(200);

        $data = $this->responseData();
        self::assertSame('open', $data['status']);
        self::assertNull($data['assignee']);
    }

    #[Test]
    public function listsTasksFilterByStatus(): void
    {
        $this->createTask('Open task');
        $doneId = $this->createTask('Done task');

        $this->client->request('POST', "/api/tasks/{$doneId}/complete");

        $this->client->request('GET', '/api/tasks?status=completed');

        self::assertResponseStatusCodeSame(200);

        $json = $this->responseJson();
        self::assertCount(1, $json['data']);
        self::assertSame('Done task', $json['data'][0]['title']);
    }

    #[Test]
    public function listsTasksFilterByAssignee(): void
    {
        $taskA = $this->createTask('Task A');
        $this->createTask('Task B');

        $this->client->request('POST', "/api/tasks/{$taskA}/assign", [], [], ['CONTENT_TYPE' => 'application/json'], json_encode([
            'assignee' => 'Kadir',
        ]));

        $this->client->request('GET', '/api/tasks?assignee=Kadir');

        self::assertResponseStatusCodeSame(200);

        $json = $this->responseJson();
        self::assertCount(1, $json['data']);
        self::assertSame('Kadir', $json['data'][0]['assignee']);
    }

    private function createProject(string $name): string
    {
        $this->client->request('POST', '/api/projects', [], [], ['CONTENT_TYPE' => 'application/json'], json_encode([
            'name' => $name,
        ]));

        return $this->responseData()['id'];
    }

    private function createTask(string $title, ?string $projectId = null): string
    {
        $this->client->request('POST', '/api/tasks', [], [], ['CONTENT_TYPE' => 'application/json'], json_encode([
            'project_id' => $projectId ?? $this->projectId,
            'title' => $title,
        ]));

        return $this->responseData()['id'];
    }

    /** @return array<string, mixed> */
    private function responseData(): array
    {
        return $this->responseJson()['data'];
    }

    /** @return array<string, mixed> */
    private function responseJson(): array
    {
        return json_decode($this->client->getResponse()->getContent(), true);
    }
}
