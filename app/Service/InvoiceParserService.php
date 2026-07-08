<?php

namespace App\Service;

class InvoiceParserService
{
    public function cleanOcrText(string $text): string
    {
        // normalize line breaks
        $text = str_replace(["\r\n", "\r"], "\n", $text);

        // remove junk characters but keep useful symbols
        $text = preg_replace('/[^a-zA-Z0-9\s:\-x.\/\n]/', ' ', $text);

        // merge broken colons (Item \n : → Item:)
        $text = preg_replace('/\n\s*:\s*/', ': ', $text);

        // remove multiple spaces
        $text = preg_replace('/[ \t]+/', ' ', $text);

        // remove empty junk lines
        $lines = array_filter(array_map('trim', explode("\n", $text)));

        return implode("\n", $lines);
    }
}
