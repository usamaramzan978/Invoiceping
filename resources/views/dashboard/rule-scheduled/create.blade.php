@extends('layouts.app')

@section('content')
    <div class="container-fluid">

        <!-- Page Header -->
        <div class="d-md-flex d-block align-items-center justify-content-between my-4 page-header-breadcrumb">
            <h1 class="page-title fw-semibold fs-18 mb-0">Schedule Rule-Based Reminder</h1>
            <div class="ms-md-1 ms-0">
                <nav>
                    <ol class="breadcrumb mb-0">
                        <li class="breadcrumb-item"><a href="javascript:void(0);">Automation</a></li>
                        <li class="breadcrumb-item"><a href="{{ route('schedule-reminders.index') }}">All Scheduled</a></li>
                        <li class="breadcrumb-item active" aria-current="page">Rule Scheduled</li>
                    </ol>
                </nav>
            </div>
        </div>

        @if ($errors->any())
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                <i class="ri-error-warning-line me-2"></i>
                <strong>Please fix the following errors:</strong>
                <ul class="mb-0 mt-2">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        @endif

        <div class="row">
            <div class="col-xl-8">
                <div class="card custom-card">
                    <div class="card-header bg-light">
                        <div class="card-title">Rule-Based Reminder Configuration</div>
                    </div>
                    <div class="card-body">
                        <form action="{{ route('rule-scheduled.store') }}" method="POST" id="reminderForm">
                            @csrf
                            <div class="row gy-4">

                                <!-- STEP 1: Select Invoice -->
                                <div class="col-md-6">
                                    <label for="invoice_id" class="form-label fw-600">
                                        <span class="badge bg-primary">Step 1</span> Select Invoice(s)
                                    </label>
                                    <select name="invoice_ids[]" id="invoice_id"
                                        class="form-select select2-multiple-invoice" required multiple>
                                        @foreach ($invoices as $invoice)
                                            <option value="{{ $invoice->id }}"
                                                {{ is_array(old('invoice_ids')) && in_array($invoice->id, old('invoice_ids')) ? 'selected' : '' }}>
                                                #{{ $invoice->invoice_number }} - {{ $invoice->client->name }}
                                                ({{ $invoice->total_amount }} {{ $invoice->currency }})
                                            </option>
                                        @endforeach
                                    </select>
                                    @error('invoice_ids')
                                        <div class="text-danger small">{{ $message }}</div>
                                    @enderror
                                </div>

                                <!-- STEP 2: Select Rule -->
                                <div class="col-md-6">
                                    <label for="reminder_rule_id" class="form-label fw-600">
                                        <span class="badge bg-success">Step 2</span> Select Predefined Rule
                                    </label>
                                    <select name="reminder_rule_id" id="reminder_rule_id" class="form-select select2-single"
                                        required>
                                        <option value="">Choose a rule...</option>
                                        @foreach ($rules as $rule)
                                            <option value="{{ $rule->id }}"
                                                {{ old('reminder_rule_id') == $rule->id ? 'selected' : '' }}>
                                                {{ $rule->name }}
                                            </option>
                                        @endforeach
                                    </select>
                                    <small class="text-muted d-block mt-1">Selecting a rule will schedule all its steps
                                        automatically.</small>
                                    @error('reminder_rule_id')
                                        <div class="text-danger small">{{ $message }}</div>
                                    @enderror
                                </div>

                                <!-- STEP 3: Schedule Date/Time -->
                                <div class="col-md-6">
                                    <label for="scheduled_at" class="form-label fw-600">
                                        <span class="badge bg-info">Step 3</span> Reference Date & Time
                                    </label>
                                    <input type="datetime-local" class="form-control" id="scheduled_at" name="scheduled_at"
                                        value="{{ old('scheduled_at') }}" required>
                                    <small class="text-muted d-block mt-1">⏰ This date will be used as the reference point
                                        for calculating when each step in the rule should be sent.</small>
                                    @error('scheduled_at')
                                        <div class="text-danger small">{{ $message }}</div>
                                    @enderror
                                </div>

                                <!-- Form Actions -->
                                <div class="col-12 text-end pt-3">
                                    <a href="{{ route('schedule-reminders.index') }}" class="btn btn-secondary me-2">
                                        <i class="ri-arrow-left-line"></i> Cancel
                                    </a>
                                    <button type="submit" class="btn btn-primary">
                                        <i class="ri-check-line"></i> Schedule Rule
                                    </button>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            </div>

            <!-- Info Card -->
            <div class="col-xl-4">
                <div class="card custom-card border-success">
                    <div class="card-header bg-light">
                        <div class="card-title text-success">💡 How Rule Scheduling Works</div>
                    </div>
                    <div class="card-body">
                        <div class="mb-3">
                            <h6 class="fw-600 mb-2">⚙️ Automated Reminders</h6>
                            <p class="text-muted small mb-0">When you select a rule, the system will automatically schedule
                                all reminder steps defined in that rule. Each step will be calculated based on the reference
                                date you provide.</p>
                        </div>
                        <hr>
                        <div class="mb-3">
                            <h6 class="fw-600 mb-2">📅 Reference Date</h6>
                            <p class="text-muted small mb-0">The reference date is used to calculate when each step should
                                be sent. For example, if a step is "3 days before due date", it will be scheduled 3 days
                                before your reference date.</p>
                        </div>
                        <hr>
                        <div class="alert alert-info py-2 mb-0" style="font-size: 12px;">
                            <strong>Pro Tip:</strong> Use rule-based scheduling for automated follow-up sequences. Perfect
                            for recurring reminder workflows!
                        </div>
                    </div>
                </div>
            </div>
        </div>

    </div>
@endsection

@section('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const reminderForm = document.getElementById('reminderForm');
            const scheduledAtInput = document.getElementById('scheduled_at');

            // Initialize Select2
            $('.select2-multiple-invoice').select2({
                placeholder: "Select invoices...",
                allowClear: false,
                width: '100%'
            });

            $('#reminder_rule_id').select2({
                allowClear: false,
                width: '100%'
            });

            // Form validation
            reminderForm.addEventListener('submit', function(e) {
                if (!scheduledAtInput.value) {
                    e.preventDefault();
                    alert('Please select a reference date and time');
                    return false;
                }

                // Validate that scheduled date is in the future
                const scheduledDate = new Date(scheduledAtInput.value);
                const now = new Date();
                if (scheduledDate <= now) {
                    e.preventDefault();
                    alert(
                        'The reference date must be in the future. Please select a future date and time.'
                        );
                    scheduledAtInput.focus();
                    return false;
                }
            });
        });
    </script>
@endsection
