<?php

namespace Tests\Feature\Api;

use App\Models\{User, Project, Task};
use Tests\TestCase;

class TaskApiTest extends TestCase
{
    private User $user;
    private Project $project;

    protected function setUp(): void
    {
        parent::setUp();
        $this->user = User::factory()->create();
        $this->project = Project::factory()->for($this->user)->create();
    }

    public function test_can_list_project_tasks()
    {
        Task::factory(3)->for($this->project)->create();

        $response = $this->actingAs($this->user)
            ->getJson("/api/projects/{$this->project->id}/tasks");

        $response->assertOk()
            ->assertJsonCount(3, 'data');
    }

    public function test_can_create_task()
    {
        $data = [
            'title' => 'New Task',
            'description' => 'Task Description',
            'status' => 'todo',
        ];

        $response = $this->actingAs($this->user)
            ->postJson("/api/projects/{$this->project->id}/tasks", $data);

        $response->assertCreated()
            ->assertJsonPath('data.title', 'New Task');

        $this->assertDatabaseHas('tasks', ['title' => 'New Task']);
    }

    public function test_can_update_task()
    {
        $task = Task::factory()->for($this->project)->create();

        $response = $this->actingAs($this->user)
            ->patchJson("/api/tasks/{$task->id}", ['status' => 'in-progress']);

        $response->assertOk()
            ->assertJsonPath('data.status', 'in-progress');
    }

    public function test_can_delete_task()
    {
        $task = Task::factory()->for($this->project)->create();

        $response = $this->actingAs($this->user)
            ->deleteJson("/api/tasks/{$task->id}");

        $response->assertOk();
        $this->assertDatabaseMissing('tasks', ['id' => $task->id]);
    }

    public function test_cannot_modify_others_tasks()
    {
        $otherUser = User::factory()->create();
        $task = Task::factory()->for($this->project)->create();

        $response = $this->actingAs($otherUser)
            ->patchJson("/api/tasks/{$task->id}", ['status' => 'done']);

        $response->assertForbidden();
    }
}