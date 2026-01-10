@extends('layouts.app')

@section('content')
    <div class="container-fluid">

        <!-- Page Header -->
        <div class="d-md-flex d-block align-items-center justify-content-between my-4 page-header-breadcrumb">
            <h1 class="page-title fw-semibold fs-18 mb-0">Setup Business Profile</h1>
            <div class="ms-md-1 ms-0">
                <nav>
                    <ol class="breadcrumb mb-0">
                        <li class="breadcrumb-item"><a href="{{ route('home') }}">Dashboard</a></li>
                        <li class="breadcrumb-item active" aria-current="page">Create Profile</li>
                    </ol>
                </nav>
            </div>
        </div>
        <!-- Page Header Close -->

        <!-- Alert Messages -->
        @if (session('success'))
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                {{ session('success') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        @endif

        <div class="row">
            <div class="col-xl-8 mx-auto">
                <div class="card custom-card">
                    <div class="card-header">
                        <div class="card-title">
                            Business Information
                        </div>
                    </div>
                    <div class="card-body">
                        <form method="POST" action="{{ route('business-profile.store') }}" enctype="multipart/form-data">
                            @csrf

                            <!-- Basic Information Section -->
                            <div class="row gy-3">
                                <div class="col-xl-12">
                                    <label for="business_name" class="form-label">Business Name <span class="text-danger">*</span></label>
                                    <input type="text" 
                                           class="form-control @error('business_name') is-invalid @enderror" 
                                           id="business_name" 
                                           name="business_name" 
                                           value="{{ old('business_name') }}" 
                                           placeholder="Enter your business name"
                                           required>
                                    @error('business_name')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="col-xl-6">
                                    <label for="tax_id" class="form-label">Tax ID / VAT Number</label>
                                    <div class="input-group">
                                        <span class="input-group-text"><i class="ri-file-text-line"></i></span>
                                        <input type="text" 
                                               class="form-control @error('tax_id') is-invalid @enderror" 
                                               id="tax_id" 
                                               name="tax_id" 
                                               value="{{ old('tax_id') }}"
                                               placeholder="e.g., VAT123456">
                                        @error('tax_id')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                    <small class="text-muted">Optional: For tax invoices</small>
                                </div>

                                <div class="col-xl-6">
                                    <label for="currency" class="form-label">Currency <span class="text-danger">*</span></label>
                                    <select class="form-select @error('currency') is-invalid @enderror" id="currency" name="currency" required>
                                        <option value="">Select Currency</option>
                                        <option value="USD" {{ old('currency') == 'USD' ? 'selected' : '' }}>USD - US Dollar</option>
                                        <option value="EUR" {{ old('currency') == 'EUR' ? 'selected' : '' }}>EUR - Euro</option>
                                        <option value="GBP" {{ old('currency') == 'GBP' ? 'selected' : '' }}>GBP - British Pound</option>
                                        <option value="PKR" {{ old('currency') == 'PKR' ? 'selected' : '' }}>PKR - Pakistani Rupee</option>
                                        <option value="INR" {{ old('currency') == 'INR' ? 'selected' : '' }}>INR - Indian Rupee</option>
                                        <option value="AED" {{ old('currency') == 'AED' ? 'selected' : '' }}>AED - UAE Dirham</option>
                                        <option value="SAR" {{ old('currency') == 'SAR' ? 'selected' : '' }}>SAR - Saudi Riyal</option>
                                    </select>
                                    @error('currency')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>

                            <hr class="my-4">

                            <!-- Contact Information Section -->
                            <h6 class="fw-semibold mb-3">Contact Information</h6>
                            <div class="row gy-3">
                                <div class="col-xl-6">
                                    <label for="whatsapp_number" class="form-label">WhatsApp Number</label>
                                    <div class="input-group">
                                        <span class="input-group-text"><i class="ri-whatsapp-line"></i></span>
                                        <input type="text" 
                                               class="form-control @error('whatsapp_number') is-invalid @enderror" 
                                               id="whatsapp_number" 
                                               name="whatsapp_number" 
                                               value="{{ old('whatsapp_number') }}"
                                               placeholder="+1234567890">
                                        @error('whatsapp_number')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                    <small class="text-muted">For WhatsApp notifications</small>
                                </div>

                                <div class="col-xl-6">
                                    <label for="email" class="form-label">Business Email</label>
                                    <div class="input-group">
                                        <span class="input-group-text"><i class="ri-mail-line"></i></span>
                                        <input type="email" 
                                               class="form-control @error('email') is-invalid @enderror" 
                                               id="email" 
                                               name="email" 
                                               value="{{ old('email') }}"
                                               placeholder="business@example.com">
                                        @error('email')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>

                                <div class="col-xl-12">
                                    <label for="address" class="form-label">Business Address</label>
                                    <textarea class="form-control @error('address') is-invalid @enderror" 
                                              id="address" 
                                              name="address" 
                                              rows="3"
                                              placeholder="Enter your complete business address">{{ old('address') }}</textarea>
                                    @error('address')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>

                            <hr class="my-4">

                            <!-- Additional Settings Section -->
                            <h6 class="fw-semibold mb-3">Additional Settings</h6>
                            <div class="row gy-3">
                                <div class="col-xl-6">
                                    <label for="timezone" class="form-label">Timezone</label>
                                    <select class="form-select @error('timezone') is-invalid @enderror" id="timezone" name="timezone">
                                        <option value="">Select Timezone</option>
                                        <option value="UTC" {{ old('timezone') == 'UTC' ? 'selected' : '' }}>UTC</option>
                                        <option value="America/New_York" {{ old('timezone') == 'America/New_York' ? 'selected' : '' }}>Eastern Time (US)</option>
                                        <option value="America/Chicago" {{ old('timezone') == 'America/Chicago' ? 'selected' : '' }}>Central Time (US)</option>
                                        <option value="America/Los_Angeles" {{ old('timezone') == 'America/Los_Angeles' ? 'selected' : '' }}>Pacific Time (US)</option>
                                        <option value="Europe/London" {{ old('timezone') == 'Europe/London' ? 'selected' : '' }}>London</option>
                                        <option value="Europe/Paris" {{ old('timezone') == 'Europe/Paris' ? 'selected' : '' }}>Paris</option>
                                        <option value="Asia/Dubai" {{ old('timezone') == 'Asia/Dubai' ? 'selected' : '' }}>Dubai</option>
                                        <option value="Asia/Karachi" {{ old('timezone') == 'Asia/Karachi' ? 'selected' : '' }}>Karachi</option>
                                        <option value="Asia/Kolkata" {{ old('timezone') == 'Asia/Kolkata' ? 'selected' : '' }}>India</option>
                                    </select>
                                    @error('timezone')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="col-xl-6">
                                    <label for="image" class="form-label">Business Logo</label>
                                    <input type="file" 
                                           class="form-control @error('image') is-invalid @enderror" 
                                           id="image" 
                                           name="image"
                                           accept="image/*">
                                    @error('image')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                    <small class="text-muted">Recommended: 200x200px, PNG or JPG</small>
                                </div>

                                <div class="col-xl-12">
                                    <div class="form-check form-switch">
                                        <input class="form-check-input" 
                                               type="checkbox" 
                                               id="auto_apply_reminders" 
                                               name="auto_apply_reminders"
                                               value="1"
                                               checked>
                                        <label class="form-check-label" for="auto_apply_reminders">
                                            Auto-apply reminder rules to new invoices
                                        </label>
                                    </div>
                                    <small class="text-muted">Automatically send payment reminders based on your default rule</small>
                                </div>
                            </div>

                            <hr class="my-4">

                            <!-- Submit Button -->
                            <div class="d-flex justify-content-end gap-2">
                                <a href="{{ route('home') }}" class="btn btn-light">Cancel</a>
                                <button type="submit" class="btn btn-primary">
                                    <i class="ri-check-line me-1"></i> Create Business Profile
                                </button>
                            </div>
                        </form>
                    </div>
                </div>

                <!-- Help Card -->
                <div class="card custom-card">
                    <div class="card-body">
                        <div class="d-flex align-items-center">
                            <div class="me-3">
                                <span class="avatar avatar-md avatar-rounded bg-primary-transparent">
                                    <i class="ri-information-line fs-18"></i>
                                </span>
                            </div>
                            <div>
                                <h6 class="fw-semibold mb-1">Setting up your business profile</h6>
                                <p class="mb-0 text-muted fs-12">
                                    This information will appear on your invoices and help clients identify your business. 
                                    You can update this information anytime from your settings.
                                </p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
