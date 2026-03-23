<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Project;
use App\Models\Task;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TaskTest extends TestCase
{
    use RefreshDatabase;

    protected User $user;
    protected Project $project;

    /**
     * Set up test case.
     */
    protected function setUp(): void
    {
        parent::setUp();
        $this->user = User::factory()->create();
        $this->project = Project::factory()->for($this->user)->create();
    }

    /**
     * Test user can view project tasks.
     */
    public function test_user_can_view_project_tasks(): void
    {
        Task::factory(5)->for($this->project)->create();

        $response = $this->actingAs($this->user)
            ->getJson("/api/projects/{$this->project->id}/tasks");

        $response->assertOk()
            ->assertJsonCount(5, 'data');
    }

    /**
     * Test user can filter tasks by status.
     */
    public function test_user_can_filter_tasks_by_status(): void
    {
        Task::factory(3)->for($this->project)->create(['status' => 'todo']);
        Task::factory(2)->for($this->project)->create(['status' => 'in-progress']);

        $response = $this->actingAs($this->user)
            ->getJson("/api/projects/{$this->project->id}/tasks?status=todo");

        $response->assertOk()
            ->assertJsonCount(3, 'data');
    }

    /**
     * Test user can create task.
     */
    public function test_user_can_create_task(): void
    {
        $response = $this->actingAs($this->user)
            ->postJson("/api/projects/{$this->project->id}/tasks", [
                'title' => 'New Task',
                'description' => 'Task Description',
                'status' => 'todo',
                'priority' => 5,
            ]);

        $response->assertCreated()
            ->assertJsonStructure([
                'success',
                'data' => ['id', 'project_id', 'title', 'description', 'status', 'priority', 'created_at'],
            ]);
    }

    /**
     * Test user can update task.
     */
    public function test_user_can_update_task(): void
    {
        $task = Task::factory()->for($this->project)->create();

        $response = $this->actingAs($this->user)
            ->patchJson("/api/projects/{$this->project->id}/tasks/{$task->id}", [
                'status' => 'done',
                'title' => 'Updated Title',
            ]);

        $response->assertOk()
            ->assertJsonPath('data.status', 'done')
            ->assertJsonPath('data.title', 'Updated Title');
    }

    /**
     * Test user can delete task.
     */
    public function test_user_can_delete_task(): void
    {
        $task = Task::factory()->for($this->project)->create();

        $response = $this->actingAs($this->user)
            ->deleteJson("/api/projects/{$this->project->id}/tasks/{$task->id}");

        $response->assertOk();
        $this->assertDatabaseMissing('tasks', ['id' => $task->id]);
    }

    /**
     * Test task cannot be assigned to nonexistent user.
     */
    public function test_task_cannot_be_assigned_to_nonexistent_user(): void
    {
        $response = $this->actingAs($this->user)
            ->postJson("/api/projects/{$this->project->id}/tasks", [
                'title' => 'New Task',
                'assigned_to' => 99999,
            ]);

        $response->assertUnprocessable();
    }
}