@extends('layouts.app')

@section('content')
    <div class="container-fluid">
        <div class="d-md-flex d-block align-items-center justify-content-between my-4 page-header-breadcrumb">
            <h1 class="page-title fw-semibold fs-18 mb-0">{{ $provider->name }}</h1>
            <div class="ms-md-1 ms-0">
                <nav>
                    <ol class="breadcrumb mb-0">
                        <li class="breadcrumb-item"><a href="{{ route('home') }}">Dashboard</a></li>
                        <li class="breadcrumb-item"><a href="{{ route('whatsapp-providers.index') }}">WhatsApp Providers</a>
                        </li>
                        <li class="breadcrumb-item active" aria-current="page">Details</li>
                    </ol>
                </nav>
            </div>
        </div>

        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <h5 class="fw-semibold mb-1">{{ $provider->name }}</h5>
                <p class="text-muted mb-0">Provider Details</p>
            </div>
            <div class="hstack gap-2">
                <a href="{{ route('whatsapp-providers.edit', $provider) }}" class="btn btn-primary">Edit</a>
                <a href="{{ route('whatsapp-providers.index') }}" class="btn btn-outline-secondary">Back</a>
            </div>
        </div>

        <div class="row">
            <div class="col-md-8">
                <div class="card custom-card">
                    <div class="card-header">
                        <h6 class="mb-0">Provider Information</h6>
                    </div>
                    <div class="card-body">
                        <div class="row mb-3">
                            <div class="col-md-4">
                                <strong>Name:</strong>
                            </div>
                            <div class="col-md-8">
                                {{ $provider->name }}
                            </div>
                        </div>
                        <div class="row mb-3">
                            <div class="col-md-4">
                                <strong>Type:</strong>
                            </div>
                            <div class="col-md-8">
                                <span class="badge bg-info-transparent">
                                    {{ $provider->type->label() }}
                                </span>
                            </div>
                        </div>
                        <div class="row mb-3">
                            <div class="col-md-4">
                                <strong>Status:</strong>
                            </div>
                            <div class="col-md-8">
                                <span class="badge bg-{{ $provider->is_active ? 'success' : 'secondary' }}-transparent">
                                    {{ $provider->is_active ? 'Active' : 'Inactive' }}
                                </span>
                            </div>
                        </div>
                        <div class="row mb-3">
                            <div class="col-md-4">
                                <strong>Default:</strong>
                            </div>
                            <div class="col-md-8">
                                @if ($provider->is_default)
                                    <span class="badge bg-primary-transparent">
                                        <i class="ri-star-fill me-1"></i>Default Provider
                                    </span>
                                @else
                                    <span class="text-muted">No</span>
                                @endif
                            </div>
                        </div>
                        @if ($provider->notes)
                            <div class="row mb-3">
                                <div class="col-md-4">
                                    <strong>Notes:</strong>
                                </div>
                                <div class="col-md-8">
                                    {{ $provider->notes }}
                                </div>
                            </div>
                        @endif
                        <div class="row mb-3">
                            <div class="col-md-4">
                                <strong>Created:</strong>
                            </div>
                            <div class="col-md-8">
                                {{ $provider->created_at->format('M d, Y h:i A') }}
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-4">
                                <strong>Last Updated:</strong>
                            </div>
                            <div class="col-md-8">
                                {{ $provider->updated_at->format('M d, Y h:i A') }}
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Credentials Card -->
                <div class="card custom-card mt-4">
                    <div class="card-header">
                        <h6 class="mb-0">Provider Credentials</h6>
                    </div>
                    <div class="card-body">
                        @php
                            $requiredCredentials = \App\Models\WhatsAppProvider::getRequiredCredentials($provider->type);
                        @endphp
                        @if (!empty($requiredCredentials))
                            <div class="table-responsive">
                                <table class="table table-sm">
                                    <thead>
                                        <tr>
                                            <th>Field</th>
                                            <th>Value</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach ($requiredCredentials as $key => $config)
                                            @if ($provider->$key)
                                                <tr>
                                                    <td><strong>{{ $config['label'] }}:</strong></td>
                                                    <td>
                                                        @if (in_array($key, ['auth_token', 'api_secret', 'app_secret', 'access_token']))
                                                            <code>••••••••</code>
                                                            <small class="text-muted ms-2">(Hidden for security)</small>
                                                        @else
                                                            <code>{{ $provider->$key }}</code>
                                                        @endif
                                                    </td>
                                                </tr>
                                            @endif
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        @else
                            <p class="text-muted mb-0">No credentials configured.</p>
                        @endif
                    </div>
                </div>
            </div>

            <!-- Actions Sidebar -->
            <div class="col-md-4">
                <div class="card custom-card">
                    <div class="card-header">
                        <h6 class="mb-0">Actions</h6>
                    </div>
                    <div class="card-body">
                        <div class="d-grid gap-2">
                            <a href="{{ route('whatsapp-providers.edit', $provider) }}" class="btn btn-primary">
                                <i class="ri-pencil-line me-1"></i>Edit Provider
                            </a>

                            <form action="{{ route('whatsapp-providers.toggle-status', $provider) }}" method="POST">
                                @csrf
                                <button type="submit" class="btn btn-{{ $provider->is_active ? 'warning' : 'success' }} w-100">
                                    <i class="ri-{{ $provider->is_active ? 'pause' : 'play' }}-line me-1"></i>
                                    {{ $provider->is_active ? 'Deactivate' : 'Activate' }}
                                </button>
                            </form>

                            @if (!$provider->is_default)
                                <form action="{{ route('whatsapp-providers.set-default', $provider) }}" method="POST">
                                    @csrf
                                    <button type="submit" class="btn btn-secondary w-100">
                                        <i class="ri-star-line me-1"></i>Set as Default
                                    </button>
                                </form>
                            @endif

                            <form action="{{ route('whatsapp-providers.destroy', $provider) }}" method="POST"
                                onsubmit="return confirm('Are you sure you want to delete this provider? This action cannot be undone.');">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn btn-danger w-100">
                                    <i class="ri-delete-bin-line me-1"></i>Delete Provider
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

