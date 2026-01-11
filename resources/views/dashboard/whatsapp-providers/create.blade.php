@extends('layouts.app')

@section('content')
    <div class="container-fluid">
        <div class="d-md-flex d-block align-items-center justify-content-between my-4 page-header-breadcrumb">
            <h1 class="page-title fw-semibold fs-18 mb-0">Create WhatsApp Provider</h1>
            <div class="ms-md-1 ms-0">
                <nav>
                    <ol class="breadcrumb mb-0">
                        <li class="breadcrumb-item"><a href="{{ route('home') }}">Dashboard</a></li>
                        <li class="breadcrumb-item"><a href="{{ route('whatsapp-providers.index') }}">WhatsApp Providers</a>
                        </li>
                        <li class="breadcrumb-item active" aria-current="page">Create</li>
                    </ol>
                </nav>
            </div>
        </div>

        <div class="card custom-card">
            <div class="card-body">
                <form action="{{ route('whatsapp-providers.store') }}" method="POST" id="providerForm">
                    @csrf
                    <div class="row g-3">
                        <!-- Provider Name -->
                        <div class="col-md-6">
                            <label class="form-label">Provider Name <span class="text-danger">*</span></label>
                            <input type="text" name="name" class="form-control" value="{{ old('name') }}" required
                                placeholder="e.g., My WhatsApp Cloud API">
                            @error('name')
                                <div class="text-danger small">{{ $message }}</div>
                            @enderror
                        </div>

                        <!-- Provider Type -->
                        <div class="col-md-6">
                            <label class="form-label">Provider Type <span class="text-danger">*</span></label>
                            <select name="type" id="providerType" class="form-control" required>
                                <option value="">Select Provider Type</option>
                                @foreach ($typeOptions as $value => $label)
                                    <option value="{{ $value }}"
                                        {{ old('type') == $value ? 'selected' : '' }}>{{ $label }}</option>
                                @endforeach
                            </select>
                            @error('type')
                                <div class="text-danger small">{{ $message }}</div>
                            @enderror
                        </div>

                        <!-- Credentials Section (Dynamic based on type) -->
                        <div class="col-12">
                            <div id="credentialsSection" style="display: none;">
                                <h6 class="mb-3">Provider Credentials</h6>
                                <div class="row g-3" id="credentialsFields">
                                    <!-- Fields will be dynamically inserted here -->
                                </div>
                            </div>
                        </div>

                        <!-- Status -->
                        <div class="col-md-6">
                            <label class="form-label">Status</label>
                            <div class="form-check form-switch">
                                <input class="form-check-input" type="checkbox" name="is_active" value="1"
                                    id="isActive" {{ old('is_active', true) ? 'checked' : '' }}>
                                <label class="form-check-label" for="isActive">Active</label>
                            </div>
                        </div>

                        <!-- Default -->
                        <div class="col-md-6">
                            <label class="form-label">Set as Default</label>
                            <div class="form-check form-switch">
                                <input class="form-check-input" type="checkbox" name="is_default" value="1"
                                    id="isDefault" {{ old('is_default') ? 'checked' : '' }}>
                                <label class="form-check-label" for="isDefault">Default Provider</label>
                            </div>
                        </div>

                        <!-- Notes -->
                        <div class="col-12">
                            <label class="form-label">Notes</label>
                            <textarea name="notes" class="form-control" rows="3"
                                placeholder="Optional notes about this provider">{{ old('notes') }}</textarea>
                            @error('notes')
                                <div class="text-danger small">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    <div class="mt-4 d-flex justify-content-end gap-2">
                        <a href="{{ route('whatsapp-providers.index') }}" class="btn btn-outline-secondary">Cancel</a>
                        <button type="submit" class="btn btn-primary">Create Provider</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script>
        // Provider type credentials mapping
        const credentialsMap = {
            whatsapp_cloud_api: {
                'access_token': {
                    label: 'Access Token',
                    type: 'text',
                    required: true,
                    placeholder: 'Enter your WhatsApp Cloud API access token'
                },
                'phone_number_id': {
                    label: 'Phone Number ID',
                    type: 'text',
                    required: true,
                    placeholder: 'Enter your phone number ID'
                },
                'business_account_id': {
                    label: 'Business Account ID',
                    type: 'text',
                    required: true,
                    placeholder: 'Enter your business account ID'
                },
                'app_id': {
                    label: 'App ID',
                    type: 'text',
                    required: false,
                    placeholder: 'Enter your app ID (optional)'
                },
                'app_secret': {
                    label: 'App Secret',
                    type: 'text',
                    required: false,
                    placeholder: 'Enter your app secret (optional)'
                }
            },
            twilio: {
                'account_sid': {
                    label: 'Account SID',
                    type: 'text',
                    required: true,
                    placeholder: 'Enter your Twilio account SID'
                },
                'auth_token': {
                    label: 'Auth Token',
                    type: 'password',
                    required: true,
                    placeholder: 'Enter your Twilio auth token'
                },
                'from_phone_number': {
                    label: 'From Phone Number',
                    type: 'text',
                    required: true,
                    placeholder: 'Enter your Twilio phone number'
                },
                'whatsapp_sandbox_number': {
                    label: 'WhatsApp Sandbox Number',
                    type: 'text',
                    required: false,
                    placeholder: 'Enter WhatsApp sandbox number (optional)'
                }
            },
            vonage: {
                'api_key': {
                    label: 'API Key',
                    type: 'text',
                    required: true,
                    placeholder: 'Enter your Vonage API key'
                },
                'api_secret': {
                    label: 'API Secret',
                    type: 'password',
                    required: true,
                    placeholder: 'Enter your Vonage API secret'
                },
                'from_number': {
                    label: 'From Number',
                    type: 'text',
                    required: true,
                    placeholder: 'Enter your Vonage phone number'
                },
                'application_id': {
                    label: 'Application ID',
                    type: 'text',
                    required: false,
                    placeholder: 'Enter your application ID (optional)'
                }
            }
        };

        // Handle provider type change
        document.getElementById('providerType').addEventListener('change', function() {
            const type = this.value;
            const credentialsSection = document.getElementById('credentialsSection');
            const credentialsFields = document.getElementById('credentialsFields');

            if (type && credentialsMap[type]) {
                credentialsSection.style.display = 'block';
                credentialsFields.innerHTML = '';

                Object.entries(credentialsMap[type]).forEach(([name, config]) => {
                    const fieldDiv = document.createElement('div');
                    fieldDiv.className = 'col-md-6';

                    const label = document.createElement('label');
                    label.className = 'form-label';
                    label.textContent = config.label;
                    if (config.required) {
                        const span = document.createElement('span');
                        span.className = 'text-danger';
                        span.textContent = ' *';
                        label.appendChild(span);
                    }

                    const input = document.createElement('input');
                    input.type = config.type;
                    input.name = name;
                    input.className = 'form-control';
                    input.placeholder = config.placeholder;
                    if (config.required) {
                        input.required = true;
                    }

                    const errorDiv = document.createElement('div');
                    errorDiv.className = 'text-danger small';
                    errorDiv.id = 'error-' + name.replace(/[\[\]]/g, '-');

                    fieldDiv.appendChild(label);
                    fieldDiv.appendChild(input);
                    fieldDiv.appendChild(errorDiv);
                    credentialsFields.appendChild(fieldDiv);
                });
            } else {
                credentialsSection.style.display = 'none';
                credentialsFields.innerHTML = '';
            }
        });

        // Trigger change on page load if type is already selected
        @if (old('type'))
            document.getElementById('providerType').dispatchEvent(new Event('change'));
        @endif
    </script>
@endsection

