<?php

namespace App\Services;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

class ImageOptimizationService
{
    private const MAX_WIDTH = 1920;

    private const MAX_HEIGHT = 1920;

    private const JPEG_QUALITY = 85;

    /**
     * Optimize an uploaded image: resize if too large, compress.
     * Returns the stored path on the given disk.
     */
    public function optimizeAndStore(UploadedFile $file, string $directory, string $disk = 'public'): string
    {
        $extension = strtolower($file->getClientOriginalExtension());
        $mime = $file->getMimeType();

        if (! $this->isProcessable($extension, $mime)) {
            return $file->store($directory, $disk);
        }

        if (! extension_loaded('gd')) {
            return $file->store($directory, $disk);
        }

        $image = $this->loadImage($file, $extension);
        if (! $image) {
            return $file->store($directory, $disk);
        }

        $resized = $this->resizeIfNeeded($image, $extension);
        imagedestroy($image);

        if (! $resized) {
            return $file->store($directory, $disk);
        }

        $filename = uniqid('img_', true) . '.jpg';
        $fullPath = rtrim($directory, '/') . '/' . $filename;

        $tempPath = sys_get_temp_dir() . '/' . $filename;
        imagejpeg($resized, $tempPath, self::JPEG_QUALITY);
        imagedestroy($resized);

        Storage::disk($disk)->put($fullPath, file_get_contents($tempPath));
        @unlink($tempPath);

        return $fullPath;
    }

    private function isProcessable(string $ext, string $mime): bool
    {
        return in_array($ext, ['jpg', 'jpeg', 'png']) && in_array($mime, ['image/jpeg', 'image/png']);
    }

    private function loadImage(UploadedFile $file, string $ext): \GdImage|false
    {
        $path = $file->getRealPath();
        return match ($ext) {
            'jpg', 'jpeg' => @imagecreatefromjpeg($path),
            'png' => @imagecreatefrompng($path),
            default => false,
        };
    }

    private function resizeIfNeeded(\GdImage $image, string $ext): \GdImage|false
    {
        $w = imagesx($image);
        $h = imagesy($image);

        if ($w <= self::MAX_WIDTH && $h <= self::MAX_HEIGHT) {
            $out = imagecreatetruecolor($w, $h);
            if (! $out) {
                return false;
            }
            imagecopy($out, $image, 0, 0, 0, 0, $w, $h);
            return $out;
        }

        $ratio = min(self::MAX_WIDTH / $w, self::MAX_HEIGHT / $h);
        $nw = (int) round($w * $ratio);
        $nh = (int) round($h * $ratio);

        $out = imagecreatetruecolor($nw, $nh);
        if (! $out) {
            return false;
        }

        imagecopyresampled($out, $image, 0, 0, 0, 0, $nw, $nh, $w, $h);
        return $out;
    }
}
