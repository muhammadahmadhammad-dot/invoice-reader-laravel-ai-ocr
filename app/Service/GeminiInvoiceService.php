<?php

namespace App\Service;

use Carbon\Carbon;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class GeminiInvoiceService
{
    public function parse(string $ocrText)
    {
        $prompt = $this->buildPrompt($ocrText);
        $apiKey = config('services.gemini.key');
        $response = Http::withHeaders([
            'x-goog-api-key' => $apiKey,
            'Content-Type' => 'application/json',
        ])->post(
            'https://generativelanguage.googleapis.com/v1beta/interactions',
            [
                'model' => 'gemini-3.5-flash',
                'input' =>  $prompt,
                'generation_config' => [
                    'thinking_level' => 'low',
                ],
            ]
        );

        if (!$response->successful()) {
            throw new \Exception("Gemini API error: " . $response->body());
        }
        $apiResponse = $response->json();
        $text = data_get($apiResponse, 'steps.1.content.0.text');

        // Agar second step na ho to search kar lo
        if (!$text && isset($apiResponse['steps'])) {
            foreach ($apiResponse['steps'] as $step) {
                if (($step['type'] ?? '') === 'model_output') {
                    $text = $step['content'][0]['text'] ?? null;
                    break;
                }
            }
        }

        if (!$text) {
            throw new \Exception('Model output text not found.');
        }

        // Markdown remove karo
        $text = str_replace(['```json', '```'], '', $text);
        $text = trim($text);

        // JSON decode
        $data = json_decode($text, true);

        if (json_last_error() !== JSON_ERROR_NONE) {
            throw new \Exception(json_last_error_msg());
        }


        return [
            'vendor_name' => $data['vendor_name'] ?? 'Unknown',
            'number' => $data['invoice_number'] ?? ('INV-' . time()),
            'total' => $data['total'] ?? 0,
            'date' => $data['date'] ?? now()->format('Y-m-d'),
            'items' => $data['items'] ?? [],
        ];
    }

    private function buildPrompt($text): string
    {
        return "
        You are an invoice extraction engine.

        IMPORTANT:
        - OCR text may messy or may contain garbage and broken lines.
        - Ignore garbage lines like '.', ''', ':'
        - Combine broken lines logically
        - Detect ITEM blocks carefully
        Rules:

        1. Ignore words like:
        - Item
        - Items
        - Description
        - Qty
        - Price
        - Total
        when they appear as headings.

        2. A product line MUST contain a real product name.

        3. Never treat Item as a product.

        4. Ignore isolated numbers.

        5. Invoice number should only come from the line starting with Invoice No.

        6. Return ONLY JSON.

        {
        \"vendor_name\": \"\",
        \"invoice_number\": \"\",
        \"date\": \"\",
        \"total\": 0,
        \"items\": [
            {
            \"product_name\": \"\",
            \"qty\": 0,
            \"price\": 0
            }
        ]
        }

        OCR TEXT:  $text ";
    }
}
