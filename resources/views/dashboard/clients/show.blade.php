@extends('layouts.app')

@section('content')
    <div class="container-fluid">
        <div class="d-flex justify-content-between align-items-center my-4">
            <div>
                <h5 class="fw-semibold mb-1">{{ $client->name }}</h5>
                <p class="text-muted mb-0">Client Details</p>
            </div>
            <div class="hstack gap-2">
                <a href="{{ route('clients.edit', $client) }}" class="btn btn-primary">Edit</a>
                <a href="{{ route('clients.index') }}" class="btn btn-outline-secondary">Back</a>
            </div>
        </div>

        <div class="row">
            <div class="col-md-4">
                <div class="card custom-card">
                    <div class="card-header">
                        <h6 class="mb-0">Client Information</h6>
                    </div>
                    <div class="card-body">
                        <p><strong>Name:</strong> {{ $client->name }}</p>
                        <p><strong>WhatsApp Number:</strong> {{ $client->whatsapp_number }}</p>
                        <p><strong>Email:</strong> {{ $client->email ?? '—' }}</p>
                        <p><strong>Status:</strong> {{ $client->status ? ucfirst($client->status) : '—' }}</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
