@extends('layouts.app')

@section('content')
    <div class="container-fluid">
        <!-- Page Header -->
        <div class="d-md-flex d-block align-items-center justify-content-between my-4 page-header-breadcrumb">
            <div>
                <h5 class="fw-semibold mb-1">WhatsApp Providers</h5>
                <p class="text-muted mb-0">Manage your WhatsApp API providers (Cloud API, Twilio, Vonage).</p>
            </div>
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

        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <h6 class="mb-0 text-muted">Total Providers: <strong class="text-dark">{{ $providers->count() }}</strong>
                </h6>
            </div>
            <a href="{{ route('whatsapp-providers.create') }}" class="btn btn-primary">
                <i class="ri-add-line me-1"></i>Add Provider
            </a>
        </div>

        @if ($providers->count() > 0)
            <div class="row g-3">
                @foreach ($providers as $provider)
                    <div class="col-xl-3 col-lg-4 col-md-6 col-sm-12">
                        <div class="card custom-card h-100 provider-card" style="transition: all 0.3s ease;">
                            <div class="card-body d-flex flex-column p-4">
                                <!-- Top Section: Status Indicator & Favorite -->
                                <div class="d-flex justify-content-between align-items-start mb-3">
                                    @if ($provider->is_active)
                                        <span class="badge bg-success-transparent rounded-pill px-2 py-1">
                                            <span class="badge-dot bg-success me-1"></span>Active
                                        </span>
                                    @else
                                        <span class="badge bg-secondary-transparent rounded-pill px-2 py-1">
                                            <span class="badge-dot bg-secondary me-1"></span>Inactive
                                        </span>
                                    @endif
                                    @if ($provider->is_default)
                                        <button class="btn btn-icon btn-sm p-0 text-warning" type="button"
                                            data-bs-toggle="tooltip" data-bs-placement="top" title="Default Provider">
                                            <i class="ri-star-fill fs-18"></i>
                                        </button>
                                    @else
                                        <form action="{{ route('whatsapp-providers.set-default', $provider->id) }}"
                                            method="POST" class="d-inline">
                                            @csrf
                                            <button type="submit" class="btn btn-icon btn-sm p-0 text-muted"
                                                data-bs-toggle="tooltip" data-bs-placement="top" title="Set as Default">
                                                <i class="ri-star-line fs-18"></i>
                                            </button>
                                        </form>
                                    @endif
                                </div>

                                <!-- Logo/Icon Section -->
                                <div class="text-center mb-3">
                                    <div class="provider-icon-wrapper mx-auto mb-2"
                                        style="width: 80px; height: 80px; border-radius: 16px; background: linear-gradient(135deg, {{ $provider->is_active ? '#25D366' : '#6c757d' }}15, {{ $provider->is_active ? '#128C7E' : '#495057' }}15); display: flex; align-items: center; justify-content: center;">
                                        <i class="ri-whatsapp-fill"
                                            style="font-size: 48px; color: {{ $provider->is_active ? '#25D366' : '#6c757d' }};"></i>
                                    </div>
                                </div>

                                <!-- Provider Name -->
                                <h5 class="text-center fw-semibold mb-2" style="font-size: 18px;">
                                    {{ $provider->name }}
                                </h5>

                                <!-- Category Tag -->
                                <div class="text-center mb-3">
                                    <span class="badge bg-info-transparent rounded-pill px-3 py-1">
                                        <i
                                            class="ri-{{ $provider->type->value === 'whatsapp_cloud_api' ? 'whatsapp' : ($provider->type->value === 'twilio' ? 'message-3' : 'phone') }}-line me-1"></i>
                                        {{ $provider->type->label() }}
                                    </span>
                                </div>

                                <!-- Action Icons at Bottom -->
                                <div class="mt-auto pt-3 border-top d-flex justify-content-center gap-2">
                                    <a href="{{ route('whatsapp-providers.show', $provider->id) }}"
                                        class="btn btn-sm btn-icon btn-info-light" data-bs-toggle="tooltip"
                                        data-bs-placement="top" title="View Details">
                                        <i class="ri-eye-line"></i>
                                    </a>
                                    <a href="{{ route('whatsapp-providers.edit', $provider->id) }}"
                                        class="btn btn-sm btn-icon btn-success-light" data-bs-toggle="tooltip"
                                        data-bs-placement="top" title="Edit Provider">
                                        <i class="ri-pencil-line"></i>
                                    </a>
                                    <form action="{{ route('whatsapp-providers.toggle-status', $provider->id) }}"
                                        method="POST" class="d-inline">
                                        @csrf
                                        <button type="submit"
                                            class="btn btn-sm btn-icon btn-{{ $provider->is_active ? 'warning' : 'success' }}-light"
                                            data-bs-toggle="tooltip" data-bs-placement="top"
                                            title="{{ $provider->is_active ? 'Deactivate' : 'Activate' }}">
                                            <i class="ri-{{ $provider->is_active ? 'pause' : 'play' }}-line"></i>
                                        </button>
                                    </form>
                                    <button type="button" class="btn btn-sm btn-icon btn-danger-light"
                                        data-bs-toggle="tooltip" data-bs-placement="top" title="Delete Provider"
                                        data-delete-modal data-title="Delete WhatsApp Provider"
                                        data-message="Are you sure you want to delete '{{ $provider->name }}'? This action cannot be undone."
                                        data-form-id="{{ route('whatsapp-providers.destroy', $provider->id) }}"
                                        data-record-name="{{ $provider->name }}">
                                        <i class="ri-delete-bin-line"></i>
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
        @else
            <div class="card custom-card">
                <div class="card-body">
                    <div class="text-center py-5">
                        <i class="ri-inbox-line fs-48 text-muted"></i>
                        <h5 class="mt-3 mb-2">No WhatsApp Providers</h5>
                        <p class="text-muted mb-4">Get started by adding your first WhatsApp provider to send messages.</p>
                        <a href="{{ route('whatsapp-providers.create') }}" class="btn btn-primary">
                            <i class="ri-add-line me-1"></i>Add Your First Provider
                        </a>
                    </div>
                </div>
            </div>
        @endif
    </div>

    @push('styles')
        <style>
            .provider-card {
                border: 1px solid #e9ecef;
                border-radius: 12px;
            }

            .provider-card:hover {
                transform: translateY(-4px);
                box-shadow: 0 8px 24px rgba(0, 0, 0, 0.1);
                border-color: #25D366;
            }

            .provider-icon-wrapper {
                transition: transform 0.3s ease;
            }

            .provider-card:hover .provider-icon-wrapper {
                transform: scale(1.1);
            }

            .badge-dot {
                display: inline-block;
                width: 6px;
                height: 6px;
                border-radius: 50%;
            }

            .provider-card .btn-icon {
                transition: all 0.2s ease;
                position: relative;
                z-index: 10;
            }

            .provider-card .btn-icon:hover {
                transform: scale(1.1);
            }

            .provider-card .btn,
            .provider-card a,
            .provider-card form {
                position: relative;
                z-index: 10;
            }

            .provider-card .card-body {
                position: relative;
            }
        </style>
    @endpush

    @push('scripts')
        <script>
            document.addEventListener('DOMContentLoaded', function() {
                // Ensure all action buttons work properly
                const actionButtons = document.querySelectorAll('.provider-card .btn, .provider-card a');
                actionButtons.forEach(function(button) {
                    button.addEventListener('click', function(e) {
                        // Allow event to propagate normally
                        e.stopPropagation();
                    });
                });

                // Prevent card from interfering with form submissions
                const forms = document.querySelectorAll('.provider-card form');
                forms.forEach(function(form) {
                    form.addEventListener('submit', function(e) {
                        e.stopPropagation();
                    });
                });
            });
        </script>
    @endpush
@endsection
