<?php

namespace App\Services;

use App\Models\Attachment;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\Storage;

class MaintenanceInvoicePdfService
{
    /**
     * Generate a high-quality CamScanner-style PDF containing only the image.
     * Full-page A4, centered, optimized for readability.
     */
    public function generateFromImage(string $imageStoragePath): string
    {
        $fullPath = storage_path('app/public/' . ltrim($imageStoragePath, '/'));
        if (!file_exists($fullPath)) {
            throw new \RuntimeException('Image file not found: ' . $imageStoragePath);
        }

        $mime = match (strtolower(pathinfo($fullPath, PATHINFO_EXTENSION))) {
            'jpg', 'jpeg' => 'image/jpeg',
            'png' => 'image/png',
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
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body, html { width: 100%; height: 100%; }
        .page {
            width: 210mm;
            min-height: 297mm;
            padding: 0;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        .page img {
            max-width: 100%;
            max-height: 297mm;
            width: auto;
            height: auto;
            object-fit: contain;
        }
    </style>
</head>
<body>
    <div class="page">
        <img src="{$imageDataUri}" alt="" />
    </div>
</body>
</html>
HTML;
    }
}
