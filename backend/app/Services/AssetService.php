<?php

namespace App\Services;

use App\Models\Asset;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class AssetService
{
    private const ALLOWED_MIMES = [
        'image/jpeg',
        'image/png',
        'image/gif',
        'image/webp',
        'application/pdf',
    ];

    private const MAX_SIZE = 10 * 1024 * 1024;

    private ImageCompressorService $compressor;

    public function __construct(ImageCompressorService $compressor)
    {
        $this->compressor = $compressor;
    }

    public function upload(UploadedFile $file, int $userId, ?int $topicId = null): Asset
    {
        $this->validateFile($file);

        $originalName = $file->getClientOriginalName();
        $storedName = Str::uuid() . '.' . $file->getClientOriginalExtension();
        $mimeType = $file->getMimeType();
        $isImage = Str::startsWith($mimeType, 'image/');

        $path = $this->getStoragePath($topicId);
        $disk = config('filesystems.default', 'local');

        $fileToStore = $file;
        $size = $file->getSize();

        if ($isImage) {
            $fileToStore = $this->compressor->compress($file);
            $size = $fileToStore->getSize();
        }

        Storage::disk($disk)->putFileAs($path, $fileToStore, $storedName);

        if ($fileToStore !== $file && file_exists($fileToStore->getPathname())) {
            @unlink($fileToStore->getPathname());
        }

        return Asset::create([
            'user_id' => $userId,
            'topic_id' => $topicId,
            'original_name' => $originalName,
            'stored_name' => $storedName,
            'mime_type' => $mimeType,
            'size' => $size,
            'path' => $path . '/' . $storedName,
            'disk' => $disk,
            'is_image' => $isImage,
        ]);
    }

    public function delete(Asset $asset): bool
    {
        Storage::disk($asset->disk)->delete($asset->path);

        return $asset->delete();
    }

    public function uploadInlineImage(UploadedFile $file, int $userId): Asset
    {
        $allowedMimes = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
        
        if (!in_array($file->getMimeType(), $allowedMimes)) {
            throw new \InvalidArgumentException(
                'Tipo de imagem não permitido. Use: JPEG, PNG, GIF ou WebP.'
            );
        }

        $maxSize = 5 * 1024 * 1024;
        if ($file->getSize() > $maxSize) {
            throw new \InvalidArgumentException(
                'Imagem muito grande. Máximo: 5MB.'
            );
        }

        $originalName = $file->getClientOriginalName();
        $extension = $file->getClientOriginalExtension() ?: 'png';
        $storedName = Str::uuid() . '.' . $extension;
        $mimeType = $file->getMimeType();

        $path = 'assets/inline/' . date('Y/m');
        $disk = config('filesystems.default', 'local');

        $fileToStore = $this->compressor->compressForInline($file);
        $size = $fileToStore->getSize();

        Storage::disk($disk)->putFileAs($path, $fileToStore, $storedName);

        if ($fileToStore !== $file && file_exists($fileToStore->getPathname())) {
            @unlink($fileToStore->getPathname());
        }

        return Asset::create([
            'user_id' => $userId,
            'topic_id' => null,
            'original_name' => $originalName,
            'stored_name' => $storedName,
            'mime_type' => $mimeType,
            'size' => $size,
            'path' => $path . '/' . $storedName,
            'disk' => $disk,
            'is_image' => true,
        ]);
    }

    public function getStream(Asset $asset)
    {
        return Storage::disk($asset->disk)->readStream($asset->path);
    }

    public function exists(Asset $asset): bool
    {
        return Storage::disk($asset->disk)->exists($asset->path);
    }

    private function validateFile(UploadedFile $file): void
    {
        if (!in_array($file->getMimeType(), self::ALLOWED_MIMES)) {
            throw new \InvalidArgumentException(
                'Tipo de arquivo não permitido. Use: JPEG, PNG, GIF, WebP ou PDF.'
            );
        }

        if ($file->getSize() > self::MAX_SIZE) {
            throw new \InvalidArgumentException(
                'Arquivo muito grande. Máximo: 10MB.'
            );
        }
    }

    private function getStoragePath(?int $topicId): string
    {
        $base = 'assets';
        if ($topicId) {
            return $base . '/topics/' . $topicId;
        }
        return $base . '/general';
    }

    public static function getAllowedMimes(): array
    {
        return self::ALLOWED_MIMES;
    }

    public static function getAllowedExtensions(): array
    {
        return ['jpg', 'jpeg', 'png', 'gif', 'webp', 'pdf'];
    }

    public static function getMaxSize(): int
    {
        return self::MAX_SIZE;
    }
}
