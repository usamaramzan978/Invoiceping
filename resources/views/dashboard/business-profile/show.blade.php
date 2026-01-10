@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <span>{{ __('My Business Profile') }}</span>
                    <a href="{{ route('business-profile.edit', $businessProfile) }}" class="btn btn-sm btn-secondary">Edit</a>
                </div>

                <div class="card-body">
                    <h5 class="card-title">{{ $businessProfile->business_name }}</h5>
                    <p class="card-text">
                        <strong>WhatsApp:</strong> {{ $businessProfile->whatsapp_number }}<br>
                        <strong>Email:</strong> {{ $businessProfile->email ?? 'N/A' }}<br>
                        <strong>Currency:</strong> {{ $businessProfile->currency }}
                    </p>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
