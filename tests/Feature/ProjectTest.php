<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Project;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ProjectTest extends TestCase
{
    use RefreshDatabase;

    protected User $user;

    /**
     * Set up test case.
     */
    protected function setUp(): void
    {
        parent::setUp();
        $this->user = User::factory()->create();
    }

    /**
     * Test user can view their projects.
     */
    public function test_user_can_view_their_projects(): void
    {
        Project::factory(3)->for($this->user)->create();

        $response = $this->actingAs($this->user)
            ->getJson('/api/projects');

        $response->assertOk()
            ->assertJsonCount(3, 'data')
            ->assertJsonStructure([
                'success',
                'data' => [
                    '*' => ['id', 'name', 'description', 'status', 'user_id', 'created_at', 'updated_at'],
                ],
                'meta' => ['total', 'per_page', 'current_page', 'last_page'],
            ]);
    }

    /**
     * Test user can view a single project.
     */
    public function test_user_can_view_single_project(): void
    {
        $project = Project::factory()->for($this->user)->create();

        $response = $this->actingAs($this->user)
            ->getJson("/api/projects/{$project->id}");

        $response->assertOk()
            ->assertJsonPath('data.id', $project->id)
            ->assertJsonPath('data.name', $project->name);
    }

    /**
     * Test user cannot view other user's project.
     */
    public function test_user_cannot_view_other_users_project(): void
    {
        $otherUser = User::factory()->create();
        $project = Project::factory()->for($otherUser)->create();

        $response = $this->actingAs($this->user)
            ->getJson("/api/projects/{$project->id}");

        $response->assertForbidden();
    }

    /**
     * Test user can create a project.
     */
    public function test_user_can_create_project(): void
    {
        $response = $this->actingAs($this->user)
            ->postJson('/api/projects', [
                'name' => 'Test Project',
                'description' => 'Test Description',
                'status' => 'active',
            ]);

        $response->assertCreated()
            ->assertJsonStructure([
                'success',
                'data' => ['id', 'name', 'description', 'status', 'user_id', 'created_at', 'updated_at'],
            ]);

        $this->assertDatabaseHas('projects', [
            'user_id' => $this->user->id,
            'name' => 'Test Project',
        ]);
    }

    /**
     * Test user can update their project.
     */
    public function test_user_can_update_their_project(): void
    {
        $project = Project::factory()->for($this->user)->create();

        $response = $this->actingAs($this->user)
            ->patchJson("/api/projects/{$project->id}", [
                'name' => 'Updated Project Name',
                'status' => 'archived',
            ]);

        $response->assertOk()
            ->assertJsonPath('data.name', 'Updated Project Name')
            ->assertJsonPath('data.status', 'archived');

        $this->assertDatabaseHas('projects', [
            'id' => $project->id,
            'name' => 'Updated Project Name',
        ]);
    }

    /**
     * Test user cannot update other user's project.
     */
    public function test_user_cannot_update_other_users_project(): void
    {
        $otherUser = User::factory()->create();
        $project = Project::factory()->for($otherUser)->create();

        $response = $this->actingAs($this->user)
            ->patchJson("/api/projects/{$project->id}", [
                'name' => 'Updated Name',
            ]);

        $response->assertForbidden();
    }

    /**
     * Test user can delete their project.
     */
    public function test_user_can_delete_their_project(): void
    {
        $project = Project::factory()->for($this->user)->create();

        $response = $this->actingAs($this->user)
            ->deleteJson("/api/projects/{$project->id}");

        $response->assertOk();
        $this->assertDatabaseMissing('projects', ['id' => $project->id]);
    }

    /**
     * Test project creation validation.
     */
    public function test_project_creation_validation(): void
    {
        $response = $this->actingAs($this->user)
            ->postJson('/api/projects', [
                'description' => 'Missing required name field',
            ]);

        $response->assertUnprocessable()
            ->assertJsonStructure(['success', 'message', 'errors']);
    }
}