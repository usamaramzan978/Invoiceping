@extends('layouts.app')

@section('content')
    <div class="container-fluid">
        <div class="d-md-flex d-block align-items-center justify-content-between my-4 page-header-breadcrumb">
            <h1 class="page-title fw-semibold fs-18 mb-0">Edit WhatsApp Provider</h1>
            <div class="ms-md-1 ms-0">
                <nav>
                    <ol class="breadcrumb mb-0">
                        <li class="breadcrumb-item"><a href="{{ route('home') }}">Dashboard</a></li>
                        <li class="breadcrumb-item"><a href="{{ route('whatsapp-providers.index') }}">WhatsApp Providers</a>
                        </li>
                        <li class="breadcrumb-item active" aria-current="page">Edit</li>
                    </ol>
                </nav>
            </div>
        </div>

        <div class="card custom-card">
            <div class="card-body">
                <form action="{{ route('whatsapp-providers.update', $provider) }}" method="POST" id="providerForm">
                    @csrf
                    @method('PUT')
                    <div class="row g-3">
                        <!-- Provider Name -->
                        <div class="col-md-6">
                            <label class="form-label">Provider Name <span class="text-danger">*</span></label>
                            <input type="text" name="name" class="form-control"
                                value="{{ old('name', $provider->name) }}" required
                                placeholder="e.g., My WhatsApp Cloud API">
                            @error('name')
                                <div class="text-danger small">{{ $message }}</div>
                            @enderror
                        </div>

                        <!-- Provider Type (read-only) -->
                        <div class="col-md-6">
                            <label class="form-label">Provider Type</label>
                            <input type="text" class="form-control" value="{{ $provider->type->label() }}" readonly>
                            <input type="hidden" name="type" value="{{ $provider->type->value }}">
                            <small class="text-muted">Provider type cannot be changed after creation.</small>
                        </div>

                        <!-- Credentials Section -->
                        <div class="col-12">
                            <div id="credentialsSection">
                                <h6 class="mb-3">Provider Credentials</h6>
                                <div class="row g-3" id="credentialsFields">
                                    @foreach ($requiredCredentials as $key => $config)
                                        <div class="col-md-6">
                                            <label class="form-label">
                                                {{ $config['label'] }}
                                                @if ($config['required'])
                                                    <span class="text-danger">*</span>
                                                @endif
                                            </label>
                                            <input type="{{ $config['type'] }}" name="{{ $key }}"
                                                class="form-control" value="{{ old($key, $provider->$key ?? '') }}"
                                                placeholder="Enter {{ strtolower($config['label']) }}"
                                                {{ $config['required'] ? 'required' : '' }}>
                                            @error($key)
                                                <div class="text-danger small">{{ $message }}</div>
                                            @enderror
                                        </div>
                                    @endforeach
                                </div>
                            </div>
                        </div>

                        <!-- Status -->
                        <div class="col-md-6">
                            <label class="form-label">Status</label>
                            <div class="form-check form-switch">
                                <input class="form-check-input" type="checkbox" name="is_active" value="1"
                                    id="isActive" {{ old('is_active', $provider->is_active) ? 'checked' : '' }}>
                                <label class="form-check-label" for="isActive">Active</label>
                            </div>
                        </div>

                        <!-- Default -->
                        <div class="col-md-6">
                            <label class="form-label">Set as Default</label>
                            <div class="form-check form-switch">
                                <input class="form-check-input" type="checkbox" name="is_default" value="1"
                                    id="isDefault" {{ old('is_default', $provider->is_default) ? 'checked' : '' }}>
                                <label class="form-check-label" for="isDefault">Default Provider</label>
                            </div>
                        </div>

                        <!-- Notes -->
                        <div class="col-12">
                            <label class="form-label">Notes</label>
                            <textarea name="notes" class="form-control" rows="3" placeholder="Optional notes about this provider">{{ old('notes', $provider->notes) }}</textarea>
                            @error('notes')
                                <div class="text-danger small">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    <div class="mt-4 d-flex justify-content-end gap-2">
                        <a href="{{ route('whatsapp-providers.index') }}" class="btn btn-outline-secondary">Cancel</a>
                        <button type="submit" class="btn btn-primary">Update Provider</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection
