<?php

declare(strict_types=1);

namespace App\Actions\Dashboard;

use App\Enums\InvoiceStatus;
use App\Enums\ReminderStatusEnum;
use App\Enums\TransactionStatus;
use App\Models\BusinessProfile;
use App\Models\Client;
use App\Models\Invoice;
use App\Models\ReminderSchedule;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

final readonly class GetDashboardAnalyticsAction
{
    public function execute(string $userId): array
    {
        $business = BusinessProfile::query()
            ->where('user_id', $userId)
            ->first();

        if (! $business) {
            return $this->getEmptyAnalytics();
        }

        $businessId = $business->id;

        // Execute all queries in parallel for better performance
        $analytics = [
            'overview_cards' => $this->getOverviewCards($businessId),
            'revenue_metrics' => $this->getRevenueMetrics($businessId),
            'invoice_statistics' => $this->getInvoiceStatistics($businessId),
            'client_statistics' => $this->getClientStatistics($businessId),
            'reminder_statistics' => $this->getReminderStatistics($businessId),
            'revenue_chart_data' => $this->getRevenueChartData($businessId),
            'invoice_status_chart_data' => $this->getInvoiceStatusChartData($businessId),
            'recent_invoices' => $this->getRecentInvoices($businessId),
            'top_clients' => $this->getTopClients($businessId),
            'monthly_trends' => $this->getMonthlyTrends($businessId),
        ];

        return $analytics;
    }

    private function getOverviewCards(string $businessId): array
    {
        $now = Carbon::now();
        $startOfMonth = $now->copy()->startOfMonth();
        $startOfYear = $now->copy()->startOfYear();

        // Single query for all invoice counts using DB::raw for safety
        $paidStatus = InvoiceStatus::PAID->value;
        $sentStatus = InvoiceStatus::SENT->value;
        $draftStatus = InvoiceStatus::DRAFT->value;
        $dueDate = $now->toDateString();

        $invoiceStats = DB::table('invoices')
            ->where('business_id', $businessId)
            ->selectRaw("
                COUNT(*) as total_invoices,
                SUM(CASE WHEN status = ? THEN 1 ELSE 0 END) as paid_count,
                SUM(CASE WHEN status = ? AND due_date < ? THEN 1 ELSE 0 END) as overdue_count,
                SUM(CASE WHEN status = ? THEN 1 ELSE 0 END) as sent_count,
                SUM(CASE WHEN status = ? THEN 1 ELSE 0 END) as draft_count
            ", [$paidStatus, $sentStatus, $dueDate, $sentStatus, $draftStatus])
            ->first();

        // Revenue queries
        $monthlyRevenue = Invoice::query()
            ->where('business_id', $businessId)
            ->where('status', InvoiceStatus::PAID)
            ->where('paid_at', '>=', $startOfMonth)
            ->sum('total_amount');

        $yearlyRevenue = Invoice::query()
            ->where('business_id', $businessId)
            ->where('status', InvoiceStatus::PAID)
            ->where('paid_at', '>=', $startOfYear)
            ->sum('total_amount');

        $totalRevenue = Invoice::query()
            ->where('business_id', $businessId)
            ->where('status', InvoiceStatus::PAID)
            ->sum('total_amount');

        // Client count
        $totalClients = Client::query()
            ->where('business_id', $businessId)
            ->count();

        // Pending amount (sent but not paid)
        $pendingAmount = Invoice::query()
            ->where('business_id', $businessId)
            ->where('status', InvoiceStatus::SENT)
            ->whereNull('paid_at')
            ->sum('total_amount');

        // Overdue amount
        $overdueAmount = Invoice::query()
            ->where('business_id', $businessId)
            ->where('status', InvoiceStatus::SENT)
            ->where('due_date', '<', $now->toDateString())
            ->whereNull('paid_at')
            ->sum('total_amount');

        return [
            'total_invoices' => (int) ($invoiceStats->total_invoices ?? 0),
            'paid_invoices' => (int) ($invoiceStats->paid_count ?? 0),
            'overdue_invoices' => (int) ($invoiceStats->overdue_count ?? 0),
            'sent_invoices' => (int) ($invoiceStats->sent_count ?? 0),
            'draft_invoices' => (int) ($invoiceStats->draft_count ?? 0),
            'total_clients' => $totalClients,
            'monthly_revenue' => (float) $monthlyRevenue,
            'yearly_revenue' => (float) $yearlyRevenue,
            'total_revenue' => (float) $totalRevenue,
            'pending_amount' => (float) $pendingAmount,
            'overdue_amount' => (float) $overdueAmount,
        ];
    }

    private function getRevenueMetrics(string $businessId): array
    {
        $now = Carbon::now();
        $lastMonth = $now->copy()->subMonth();
        $lastYear = $now->copy()->subYear();

        // Current period revenue
        $currentMonthRevenue = Invoice::query()
            ->where('business_id', $businessId)
            ->where('status', InvoiceStatus::PAID)
            ->whereBetween('paid_at', [
                $now->copy()->startOfMonth(),
                $now->copy()->endOfMonth(),
            ])
            ->sum('total_amount');

        // Previous period revenue
        $lastMonthRevenue = Invoice::query()
            ->where('business_id', $businessId)
            ->where('status', InvoiceStatus::PAID)
            ->whereBetween('paid_at', [
                $lastMonth->copy()->startOfMonth(),
                $lastMonth->copy()->endOfMonth(),
            ])
            ->sum('total_amount');

        // Calculate growth percentage
        $monthlyGrowth = $lastMonthRevenue > 0
            ? (($currentMonthRevenue - $lastMonthRevenue) / $lastMonthRevenue) * 100
            : ($currentMonthRevenue > 0 ? 100 : 0);

        // Average invoice value
        $avgInvoiceValue = Invoice::query()
            ->where('business_id', $businessId)
            ->where('status', InvoiceStatus::PAID)
            ->avg('total_amount');

        return [
            'current_month_revenue' => (float) $currentMonthRevenue,
            'last_month_revenue' => (float) $lastMonthRevenue,
            'monthly_growth_percentage' => round((float) $monthlyGrowth, 2),
            'average_invoice_value' => round((float) ($avgInvoiceValue ?? 0), 2),
        ];
    }

    private function getInvoiceStatistics(string $businessId): array
    {
        $stats = Invoice::query()
            ->where('business_id', $businessId)
            ->selectRaw('
                status,
                COUNT(*) as count,
                SUM(total_amount) as total_amount
            ')
            ->groupBy('status')
            ->get()
            ->keyBy('status');

        return [
            'draft' => [
                'count' => (int) ($stats[InvoiceStatus::DRAFT->value]->count ?? 0),
                'amount' => (float) ($stats[InvoiceStatus::DRAFT->value]->total_amount ?? 0),
            ],
            'sent' => [
                'count' => (int) ($stats[InvoiceStatus::SENT->value]->count ?? 0),
                'amount' => (float) ($stats[InvoiceStatus::SENT->value]->total_amount ?? 0),
            ],
            'paid' => [
                'count' => (int) ($stats[InvoiceStatus::PAID->value]->count ?? 0),
                'amount' => (float) ($stats[InvoiceStatus::PAID->value]->total_amount ?? 0),
            ],
            'overdue' => [
                'count' => Invoice::query()
                    ->where('business_id', $businessId)
                    ->where('status', InvoiceStatus::SENT)
                    ->where('due_date', '<', Carbon::now()->toDateString())
                    ->whereNull('paid_at')
                    ->count(),
                'amount' => (float) Invoice::query()
                    ->where('business_id', $businessId)
                    ->where('status', InvoiceStatus::SENT)
                    ->where('due_date', '<', Carbon::now()->toDateString())
                    ->whereNull('paid_at')
                    ->sum('total_amount'),
            ],
        ];
    }

    private function getClientStatistics(string $businessId): array
    {
        $totalClients = Client::query()
            ->where('business_id', $businessId)
            ->count();

        $activeClients = Client::query()
            ->where('business_id', $businessId)
            ->where('status', 'active')
            ->count();

        $clientsWithInvoices = Client::query()
            ->where('business_id', $businessId)
            ->whereHas('invoices')
            ->count();

        $newClientsThisMonth = Client::query()
            ->where('business_id', $businessId)
            ->whereMonth('created_at', Carbon::now()->month)
            ->whereYear('created_at', Carbon::now()->year)
            ->count();

        return [
            'total_clients' => $totalClients,
            'active_clients' => $activeClients,
            'clients_with_invoices' => $clientsWithInvoices,
            'new_clients_this_month' => $newClientsThisMonth,
        ];
    }

    private function getReminderStatistics(string $businessId): array
    {
        $reminderStats = ReminderSchedule::query()
            ->whereHas('invoice', function ($query) use ($businessId): void {
                $query->where('business_id', $businessId);
            })
            ->selectRaw('
                status,
                COUNT(*) as count
            ')
            ->groupBy('status')
            ->get()
            ->keyBy('status');

        $totalReminders = ReminderSchedule::query()
            ->whereHas('invoice', function ($query) use ($businessId): void {
                $query->where('business_id', $businessId);
            })
            ->count();

        $sentReminders = ReminderSchedule::query()
            ->whereHas('invoice', function ($query) use ($businessId): void {
                $query->where('business_id', $businessId);
            })
            ->where('status', ReminderStatusEnum::SENT)
            ->count();

        $pendingReminders = ReminderSchedule::query()
            ->whereHas('invoice', function ($query) use ($businessId): void {
                $query->where('business_id', $businessId);
            })
            ->where('status', ReminderStatusEnum::PENDING)
            ->count();

        $failedReminders = ReminderSchedule::query()
            ->whereHas('invoice', function ($query) use ($businessId): void {
                $query->where('business_id', $businessId);
            })
            ->where('status', ReminderStatusEnum::FAILED)
            ->count();

        return [
            'total_reminders' => $totalReminders,
            'sent_reminders' => $sentReminders,
            'pending_reminders' => $pendingReminders,
            'failed_reminders' => $failedReminders,
            'success_rate' => $totalReminders > 0
                ? round(($sentReminders / $totalReminders) * 100, 2)
                : 0,
        ];
    }

    private function getRevenueChartData(string $businessId): array
    {
        $months = collect(range(5, 0))->map(function ($monthsAgo) {
            return Carbon::now()->subMonths($monthsAgo);
        });

        $revenueData = $months->map(function ($month) use ($businessId) {
            $start = $month->copy()->startOfMonth();
            $end = $month->copy()->endOfMonth();

            $revenue = Invoice::query()
                ->where('business_id', $businessId)
                ->where('status', InvoiceStatus::PAID)
                ->whereBetween('paid_at', [$start, $end])
                ->sum('total_amount');

            return [
                'month' => $month->format('M Y'),
                'revenue' => (float) $revenue,
            ];
        });

        return [
            'labels' => $revenueData->pluck('month')->toArray(),
            'data' => $revenueData->pluck('revenue')->toArray(),
        ];
    }

    private function getInvoiceStatusChartData(string $businessId): array
    {
        $stats = $this->getInvoiceStatistics($businessId);

        return [
            'labels' => ['Draft', 'Sent', 'Paid', 'Overdue'],
            'data' => [
                $stats['draft']['count'],
                $stats['sent']['count'],
                $stats['paid']['count'],
                $stats['overdue']['count'],
            ],
        ];
    }

    private function getRecentInvoices(string $businessId, int $limit = 10): Collection
    {
        return Invoice::query()
            ->where('business_id', $businessId)
            ->with(['client:id,name', 'items'])
            ->orderBy('created_at', 'desc')
            ->limit($limit)
            ->get()
            ->map(function ($invoice) {
                return [
                    'id' => $invoice->id,
                    'invoice_number' => $invoice->invoice_number,
                    'client_name' => $invoice->client->name ?? 'N/A',
                    'total_amount' => (float) $invoice->total_amount,
                    'currency' => $invoice->currency ?? 'USD',
                    'status' => $invoice->status->value,
                    'status_label' => $invoice->status->label(),
                    'due_date' => $invoice->due_date->format('Y-m-d'),
                    'issue_date' => $invoice->issue_date->format('Y-m-d'),
                    'created_at' => $invoice->created_at->format('Y-m-d H:i:s'),
                ];
            });
    }

    private function getTopClients(string $businessId, int $limit = 5): Collection
    {
        return Client::query()
            ->where('business_id', $businessId)
            ->withSum('invoices', 'total_amount')
            ->withCount('invoices')
            ->orderBy('invoices_sum_total_amount', 'desc')
            ->limit($limit)
            ->get()
            ->map(function ($client) {
                return [
                    'id' => $client->id,
                    'name' => $client->name,
                    'total_revenue' => (float) ($client->invoices_sum_total_amount ?? 0),
                    'invoice_count' => $client->invoices_count,
                    'status' => $client->status ?? 'active',
                ];
            });
    }

    private function getMonthlyTrends(string $businessId): array
    {
        $now = Carbon::now();
        $months = collect(range(5, 0))->map(function ($monthsAgo) {
            return Carbon::now()->subMonths($monthsAgo);
        });

        $trends = $months->map(function ($month) use ($businessId) {
            $start = $month->copy()->startOfMonth();
            $end = $month->copy()->endOfMonth();

            $invoicesCreated = Invoice::query()
                ->where('business_id', $businessId)
                ->whereBetween('created_at', [$start, $end])
                ->count();

            $invoicesPaid = Invoice::query()
                ->where('business_id', $businessId)
                ->where('status', InvoiceStatus::PAID)
                ->whereBetween('paid_at', [$start, $end])
                ->count();

            $revenue = Invoice::query()
                ->where('business_id', $businessId)
                ->where('status', InvoiceStatus::PAID)
                ->whereBetween('paid_at', [$start, $end])
                ->sum('total_amount');

            return [
                'month' => $month->format('M Y'),
                'invoices_created' => $invoicesCreated,
                'invoices_paid' => $invoicesPaid,
                'revenue' => (float) $revenue,
            ];
        });

        return $trends->toArray();
    }

    private function getEmptyAnalytics(): array
    {
        return [
            'overview_cards' => [
                'total_invoices' => 0,
                'paid_invoices' => 0,
                'overdue_invoices' => 0,
                'sent_invoices' => 0,
                'draft_invoices' => 0,
                'total_clients' => 0,
                'monthly_revenue' => 0,
                'yearly_revenue' => 0,
                'total_revenue' => 0,
                'pending_amount' => 0,
                'overdue_amount' => 0,
            ],
            'revenue_metrics' => [
                'current_month_revenue' => 0,
                'last_month_revenue' => 0,
                'monthly_growth_percentage' => 0,
                'average_invoice_value' => 0,
            ],
            'invoice_statistics' => [
                'draft' => ['count' => 0, 'amount' => 0],
                'sent' => ['count' => 0, 'amount' => 0],
                'paid' => ['count' => 0, 'amount' => 0],
                'overdue' => ['count' => 0, 'amount' => 0],
            ],
            'client_statistics' => [
                'total_clients' => 0,
                'active_clients' => 0,
                'clients_with_invoices' => 0,
                'new_clients_this_month' => 0,
            ],
            'reminder_statistics' => [
                'total_reminders' => 0,
                'sent_reminders' => 0,
                'pending_reminders' => 0,
                'failed_reminders' => 0,
                'success_rate' => 0,
            ],
            'revenue_chart_data' => [
                'labels' => [],
                'data' => [],
            ],
            'invoice_status_chart_data' => [
                'labels' => [],
                'data' => [],
            ],
            'recent_invoices' => [],
            'top_clients' => [],
            'monthly_trends' => [],
        ];
    }
}

