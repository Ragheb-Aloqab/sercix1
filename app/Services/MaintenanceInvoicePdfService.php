<?php

namespace App\Services;

use App\Models\Attachment;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\Storage;

class MaintenanceInvoicePdfService
{
    public const MAX_IMAGE_SIZE_BYTES = 4 * 1024 * 1024; // 4 MB

    /**
     * Generate a high-quality CamScanner-style PDF containing only the image.
     * Full-page A4, centered, optimized for readability.
     */
    public function generateFromImage(string $imageStoragePath): string
    {
        return $this->generateFromImageOnDisk($imageStoragePath, 'public');
    }

    /**
     * Generate CamScanner-style PDF from image stored on any disk.
     * Images over 4 MB are compressed before conversion.
     */
    public function generateFromImageOnDisk(string $imageStoragePath, string $disk = 'public'): string
    {
        $imageStoragePath = ltrim($imageStoragePath, '/');
        $fullPath = Storage::disk($disk)->path($imageStoragePath);
        if (!file_exists($fullPath)) {
            throw new \RuntimeException('Image file not found: ' . $imageStoragePath);
        }

        $this->compressImageIfNeeded($fullPath, $disk, $imageStoragePath);

        $mime = match (strtolower(pathinfo($fullPath, PATHINFO_EXTENSION))) {
            'jpg', 'jpeg' => 'image/jpeg',
            'png' => 'image/png',
            'gif' => 'image/gif',
            'webp' => 'image/webp',
            default => 'image/jpeg',
        };
        $base64 = 'data:' . $mime . ';base64,' . base64_encode(file_get_contents($fullPath));

        $html = $this->buildHtml($base64);

        $pdf = Pdf::loadHTML($html)
            ->setPaper('a4', 'portrait')
            ->setOption('isHtml5ParserEnabled', true)
            ->setOption('isRemoteEnabled', false)
            ->setOption('isFontSubsettingEnabled', true)
            ->setOption('defaultFont', 'sans-serif');

        return $pdf->output();
    }

    /**
     * Convert image to PDF and save to storage. Returns the storage path.
     * Deletes the original image after conversion.
     */
    public function convertImageToPdfAndSave(string $imagePath, string $disk = 'private'): string
    {
        $ext = strtolower(pathinfo($imagePath, PATHINFO_EXTENSION));
        if (!in_array($ext, ['jpg', 'jpeg', 'png', 'gif', 'webp'])) {
            throw new \RuntimeException('File is not an image: ' . $imagePath);
        }

        $pdfContent = $this->generateFromImageOnDisk($imagePath, $disk);

        $dir = dirname($imagePath);
        $pdfPath = $dir . '/invoice-' . pathinfo($imagePath, PATHINFO_FILENAME) . '.pdf';

        Storage::disk($disk)->put($pdfPath, $pdfContent);
        Storage::disk($disk)->delete($imagePath);

        return $pdfPath;
    }

    /**
     * Generate PDF and save to storage. Returns the storage path.
     */
    public function generateAndSave(Attachment $attachment): string
    {
        $pdfContent = $this->generateFromImage($attachment->file_path);

        $dir = 'maintenance-invoice-pdfs/' . $attachment->order_id;
        $filename = 'invoice-' . $attachment->id . '.pdf';
        $path = $dir . '/' . $filename;

        Storage::disk('public')->put($path, $pdfContent);

        return $path;
    }

    private function buildHtml(string $imageDataUri): string
    {
        return <<<HTML
<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <style>
        * { margin: 0; padding: 0; }
        body, html { width: 100%; height: 100%; }
        .page {
            width: 210mm;
            height: 297mm;
            text-align: center;
        }
        .page table {
            width: 100%;
            height: 297mm;
            border-collapse: collapse;
        }
        .page td {
            vertical-align: middle;
            text-align: center;
            padding: 10mm;
        }
        .page img {
            max-width: 190mm;
            max-height: 277mm;
            width: auto;
            height: auto;
            display: block;
            margin-left: auto;
            margin-right: auto;
        }
    </style>
</head>
<body>
    <div class="page">
        <table><tr><td>
            <img src="{$imageDataUri}" alt="" />
        </td></tr></table>
    </div>
</body>
</html>
HTML;
    }

    /**
     * Compress image in place if it exceeds max size (4 MB).
     */
    private function compressImageIfNeeded(string $fullPath, string $disk, string $storagePath): void
    {
        $size = filesize($fullPath);
        if ($size === false || $size <= self::MAX_IMAGE_SIZE_BYTES) {
            return;
        }

        $ext = strtolower(pathinfo($fullPath, PATHINFO_EXTENSION));
        $image = match ($ext) {
            'jpg', 'jpeg' => @imagecreatefromjpeg($fullPath),
            'png' => @imagecreatefrompng($fullPath),
            'gif' => @imagecreatefromgif($fullPath),
            'webp' => function_exists('imagecreatefromwebp') ? @imagecreatefromwebp($fullPath) : null,
            default => null,
        };

        if (!$image) {
            return;
        }

        $width = imagesx($image);
        $height = imagesy($image);
        if ($width <= 0 || $height <= 0) {
            imagedestroy($image);
            return;
        }

        $targetPath = $fullPath . '.tmp.' . $ext;
        $quality = 85;
        $scale = 1.0;

        while ($quality >= 20 || $scale > 0.5) {
            $outW = (int) round($width * $scale);
            $outH = (int) round($height * $scale);
            if ($outW < 100 || $outH < 100) {
                break;
            }

            $resized = imagecreatetruecolor($outW, $outH);
            if (!$resized) {
                break;
            }

            imagecopyresampled($resized, $image, 0, 0, 0, 0, $outW, $outH, $width, $height);

            $saved = false;
            if (in_array($ext, ['jpg', 'jpeg'])) {
                $saved = imagejpeg($resized, $targetPath, $quality);
            } elseif ($ext === 'png') {
                $pngComp = max(0, (int) round(9 * (1 - $quality / 100)));
                $saved = imagepng($resized, $targetPath, $pngComp);
            } elseif ($ext === 'gif') {
                $saved = imagegif($resized, $targetPath);
            } elseif ($ext === 'webp' && function_exists('imagewebp')) {
                $saved = imagewebp($resized, $targetPath, $quality);
            }

            imagedestroy($resized);

            if ($saved && file_exists($targetPath)) {
                $newSize = filesize($targetPath);
                if ($newSize !== false && $newSize <= self::MAX_IMAGE_SIZE_BYTES) {
                    @unlink($fullPath);
                    rename($targetPath, $fullPath);
                    break;
                }
                @unlink($targetPath);
            }

            if ($quality > 20) {
                $quality -= 15;
            } else {
                $quality = 85;
                $scale -= 0.15;
            }
        }

        imagedestroy($image);
    }
}
