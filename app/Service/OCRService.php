<?php

namespace App\Service;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class OCRService
{
    public function extractText($filePath)
    {
        $file = storage_path('app/public/' . $filePath);
        if (!file_exists($file)) {
            throw new \Exception("File not found: " . $file);
        }
        $response = Http::attach(
            'file',
            fopen($file, 'r'),
            basename($file)
        )->post('https://api.ocr.space/parse/image', [
            'apikey' => config('services.ocrspace.key'),
            'language' => 'eng',
        ]);

        $data = $response->json();
        if (!empty($data['IsErroredOnProcessing'])) {
            throw new \Exception(
                $data['ErrorMessage'][0] ?? 'OCR failed'
            );
        }
        return $data['ParsedResults'][0]['ParsedText'] ?? '';
    }
}
