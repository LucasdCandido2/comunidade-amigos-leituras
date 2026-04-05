<?php

namespace App\Services;

use Illuminate\Http\UploadedFile;
use Intervention\Image\ImageManager;
use Intervention\Image\Drivers\Gd\Driver;
use Illuminate\Support\Str;

class ImageCompressorService
{
    private const MAX_WIDTH = 1920;
    private const MAX_HEIGHT = 1080;
    private const JPEG_QUALITY = 80;
    private const WEBP_QUALITY = 80;

    private const INLINE_MAX_WIDTH = 800;
    private const INLINE_MAX_HEIGHT = 600;
    private const INLINE_JPEG_QUALITY = 75;
    private const INLINE_WEBP_QUALITY = 75;

    private static ?bool $webpSupported = null;

    public static function isWebpSupported(): bool
    {
        if (self::$webpSupported === null) {
            self::$webpSupported = function_exists('imagecreatefromwebp') && function_exists('imagewebp');
        }
        return self::$webpSupported;
    }

    public function compress(UploadedFile $file): UploadedFile
    {
        $mimeType = $file->getMimeType();

        if (!Str::startsWith($mimeType, 'image/')) {
            return $file;
        }

        if ($mimeType === 'image/webp' && !self::isWebpSupported()) {
            return $this->convertWebpToJpeg($file);
        }

        try {
            $manager = new ImageManager(new Driver());
            $image = $manager->read($file->getPathname());

            $width = $image->width();
            $height = $image->height();

            if ($width > self::MAX_WIDTH || $height > self::MAX_HEIGHT) {
                if ($width > $height) {
                    $image->resize(width: self::MAX_WIDTH);
                } else {
                    $image->resize(height: self::MAX_HEIGHT);
                }
            }

            $extension = $this->getSafeExtension($mimeType, $file->getClientOriginalExtension());
            $tempPath = tempnam(sys_get_temp_dir(), 'img_') . '.' . $extension;

            $this->saveImage($image, $mimeType, $tempPath);

            return new UploadedFile(
                $tempPath,
                $file->getClientOriginalName(),
                $mimeType,
                null,
                true
            );
        } catch (\Exception $e) {
            return $file;
        }
    }

    public function compressForInline(UploadedFile $file): UploadedFile
    {
        $mimeType = $file->getMimeType();

        if (!Str::startsWith($mimeType, 'image/')) {
            return $file;
        }

        if ($mimeType === 'image/webp' && !self::isWebpSupported()) {
            return $this->convertWebpToJpeg($file);
        }

        try {
            $manager = new ImageManager(new Driver());
            $image = $manager->read($file->getPathname());

            $width = $image->width();
            $height = $image->height();

            if ($width > self::INLINE_MAX_WIDTH || $height > self::INLINE_MAX_HEIGHT) {
                if ($width > $height) {
                    $image->resize(width: self::INLINE_MAX_WIDTH);
                } else {
                    $image->resize(height: self::INLINE_MAX_HEIGHT);
                }
            }

            $extension = $this->getSafeExtension($mimeType, $file->getClientOriginalExtension());
            $tempPath = tempnam(sys_get_temp_dir(), 'img_inline_') . '.' . $extension;

            $this->saveImage($image, $mimeType, $tempPath);

            return new UploadedFile(
                $tempPath,
                $file->getClientOriginalName(),
                $mimeType,
                null,
                true
            );
        } catch (\Exception $e) {
            return $file;
        }
    }

    private function convertWebpToJpeg(UploadedFile $file): UploadedFile
    {
        try {
            $image = imagecreatefromstring(file_get_contents($file->getPathname()));
            
            if (!$image) {
                return $file;
            }

            $width = imagesx($image);
            $height = imagesy($image);

            $maxWidth = self::INLINE_MAX_WIDTH;
            $maxHeight = self::INLINE_MAX_HEIGHT;

            if ($width > $maxWidth || $height > $maxHeight) {
                $ratio = min($maxWidth / $width, $maxHeight / $height);
                $newWidth = (int)($width * $ratio);
                $newHeight = (int)($height * $ratio);

                $resized = imagecreatetruecolor($newWidth, $newHeight);
                imagecopyresampled($resized, $image, 0, 0, 0, 0, $newWidth, $newHeight, $width, $height);
                imagedestroy($image);
                $image = $resized;
            }

            $tempPath = tempnam(sys_get_temp_dir(), 'webp_convert_') . '.jpg';
            imagejpeg($image, $tempPath, self::INLINE_JPEG_QUALITY);
            imagedestroy($image);

            return new UploadedFile(
                $tempPath,
                $file->getClientOriginalName(),
                'image/jpeg',
                null,
                true
            );
        } catch (\Exception $e) {
            return $file;
        }
    }

    private function saveImage($image, string $mimeType, string $tempPath): void
    {
        if ($mimeType === 'image/webp' && !self::isWebpSupported()) {
            $image->toJpeg(quality: self::JPEG_QUALITY)->save($tempPath);
            return;
        }

        match ($mimeType) {
            'image/jpeg', 'image/jpg' => $image->toJpeg(quality: self::JPEG_QUALITY)->save($tempPath),
            'image/png' => $image->toPng()->save($tempPath),
            'image/gif' => $image->toGif()->save($tempPath),
            'image/webp' => $image->toWebp(quality: self::WEBP_QUALITY)->save($tempPath),
            default => copy($image->getCore()->base(), $tempPath),
        };
    }

    private function getSafeExtension(string $mimeType, ?string $originalExtension): string
    {
        if ($mimeType === 'image/webp' && !self::isWebpSupported()) {
            return 'jpg';
        }

        $fallbackExtensions = [
            'image/jpeg' => 'jpg',
            'image/png' => 'png',
            'image/gif' => 'gif',
            'image/webp' => 'jpg',
        ];

        return $originalExtension ?: ($fallbackExtensions[$mimeType] ?? 'jpg');
    }

    public function getCompressedSize(UploadedFile $file): int
    {
        try {
            $compressed = $this->compress($file);
            $size = $compressed->getSize();
            
            if ($compressed->getPathname() !== $file->getPathname() && file_exists($compressed->getPathname())) {
                @unlink($compressed->getPathname());
            }
            
            return $size;
        } catch (\Exception $e) {
            return $file->getSize();
        }
    }
}