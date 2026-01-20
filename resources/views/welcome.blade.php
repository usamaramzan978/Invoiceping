@extends('layouts.app')

@section('title', 'Dashboard - One Ping Away From Payment')

@section('styles')
    <style>
        .stat-card {
            transition: transform 0.2s;
        }

        .stat-card:hover {
            transform: translateY(-5px);
        }

        .chart-container {
            position: relative;
            height: 300px;
        }
    </style>
@endsection

@section('content')
    <div class="container-fluid">
        <!-- Page Header -->
        <div class="d-md-flex d-block align-items-center justify-content-between my-4 page-header-breadcrumb">
            <div>
                <h1 class="page-title fw-semibold fs-18 mb-0">Dashboard</h1>
                <p class="text-muted mb-0">Welcome back! Here's what's happening with your business.</p>
            </div>
            <div class="ms-md-1 ms-0">
                <nav>
                    <ol class="breadcrumb mb-0">
                        <li class="breadcrumb-item"><a href="javascript:void(0);">Dashboard</a></li>
                        <li class="breadcrumb-item active" aria-current="page">Overview</li>
                    </ol>
                </nav>
            </div>
        </div>
        <!-- Page Header Close -->

        @php
            $overview = $analytics['overview_cards'] ?? [];
            $revenue = $analytics['revenue_metrics'] ?? [];
            $invoiceStats = $analytics['invoice_statistics'] ?? [];
            $clientStats = $analytics['client_statistics'] ?? [];
            $reminderStats = $analytics['reminder_statistics'] ?? [];
            $revenueChart = $analytics['revenue_chart_data'] ?? ['labels' => [], 'data' => []];
            $statusChart = $analytics['invoice_status_chart_data'] ?? ['labels' => [], 'data' => []];
            $recentInvoices = $analytics['recent_invoices'] ?? collect();
            $topClients = $analytics['top_clients'] ?? collect();
        @endphp

        <!-- Overview Cards -->
        <div class="row">
            <div class="col-xl-3 col-lg-6 col-md-6 col-sm-6">
                <div class="card custom-card stat-card">
                    <div class="card-body">
                        <div class="d-flex align-items-top justify-content-between mb-3">
                            <div>
                                <span class="avatar avatar-md avatar-rounded bg-primary-transparent">
                                    <i class="ri-file-list-3-line fs-20"></i>
                                </span>
                            </div>
                            <div class="text-end">
                                @if ($revenue['monthly_growth_percentage'] ?? 0 > 0)
                                    <span class="badge bg-success-transparent">
                                        <i
                                            class="ri-arrow-up-line me-1"></i>{{ number_format($revenue['monthly_growth_percentage'] ?? 0, 1) }}%
                                    </span>
                                @elseif (($revenue['monthly_growth_percentage'] ?? 0) < 0)
                                    <span class="badge bg-danger-transparent">
                                        <i
                                            class="ri-arrow-down-line me-1"></i>{{ number_format(abs($revenue['monthly_growth_percentage'] ?? 0), 1) }}%
                                    </span>
                                @endif
                            </div>
                        </div>
                        <div class="mb-1">
                            <span class="fs-12 text-muted">Total Invoices</span>
                        </div>
                        <div class="d-flex align-items-center justify-content-between">
                            <div>
                                <span class="fs-20 fw-semibold">{{ number_format($overview['total_invoices'] ?? 0) }}</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-xl-3 col-lg-6 col-md-6 col-sm-6">
                <div class="card custom-card stat-card">
                    <div class="card-body">
                        <div class="d-flex align-items-top justify-content-between mb-3">
                            <div>
                                <span class="avatar avatar-md avatar-rounded bg-success-transparent">
                                    <i class="ri-money-dollar-circle-line fs-20"></i>
                                </span>
                            </div>
                        </div>
                        <div class="mb-1">
                            <span class="fs-12 text-muted">Total Revenue</span>
                        </div>
                        <div class="d-flex align-items-center justify-content-between">
                            <div>
                                <span
                                    class="fs-20 fw-semibold">${{ number_format($overview['total_revenue'] ?? 0, 2) }}</span>
                            </div>
                        </div>
                        <div class="mt-2">
                            <small class="text-muted">This month:
                                ${{ number_format($overview['monthly_revenue'] ?? 0, 2) }}</small>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-xl-3 col-lg-6 col-md-6 col-sm-6">
                <div class="card custom-card stat-card">
                    <div class="card-body">
                        <div class="d-flex align-items-top justify-content-between mb-3">
                            <div>
                                <span class="avatar avatar-md avatar-rounded bg-warning-transparent">
                                    <i class="ri-time-line fs-20"></i>
                                </span>
                            </div>
                        </div>
                        <div class="mb-1">
                            <span class="fs-12 text-muted">Pending Amount</span>
                        </div>
                        <div class="d-flex align-items-center justify-content-between">
                            <div>
                                <span
                                    class="fs-20 fw-semibold">${{ number_format($overview['pending_amount'] ?? 0, 2) }}</span>
                            </div>
                        </div>
                        <div class="mt-2">
                            <small class="text-danger">Overdue:
                                ${{ number_format($overview['overdue_amount'] ?? 0, 2) }}</small>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-xl-3 col-lg-6 col-md-6 col-sm-6">
                <div class="card custom-card stat-card">
                    <div class="card-body">
                        <div class="d-flex align-items-top justify-content-between mb-3">
                            <div>
                                <span class="avatar avatar-md avatar-rounded bg-info-transparent">
                                    <i class="ri-user-line fs-20"></i>
                                </span>
                            </div>
                        </div>
                        <div class="mb-1">
                            <span class="fs-12 text-muted">Total Clients</span>
                        </div>
                        <div class="d-flex align-items-center justify-content-between">
                            <div>
                                <span class="fs-20 fw-semibold">{{ number_format($overview['total_clients'] ?? 0) }}</span>
                            </div>
                        </div>
                        <div class="mt-2">
                            <small class="text-muted">Active: {{ $clientStats['active_clients'] ?? 0 }}</small>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Revenue & Status Charts -->
        <div class="row">
            <div class="col-xl-8">
                <div class="card custom-card">
                    <div class="card-header justify-content-between">
                        <div class="card-title">Revenue Trend (Last 6 Months)</div>
                        <div class="dropdown">
                            <button class="btn btn-icon btn-sm btn-light" type="button" data-bs-toggle="dropdown">
                                <i class="ri-more-2-line"></i>
                            </button>
                        </div>
                    </div>
                    <div class="card-body">
                        <div class="chart-container">
                            <div id="revenueChart"></div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-xl-4">
                <div class="card custom-card">
                    <div class="card-header justify-content-between">
                        <div class="card-title">Invoice Status Distribution</div>
                    </div>
                    <div class="card-body">
                        <div class="chart-container">
                            <div id="statusChart"></div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Invoice Statistics Cards -->
        <div class="row">
            <div class="col-xl-3 col-lg-6 col-md-6">
                <div class="card custom-card">
                    <div class="card-body">
                        <div class="d-flex align-items-center justify-content-between">
                            <div>
                                <span class="fs-12 text-muted mb-1 d-block">Draft Invoices</span>
                                <span class="fs-18 fw-semibold">{{ $invoiceStats['draft']['count'] ?? 0 }}</span>
                                <div class="mt-1">
                                    <small
                                        class="text-muted">${{ number_format($invoiceStats['draft']['amount'] ?? 0, 2) }}</small>
                                </div>
                            </div>
                            <div>
                                <span class="avatar avatar-md avatar-rounded bg-info-transparent">
                                    <i class="ri-file-edit-line fs-20"></i>
                                </span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-xl-3 col-lg-6 col-md-6">
                <div class="card custom-card">
                    <div class="card-body">
                        <div class="d-flex align-items-center justify-content-between">
                            <div>
                                <span class="fs-12 text-muted mb-1 d-block">Sent Invoices</span>
                                <span class="fs-18 fw-semibold">{{ $invoiceStats['sent']['count'] ?? 0 }}</span>
                                <div class="mt-1">
                                    <small
                                        class="text-muted">${{ number_format($invoiceStats['sent']['amount'] ?? 0, 2) }}</small>
                                </div>
                            </div>
                            <div>
                                <span class="avatar avatar-md avatar-rounded bg-primary-transparent">
                                    <i class="ri-send-plane-line fs-20"></i>
                                </span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-xl-3 col-lg-6 col-md-6">
                <div class="card custom-card">
                    <div class="card-body">
                        <div class="d-flex align-items-center justify-content-between">
                            <div>
                                <span class="fs-12 text-muted mb-1 d-block">Paid Invoices</span>
                                <span class="fs-18 fw-semibold">{{ $invoiceStats['paid']['count'] ?? 0 }}</span>
                                <div class="mt-1">
                                    <small
                                        class="text-muted">${{ number_format($invoiceStats['paid']['amount'] ?? 0, 2) }}</small>
                                </div>
                            </div>
                            <div>
                                <span class="avatar avatar-md avatar-rounded bg-success-transparent">
                                    <i class="ri-checkbox-circle-line fs-20"></i>
                                </span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-xl-3 col-lg-6 col-md-6">
                <div class="card custom-card">
                    <div class="card-body">
                        <div class="d-flex align-items-center justify-content-between">
                            <div>
                                <span class="fs-12 text-muted mb-1 d-block">Overdue Invoices</span>
                                <span
                                    class="fs-18 fw-semibold text-danger">{{ $invoiceStats['overdue']['count'] ?? 0 }}</span>
                                <div class="mt-1">
                                    <small
                                        class="text-danger">${{ number_format($invoiceStats['overdue']['amount'] ?? 0, 2) }}</small>
                                </div>
                            </div>
                            <div>
                                <span class="avatar avatar-md avatar-rounded bg-danger-transparent">
                                    <i class="ri-alert-line fs-20"></i>
                                </span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Reminder Statistics & Client Stats -->
        <div class="row">
            <div class="col-xl-6">
                <div class="card custom-card">
                    <div class="card-header justify-content-between">
                        <div class="card-title">Reminder Statistics</div>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-6 mb-3">
                                <div class="d-flex align-items-center">
                                    <span class="avatar avatar-sm avatar-rounded bg-primary-transparent me-2">
                                        <i class="ri-mail-send-line"></i>
                                    </span>
                                    <div>
                                        <div class="fw-semibold">{{ $reminderStats['total_reminders'] ?? 0 }}</div>
                                        <small class="text-muted">Total Reminders</small>
                                    </div>
                                </div>
                            </div>
                            <div class="col-6 mb-3">
                                <div class="d-flex align-items-center">
                                    <span class="avatar avatar-sm avatar-rounded bg-success-transparent me-2">
                                        <i class="ri-check-line"></i>
                                    </span>
                                    <div>
                                        <div class="fw-semibold">{{ $reminderStats['sent_reminders'] ?? 0 }}</div>
                                        <small class="text-muted">Sent</small>
                                    </div>
                                </div>
                            </div>
                            <div class="col-6 mb-3">
                                <div class="d-flex align-items-center">
                                    <span class="avatar avatar-sm avatar-rounded bg-warning-transparent me-2">
                                        <i class="ri-time-line"></i>
                                    </span>
                                    <div>
                                        <div class="fw-semibold">{{ $reminderStats['pending_reminders'] ?? 0 }}</div>
                                        <small class="text-muted">Pending</small>
                                    </div>
                                </div>
                            </div>
                            <div class="col-6 mb-3">
                                <div class="d-flex align-items-center">
                                    <span class="avatar avatar-sm avatar-rounded bg-danger-transparent me-2">
                                        <i class="ri-close-line"></i>
                                    </span>
                                    <div>
                                        <div class="fw-semibold">{{ $reminderStats['failed_reminders'] ?? 0 }}</div>
                                        <small class="text-muted">Failed</small>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="mt-3 pt-3 border-top">
                            <div class="d-flex justify-content-between align-items-center">
                                <span class="text-muted">Success Rate</span>
                                <span
                                    class="fw-semibold">{{ number_format($reminderStats['success_rate'] ?? 0, 1) }}%</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-xl-6">
                <div class="card custom-card">
                    <div class="card-header justify-content-between">
                        <div class="card-title">Client Statistics</div>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-6 mb-3">
                                <div class="d-flex align-items-center">
                                    <span class="avatar avatar-sm avatar-rounded bg-primary-transparent me-2">
                                        <i class="ri-group-line"></i>
                                    </span>
                                    <div>
                                        <div class="fw-semibold">{{ $clientStats['total_clients'] ?? 0 }}</div>
                                        <small class="text-muted">Total Clients</small>
                                    </div>
                                </div>
                            </div>
                            <div class="col-6 mb-3">
                                <div class="d-flex align-items-center">
                                    <span class="avatar avatar-sm avatar-rounded bg-success-transparent me-2">
                                        <i class="ri-user-star-line"></i>
                                    </span>
                                    <div>
                                        <div class="fw-semibold">{{ $clientStats['active_clients'] ?? 0 }}</div>
                                        <small class="text-muted">Active</small>
                                    </div>
                                </div>
                            </div>
                            <div class="col-6 mb-3">
                                <div class="d-flex align-items-center">
                                    <span class="avatar avatar-sm avatar-rounded bg-info-transparent me-2">
                                        <i class="ri-file-list-line"></i>
                                    </span>
                                    <div>
                                        <div class="fw-semibold">{{ $clientStats['clients_with_invoices'] ?? 0 }}</div>
                                        <small class="text-muted">With Invoices</small>
                                    </div>
                                </div>
                            </div>
                            <div class="col-6 mb-3">
                                <div class="d-flex align-items-center">
                                    <span class="avatar avatar-sm avatar-rounded bg-warning-transparent me-2">
                                        <i class="ri-user-add-line"></i>
                                    </span>
                                    <div>
                                        <div class="fw-semibold">{{ $clientStats['new_clients_this_month'] ?? 0 }}</div>
                                        <small class="text-muted">New This Month</small>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Recent Invoices & Top Clients Tables -->
        <div class="row">
            <div class="col-xl-8">
                <div class="card custom-card">
                    <div class="card-header justify-content-between">
                        <div class="card-title">Recent Invoices</div>
                        <a href="{{ route('invoices.index') }}" class="btn btn-sm btn-primary">
                            View All <i class="ri-arrow-right-line ms-1"></i>
                        </a>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table text-nowrap table-hover align-middle">
                                <thead>
                                    <tr>
                                        <th>Invoice #</th>
                                        <th>Client</th>
                                        <th>Amount</th>
                                        <th>Status</th>
                                        <th>Due Date</th>
                                        <th>Action</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse ($recentInvoices as $invoice)
                                        <tr>
                                            <td>
                                                <a href="{{ route('invoices.show', $invoice['id']) }}"
                                                    class="fw-semibold text-primary">
                                                    #{{ $invoice['invoice_number'] }}
                                                </a>
                                            </td>
                                            <td>{{ $invoice['client_name'] }}</td>
                                            <td>
                                                <span class="fw-semibold">{{ $invoice['currency'] }}
                                                    {{ number_format($invoice['total_amount'], 2) }}</span>
                                            </td>
                                            <td>
                                                @php
                                                    $badgeClass = match ($invoice['status']) {
                                                        'draft' => 'info',
                                                        'sent' => 'primary',
                                                        'paid' => 'success',
                                                        'overdue' => 'danger',
                                                        default => 'secondary',
                                                    };
                                                @endphp
                                                <span class="badge bg-{{ $badgeClass }}-transparent">
                                                    {{ $invoice['status_label'] }}
                                                </span>
                                            </td>
                                            <td>{{ \Carbon\Carbon::parse($invoice['due_date'])->format('M d, Y') }}</td>
                                            <td>
                                                <a href="{{ route('invoices.show', $invoice['id']) }}"
                                                    class="btn btn-sm btn-info-light">
                                                    <i class="ri-eye-line"></i>
                                                </a>
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="6" class="text-center text-muted py-4">
                                                <i class="ri-inbox-line fs-24 d-block mb-2"></i>
                                                No invoices found.
                                            </td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-xl-4">
                <div class="card custom-card">
                    <div class="card-header justify-content-between">
                        <div class="card-title">Top Clients</div>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table text-nowrap table-hover align-middle">
                                <thead>
                                    <tr>
                                        <th>Client</th>
                                        <th>Revenue</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse ($topClients as $client)
                                        <tr>
                                            <td>
                                                <div class="d-flex align-items-center">
                                                    <span
                                                        class="avatar avatar-sm avatar-rounded bg-primary-transparent me-2">
                                                        <i class="ri-user-line"></i>
                                                    </span>
                                                    <div>
                                                        <div class="fw-semibold">{{ $client['name'] }}</div>
                                                        <small class="text-muted">{{ $client['invoice_count'] }}
                                                            invoices</small>
                                                    </div>
                                                </div>
                                            </td>
                                            <td>
                                                <span
                                                    class="fw-semibold">{{ number_format($client['total_revenue'], 2) }}</span>
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="2" class="text-center text-muted py-4">
                                                <i class="ri-inbox-line fs-24 d-block mb-2"></i>
                                                No clients found.
                                            </td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('scripts')
    <script src="https://cdn.jsdelivr.net/npm/apexcharts"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Revenue Chart
            const revenueChartData = @json($revenueChart);
            const revenueOptions = {
                series: [{
                    name: 'Revenue',
                    data: revenueChartData.data || []
                }],
                chart: {
                    type: 'area',
                    height: 300,
                    toolbar: {
                        show: false
                    },
                    zoom: {
                        enabled: false
                    }
                },
                dataLabels: {
                    enabled: false
                },
                stroke: {
                    curve: 'smooth',
                    width: 2
                },
                fill: {
                    type: 'gradient',
                    gradient: {
                        shadeIntensity: 1,
                        opacityFrom: 0.7,
                        opacityTo: 0.3,
                        stops: [0, 90, 100]
                    }
                },
                colors: ['#4f46e5'],
                xaxis: {
                    categories: revenueChartData.labels || []
                },
                yaxis: {
                    labels: {
                        formatter: function(val) {
                            return '$' + val.toFixed(0);
                        }
                    }
                },
                tooltip: {
                    y: {
                        formatter: function(val) {
                            return '$' + val.toFixed(2);
                        }
                    }
                }
            };

            const revenueChart = new ApexCharts(document.querySelector("#revenueChart"), revenueOptions);
            revenueChart.render();

            // Status Chart
            const statusChartData = @json($statusChart);
            const statusOptions = {
                series: statusChartData.data || [],
                chart: {
                    type: 'donut',
                    height: 300
                },
                labels: statusChartData.labels || [],
                colors: ['#0d6efd', '#4f46e5', '#10b981', '#ef4444'],
                legend: {
                    position: 'bottom'
                },
                plotOptions: {
                    pie: {
                        donut: {
                            size: '70%'
                        }
                    }
                },
                dataLabels: {
                    enabled: true,
                    formatter: function(val) {
                        return val.toFixed(0) + '%';
                    }
                }
            };

            const statusChart = new ApexCharts(document.querySelector("#statusChart"), statusOptions);
            statusChart.render();
        });
    </script>
@endsection
