@extends('layouts.app')

@section('content')
    <div class="container py-4">

        {{-- HEADER --}}
        <div class="d-flex justify-content-between align-items-center mb-3">
            <div>
                <h4 class="mb-0">Invoices</h4>
                <small class="text-muted">Manage all your invoices</small>
            </div>
           <div class="text-end">
             <button class="btn btn-success" data-bs-toggle="modal" data-bs-target="#uploadInvoiceModal">
                + Upload Invoice
            </button>
            <a href="{{ route('invoices.create') }}" class="btn btn-primary">
                + Create Invoice
            </a>
           </div>
        </div>

        {{-- CARD --}}
        <div class="card shadow-sm">

            {{-- FILTER BAR --}}
            <div class="card-body border-bottom">

                <form method="GET" action="{{ route('invoices.index') }}" class="row g-2">

                    <div class="col-md-3">
                        <input type="text" name="search" class="form-control" placeholder="Search invoice / vendor"
                            value="{{ request('search') }}">
                    </div>

                    <div class="col-md-3">
                        <input type="date" name="from" class="form-control" value="{{ request('from') }}">
                    </div>

                    <div class="col-md-3">
                        <input type="date" name="to" class="form-control" value="{{ request('to') }}">
                    </div>

                    <div class="col-md-3 d-flex gap-2">
                        <button class="btn btn-dark w-100">
                            Filter
                        </button>

                        <a href="{{ route('invoices.index') }}" class="btn btn-light w-100">
                            Reset
                        </a>
                    </div>

                </form>

            </div>

            {{-- TABLE --}}
            <div class="card-body p-0">

                <div class="table-responsive">

                    <table class="table table-hover align-middle mb-0">

                        <thead class="table-light">
                            <tr>
                                <th>#</th>
                                <th>Invoice No</th>
                                <th>Vendor</th>
                                <th>Date</th>
                                <th>Total</th>
                                <th class="text-end">Actions</th>
                            </tr>
                        </thead>

                        <tbody>

                            @forelse($invoices as $invoice)
                                <tr>

                                    <td>{{ $loop->iteration }}</td>

                                    <td>
                                        <strong>{{ $invoice->number }}</strong>
                                    </td>

                                    <td>
                                        {{ $invoice->vendor_name }}
                                    </td>

                                    <td>
                                        {{ \Carbon\Carbon::parse($invoice->invoice_date)->format('d M, Y') }}
                                    </td>

                                    <td>
                                        <span class="fw-bold text-success">
                                            {{ number_format($invoice->total_amount, 2) }}
                                        </span>
                                    </td>

                                    <td class="text-end">

                                        <a href="{{ route('invoices.show', $invoice->id) }}"
                                            class="btn btn-sm btn-info text-white">
                                            View
                                        </a>

                                        <a href="{{ route('invoices.edit', $invoice->id) }}"
                                            class="btn btn-sm btn-warning">
                                            Edit
                                        </a>

                                        <form action="{{ route('invoices.destroy', $invoice->id) }}" method="POST"
                                            class="d-inline">

                                            @csrf
                                            @method('DELETE')

                                            <button class="btn btn-sm btn-danger" onclick="return confirm('Are you sure?')">
                                                Delete
                                            </button>

                                        </form>

                                    </td>

                                </tr>
                            @empty

                                <tr>
                                    <td colspan="7" class="text-center py-4 text-muted">
                                        No invoices found
                                    </td>
                                </tr>
                            @endforelse

                        </tbody>

                    </table>

                </div>

            </div>

            {{-- PAGINATION --}}
            <div class="card-footer d-flex justify-content-end">
                {{ $invoices->links() }}
            </div>

        </div>
    </div>
    <!-- Upload Invoice Modal -->
    <div class="modal fade" id="uploadInvoiceModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-md modal-dialog-centered">

            <div class="modal-content">

                {{-- HEADER --}}
                <div class="modal-header bg-primary text-white">
                    <h5 class="modal-title">Upload Invoice</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>

                {{-- BODY --}}
                <div class="modal-body">

                    <form method="POST" id="uploadForm" enctype="multipart/form-data">

                        @csrf

                        <div class="mb-3">
                            <label class="form-label">Select Invoice File</label>

                            <input type="file" id="fileInput" name="file" class="form-control" required>
                        </div>

                        <div class="alert alert-info py-2">
                            <small>
                                Supported: PDF, JPG, PNG<br>
                                System will process invoice automatically (future OCR ready)
                            </small>
                        </div>

                        <button class="btn btn-primary w-100">
                            Upload & Process Invoice
                        </button>

                    </form>

                </div>

            </div>
        </div>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>

    <script>
        $('#uploadForm').on('submit', function(e) {
            e.preventDefault();

            let formData = new FormData(this);

            $.ajax({
                url: "/invoice/upload",
                type: "POST",
                data: formData,
                processData: false,
                contentType: false,

                beforeSend: function() {
                    $('.btn-primary').text('Uploading...').prop('disabled', true);
                },

                success: function(response) {
                    alert('Invoice uploaded successfully');

                    // close modal
                    $('#uploadInvoiceModal').modal('hide');

                    // reset form
                    $('#uploadForm')[0].reset();

                    location.reload(); // optional (remove if fully dynamic)
                },

                error: function(err) {
                    alert('Upload failed');
                },

                complete: function() {
                    $('.btn-primary').text('Upload & Process').prop('disabled', false);
                }
            });

        });
    </script>
@endsection
