@extends('layouts.app')

@section('content')
    <div class="container-fluid">

        <!-- Page Header -->
        <div class="d-md-flex d-block align-items-center justify-content-between my-4 page-header-breadcrumb">
            <h1 class="page-title fw-semibold fs-18 mb-0">Business Profile</h1>
            <div class="ms-md-1 ms-0">
                <nav>
                    <ol class="breadcrumb mb-0">
                        <li class="breadcrumb-item"><a href="{{ route('home') }}">Dashboard</a></li>
                        <li class="breadcrumb-item active" aria-current="page">Business Profile</li>
                    </ol>
                </nav>
            </div>
        </div>
        <!-- Page Header Close -->

        <div class="row">
            <!-- Profile Card -->
            <div class="col-xl-8">
                <div class="card custom-card">
                    <div class="card-header justify-content-between">
                        <div class="card-title">
                            Business Information
                        </div>
                        <div>
                            <a href="{{ route('business-profile.edit', $businessProfile) }}" class="btn btn-sm btn-primary btn-wave waves-light">
                                <i class="ri-edit-line fw-semibold align-middle me-1"></i> Edit Profile
                            </a>
                        </div>
                    </div>
                    <div class="card-body">
                        <!-- Business Logo -->
                        @if($businessProfile->image)
                            <div class="text-center mb-4">
                                <img src="{{ asset('storage/' . $businessProfile->image) }}" alt="Business Logo" class="avatar avatar-xxl avatar-rounded">
                            </div>
                        @endif

                        <!-- Business Details Grid -->
                        <div class="row g-4">
                            <div class="col-xl-6 col-lg-6 col-md-6 col-sm-6">
                                <div class="p-3 border rounded">
                                    <div class="d-flex align-items-start">
                                        <div class="me-3">
                                            <span class="avatar avatar-md avatar-rounded bg-primary-transparent">
                                                <i class="ri-building-line fs-18"></i>
                                            </span>
                                        </div>
                                        <div class="flex-fill">
                                            <h6 class="fw-semibold mb-1 text-muted fs-12">Business Name</h6>
                                            <div class="fs-14 fw-semibold">{{ $businessProfile->business_name }}</div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            @if($businessProfile->tax_id)
                                <div class="col-xl-6 col-lg-6 col-md-6 col-sm-6">
                                    <div class="p-3 border rounded">
                                        <div class="d-flex align-items-start">
                                            <div class="me-3">
                                                <span class="avatar avatar-md avatar-rounded bg-secondary-transparent">
                                                    <i class="ri-file-text-line fs-18"></i>
                                                </span>
                                            </div>
                                            <div class="flex-fill">
                                                <h6 class="fw-semibold mb-1 text-muted fs-12">Tax ID / VAT</h6>
                                                <div class="fs-14 fw-semibold">{{ $businessProfile->tax_id }}</div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            @endif

                            @if($businessProfile->whatsapp_number)
                                <div class="col-xl-6 col-lg-6 col-md-6 col-sm-6">
                                    <div class="p-3 border rounded">
                                        <div class="d-flex align-items-start">
                                            <div class="me-3">
                                                <span class="avatar avatar-md avatar-rounded bg-success-transparent">
                                                    <i class="ri-whatsapp-line fs-18"></i>
                                                </span>
                                            </div>
                                            <div class="flex-fill">
                                                <h6 class="fw-semibold mb-1 text-muted fs-12">WhatsApp Number</h6>
                                                <div class="fs-14 fw-semibold">{{ $businessProfile->whatsapp_number }}</div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            @endif

                            @if($businessProfile->email)
                                <div class="col-xl-6 col-lg-6 col-md-6 col-sm-6">
                                    <div class="p-3 border rounded">
                                        <div class="d-flex align-items-start">
                                            <div class="me-3">
                                                <span class="avatar avatar-md avatar-rounded bg-info-transparent">
                                                    <i class="ri-mail-line fs-18"></i>
                                                </span>
                                            </div>
                                            <div class="flex-fill">
                                                <h6 class="fw-semibold mb-1 text-muted fs-12">Email Address</h6>
                                                <div class="fs-14 fw-semibold">{{ $businessProfile->email }}</div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            @endif

                            @if($businessProfile->address)
                                <div class="col-12">
                                    <div class="p-3 border rounded">
                                        <div class="d-flex align-items-start">
                                            <div class="me-3">
                                                <span class="avatar avatar-md avatar-rounded bg-warning-transparent">
                                                    <i class="ri-map-pin-line fs-18"></i>
                                                </span>
                                            </div>
                                            <div class="flex-fill">
                                                <h6 class="fw-semibold mb-1 text-muted fs-12">Business Address</h6>
                                                <div class="fs-14">{{ $businessProfile->address }}</div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            @endif

                            <div class="col-xl-6 col-lg-6 col-md-6 col-sm-6">
                                <div class="p-3 border rounded">
                                    <div class="d-flex align-items-start">
                                        <div class="me-3">
                                            <span class="avatar avatar-md avatar-rounded bg-pink-transparent">
                                                <i class="ri-money-dollar-circle-line fs-18"></i>
                                            </span>
                                        </div>
                                        <div class="flex-fill">
                                            <h6 class="fw-semibold mb-1 text-muted fs-12">Currency</h6>
                                            <div class="fs-14 fw-semibold">{{ $businessProfile->currency ?? 'USD' }}</div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            @if($businessProfile->timezone)
                                <div class="col-xl-6 col-lg-6 col-md-6 col-sm-6">
                                    <div class="p-3 border rounded">
                                        <div class="d-flex align-items-start">
                                            <div class="me-3">
                                                <span class="avatar avatar-md avatar-rounded bg-teal-transparent">
                                                    <i class="ri-time-line fs-18"></i>
                                                </span>
                                            </div>
                                            <div class="flex-fill">
                                                <h6 class="fw-semibold mb-1 text-muted fs-12">Timezone</h6>
                                                <div class="fs-14 fw-semibold">{{ $businessProfile->timezone }}</div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            @endif
                        </div>
                    </div>
                </div>
            </div>

            <!-- Settings Card -->
            <div class="col-xl-4">
                <div class="card custom-card">
                    <div class="card-header">
                        <div class="card-title">
                            Reminder Settings
                        </div>
                    </div>
                    <div class="card-body">
                        <div class="mb-4">
                            <div class="d-flex align-items-center justify-content-between mb-2">
                                <label class="form-label mb-0">Auto-Apply Reminders</label>
                                <div class="form-check form-switch">
                                    <input class="form-check-input" type="checkbox" disabled {{ $businessProfile->auto_apply_reminders ? 'checked' : '' }}>
                                </div>
                            </div>
                            <small class="text-muted">Automatically apply reminder rules to new invoices</small>
                        </div>

                        @if($businessProfile->default_reminder_rule_id)
                            <div>
                                <label class="form-label">Default Reminder Rule</label>
                                <div class="alert alert-primary mb-0" role="alert">
                                    <i class="ri-information-line me-2"></i>
                                    Rule ID: {{ $businessProfile->default_reminder_rule_id }}
                                </div>
                            </div>
                        @endif
                    </div>
                </div>

                <!-- Quick Stats -->
                <div class="card custom-card">
                    <div class="card-header">
                        <div class="card-title">
                            Profile Status
                        </div>
                    </div>
                    <div class="card-body">
                        <div class="mb-3">
                            <div class="d-flex justify-content-between mb-2">
                                <span class="text-muted">Profile Completion</span>
                                <span class="fw-semibold">{{ calculateCompletion($businessProfile) }}%</span>
                            </div>
                            <div class="progress progress-sm">
                                <div class="progress-bar bg-primary" role="progressbar" style="width: {{ calculateCompletion($businessProfile) }}%" 
                                     aria-valuenow="{{ calculateCompletion($businessProfile) }}" aria-valuemin="0" aria-valuemax="100"></div>
                            </div>
                        </div>

                        <div class="list-group">
                            <div class="list-group-item d-flex justify-content-between align-items-center">
                                <span><i class="ri-check-line text-success me-2"></i>Business Name</span>
                                <span class="badge bg-success">✓</span>
                            </div>
                            <div class="list-group-item d-flex justify-content-between align-items-center">
                                <span><i class="ri-{{ $businessProfile->whatsapp_number ? 'check-line text-success' : 'close-line text-danger' }} me-2"></i>WhatsApp</span>
                                <span class="badge bg-{{ $businessProfile->whatsapp_number ? 'success' : 'danger' }}">{{ $businessProfile->whatsapp_number ? '✓' : '✗' }}</span>
                            </div>
                            <div class="list-group-item d-flex justify-content-between align-items-center">
                                <span><i class="ri-{{ $businessProfile->email ? 'check-line text-success' : 'close-line text-danger' }} me-2"></i>Email</span>
                                <span class="badge bg-{{ $businessProfile->email ? 'success' : 'danger' }}">{{ $businessProfile->email ? '✓' : '✗' }}</span>
                            </div>
                            <div class="list-group-item d-flex justify-content-between align-items-center">
                                <span><i class="ri-{{ $businessProfile->address ? 'check-line text-success' : 'close-line text-danger' }} me-2"></i>Address</span>
                                <span class="badge bg-{{ $businessProfile->address ? 'success' : 'danger' }}">{{ $businessProfile->address ? '✓' : '✗' }}</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    @php
    function calculateCompletion($profile) {
        $fields = ['business_name', 'tax_id', 'whatsapp_number', 'email', 'address', 'image', 'currency', 'timezone'];
        $filled = 0;
        foreach ($fields as $field) {
            if (!empty($profile->$field)) $filled++;
        }
        return round(($filled / count($fields)) * 100);
    }
    @endphp
@endsection
