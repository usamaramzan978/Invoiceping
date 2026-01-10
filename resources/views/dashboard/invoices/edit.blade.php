@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-10">
            <div class="card">
                <div class="card-header">{{ __('Edit Invoice') }}</div>

                <div class="card-body">
                    <form method="POST" action="{{ route('invoices.update', $invoice) }}" id="invoice-form">
                        @csrf
                        @method('PUT')

                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label for="client_id" class="form-label">Client</label>
                                <select class="form-select @error('client_id') is-invalid @enderror" id="client_id" name="client_id" required>
                                    <option value="">Select Client</option>
                                    @foreach($clients as $client)
                                        <option value="{{ $client->id }}" {{ old('client_id', $invoice->client_id) == $client->id ? 'selected' : '' }}>{{ $client->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-6">
                                <label for="invoice_number" class="form-label">Invoice Number</label>
                                <input type="text" class="form-control @error('invoice_number') is-invalid @enderror" id="invoice_number" name="invoice_number" value="{{ old('invoice_number', $invoice->invoice_number) }}" required>
                            </div>
                        </div>

                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label for="issue_date" class="form-label">Issue Date</label>
                                <input type="date" class="form-control @error('issue_date') is-invalid @enderror" id="issue_date" name="issue_date" value="{{ old('issue_date', $invoice->issue_date->format('Y-m-d')) }}" required>
                            </div>
                            <div class="col-md-6">
                                <label for="due_date" class="form-label">Due Date</label>
                                <input type="date" class="form-control @error('due_date') is-invalid @enderror" id="due_date" name="due_date" value="{{ old('due_date', $invoice->due_date->format('Y-m-d')) }}" required>
                            </div>
                        </div>

                        <div class="mb-3">
                            <label for="currency" class="form-label">Currency</label>
                            <input type="text" class="form-control @error('currency') is-invalid @enderror" id="currency" name="currency" value="{{ old('currency', $invoice->currency) }}">
                        </div>

                        <hr>
                        <h5>Items</h5>
                        <div id="items-container">
                            @foreach($invoice->items as $index => $item)
                            <div class="row mb-2 item-row" id="item-{{ $index }}">
                                <div class="col-md-5">
                                    <input type="text" name="items[{{ $index }}][description]" class="form-control" placeholder="Description" value="{{ $item->description }}" required>
                                </div>
                                <div class="col-md-2">
                                    <input type="number" name="items[{{ $index }}][quantity]" class="form-control" placeholder="Qty" step="0.01" value="{{ $item->quantity }}" required>
                                </div>
                                <div class="col-md-3">
                                    <input type="number" name="items[{{ $index }}][unit_price]" class="form-control" placeholder="Price" step="0.01" value="{{ $item->unit_price }}" required>
                                </div>
                                <div class="col-md-2">
                                    <button type="button" class="btn btn-danger btn-sm" onclick="removeItem({{ $index }})">Remove</button>
                                </div>
                            </div>
                            @endforeach
                        </div>
                        <button type="button" class="btn btn-secondary btn-sm mb-3" onclick="addItem()">Add Item</button>

                        <div class="mb-3">
                            <label for="notes" class="form-label">Notes</label>
                            <textarea class="form-control @error('notes') is-invalid @enderror" id="notes" name="notes" rows="3">{{ old('notes', $invoice->notes) }}</textarea>
                        </div>

                        <button type="submit" class="btn btn-primary">Update Invoice</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    let itemIndex = {{ $invoice->items->count() }};

    function addItem() {
        const container = document.getElementById('items-container');
        const html = `
            <div class="row mb-2 item-row" id="item-${itemIndex}">
                <div class="col-md-5">
                    <input type="text" name="items[${itemIndex}][description]" class="form-control" placeholder="Description" required>
                </div>
                <div class="col-md-2">
                    <input type="number" name="items[${itemIndex}][quantity]" class="form-control" placeholder="Qty" step="0.01" required>
                </div>
                <div class="col-md-3">
                    <input type="number" name="items[${itemIndex}][unit_price]" class="form-control" placeholder="Price" step="0.01" required>
                </div>
                <div class="col-md-2">
                    <button type="button" class="btn btn-danger btn-sm" onclick="removeItem(${itemIndex})">Remove</button>
                </div>
            </div>
        `;
        container.insertAdjacentHTML('beforeend', html);
        itemIndex++;
    }

    function removeItem(index) {
        document.getElementById(`item-${index}`).remove();
    }
</script>
@endsection
