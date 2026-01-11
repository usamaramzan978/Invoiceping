@extends('layouts.app')

@section('content')
    <div class="container-fluid">

        <!-- Page Header -->
        <div class="d-md-flex d-block align-items-center justify-content-between my-4 page-header-breadcrumb">
            <h1 class="page-title fw-semibold fs-18 mb-0">Activity Logs</h1>
            <div class="ms-md-1 ms-0">
                <nav>
                    <ol class="breadcrumb mb-0">
                        <li class="breadcrumb-item"><a href="{{ route('home') }}">Dashboard</a></li>
                        <li class="breadcrumb-item active" aria-current="page">Logs</li>
                    </ol>
                </nav>
            </div>
        </div>
        <!-- Page Header Close -->

        <!-- Stats Cards -->
        <div class="row mb-4">
            <div class="col-xl-3 col-lg-6 col-md-6 col-sm-12">
                <div class="card custom-card">
                    <div class="card-body">
                        <div class="d-flex align-items-center">
                            <div class="me-3">
                                <span class="avatar avatar-md avatar-rounded bg-primary-transparent">
                                    <i class="ri-mail-line fs-18"></i>
                                </span>
                            </div>
                            <div>
                                <p class="fw-semibold mb-0 text-muted fs-12">Total Messages</p>
                                <h5 class="fw-semibold mb-0">{{ number_format($stats['total_messages']) }}</h5>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-xl-3 col-lg-6 col-md-6 col-sm-12">
                <div class="card custom-card">
                    <div class="card-body">
                        <div class="d-flex align-items-center">
                            <div class="me-3">
                                <span class="avatar avatar-md avatar-rounded bg-info-transparent">
                                    <i class="ri-mail-send-line fs-18"></i>
                                </span>
                            </div>
                            <div>
                                <p class="fw-semibold mb-0 text-muted fs-12">Emails Sent</p>
                                <h5 class="fw-semibold mb-0">{{ number_format($stats['emails_sent']) }}</h5>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-xl-3 col-lg-6 col-md-6 col-sm-12">
                <div class="card custom-card">
                    <div class="card-body">
                        <div class="d-flex align-items-center">
                            <div class="me-3">
                                <span class="avatar avatar-md avatar-rounded bg-success-transparent">
                                    <i class="ri-whatsapp-line fs-18"></i>
                                </span>
                            </div>
                            <div>
                                <p class="fw-semibold mb-0 text-muted fs-12">WhatsApp Sent</p>
                                <h5 class="fw-semibold mb-0">{{ number_format($stats['whatsapp_sent']) }}</h5>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-xl-3 col-lg-6 col-md-6 col-sm-12">
                <div class="card custom-card">
                    <div class="card-body">
                        <div class="d-flex align-items-center">
                            <div class="me-3">
                                <span class="avatar avatar-md avatar-rounded bg-warning-transparent">
                                    <i class="ri-message-2-line fs-18"></i>
                                </span>
                            </div>
                            <div>
                                <p class="fw-semibold mb-0 text-muted fs-12">SMS Sent</p>
                                <h5 class="fw-semibold mb-0">{{ number_format($stats['sms_sent']) }}</h5>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Additional Stats Row -->
        <div class="row mb-4">
            <div class="col-xl-3 col-lg-6 col-md-6 col-sm-12">
                <div class="card custom-card">
                    <div class="card-body">
                        <div class="d-flex align-items-center">
                            <div class="me-3">
                                <span class="avatar avatar-md avatar-rounded bg-danger-transparent">
                                    <i class="ri-close-circle-line fs-18"></i>
                                </span>
                            </div>
                            <div>
                                <p class="fw-semibold mb-0 text-muted fs-12">Failed Messages</p>
                                <h5 class="fw-semibold mb-0">{{ number_format($stats['failed_messages']) }}</h5>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-xl-3 col-lg-6 col-md-6 col-sm-12">
                <div class="card custom-card">
                    <div class="card-body">
                        <div class="d-flex align-items-center">
                            <div class="me-3">
                                <span class="avatar avatar-md avatar-rounded bg-danger-transparent">
                                    <i class="ri-error-warning-line fs-18"></i>
                                </span>
                            </div>
                            <div>
                                <p class="fw-semibold mb-0 text-muted fs-12">Errors</p>
                                <h5 class="fw-semibold mb-0">{{ number_format($stats['errors']) }}</h5>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-xl-3 col-lg-6 col-md-6 col-sm-12">
                <div class="card custom-card">
                    <div class="card-body">
                        <div class="d-flex align-items-center">
                            <div class="me-3">
                                <span class="avatar avatar-md avatar-rounded bg-primary-transparent">
                                    <i class="ri-calendar-line fs-18"></i>
                                </span>
                            </div>
                            <div>
                                <p class="fw-semibold mb-0 text-muted fs-12">Today</p>
                                <h5 class="fw-semibold mb-0">{{ number_format($stats['today_messages']) }}</h5>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-xl-3 col-lg-6 col-md-6 col-sm-12">
                <div class="card custom-card">
                    <div class="card-body">
                        <div class="d-flex align-items-center">
                            <div class="me-3">
                                <span class="avatar avatar-md avatar-rounded bg-primary-transparent">
                                    <i class="ri-bar-chart-line fs-18"></i>
                                </span>
                            </div>
                            <div>
                                <p class="fw-semibold mb-0 text-muted fs-12">This Month</p>
                                <h5 class="fw-semibold mb-0">{{ number_format($stats['this_month_messages']) }}</h5>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Success Metrics Row -->
        <div class="row mb-4">
            <div class="col-xl-3 col-lg-6 col-md-6 col-sm-12">
                <div class="card custom-card">
                    <div class="card-body">
                        <div class="d-flex align-items-center">
                            <div class="me-3">
                                <span class="avatar avatar-md avatar-rounded bg-success-transparent">
                                    <i class="ri-checkbox-circle-line fs-18"></i>
                                </span>
                            </div>
                            <div class="flex-grow-1">
                                <p class="fw-semibold mb-0 text-muted fs-12">Success Rate</p>
                                <h5 class="fw-semibold mb-0">{{ number_format($stats['success_rate'], 1) }}%</h5>
                                @if($stats['total_messages'] > 0)
                                    <div class="progress mt-2" style="height: 6px;">
                                        <div class="progress-bar bg-success" role="progressbar" 
                                             style="width: {{ $stats['success_rate'] }}%" 
                                             aria-valuenow="{{ $stats['success_rate'] }}" 
                                             aria-valuemin="0" 
                                             aria-valuemax="100">
                                        </div>
                                    </div>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-xl-3 col-lg-6 col-md-6 col-sm-12">
                <div class="card custom-card">
                    <div class="card-body">
                        <div class="d-flex align-items-center">
                            <div class="me-3">
                                <span class="avatar avatar-md avatar-rounded bg-success-transparent">
                                    <i class="ri-check-line fs-18"></i>
                                </span>
                            </div>
                            <div>
                                <p class="fw-semibold mb-0 text-muted fs-12">Successful Messages</p>
                                <h5 class="fw-semibold mb-0">{{ number_format($stats['successful_messages']) }}</h5>
                                @if($stats['failed_messages'] > 0)
                                    <p class="text-muted mb-0 fs-11 mt-1">
                                        {{ number_format($stats['success_vs_failed_ratio'], 1) }}:1 ratio
                                    </p>
                                @elseif($stats['successful_messages'] > 0)
                                    <p class="text-success mb-0 fs-11 mt-1">
                                        <i class="ri-checkbox-circle-line"></i> Perfect!
                                    </p>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-xl-3 col-lg-6 col-md-6 col-sm-12">
                <div class="card custom-card">
                    <div class="card-body">
                        <div class="d-flex align-items-center">
                            <div class="me-3">
                                <span class="avatar avatar-md avatar-rounded bg-info-transparent">
                                    <i class="ri-pie-chart-line fs-18"></i>
                                </span>
                            </div>
                            <div>
                                <p class="fw-semibold mb-0 text-muted fs-12">Most Used Channel</p>
                                <h5 class="fw-semibold mb-0 text-capitalize">{{ $stats['most_used_channel'] }}</h5>
                                <p class="text-muted mb-0 fs-11 mt-1">
                                    {{ number_format($stats['most_used_channel_count']) }} messages
                                </p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-xl-3 col-lg-6 col-md-6 col-sm-12">
                <div class="card custom-card">
                    <div class="card-body">
                        <div class="d-flex align-items-center">
                            <div class="me-3">
                                <span class="avatar avatar-md avatar-rounded bg-primary-transparent">
                                    <i class="ri-speed-up-line fs-18"></i>
                                </span>
                            </div>
                            <div>
                                <p class="fw-semibold mb-0 text-muted fs-12">Avg. Per Day</p>
                                <h5 class="fw-semibold mb-0">{{ number_format($stats['avg_messages_per_day'], 1) }}</h5>
                                <p class="text-muted mb-0 fs-11 mt-1">This month</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Filters Card -->
        <div class="card custom-card mb-4">
            <div class="card-header">
                <div class="card-title">Filters</div>
            </div>
            <div class="card-body">
                <form method="GET" action="{{ route('logs.index') }}" class="row g-3">
                    <div class="col-md-3">
                        <label class="form-label">Type</label>
                        <select name="type" class="form-control form-select js-example-basic-single">
                            <option value="">All Types</option>
                            <option value="email" {{ request('type') === 'email' ? 'selected' : '' }}>Email</option>
                            <option value="whatsapp" {{ request('type') === 'whatsapp' ? 'selected' : '' }}>WhatsApp</option>
                            <option value="sms" {{ request('type') === 'sms' ? 'selected' : '' }}>SMS</option>
                            <option value="error" {{ request('type') === 'error' ? 'selected' : '' }}>Error</option>
                            <option value="info" {{ request('type') === 'info' ? 'selected' : '' }}>Info</option>
                            <option value="warning" {{ request('type') === 'warning' ? 'selected' : '' }}>Warning</option>
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Status</label>
                        <select name="status" class="form-control form-select js-example-basic-single">
                            <option value="">All Statuses</option>
                            <option value="success" {{ request('status') === 'success' ? 'selected' : '' }}>Success</option>
                            <option value="failed" {{ request('status') === 'failed' ? 'selected' : '' }}>Failed</option>
                            <option value="error" {{ request('status') === 'error' ? 'selected' : '' }}>Error</option>
                            <option value="warning" {{ request('status') === 'warning' ? 'selected' : '' }}>Warning</option>
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Channel</label>
                        <select name="channel" class="form-control form-select js-example-basic-single">
                            <option value="">All Channels</option>
                            <option value="email" {{ request('channel') === 'email' ? 'selected' : '' }}>Email</option>
                            <option value="whatsapp" {{ request('channel') === 'whatsapp' ? 'selected' : '' }}>WhatsApp</option>
                            <option value="sms" {{ request('channel') === 'sms' ? 'selected' : '' }}>SMS</option>
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Search</label>
                        <input type="text" name="search" class="form-control" placeholder="Search..." value="{{ request('search') }}">
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Date From</label>
                        <input type="date" name="date_from" class="form-control" value="{{ request('date_from') }}">
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Date To</label>
                        <input type="date" name="date_to" class="form-control" value="{{ request('date_to') }}">
                    </div>
                    <div class="col-md-6 d-flex align-items-end">
                        <button type="submit" class="btn btn-primary me-2">
                            <i class="ri-search-line me-1"></i>Filter
                        </button>
                        <a href="{{ route('logs.index') }}" class="btn btn-secondary">
                            <i class="ri-refresh-line me-1"></i>Reset
                        </a>
                    </div>
                </form>
            </div>
        </div>

        <!-- Logs Table -->
        <div class="card custom-card">
            <div class="card-header">
                <div class="card-title">Activity Logs</div>
            </div>
            <div class="card-body">
                @if($logs->count() > 0)
                    <div class="table-responsive">
                        <table class="table table-hover text-nowrap">
                            <thead>
                                <tr>
                                    <th>Date</th>
                                    <th>Type</th>
                                    <th>Channel</th>
                                    <th>Status</th>
                                    <th>Recipient</th>
                                    <th>Subject/Content</th>
                                    <th>Invoice</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($logs as $log)
                                    <tr>
                                        <td>
                                            <div class="d-flex flex-column">
                                                <span class="fw-semibold">{{ $log->created_at->format('M d, Y') }}</span>
                                                <span class="text-muted fs-12">{{ $log->created_at->format('h:i A') }}</span>
                                            </div>
                                        </td>
                                        <td>
                                            @php
                                                $typeColors = [
                                                    'email' => 'primary',
                                                    'whatsapp' => 'success',
                                                    'sms' => 'info',
                                                    'error' => 'danger',
                                                    'info' => 'primary',
                                                    'warning' => 'warning',
                                                ];
                                                $typeIcons = [
                                                    'email' => 'ri-mail-line',
                                                    'whatsapp' => 'ri-whatsapp-line',
                                                    'sms' => 'ri-message-2-line',
                                                    'error' => 'ri-error-warning-line',
                                                    'info' => 'ri-information-line',
                                                    'warning' => 'ri-alert-line',
                                                ];
                                                $color = $typeColors[$log->type] ?? 'secondary';
                                                $icon = $typeIcons[$log->type] ?? 'ri-file-line';
                                            @endphp
                                            <span class="badge bg-{{ $color }}-transparent">
                                                <i class="{{ $icon }} me-1"></i>{{ ucfirst($log->type) }}
                                            </span>
                                        </td>
                                        <td>
                                            @if($log->channel)
                                                <span class="badge bg-{{ $log->channel === 'email' ? 'primary' : ($log->channel === 'whatsapp' ? 'success' : 'info') }}-transparent">
                                                    {{ ucfirst($log->channel) }}
                                                </span>
                                            @else
                                                <span class="text-muted">-</span>
                                            @endif
                                        </td>
                                        <td>
                                            @if($log->status === 'success')
                                                <span class="badge bg-success-transparent">
                                                    <i class="ri-check-line me-1"></i>Success
                                                </span>
                                            @elseif($log->status === 'failed')
                                                <span class="badge bg-danger-transparent">
                                                    <i class="ri-close-line me-1"></i>Failed
                                                </span>
                                            @elseif($log->status === 'error')
                                                <span class="badge bg-danger-transparent">
                                                    <i class="ri-error-warning-line me-1"></i>Error
                                                </span>
                                            @else
                                                <span class="badge bg-warning-transparent">
                                                    <i class="ri-alert-line me-1"></i>{{ ucfirst($log->status) }}
                                                </span>
                                            @endif
                                        </td>
                                        <td>
                                            @if($log->recipient)
                                                <span class="text-muted">{{ $log->getMaskedRecipient() }}</span>
                                            @else
                                                <span class="text-muted">-</span>
                                            @endif
                                        </td>
                                        <td>
                                            @if($log->subject)
                                                <div class="d-flex flex-column">
                                                    <span class="fw-semibold">{{ Str::limit($log->subject, 40) }}</span>
                                                    @if($log->content)
                                                        <span class="text-muted fs-12">{{ Str::limit(strip_tags($log->content), 50) }}</span>
                                                    @endif
                                                </div>
                                            @elseif($log->content)
                                                <span class="text-muted">{{ Str::limit(strip_tags($log->content), 60) }}</span>
                                            @elseif($log->error_message)
                                                <span class="text-danger">{{ Str::limit($log->error_message, 60) }}</span>
                                            @else
                                                <span class="text-muted">-</span>
                                            @endif
                                        </td>
                                        <td>
                                            @if($log->invoice)
                                                <a href="{{ route('invoices.show', $log->invoice) }}" class="text-primary">
                                                    {{ $log->invoice->invoice_number }}
                                                </a>
                                            @else
                                                <span class="text-muted">-</span>
                                            @endif
                                        </td>
                                        <td>
                                            <button type="button" class="btn btn-sm btn-icon btn-info-transparent" 
                                                    data-bs-toggle="modal" 
                                                    data-bs-target="#logModal{{ $log->id }}">
                                                <i class="ri-eye-line"></i>
                                            </button>
                                        </td>
                                    </tr>

                                    <!-- Log Detail Modal -->
                                    <div class="modal fade" id="logModal{{ $log->id }}" tabindex="-1">
                                        <div class="modal-dialog modal-lg">
                                            <div class="modal-content">
                                                <div class="modal-header">
                                                    <h5 class="modal-title">Log Details</h5>
                                                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                                </div>
                                                <div class="modal-body">
                                                    <div class="row mb-3">
                                                        <div class="col-md-6">
                                                            <strong>Type:</strong> {{ ucfirst($log->type) }}
                                                        </div>
                                                        <div class="col-md-6">
                                                            <strong>Status:</strong> 
                                                            <span class="badge bg-{{ $log->status === 'success' ? 'success' : 'danger' }}-transparent">
                                                                {{ ucfirst($log->status) }}
                                                            </span>
                                                        </div>
                                                    </div>
                                                    @if($log->channel)
                                                        <div class="row mb-3">
                                                            <div class="col-md-12">
                                                                <strong>Channel:</strong> {{ ucfirst($log->channel) }}
                                                            </div>
                                                        </div>
                                                    @endif
                                                    @if($log->recipient)
                                                        <div class="row mb-3">
                                                            <div class="col-md-12">
                                                                <strong>Recipient:</strong> {{ $log->recipient }}
                                                            </div>
                                                        </div>
                                                    @endif
                                                    @if($log->subject)
                                                        <div class="row mb-3">
                                                            <div class="col-md-12">
                                                                <strong>Subject:</strong> {{ $log->subject }}
                                                            </div>
                                                        </div>
                                                    @endif
                                                    @if($log->content)
                                                        <div class="row mb-3">
                                                            <div class="col-md-12">
                                                                <strong>Content:</strong>
                                                                <div class="mt-2 p-3 bg-light rounded">
                                                                    {!! nl2br(e($log->content)) !!}
                                                                </div>
                                                            </div>
                                                        </div>
                                                    @endif
                                                    @if($log->error_message)
                                                        <div class="row mb-3">
                                                            <div class="col-md-12">
                                                                <strong>Error:</strong>
                                                                <div class="mt-2 p-3 bg-danger-transparent rounded text-danger">
                                                                    {{ $log->error_message }}
                                                                </div>
                                                            </div>
                                                        </div>
                                                    @endif
                                                    @if($log->metadata)
                                                        <div class="row mb-3">
                                                            <div class="col-md-12">
                                                                <strong>Metadata:</strong>
                                                                <pre class="mt-2 p-3 bg-light rounded">{{ json_encode($log->metadata, JSON_PRETTY_PRINT) }}</pre>
                                                            </div>
                                                        </div>
                                                    @endif
                                                    <div class="row">
                                                        <div class="col-md-6">
                                                            <strong>Created:</strong> {{ $log->created_at->format('M d, Y h:i A') }}
                                                        </div>
                                                        @if($log->sent_at)
                                                            <div class="col-md-6">
                                                                <strong>Sent At:</strong> {{ $log->sent_at->format('M d, Y h:i A') }}
                                                            </div>
                                                        @endif
                                                    </div>
                                                </div>
                                                <div class="modal-footer">
                                                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                @endforeach
                            </tbody>
                        </table>
                    </div>

                    <!-- Pagination -->
                    <div class="d-flex justify-content-center mt-4">
                        {{ $logs->links() }}
                    </div>
                @else
                    <div class="text-center py-5">
                        <i class="ri-file-list-line fs-48 text-muted"></i>
                        <p class="text-muted mt-3">No logs found</p>
                    </div>
                @endif
            </div>
        </div>

    </div>
@endsection

