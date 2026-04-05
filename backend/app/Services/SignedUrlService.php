<?php

namespace App\Services;

use App\Models\Asset;

class SignedUrlService
{
    private const DEFAULT_EXPIRY_MINUTES = 60;

    public function generateSignedAssetUrl(Asset $asset, int $expiresInMinutes = null): array
    {
        $expiresInMinutes = $expiresInMinutes ?? self::DEFAULT_EXPIRY_MINUTES;
        $expiresAt = now()->addMinutes($expiresInMinutes);

        $signature = $this->generateSignature($asset->id, $expiresAt);
        $token = $this->generateToken($asset->id, $expiresAt, $signature);

        $url = url("/api/assets/{$asset->id}/signed?token=" . urlencode($token));

        return [
            'url' => $url,
            'expires_at' => $expiresAt->toIso8601String(),
            'expires_in_minutes' => $expiresInMinutes,
        ];
    }

    public function validateSignedUrl(string $token): ?Asset
    {
        $data = $this->decodeToken($token);

        if (!$data) {
            return null;
        }

        [$assetId, $expiresAt, $signature] = $data;

        if (now()->greaterThan($expiresAt)) {
            return null;
        }

        $expectedSignature = $this->generateSignature($assetId, $expiresAt);

        if (!hash_equals($expectedSignature, $signature)) {
            return null;
        }

        return Asset::find($assetId);
    }

    public function generateShareableLink(Asset $asset, int $expiresInMinutes = 1440): array
    {
        return $this->generateSignedAssetUrl($asset, $expiresInMinutes);
    }

    public function getDefaultExpiryMinutes(): int
    {
        return self::DEFAULT_EXPIRY_MINUTES;
    }

    private function generateSignature(int|string $assetId, $expiresAt): string
    {
        $data = "asset:{$assetId}|expires:{$expiresAt->timestamp}";
        $secret = config('app.key');

        return hash_hmac('sha256', $data, $secret);
    }

    private function generateToken(int|string $assetId, $expiresAt, string $signature): string
    {
        $payload = [
            'asset_id' => $assetId,
            'expires_at' => $expiresAt->timestamp,
            'signature' => $signature,
        ];

        return base64_encode(json_encode($payload));
    }

    private function decodeToken(string $token): ?array
    {
        try {
            $decoded = base64_decode($token);
            if (!$decoded) {
                return null;
            }

            $payload = json_decode($decoded, true);
            if (!$payload) {
                return null;
            }

            if (!isset($payload['asset_id'], $payload['expires_at'], $payload['signature'])) {
                return null;
            }

            $expiresAt = \Carbon\Carbon::createFromTimestamp($payload['expires_at']);

            return [
                $payload['asset_id'],
                $expiresAt,
                $payload['signature'],
            ];
        } catch (\Exception $e) {
            return null;
        }
    }
}
