@extends('layouts.app')

@section('content')
    <div class="container py-4">

        {{-- HEADER --}}
        <div class="d-flex justify-content-between align-items-center mb-3">
            <div>
                <h4 class="mb-0">Invoice Details</h4>
                <small class="text-muted">Complete invoice overview</small>
            </div>

            <div class="d-flex gap-2">
                <a href="{{ route('invoices.edit', $invoice->id) }}" class="btn btn-warning">
                    Edit
                </a>

                <a href="{{ route('invoices.index') }}" class="btn btn-secondary">
                    Back
                </a>
            </div>
        </div>

        <div class="row">

            {{-- LEFT SIDE: INVOICE INFO --}}
            <div class="col-md-8">

                {{-- INVOICE CARD --}}
                <div class="card shadow-sm mb-3">

                    <div class="card-header bg-dark text-white d-flex justify-content-between">
                        <div>
                            <strong>Invoice:</strong> {{ $invoice->invoice_no }}
                        </div>
                        {{--
                    <div>
                        <span class="badge bg-success">Active</span>
                    </div> --}}
                    </div>

                    <div class="card-body">

                        <div class="row mb-3">

                            <div class="col-md-4">
                                <h6 class="text-muted">Vendor</h6>
                                <p class="mb-0 fw-bold">{{ $invoice->vendor_name }}</p>
                            </div>

                            <div class="col-md-4">
                                <h6 class="text-muted">Invoice Date</h6>
                                <p class="mb-0">
                                    {{ \Carbon\Carbon::parse($invoice->invoice_date)->format('d M, Y') }}
                                </p>
                            </div>

                            <div class="col-md-4">
                                <h6 class="text-muted">Total Amount</h6>
                                <p class="mb-0 text-success fw-bold fs-5">
                                    {{ number_format($invoice->total_amount, 2) }}
                                </p>
                            </div>

                        </div>

                        @if ($invoice->remarks)
                            <div class="mb-3">
                                <h6 class="text-muted">Remarks</h6>
                                <p class="mb-0">{{ $invoice->remarks }}</p>
                            </div>
                        @endif

                    </div>
                </div>

                {{-- ITEMS TABLE --}}
                <div class="card shadow-sm">

                    <div class="card-header bg-light">
                        <strong>Invoice Items</strong>
                    </div>

                    <div class="card-body p-0">

                        <div class="table-responsive">

                            <table class="table table-bordered table-hover mb-0">

                                <thead class="table-light">
                                    <tr>
                                        <th>#</th>
                                        <th>Product</th>
                                        <th>Qty</th>
                                        <th>Price</th>
                                        <th>Total</th>
                                    </tr>
                                </thead>

                                <tbody>

                                    @forelse($invoice->items as $item)
                                        <tr>
                                            <td>{{ $loop->iteration }}</td>
                                            <td>{{ $item->product_name }}</td>
                                            <td>{{ $item->qty }}</td>
                                            <td>{{ number_format($item->price, 2) }}</td>
                                            <td class="fw-bold text-success">
                                                {{ number_format($item->total, 2) }}
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="5" class="text-center text-muted py-3">
                                                No items found
                                            </td>
                                        </tr>
                                    @endforelse

                                </tbody>

                            </table>

                        </div>

                    </div>
                </div>

            </div>

            {{-- RIGHT SIDE: SUMMARY --}}
            <div class="col-md-4">

                {{-- SUMMARY CARD --}}
                <div class="card shadow-sm mb-3">

                    <div class="card-header bg-primary text-white">
                        Summary
                    </div>

                    <div class="card-body">

                        <div class="d-flex justify-content-between mb-2">
                            <span>Total Items</span>
                            <strong>{{ $invoice->items->count() }}</strong>
                        </div>

                        <div class="d-flex justify-content-between mb-2">
                            <span>Total Quantity</span>
                            <strong>{{ $invoice->items->sum('qty') }}</strong>
                        </div>

                        <hr>

                        <div class="d-flex justify-content-between">
                            <span class="fs-5">Grand Total</span>
                            <span class="fs-5 text-success fw-bold">
                                {{ number_format($invoice->total_amount, 2) }}
                            </span>
                        </div>

                    </div>
                </div>

                {{-- ACTION CARD --}}
                <div class="card shadow-sm">

                    <div class="card-header bg-light">
                        Actions
                    </div>

                    <div class="card-body d-grid gap-2">

                        <a href="{{ route('invoices.edit', $invoice->id) }}" class="btn btn-warning">
                            Edit Invoice
                        </a>

                        <form method="POST" action="{{ route('invoices.destroy', $invoice->id) }}">
                            @csrf
                            @method('DELETE')

                            <button class="btn btn-danger w-100" onclick="return confirm('Are you sure?')">
                                Delete Invoice
                            </button>
                        </form>

                    </div>
                </div>

            </div>

        </div>
    </div>
@endsection
