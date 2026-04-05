<?php

namespace Tests\Feature;

use App\Models\Asset;
use App\Models\User;
use App\Services\SignedUrlService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class SignedUrlTest extends TestCase
{
    use RefreshDatabase;

    protected User $user;
    protected Asset $asset;
    protected SignedUrlService $signedUrlService;

    protected function setUp(): void
    {
        parent::setUp();
        Storage::fake('local');
        $this->user = User::factory()->create();
        $this->asset = Asset::factory()->create(['user_id' => $this->user->id]);
        $this->signedUrlService = new SignedUrlService();
    }

    protected function actingAsWithToken($user)
    {
        $token = $user->createToken('test-token')->plainTextToken;
        return $this->withHeader('Authorization', "Bearer $token");
    }

    public function test_generate_signed_url()
    {
        $signedUrl = $this->signedUrlService->generateSignedAssetUrl($this->asset, 60);

        $this->assertArrayHasKey('url', $signedUrl);
        $this->assertArrayHasKey('expires_at', $signedUrl);
        $this->assertArrayHasKey('expires_in_minutes', $signedUrl);
        $this->assertEquals(60, $signedUrl['expires_in_minutes']);
        $this->assertStringContainsString('/signed', $signedUrl['url']);
    }

    public function test_validate_signed_url_valid()
    {
        $signedUrl = $this->signedUrlService->generateSignedAssetUrl($this->asset, 60);
        preg_match('/token=([^&]+)/', $signedUrl['url'], $matches);
        $token = urldecode($matches[1]);

        $validatedAsset = $this->signedUrlService->validateSignedUrl($token);

        $this->assertNotNull($validatedAsset);
        $this->assertEquals($this->asset->id, $validatedAsset->id);
    }

    public function test_validate_signed_url_invalid_token()
    {
        $validatedAsset = $this->signedUrlService->validateSignedUrl('invalid-token');

        $this->assertNull($validatedAsset);
    }

    public function test_validate_signed_url_tampered_token()
    {
        $signedUrl = $this->signedUrlService->generateSignedAssetUrl($this->asset, 60);
        preg_match('/token=([^&]+)/', $signedUrl['url'], $matches);
        $token = urldecode($matches[1]);

        $decoded = json_decode(base64_decode($token), true);
        $decoded['asset_id'] = 99999;
        $tamperedToken = base64_encode(json_encode($decoded));

        $validatedAsset = $this->signedUrlService->validateSignedUrl($tamperedToken);

        $this->assertNull($validatedAsset);
    }

    public function test_api_generate_signed_url_endpoint()
    {
        $response = $this->actingAsWithToken($this->user)
            ->getJson("/api/assets/{$this->asset->id}/signed-url?expires_in=120");

        $response->assertStatus(200);
        $response->assertJsonStructure([
            'url',
            'expires_at',
            'expires_in_minutes',
        ]);
    }

    public function test_api_generate_signed_url_requires_authentication()
    {
        $response = $this->getJson("/api/assets/{$this->asset->id}/signed-url");

        $response->assertStatus(401);
    }

    public function test_api_signed_download_with_valid_token()
    {
        Storage::disk('local')->put($this->asset->path, 'fake-content');

        $signedUrl = $this->signedUrlService->generateSignedAssetUrl($this->asset, 60);

        $response = $this->get($signedUrl['url']);

        $response->assertStatus(200);
        $response->assertHeader('Content-Disposition', 'attachment; filename="' . $this->asset->original_name . '"');
    }

    public function test_api_signed_download_without_token()
    {
        $response = $this->getJson("/api/assets/{$this->asset->id}/signed");

        $response->assertStatus(401);
    }

    public function test_api_signed_download_with_invalid_token()
    {
        $response = $this->getJson("/api/assets/{$this->asset->id}/signed?token=invalid");

        $response->assertStatus(403);
    }

    public function test_signed_url_expires_in_specified_time()
    {
        $signedUrl = $this->signedUrlService->generateSignedAssetUrl($this->asset, 5);

        $this->assertEquals(5, $signedUrl['expires_in_minutes']);
    }

    public function test_signed_url_max_expiry_is_43200_minutes()
    {
        $response = $this->actingAsWithToken($this->user)
            ->getJson("/api/assets/{$this->asset->id}/signed-url?expires_in=999999");

        $response->assertStatus(200);
        $this->assertLessThanOrEqual(43200, $response->json('expires_in_minutes'));
    }
}
