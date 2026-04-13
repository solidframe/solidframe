<?php

declare(strict_types=1);

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

final class TaskApiTest extends TestCase
{
    use RefreshDatabase;

    private string $projectId;

    protected function setUp(): void
    {
        parent::setUp();

        $response = $this->postJson('/api/projects', ['name' => 'Test Project']);
        $this->projectId = $response->json('data.id');
    }

    #[Test]
    public function createTask(): void
    {
        $response = $this->postJson('/api/tasks', [
            'project_id' => $this->projectId,
            'title' => 'Write unit tests',
            'description' => 'Cover all domain logic',
            'priority' => 'high',
        ]);

        $response->assertStatus(201)
            ->assertJsonPath('data.title', 'Write unit tests')
            ->assertJsonPath('data.description', 'Cover all domain logic')
            ->assertJsonPath('data.priority', 'high')
            ->assertJsonPath('data.status', 'open')
            ->assertJsonPath('data.assignee', null);
    }

    #[Test]
    public function createTaskWithDefaults(): void
    {
        $response = $this->postJson('/api/tasks', [
            'project_id' => $this->projectId,
            'title' => 'Simple task',
        ]);

        $response->assertStatus(201)
            ->assertJsonPath('data.priority', 'medium')
            ->assertJsonPath('data.description', null);
    }

    #[Test]
    public function createTaskValidation(): void
    {
        $response = $this->postJson('/api/tasks', []);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['project_id', 'title']);
    }

    #[Test]
    public function createTaskForNonexistentProject(): void
    {
        $response = $this->postJson('/api/tasks', [
            'project_id' => '00000000-0000-0000-0000-000000000000',
            'title' => 'Orphan task',
        ]);

        $response->assertStatus(500);
    }

    #[Test]
    public function listTasks(): void
    {
        $this->postJson('/api/tasks', ['project_id' => $this->projectId, 'title' => 'Task A']);
        $this->postJson('/api/tasks', ['project_id' => $this->projectId, 'title' => 'Task B']);

        $response = $this->getJson('/api/tasks');

        $response->assertStatus(200)
            ->assertJsonCount(2, 'data');
    }

    #[Test]
    public function listTasksFilterByProject(): void
    {
        $other = $this->postJson('/api/projects', ['name' => 'Other Project']);
        $otherId = $other->json('data.id');

        $this->postJson('/api/tasks', ['project_id' => $this->projectId, 'title' => 'Task A']);
        $this->postJson('/api/tasks', ['project_id' => $otherId, 'title' => 'Task B']);

        $response = $this->getJson("/api/tasks?project={$this->projectId}");

        $response->assertStatus(200)
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.title', 'Task A');
    }

    #[Test]
    public function showTask(): void
    {
        $created = $this->postJson('/api/tasks', [
            'project_id' => $this->projectId,
            'title' => 'My Task',
        ]);
        $id = $created->json('data.id');

        $response = $this->getJson("/api/tasks/{$id}");

        $response->assertStatus(200)
            ->assertJsonPath('data.title', 'My Task');
    }

    #[Test]
    public function assignTask(): void
    {
        $created = $this->postJson('/api/tasks', [
            'project_id' => $this->projectId,
            'title' => 'Assign me',
        ]);
        $id = $created->json('data.id');

        $response = $this->postJson("/api/tasks/{$id}/assign", [
            'assignee' => 'Kadir',
        ]);

        $response->assertStatus(200)
            ->assertJsonPath('data.assignee', 'Kadir')
            ->assertJsonPath('data.status', 'in_progress');
    }

    #[Test]
    public function completeTask(): void
    {
        $created = $this->postJson('/api/tasks', [
            'project_id' => $this->projectId,
            'title' => 'Complete me',
        ]);
        $id = $created->json('data.id');

        $response = $this->postJson("/api/tasks/{$id}/complete");

        $response->assertStatus(200)
            ->assertJsonPath('data.status', 'completed');
    }

    #[Test]
    public function cannotCompleteAlreadyCompletedTask(): void
    {
        $created = $this->postJson('/api/tasks', [
            'project_id' => $this->projectId,
            'title' => 'Done task',
        ]);
        $id = $created->json('data.id');

        $this->postJson("/api/tasks/{$id}/complete");
        $response = $this->postJson("/api/tasks/{$id}/complete");

        $response->assertStatus(500);
    }

    #[Test]
    public function reopenTask(): void
    {
        $created = $this->postJson('/api/tasks', [
            'project_id' => $this->projectId,
            'title' => 'Reopen me',
        ]);
        $id = $created->json('data.id');

        $this->postJson("/api/tasks/{$id}/assign", ['assignee' => 'Kadir']);
        $this->postJson("/api/tasks/{$id}/complete");

        $response = $this->postJson("/api/tasks/{$id}/reopen");

        $response->assertStatus(200)
            ->assertJsonPath('data.status', 'open')
            ->assertJsonPath('data.assignee', null);
    }

    #[Test]
    public function listTasksFilterByStatus(): void
    {
        $task1 = $this->postJson('/api/tasks', ['project_id' => $this->projectId, 'title' => 'Open']);
        $task2 = $this->postJson('/api/tasks', ['project_id' => $this->projectId, 'title' => 'Done']);

        $this->postJson('/api/tasks/' . $task2->json('data.id') . '/complete');

        $response = $this->getJson('/api/tasks?status=completed');

        $response->assertStatus(200)
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.title', 'Done');
    }

    #[Test]
    public function listTasksFilterByAssignee(): void
    {
        $task1 = $this->postJson('/api/tasks', ['project_id' => $this->projectId, 'title' => 'Task A']);
        $this->postJson('/api/tasks', ['project_id' => $this->projectId, 'title' => 'Task B']);

        $this->postJson('/api/tasks/' . $task1->json('data.id') . '/assign', ['assignee' => 'Kadir']);

        $response = $this->getJson('/api/tasks?assignee=Kadir');

        $response->assertStatus(200)
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.assignee', 'Kadir');
    }
}
