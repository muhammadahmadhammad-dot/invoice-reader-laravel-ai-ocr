<?php

namespace App\Http\Controllers;

use App\Models\Invoice;
use App\Service\InvoiceService;
use Illuminate\Http\Request;

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
        $invoice = Invoice::findOrFail($id);
        return view('invoices.show',compact('invoice'));
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
}
