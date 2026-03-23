<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Project;
use App\Models\Task;
use App\Models\Comment;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CommentTest extends TestCase
{
    use RefreshDatabase;

    protected User $user;
    protected Project $project;
    protected Task $task;

    /**
     * Set up test case.
     */
    protected function setUp(): void
    {
        parent::setUp();
        $this->user = User::factory()->create();
        $this->project = Project::factory()->for($this->user)->create();
        $this->task = Task::factory()->for($this->project)->create();
    }

    /**
     * Test user can view task comments.
     */
    public function test_user_can_view_task_comments(): void
    {
        Comment::factory(5)->for($this->task)->create();

        $response = $this->actingAs($this->user)
            ->getJson("/api/projects/{$this->project->id}/tasks/{$this->task->id}/comments");

        $response->assertOk()
            ->assertJsonCount(5, 'data');
    }

    /**
     * Test user can create comment.
     */
    public function test_user_can_create_comment(): void
    {
        $response = $this->actingAs($this->user)
            ->postJson("/api/projects/{$this->project->id}/tasks/{$this->task->id}/comments", [
                'content' => 'This is a test comment',
            ]);

        $response->assertCreated()
            ->assertJsonStructure([
                'success',
                'data' => ['id', 'task_id', 'user_id', 'content', 'user', 'created_at'],
            ]);
    }

    /**
     * Test user can update their comment.
     */
    public function test_user_can_update_their_comment(): void
    {
        $comment = Comment::factory()->for($this->task)->for($this->user)->create();

        $response = $this->actingAs($this->user)
            ->patchJson("/api/comments/{$comment->id}", [
                'content' => 'Updated comment text',
            ]);

        $response->assertOk()
            ->assertJsonPath('data.content', 'Updated comment text');
    }

    /**
     * Test user cannot update other user's comment.
     */
    public function test_user_cannot_update_other_users_comment(): void
    {
        $otherUser = User::factory()->create();
        $comment = Comment::factory()->for($this->task)->for($otherUser)->create();

        $response = $this->actingAs($this->user)
            ->patchJson("/api/comments/{$comment->id}", [
                'content' => 'Updated comment',
            ]);

        $response->assertForbidden();
    }

    /**
     * Test user can delete their comment.
     */
    public function test_user_can_delete_their_comment(): void
    {
        $comment = Comment::factory()->for($this->task)->for($this->user)->create();

        $response = $this->actingAs($this->user)
            ->deleteJson("/api/comments/{$comment->id}");

        $response->assertOk();
        $this->assertDatabaseMissing('comments', ['id' => $comment->id]);
    }

    /**
     * Test comment creation validation.
     */
    public function test_comment_creation_validation(): void
    {
        $response = $this->actingAs($this->user)
            ->postJson("/api/projects/{$this->project->id}/tasks/{$this->task->id}/comments", [
                'content' => '',
            ]);

        $response->assertUnprocessable();
    }
}