@extends('layouts.app')

@section('content')
    <div class="container-fluid">

        <!-- Page Header -->
        <div class="d-md-flex d-block align-items-center justify-content-between my-4 page-header-breadcrumb">
            <h1 class="page-title fw-semibold fs-18 mb-0">Scheduled Reminders</h1>
            <div class="ms-md-1 ms-0">
                <nav>
                    <ol class="breadcrumb mb-0">
                        <li class="breadcrumb-item"><a href="javascript:void(0);">Automation</a></li>
                        <li class="breadcrumb-item active" aria-current="page">Scheduled Reminders</li>
                    </ol>
                </nav>
            </div>
        </div>
        <!-- Page Header Close -->

        <!-- Alert Messages -->
        @if (session('success'))
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                <i class="ri-check-line me-2"></i>{{ session('success') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        @endif

        @if (session('error'))
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                <i class="ri-error-warning-line me-2"></i>{{ session('error') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        @endif

        <div class="row">
            <div class="col-xl-12">
                <div class="card custom-card">
                    <div class="card-header justify-content-between">
                        <div class="card-title">
                            Manage Scheduled Reminders
                        </div>
                        <div class="d-flex">
                            <div class="btn-group me-2">
                                <button type="button" class="btn btn-primary btn-sm dropdown-toggle"
                                    data-bs-toggle="dropdown" aria-expanded="false">
                                    <i class="ri-add-line me-1"></i> Schedule Reminder
                                </button>
                                <ul class="dropdown-menu">
                                    <li><a class="dropdown-item" href="{{ route('rule-scheduled.create') }}">
                                            <i class="ri-settings-3-line me-2"></i> Schedule Rule-Based
                                        </a></li>
                                    <li><a class="dropdown-item" href="{{ route('manual-scheduled.create') }}">
                                            <i class="ri-edit-line me-2"></i> Schedule Manual
                                        </a></li>
                                </ul>
                            </div>
                            <form action="{{ route('schedule-reminders.index') }}" method="GET" class="d-flex">
                                <select name="status" class="form-select form-select-sm me-2"
                                    onchange="this.form.submit()">
                                    <option value="">All Statuses</option>
                                    @foreach (\App\Enums\ReminderStatusEnum::cases() as $status)
                                        <option value="{{ $status->value }}"
                                            {{ request('status') == $status->value ? 'selected' : '' }}>
                                            {{ $status->label() }}
                                        </option>
                                    @endforeach
                                </select>
                            </form>
                        </div>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table text-nowrap table-bordered">
                                <thead>
                                    <tr>
                                        <th scope="col">Invoice</th>
                                        <th scope="col">Client</th>
                                        <th scope="col">Rule / Step</th>
                                        <th scope="col">Channel / Template</th>
                                        <th scope="col">Scheduled At</th>
                                        <th scope="col">Status</th>
                                        <th scope="col">Action</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse ($schedules as $schedule)
                                        @php
                                            $allSchedules = $schedule->all_schedules_in_group ?? collect([$schedule]);
                                            $invoiceCount = $allSchedules->count();
                                            $uniqueClients = $allSchedules->pluck('invoice.client.name')->unique();
                                        @endphp
                                        <tr>
                                            <td>
                                                @if ($invoiceCount > 1)
                                                    <div class="d-flex flex-wrap gap-1">
                                                        @foreach ($allSchedules as $s)
                                                            <a href="{{ route('invoices.show', $s->invoice) }}"
                                                                class="badge bg-primary-transparent"
                                                                title="{{ $s->invoice->client->name }}">
                                                                #{{ $s->invoice->invoice_number }}
                                                            </a>
                                                        @endforeach
                                                    </div>
                                                @else
                                                    <a href="{{ route('invoices.show', $schedule->invoice) }}"
                                                        class="fw-semibold text-primary">
                                                        #{{ $schedule->invoice->invoice_number }}
                                                    </a>
                                                @endif
                                            </td>
                                            <td>
                                                @if ($uniqueClients->count() > 1)
                                                    <p class="mb-0 fw-semibold">Multiple Clients</p>
                                                    <small class="text-muted">{{ $uniqueClients->count() }} clients</small>
                                                @else
                                                    <p class="mb-0 fw-semibold">{{ $schedule->invoice->client->name }}</p>
                                                    <small
                                                        class="text-muted">{{ $schedule->invoice->client->email }}</small>
                                                @endif
                                            </td>
                                            <td>
                                                <p class="mb-0">{{ $schedule->rule->name ?? 'Manual' }}</p>
                                                <small class="text-info">
                                                    @if ($schedule->step)
                                                        {{ $schedule->step->reminder_type }}
                                                        ({{ $schedule->step->offset_days }} days)
                                                    @else
                                                        Manually Scheduled
                                                    @endif
                                                </small>
                                            </td>
                                            <td>
                                                @php
                                                    $channel = $schedule->channel->value;
                                                    $isEmail = $channel === 'email';
                                                    $isWhatsApp = $channel === 'whatsapp';
                                                    $isSMS = $channel === 'sms';

                                                    $badgeClass = match ($channel) {
                                                        'email' => 'bg-primary',
                                                        'whatsapp' => 'bg-success',
                                                        'sms' => 'bg-info',
                                                        default => 'bg-secondary',
                                                    };

                                                    $icon = match ($channel) {
                                                        'email' => 'ri-mail-line',
                                                        'whatsapp' => 'ri-whatsapp-line',
                                                        'sms' => 'ri-message-2-line',
                                                        default => 'ri-question-line',
                                                    };

                                                    // Get template name
                                                    $templateName = null;
                                                    if ($isEmail && $schedule->emailTemplate) {
                                                        $templateName = $schedule->emailTemplate->name;
                                                    } elseif (($isWhatsApp || $isSMS) && $schedule->messageTemplate) {
                                                        $templateName = $schedule->messageTemplate->name;
                                                    }
                                                @endphp

                                                <div class="d-flex flex-column align-items-start">
                                                    <span class="badge {{ $badgeClass }}">
                                                        <i class="{{ $icon }} me-1"></i>
                                                        {{ ucfirst($channel) }}
                                                    </span>
                                                    @if ($templateName)
                                                        <small class="text-muted mt-1" style="font-size: 11px;">
                                                            <i class="ri-file-list-line"></i>
                                                            {{ Str::limit($templateName, 25) }}
                                                        </small>
                                                    @endif
                                                </div>
                                            </td>

                                            <td>
                                                {{ $schedule->scheduled_at->format('d M, Y H:i') }}
                                            </td>
                                            <td>
                                                @php
                                                    $badgeClass = match ($schedule->status->value) {
                                                        'pending' => 'warning',
                                                        'sent' => 'success',
                                                        'failed' => 'danger',
                                                        'skipped' => 'info',
                                                        'cancelled' => 'secondary',
                                                        default => 'light',
                                                    };
                                                @endphp
                                                <span class="badge bg-{{ $badgeClass }}-transparent">
                                                    {{ ucfirst($schedule->status->value) }}
                                                </span>
                                            </td>
                                            <td>
                                                @if ($schedule->status->value === 'pending')
                                                    <div class="btn-list">
                                                        @if ($schedule->source_type->value === 'rule')
                                                            <a href="{{ route('rule-scheduled.edit', $schedule) }}"
                                                                class="btn btn-primary-light btn-icon btn-sm">
                                                                <i class="ri-pencil-line"></i>
                                                            </a>
                                                        @else
                                                            <a href="{{ route('manual-scheduled.edit', $schedule) }}"
                                                                class="btn btn-primary-light btn-icon btn-sm">
                                                                <i class="ri-pencil-line"></i>
                                                            </a>
                                                        @endif
                                                        <button type="button" class="btn btn-info-light btn-icon btn-sm"
                                                            data-bs-toggle="modal" data-bs-target="#rescheduleModal"
                                                            data-id="{{ $schedule->id }}"
                                                            data-date="{{ $schedule->scheduled_at->format('Y-m-d\TH:i') }}">
                                                            <i class="ri-calendar-event-line"></i>
                                                        </button>
                                                        <form action="{{ route('schedule-reminders.cancel', $schedule) }}"
                                                            method="POST" class="d-inline">
                                                            @csrf
                                                            <button type="submit"
                                                                class="btn btn-danger-light btn-icon btn-sm"
                                                                onclick="return confirm('Are you sure you want to cancel this reminder?')">
                                                                <i class="ri-close-circle-line"></i>
                                                            </button>
                                                        </form>
                                                    </div>
                                                @endif
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="7" class="text-center">No scheduled reminders found.</td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>
                    <div class="card-footer">
                        {{ $schedules->links() }}
                    </div>
                </div>
            </div>
        </div>

    </div>

    <!-- Reschedule Modal -->
    <div class="modal fade" id="rescheduleModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Reschedule Reminder</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form id="reschedule-form" method="POST">
                    @csrf
                    <div class="modal-body">
                        <div class="mb-3">
                            <label for="scheduled_at" class="form-label">New Scheduled Date & Time</label>
                            <input type="datetime-local" class="form-control" id="scheduled_at" name="scheduled_at"
                                required>
                            <small class="text-muted d-block mt-1">⏰ Select a future date and time for the
                                reminder.</small>
                            @error('scheduled_at')
                                <div class="text-danger small mt-1">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                        <button type="submit" class="btn btn-primary">Reschedule</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection

@section('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const rescheduleModal = document.getElementById('rescheduleModal');
            const rescheduleForm = document.getElementById('reschedule-form');
            const scheduledAtInput = document.getElementById('scheduled_at');

            if (rescheduleModal) {
                rescheduleModal.addEventListener('show.bs.modal', function(event) {
                    const button = event.relatedTarget;
                    const id = button.getAttribute('data-id');
                    const date = button.getAttribute('data-date');

                    rescheduleForm.action = `/schedule-reminders/${id}/reschedule`;
                    scheduledAtInput.value = date;
                });
            }

            // Validate reschedule form
            if (rescheduleForm) {
                rescheduleForm.addEventListener('submit', function(e) {
                    if (!scheduledAtInput.value) {
                        e.preventDefault();
                        alert('Please select a new scheduled date and time');
                        return false;
                    }

                    // Validate that scheduled date is in the future
                    const scheduledDate = new Date(scheduledAtInput.value);
                    const now = new Date();
                    if (scheduledDate <= now) {
                        e.preventDefault();
                        alert(
                            'The scheduled date must be in the future. Please select a future date and time.'
                        );
                        scheduledAtInput.focus();
                        return false;
                    }
                });
            }
        });
    </script>
@endsection
