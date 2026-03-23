<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Notification;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class NotificationTest extends TestCase
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
     * Test user can view all notifications.
     */
    public function test_user_can_view_all_notifications(): void
    {
        Notification::factory(5)->for($this->user)->create();

        $response = $this->actingAs($this->user)
            ->getJson('/api/notifications');

        $response->assertOk()
            ->assertJsonCount(5, 'data');
    }

    /**
     * Test user can view unread notifications.
     */
    public function test_user_can_view_unread_notifications(): void
    {
        Notification::factory(3)->for($this->user)->create(['read_at' => null]);
        Notification::factory(2)->for($this->user)->read()->create();

        $response = $this->actingAs($this->user)
            ->getJson('/api/notifications/unread');

        $response->assertOk()
            ->assertJsonCount(3, 'data');
    }

    /**
     * Test user can mark notification as read.
     */
    public function test_user_can_mark_notification_as_read(): void
    {
        $notification = Notification::factory()->for($this->user)->create(['read_at' => null]);

        $response = $this->actingAs($this->user)
            ->patchJson("/api/notifications/{$notification->id}/read");

        $response->assertOk()
            ->assertJsonPath('data.read_at', $notification->fresh()->read_at?->toIso8601String());
    }

    /**
     * Test notification pagination.
     */
    public function test_notification_pagination(): void
    {
        Notification::factory(30)->for($this->user)->create();

        $response = $this->actingAs($this->user)
            ->getJson('/api/notifications?per_page=15&page=1');

        $response->assertOk()
            ->assertJsonCount(15, 'data')
            ->assertJsonPath('meta.total', 30)
            ->assertJsonPath('meta.per_page', 15);
    }
}