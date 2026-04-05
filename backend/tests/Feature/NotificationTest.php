<?php

namespace Tests\Feature;

use App\Models\Notification;
use App\Models\Topic;
use App\Models\User;
use App\Models\Work;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class NotificationTest extends TestCase
{
    use RefreshDatabase;

    protected User $user;
    protected User $otherUser;

    protected function setUp(): void
    {
        parent::setUp();
        $this->user = User::factory()->create();
        $this->otherUser = User::factory()->create();
    }

    protected function actingAsWithToken($user)
    {
        $token = $user->createToken('test-token')->plainTextToken;
        return $this->withHeader('Authorization', "Bearer $token");
    }

    public function test_get_notifications_returns_user_notifications()
    {
        Notification::factory()->count(3)->create([
            'user_id' => $this->user->id,
            'is_read' => false
        ]);
        Notification::factory()->count(2)->create(['user_id' => $this->otherUser->id]);

        $response = $this->actingAsWithToken($this->user)
            ->getJson('/api/notifications');

        $response->assertStatus(200);
        $response->assertJsonStructure([
            'notifications' => [
                '*' => ['id', 'type', 'title', 'message', 'is_read', 'created_at']
            ],
            'unread_count'
        ]);
        $this->assertCount(3, $response->json('notifications'));
        $this->assertEquals(3, $response->json('unread_count'));
    }

    public function test_get_notifications_requires_authentication()
    {
        $response = $this->getJson('/api/notifications');

        $response->assertStatus(401);
    }

    public function test_get_unread_count()
    {
        Notification::factory()->count(5)->create([
            'user_id' => $this->user->id,
            'is_read' => false
        ]);
        Notification::factory()->count(2)->create([
            'user_id' => $this->user->id,
            'is_read' => true
        ]);

        $response = $this->actingAsWithToken($this->user)
            ->getJson('/api/notifications/unread-count');

        $response->assertStatus(200);
        $response->assertJson(['count' => 5]);
    }

    public function test_mark_notification_as_read()
    {
        $notification = Notification::factory()->create([
            'user_id' => $this->user->id,
            'is_read' => false
        ]);

        $response = $this->actingAsWithToken($this->user)
            ->postJson("/api/notifications/{$notification->id}/read");

        $response->assertStatus(200);
        $response->assertJson(['success' => true]);
        
        $notification->refresh();
        $this->assertTrue($notification->is_read);
        $this->assertNotNull($notification->read_at);
    }

    public function test_cannot_mark_other_user_notification_as_read()
    {
        $notification = Notification::factory()->create([
            'user_id' => $this->otherUser->id,
            'is_read' => false
        ]);

        $response = $this->actingAsWithToken($this->user)
            ->postJson("/api/notifications/{$notification->id}/read");

        $response->assertStatus(404);
    }

    public function test_mark_all_notifications_as_read()
    {
        Notification::factory()->count(3)->create([
            'user_id' => $this->user->id,
            'is_read' => false
        ]);

        $response = $this->actingAsWithToken($this->user)
            ->postJson('/api/notifications/read-all');

        $response->assertStatus(200);
        $response->assertJson(['success' => true]);
        
        $unreadCount = Notification::where('user_id', $this->user->id)
            ->where('is_read', false)
            ->count();
        $this->assertEquals(0, $unreadCount);
    }

    public function test_delete_notification()
    {
        $notification = Notification::factory()->create([
            'user_id' => $this->user->id
        ]);

        $response = $this->actingAsWithToken($this->user)
            ->deleteJson("/api/notifications/{$notification->id}");

        $response->assertStatus(200);
        $this->assertDatabaseMissing('notifications', ['id' => $notification->id]);
    }

    public function test_cannot_delete_other_user_notification()
    {
        $notification = Notification::factory()->create([
            'user_id' => $this->otherUser->id
        ]);

        $response = $this->actingAsWithToken($this->user)
            ->deleteJson("/api/notifications/{$notification->id}");

        $response->assertStatus(404);
        $this->assertDatabaseHas('notifications', ['id' => $notification->id]);
    }

    public function test_notification_generated_on_new_comment()
    {
        $work = Work::factory()->create();
        $topic = Topic::factory()->create([
            'work_id' => $work->id,
            'user_id' => $this->user->id
        ]);

        $this->actingAsWithToken($this->otherUser)
            ->postJson("/api/topics/{$topic->id}/interactions", [
                'content' => 'This is a test comment'
            ]);

        $this->assertDatabaseHas('notifications', [
            'user_id' => $this->user->id,
            'type' => 'new_comment',
        ]);
    }
}
