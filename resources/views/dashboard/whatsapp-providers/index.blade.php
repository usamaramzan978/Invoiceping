@extends('layouts.app')

@section('content')
    <div class="container-fluid">
        <!-- Page Header -->
        <div class="d-md-flex d-block align-items-center justify-content-between my-4 page-header-breadcrumb">
            <h1 class="page-title fw-semibold fs-18 mb-0">WhatsApp Providers</h1>
            <div class="ms-md-1 ms-0">
                <nav>
                    <ol class="breadcrumb mb-0">
                        <li class="breadcrumb-item"><a href="{{ route('home') }}">Dashboard</a></li>
                        <li class="breadcrumb-item active" aria-current="page">WhatsApp Providers</li>
                    </ol>
                </nav>
            </div>
        </div>
        <!-- Page Header Close -->

        <!-- Success Alert -->
        @if (session('success'))
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                <i class="ri-check-line me-2"></i>{{ session('success') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        @endif

        <!-- Error Alert -->
        @if (session('error'))
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                <i class="ri-error-warning-line me-2"></i>{{ session('error') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        @endif

        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <h5 class="fw-semibold mb-1">WhatsApp Providers</h5>
                <p class="text-muted mb-0">Manage your WhatsApp API providers (Cloud API, Twilio, Vonage).</p>
            </div>
            <a href="{{ route('whatsapp-providers.create') }}" class="btn btn-primary">
                <i class="ri-add-line me-1"></i>Add Provider
            </a>
        </div>

        <div class="card custom-card">
            <div class="card-header justify-content-between">
                <div class="card-title">Providers</div>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-hover text-nowrap align-middle">
                        <thead>
                            <tr>
                                <th>Name</th>
                                <th>Type</th>
                                <th>Status</th>
                                <th>Default</th>
                                <th>Created</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($providers as $provider)
                                <tr>
                                    <td>
                                        <div class="d-flex align-items-center">
                                            <span class="avatar avatar-sm avatar-rounded bg-primary-transparent me-2">
                                                <i class="ri-whatsapp-line"></i>
                                            </span>
                                            <span class="fw-semibold">{{ $provider->name }}</span>
                                        </div>
                                    </td>
                                    <td>
                                        <span class="badge bg-info-transparent">
                                            {{ $provider->type->label() }}
                                        </span>
                                    </td>
                                    <td>
                                        <span
                                            class="badge bg-{{ $provider->is_active ? 'success' : 'secondary' }}-transparent">
                                            {{ $provider->is_active ? 'Active' : 'Inactive' }}
                                        </span>
                                    </td>
                                    <td>
                                        @if ($provider->is_default)
                                            <span class="badge bg-primary-transparent">
                                                <i class="ri-star-fill me-1"></i>Default
                                            </span>
                                        @else
                                            <span class="text-muted">—</span>
                                        @endif
                                    </td>
                                    <td>
                                        <span class="text-muted">{{ $provider->created_at->format('M d, Y') }}</span>
                                    </td>
                                    <td class="text-end">
                                        <div class="hstack gap-2 justify-content-end">
                                            <!-- View -->
                                            <a href="{{ route('whatsapp-providers.show', $provider) }}"
                                                class="btn btn-icon btn-sm btn-info-light" data-bs-toggle="tooltip"
                                                data-bs-placement="top" title="View">
                                                <i class="ri-eye-line"></i>
                                            </a>

                                            <!-- Edit -->
                                            <a href="{{ route('whatsapp-providers.edit', $provider) }}"
                                                class="btn btn-icon waves-effect waves-light btn-sm btn-primary-light"
                                                data-bs-toggle="tooltip" data-bs-placement="top" title="Edit">
                                                <i class="ri-pencil-line"></i>
                                            </a>

                                            <!-- Toggle Status -->
                                            <form action="{{ route('whatsapp-providers.toggle-status', $provider) }}"
                                                method="POST" class="d-inline">
                                                @csrf
                                                <button type="submit"
                                                    class="btn btn-icon btn-sm btn-{{ $provider->is_active ? 'warning' : 'success' }}-light"
                                                    data-bs-toggle="tooltip" data-bs-placement="top"
                                                    title="{{ $provider->is_active ? 'Deactivate' : 'Activate' }}">
                                                    <i class="ri-{{ $provider->is_active ? 'pause' : 'play' }}-line"></i>
                                                </button>
                                            </form>

                                            <!-- Set Default -->
                                            @if (!$provider->is_default)
                                                <form action="{{ route('whatsapp-providers.set-default', $provider) }}"
                                                    method="POST" class="d-inline">
                                                    @csrf
                                                    <button type="submit" class="btn btn-icon btn-sm btn-secondary-light"
                                                        data-bs-toggle="tooltip" data-bs-placement="top"
                                                        title="Set as Default">
                                                        <i class="ri-star-line"></i>
                                                    </button>
                                                </form>
                                            @endif

                                            <!-- Delete -->
                                            <form action="{{ route('whatsapp-providers.destroy', $provider) }}"
                                                method="POST" class="d-inline"
                                                onsubmit="return confirm('Are you sure you want to delete this provider?');">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="btn btn-icon btn-sm btn-danger-light"
                                                    data-bs-toggle="tooltip" data-bs-placement="top" title="Delete">
                                                    <i class="ri-delete-bin-line"></i>
                                                </button>
                                            </form>
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6" class="text-center py-5">
                                        <div class="text-center">
                                            <i class="ri-inbox-line fs-48 text-muted"></i>
                                            <p class="text-muted mt-2">No WhatsApp providers found.</p>
                                            <a href="{{ route('whatsapp-providers.create') }}" class="btn btn-primary">
                                                <i class="ri-add-line me-1"></i>Add Your First Provider
                                            </a>
                                        </div>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
@endsection
