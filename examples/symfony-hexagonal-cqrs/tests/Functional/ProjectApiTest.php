<?php

declare(strict_types=1);

namespace App\Tests\Functional;

use App\Infrastructure\Persistence\Dbal\SchemaManager;
use Doctrine\DBAL\Connection;
use PHPUnit\Framework\Attributes\Test;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

final class ProjectApiTest extends WebTestCase
{
    private KernelBrowser $client;

    protected function setUp(): void
    {
        $this->client = self::createClient();

        /** @var Connection $connection */
        $connection = self::getContainer()->get(Connection::class);

        $tables = $connection->createSchemaManager()->listTableNames();
        if (in_array('tasks', $tables, true)) {
            $connection->executeStatement('DELETE FROM tasks');
        }
        if (in_array('projects', $tables, true)) {
            $connection->executeStatement('DELETE FROM projects');
        } else {
            (new SchemaManager($connection))->createSchema();
        }
    }

    #[Test]
    public function createsAProject(): void
    {
        $this->client->request('POST', '/api/projects', [], [], ['CONTENT_TYPE' => 'application/json'], json_encode([
            'name' => 'Task Manager',
            'description' => 'A project management app',
        ]));

        self::assertResponseStatusCodeSame(201);

        $data = $this->responseData();
        self::assertSame('Task Manager', $data['name']);
        self::assertSame('A project management app', $data['description']);
        self::assertSame('active', $data['status']);
    }

    #[Test]
    public function createsProjectWithoutDescription(): void
    {
        $this->client->request('POST', '/api/projects', [], [], ['CONTENT_TYPE' => 'application/json'], json_encode([
            'name' => 'Simple Project',
        ]));

        self::assertResponseStatusCodeSame(201);

        $data = $this->responseData();
        self::assertSame('Simple Project', $data['name']);
        self::assertNull($data['description']);
    }

    #[Test]
    public function validatesRequiredFields(): void
    {
        $this->client->request('POST', '/api/projects', [], [], ['CONTENT_TYPE' => 'application/json'], json_encode([]));

        self::assertResponseStatusCodeSame(422);
    }

    #[Test]
    public function listsProjects(): void
    {
        $this->createProject('Project A');
        $this->createProject('Project B');

        $this->client->request('GET', '/api/projects');

        self::assertResponseStatusCodeSame(200);

        $data = $this->responseJson();
        self::assertCount(2, $data['data']);
    }

    #[Test]
    public function showsProject(): void
    {
        $id = $this->createProject('My Project');

        $this->client->request('GET', "/api/projects/{$id}");

        self::assertResponseStatusCodeSame(200);

        $data = $this->responseData();
        self::assertSame('My Project', $data['name']);
    }

    #[Test]
    public function returnsNotFoundForMissingProject(): void
    {
        $this->client->request('GET', '/api/projects/00000000-0000-0000-0000-000000000000');

        self::assertResponseStatusCodeSame(404);
    }

    #[Test]
    public function archivesProject(): void
    {
        $id = $this->createProject('My Project');

        $this->client->request('POST', "/api/projects/{$id}/archive");

        self::assertResponseStatusCodeSame(200);

        $data = $this->responseData();
        self::assertSame('archived', $data['status']);
    }

    #[Test]
    public function cannotArchiveAlreadyArchivedProject(): void
    {
        $id = $this->createProject('My Project');

        $this->client->request('POST', "/api/projects/{$id}/archive");
        $this->client->request('POST', "/api/projects/{$id}/archive");

        self::assertResponseStatusCodeSame(409);
    }

    private function createProject(string $name): string
    {
        $this->client->request('POST', '/api/projects', [], [], ['CONTENT_TYPE' => 'application/json'], json_encode([
            'name' => $name,
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
