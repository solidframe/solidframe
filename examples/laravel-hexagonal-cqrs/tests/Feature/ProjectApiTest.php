<?php

declare(strict_types=1);

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

final class ProjectApiTest extends TestCase
{
    use RefreshDatabase;

    #[Test]
    public function createProject(): void
    {
        $response = $this->postJson('/api/projects', [
            'name' => 'Task Manager',
            'description' => 'A project management app',
        ]);

        $response->assertStatus(201)
            ->assertJsonPath('data.name', 'Task Manager')
            ->assertJsonPath('data.description', 'A project management app')
            ->assertJsonPath('data.status', 'active');
    }

    #[Test]
    public function createProjectWithoutDescription(): void
    {
        $response = $this->postJson('/api/projects', [
            'name' => 'Simple Project',
        ]);

        $response->assertStatus(201)
            ->assertJsonPath('data.name', 'Simple Project')
            ->assertJsonPath('data.description', null);
    }

    #[Test]
    public function createProjectValidatesName(): void
    {
        $response = $this->postJson('/api/projects', []);

        $response->assertStatus(422)
            ->assertJsonValidationErrors('name');
    }

    #[Test]
    public function listProjects(): void
    {
        $this->postJson('/api/projects', ['name' => 'Project A']);
        $this->postJson('/api/projects', ['name' => 'Project B']);

        $response = $this->getJson('/api/projects');

        $response->assertStatus(200)
            ->assertJsonCount(2, 'data');
    }

    #[Test]
    public function showProject(): void
    {
        $created = $this->postJson('/api/projects', ['name' => 'My Project']);
        $id = $created->json('data.id');

        $response = $this->getJson("/api/projects/{$id}");

        $response->assertStatus(200)
            ->assertJsonPath('data.name', 'My Project');
    }

    #[Test]
    public function showProjectNotFound(): void
    {
        $response = $this->getJson('/api/projects/nonexistent-id');

        $response->assertStatus(500);
    }

    #[Test]
    public function archiveProject(): void
    {
        $created = $this->postJson('/api/projects', ['name' => 'My Project']);
        $id = $created->json('data.id');

        $response = $this->postJson("/api/projects/{$id}/archive");

        $response->assertStatus(200)
            ->assertJsonPath('data.status', 'archived');
    }

    #[Test]
    public function cannotArchiveAlreadyArchivedProject(): void
    {
        $created = $this->postJson('/api/projects', ['name' => 'My Project']);
        $id = $created->json('data.id');

        $this->postJson("/api/projects/{$id}/archive");
        $response = $this->postJson("/api/projects/{$id}/archive");

        $response->assertStatus(500);
    }
}
