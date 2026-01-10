@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header">{{ __('Setup Business Profile') }}</div>

                <div class="card-body">
                    <form method="POST" action="{{ route('business-profile.store') }}">
                        @csrf

                        <div class="mb-3">
                            <label for="business_name" class="form-label">Business Name</label>
                            <input type="text" class="form-control @error('business_name') is-invalid @enderror" id="business_name" name="business_name" value="{{ old('business_name') }}" required>
                            @error('business_name')
                                <span class="invalid-feedback" role="alert">
                                    <strong>{{ $message }}</strong>
                                </span>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label for="whatsapp_number" class="form-label">WhatsApp Number</label>
                            <input type="text" class="form-control @error('whatsapp_number') is-invalid @enderror" id="whatsapp_number" name="whatsapp_number" value="{{ old('whatsapp_number') }}" required>
                            @error('whatsapp_number')
                                <span class="invalid-feedback" role="alert">
                                    <strong>{{ $message }}</strong>
                                </span>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label for="email" class="form-label">Email (Optional)</label>
                            <input type="email" class="form-control @error('email') is-invalid @enderror" id="email" name="email" value="{{ old('email') }}">
                            @error('email')
                                <span class="invalid-feedback" role="alert">
                                    <strong>{{ $message }}</strong>
                                </span>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label for="currency" class="form-label">Currency</label>
                            <input type="text" class="form-control @error('currency') is-invalid @enderror" id="currency" name="currency" value="{{ old('currency', 'USD') }}">
                            @error('currency')
                                <span class="invalid-feedback" role="alert">
                                    <strong>{{ $message }}</strong>
                                </span>
                            @enderror
                        </div>

                        <button type="submit" class="btn btn-primary">Create Profile</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
