<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Service\GeminiInvoiceService;
use App\Service\InvoiceParserService;
use App\Service\WhatsAppService;
use App\Service\InvoiceService;
use App\Service\OCRService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class WebhookController extends Controller
{
    public function __construct(
        protected OCRService $ocr,
        protected InvoiceParserService $parser,
        protected GeminiInvoiceService $ai,
        protected InvoiceService $service,
        protected WhatsAppService $wpservice,
    ) {}

    public function handleIncoming(Request $request)
    {
        $from = $request->input('From');
        $body = $request->input('Body');

        $user = User::where('phone_number', $from)->first();
        Session::put('webhook_user_id',$user->id);
        // 1. Check how many media items are attached
        $numMedia = (int) $request->input('NumMedia', 0);

        if ($numMedia > 0) {
            $success = false;
            for ($i = 0; $i < $numMedia; $i++) {
                $mediaUrl = $request->input("MediaUrl{$i}");
                $contentType = $request->input("MediaContentType{$i}");
                // 3. Determine the correct file extension based on MIME type
                $extension = $this->getExtensionFromMime($contentType);
                // 4. Generate a unique, safe filename
                $filename = 'whatsapp_' . time() . '_' . Str::random(10) . '.' . $extension;
                $relativePath = 'invoices/' . $filename;

                try {
                    // 5. Download the file from Twilio's servers
                    $response = Http::withBasicAuth(config('services.twilio.sid'), config('services.twilio.auth_token'))->get($mediaUrl);

                    if ($response->successful()) {
                        // 6. Save the file to your 'public' disk inside a 'whatsapp_media' folder
                        Storage::disk('public')->put($relativePath, $response->body());

                        $this->processWhatsAppInvoice($relativePath);
                        $success = true;
                    } else {
                        Log::error("Failed to download media from Twilio. Status: " . $response->status());
                    }
                } catch (\Exception $e) {
                    Log::error("Error downloading WhatsApp media: " . $e->getMessage());
                }
            }
            if ($success) {
                $this->wpservice->send("Invoice successfully created via WhatsApp OCR.");
            }
        }
        Session::forget('webhook_user_id');
        return response('<Response></Response>', 200)
            ->header('Content-Type', 'text/xml');
    }
    private function processWhatsAppInvoice($path)
    {
        try {
            // 1. OCR text extract karein
            $text = $this->ocr->extractText($path);

            // 2. Parse text using AI
            $clean = $this->parser->cleanOcrText($text);
            $data = $this->ai->parse($clean);

            // Remarks set karein pehchan ke liye
            $data['remarks'] = 'WhatsApp OCR Generated Invoice';
            if (isset($data['items']) && is_array($data['items'])) {
                // 3. Database mein invoice create karein
                $this->service->create($data);
            } else {
                Log::error("WhatsApp AI parsing failed: Items not found in structure.");
            }
        } catch (\Exception $ex) {
            Log::error("Error in processWhatsAppInvoice: " . $ex->getMessage());
        }
    }
    private function getExtensionFromMime($mimeType)
    {
        $map = [
            'image/jpeg'      => 'jpg',
            'image/png'       => 'png',
            'application/pdf' => 'pdf',
        ];

        return $map[$mimeType] ?? 'bin'; // Falls back to .bin if unknown
    }
}
