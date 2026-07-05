@extends('layouts.app')

@section('content')
    <div class="container py-4">

        <div class="card shadow-sm">
            <div class="card-header bg-primary text-white">
                <h5 class="mb-0">Create Invoice</h5>
            </div>

            <div class="card-body">

                <form method="POST" action="{{ route('invoices.store') }}">
                    @csrf

                    {{-- BASIC INFO --}}
                    <div class="row">
                        <div class="col-md-4 mb-3">
                            <label>Invoice No</label>
                            <input type="text" name="number" class="form-control" placeholder="INV-001">
                        </div>

                        <div class="col-md-4 mb-3">
                            <label>Vendor Name</label>
                            <input type="text" name="vendor_name" class="form-control" placeholder="Vendor">
                        </div>

                        <div class="col-md-4 mb-3">
                            <label>Invoice Date</label>
                            <input type="date" name="date" class="form-control">
                        </div>
                    </div>

                    <div class="mb-3">
                        <label>Remarks</label>
                        <textarea name="remarks" class="form-control" rows="2"></textarea>
                    </div>

                    <hr>

                    {{-- ITEMS --}}
                    <div class="d-flex justify-content-between align-items-center mb-2">
                        <h5>Invoice Items</h5>
                        <button type="button" class="btn btn-sm btn-success" onclick="addItem()">
                            + Add Item
                        </button>
                    </div>

                    <div id="items-wrapper">
                        <div class="row item-row mb-2">
                            <div class="col-md-5">
                                <input type="text" name="items[0][product_name]" class="form-control"
                                    placeholder="Product Name">
                            </div>

                            <div class="col-md-2">
                                <input type="number" name="items[0][qty]" class="form-control" placeholder="Qty">
                            </div>

                            <div class="col-md-2">
                                <input type="number" name="items[0][price]" class="form-control" placeholder="Price">
                            </div>

                            <div class="col-md-3 text-end">
                                <button type="button" class="btn btn-danger btn-sm" onclick="removeItem(this)">
                                    Remove
                                </button>
                            </div>
                        </div>
                    </div>

                    <hr>

                    <button class="btn btn-primary">
                        Save Invoice
                    </button>

                </form>

            </div>
        </div>
    </div>

    <script>
        let index = 1;

        function addItem() {
            let html = `
                <div class="row item-row mb-2">
                    <div class="col-md-5">
                        <input type="text" name="items[${index}][product_name]" class="form-control" placeholder="Product Name">
                    </div>

                    <div class="col-md-2">
                        <input type="number" name="items[${index}][qty]" class="form-control" placeholder="Qty">
                    </div>

                    <div class="col-md-2">
                        <input type="number" name="items[${index}][price]" class="form-control" placeholder="Price">
                    </div>

                    <div class="col-md-3 text-end">
                        <button type="button" class="btn btn-danger btn-sm" onclick="removeItem(this)">
                            Remove
                        </button>
                    </div>
                </div>`;

            document.getElementById('items-wrapper').insertAdjacentHTML('beforeend', html);
            index++;
        }

        function removeItem(btn) {
            btn.closest('.item-row').remove();
        }
    </script>
@endsection
