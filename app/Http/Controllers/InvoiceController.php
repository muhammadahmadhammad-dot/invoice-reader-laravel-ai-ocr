<?php

namespace App\Http\Controllers;

use App\Models\Invoice;
use App\Service\GeminiInvoiceService;
use App\Service\InvoiceParserService;
use App\Service\InvoiceService;
use App\Service\OCRService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class InvoiceController extends Controller
{
    public function index(Request $request)
    {
        $query = Invoice::query();
        if ($request->filled('search')) {
            $search = $request->search;

            $query->where(function ($q) use ($search) {
                $q->where('number', 'like', "%{$search}%")
                    ->orWhere('vendor_name', 'like', "%{$search}%");
            });
        }
        if ($request->filled('from')) {
            $query->whereDate('invoice_date', '>=', $request->from);
        }
        if ($request->filled('to')) {
            $query->whereDate('invoice_date', '<=', $request->to);
        }
        $invoices = $query->latest()->paginate(10);
        $invoices->appends($request->all());

        return view('invoices.index', compact('invoices'));
    }
    public function create()
    {
        return view('invoices.create');
    }
    public function show(string $id)
    {
        $invoice = Invoice::with('items')->findOrFail($id);
        return view('invoices.show', compact('invoice'));
    }
    public function store(Request $request, InvoiceService $service)
    {
        $request->validate([
            'number' => 'required|unique:invoices',
            'vendor_name' => 'required',
            'date' => 'required|date',
            'items' => 'required|array|min:1',
            'items.*.qty' => 'required|integer|min:0',
            'items.*.price' => 'required|integer|min:0',
            'items.*.product_name' => 'required|string',
        ]);
        $service->create($request->all());

        return redirect()->route('invoices.index')
            ->with('success', 'Invoice created successfully');
    }
    public function upload(Request $request, OCRService $ocr,  InvoiceParserService $parser, GeminiInvoiceService $ai, InvoiceService $service)
    {
        $request->validate([
            'file' => 'required|file|mimes:pdf,jpg,jpeg,png|max:10240'
        ]);
        try {
            // 1. store file
            $path = $request->file('file')->store('invoices', 'public');

            // 2. OCR text
            $text = $ocr->extractText($path);

            // 3. parse - ai
            $clean = $parser->cleanOcrText($text);

            $data = $ai->parse($clean);
            $data['remarks'] = 'OCR Generated Invoice';

            if (!isset($data['items']) || !is_array($data['items'])) {
                return response()->json([
                    'error' => 'AI parsing failed',
                    'raw_text' => $text
                ], 422);
            }
            // 5. create invoice
            $invoice = $service->create($data);

            return response()->json([
                'message' => 'Invoice created from OCR.Space',
                'invoice' => $invoice,
                'raw_text' => $text
            ]);
        } catch (\Exception $ex) {
            return response()->json([
                'error' => $ex->getMessage(),
                'message' => 'Invoice creation failed',
            ], 500);
        }
    }
}
