<?php

namespace Tests\Feature;

use App\Models\Asset;
use App\Models\Topic;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class AssetUploadTest extends TestCase
{
    use RefreshDatabase;

    protected User $user;
    protected Topic $topic;

    protected function setUp(): void
    {
        parent::setUp();
        Storage::fake('local');
        $this->user = User::factory()->create();
        $this->topic = Topic::factory()->create(['user_id' => $this->user->id]);
    }

    protected function actingAsWithToken($user)
    {
        $token = $user->createToken('test-token')->plainTextToken;
        return $this->withHeader('Authorization', "Bearer $token");
    }

    // ===== UPLOAD TESTS =====

    public function test_upload_image_successfully()
    {
        $file = UploadedFile::fake()->image('test.jpg', 800, 600);

        $response = $this->actingAsWithToken($this->user)
            ->postJson('/api/assets', [
                'file' => $file,
                'topic_id' => $this->topic->id,
            ]);

        $response->assertStatus(201);
        $response->assertJsonStructure([
            'id', 'url', 'original_name', 'mime_type', 'size', 'is_image'
        ]);
        $this->assertDatabaseHas('assets', ['original_name' => 'test.jpg']);
    }

    public function test_upload_pdf_successfully()
    {
        $file = UploadedFile::fake()->create('document.pdf', 1024, 'application/pdf');

        $response = $this->actingAsWithToken($this->user)
            ->postJson('/api/assets', [
                'file' => $file,
            ]);

        $response->assertStatus(201);
        $response->assertJson(['is_image' => false]);
    }

    public function test_upload_requires_authentication()
    {
        $file = UploadedFile::fake()->image('test.jpg');

        $response = $this->postJson('/api/assets', [
            'file' => $file,
        ]);

        $response->assertStatus(401);
    }

    public function test_upload_rejects_invalid_mime_type()
    {
        $file = UploadedFile::fake()->create('document.exe', 1024, 'application/x-msdownload');

        $response = $this->actingAsWithToken($this->user)
            ->postJson('/api/assets', [
                'file' => $file,
            ]);

        $response->assertStatus(422);
        $response->assertJsonFragment(['message' => 'Tipo de arquivo não permitido. Use: JPEG, PNG, GIF, WebP ou PDF.']);
    }

    public function test_upload_rejects_file_too_large()
    {
        $file = UploadedFile::fake()->create('large.jpg', 15000);

        $response = $this->actingAsWithToken($this->user)
            ->postJson('/api/assets', [
                'file' => $file,
            ]);

        $response->assertStatus(422);
        $response->assertJsonFragment(['message' => 'Arquivo muito grande. Máximo: 10MB.']);
    }

    public function test_upload_validates_topic_exists()
    {
        $file = UploadedFile::fake()->image('test.jpg');

        $response = $this->actingAsWithToken($this->user)
            ->postJson('/api/assets', [
                'file' => $file,
                'topic_id' => 99999,
            ]);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['topic_id']);
    }

    // ===== DOWNLOAD TESTS =====

    public function test_download_asset_successfully()
    {
        $asset = Asset::factory()->create([
            'user_id' => $this->user->id,
            'topic_id' => $this->topic->id,
            'mime_type' => 'image/jpeg',
            'size' => 1024,
        ]);

        Storage::disk('local')->put($asset->path, 'fake-image-content');

        $response = $this->getJson("/api/assets/{$asset->id}");

        $response->assertStatus(200);
        $response->assertHeader('Content-Type', 'image/jpeg');
    }

    public function test_download_returns_404_for_missing_file()
    {
        $asset = Asset::factory()->create([
            'user_id' => $this->user->id,
            'path' => 'nonexistent/path.jpg',
        ]);

        $response = $this->getJson("/api/assets/{$asset->id}");

        $response->assertStatus(404);
    }

    // ===== DELETE TESTS =====

    public function test_delete_asset_successfully()
    {
        $asset = Asset::factory()->create([
            'user_id' => $this->user->id,
        ]);
        Storage::disk('local')->put($asset->path, 'content');

        $response = $this->actingAsWithToken($this->user)
            ->deleteJson("/api/assets/{$asset->id}");

        $response->assertStatus(200);
        $this->assertDatabaseMissing('assets', ['id' => $asset->id]);
    }

    public function test_delete_asset_requires_authentication()
    {
        $asset = Asset::factory()->create(['user_id' => $this->user->id]);

        $response = $this->deleteJson("/api/assets/{$asset->id}");

        $response->assertStatus(401);
    }

    public function test_delete_asset_validates_ownership()
    {
        $otherUser = User::factory()->create();
        $asset = Asset::factory()->create(['user_id' => $otherUser->id]);

        $response = $this->actingAsWithToken($this->user)
            ->deleteJson("/api/assets/{$asset->id}");

        $response->assertStatus(403);
        $this->assertDatabaseHas('assets', ['id' => $asset->id]);
    }

    // ===== BY TOPIC TESTS =====

    public function test_get_assets_by_topic()
    {
        $assets = Asset::factory()->count(3)->create([
            'topic_id' => $this->topic->id,
        ]);

        $response = $this->actingAsWithToken($this->user)
            ->getJson("/api/topics/{$this->topic->id}/assets");

        $response->assertStatus(200);
        $this->assertCount(3, $response->json());
    }

    // ===== ALLOWED TYPES TESTS =====

    public function test_get_allowed_types()
    {
        $response = $this->getJson('/api/assets/allowed-types');

        $response->assertStatus(200);
        $response->assertJsonStructure([
            'allowed_mimes',
            'allowed_extensions',
            'max_size',
            'max_size_formatted',
        ]);
        $this->assertEquals('10 MB', $response->json('max_size_formatted'));
    }

    // ===== UPLOAD INLINE TESTS =====

    public function test_upload_inline_requires_authentication()
    {
        $file = UploadedFile::fake()->image('inline.jpg');

        $response = $this->postJson('/api/assets/upload-inline', [
            'file' => $file,
        ]);

        $response->assertStatus(401);
    }

    public function test_upload_inline_image_successfully()
    {
        $file = UploadedFile::fake()->image('inline.jpg', 800, 600);

        $response = $this->actingAsWithToken($this->user)
            ->postJson('/api/assets/upload-inline', [
                'file' => $file,
            ]);

        $response->assertStatus(201);
        $response->assertJsonStructure([
            'id', 'url', 'original_name', 'mime_type', 'size', 'is_image'
        ]);
        $this->assertDatabaseHas('assets', ['original_name' => 'inline.jpg']);
    }

    public function test_upload_inline_rejects_invalid_mime_type()
    {
        $file = UploadedFile::fake()->create('document.exe', 1024, 'application/x-msdownload');

        $response = $this->actingAsWithToken($this->user)
            ->postJson('/api/assets/upload-inline', [
                'file' => $file,
            ]);

        $response->assertStatus(422);
    }

    public function test_upload_inline_rejects_file_too_large()
    {
        $file = UploadedFile::fake()->create('large.jpg', 6000); // 6MB arquivo original

        $response = $this->actingAsWithToken($this->user)
            ->postJson('/api/assets/upload-inline', [
                'file' => $file,
            ]);

        $response->assertStatus(422);
    }
}
