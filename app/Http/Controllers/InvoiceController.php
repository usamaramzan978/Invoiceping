<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Actions\Invoice\CreateInvoiceAction;
use App\Actions\Invoice\DeleteInvoiceAction;
use App\Actions\Invoice\UpdateInvoiceAction;
use App\Http\Requests\StoreInvoiceRequest;
use App\Http\Requests\UpdateInvoiceRequest;
use App\Models\Client;
use App\Models\EmailTemplate;
use App\Models\Invoice;
use App\Models\MessageTemplates;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\View\View;

final class InvoiceController extends Controller
{
    public function __construct(
        private readonly CreateInvoiceAction $createInvoice,
        private readonly DeleteInvoiceAction $deleteInvoice,
    ) {}

    public function index(): View|RedirectResponse
    {
        Gate::authorize('viewAny', Invoice::class);

        $user = auth()->user();

        if (! $user->business) {
            return to_route('business-profile.create')
                ->with('success', 'Please create a business profile first.');
        }

        $businessId = $user->business->id;

        $invoices = Invoice::with('client')
            ->where('business_id', $businessId)
            ->latest()
            ->paginate(10);

        // Calculate statistics
        $stats = [
            'total_count' => Invoice::query()->where('business_id', $businessId)->count(),
            'total_amount' => Invoice::query()->where('business_id', $businessId)->sum('total_amount'),
            'paid_count' => Invoice::query()->where('business_id', $businessId)
                ->where('status', 'paid')
                ->count(),
            'paid_amount' => Invoice::query()->where('business_id', $businessId)
                ->where('status', 'paid')
                ->sum('total_amount'),
            'pending_count' => Invoice::query()->where('business_id', $businessId)
                ->where('status', 'sent')
                ->count(),
            'pending_amount' => Invoice::query()->where('business_id', $businessId)
                ->where('status', 'sent')
                ->sum('total_amount'),
            'overdue_count' => Invoice::query()->where('business_id', $businessId)
                ->where('status', 'sent')
                ->where('due_date', '<', now())
                ->count(),
            'overdue_amount' => Invoice::query()->where('business_id', $businessId)
                ->where('status', 'sent')
                ->where('due_date', '<', now())
                ->sum('total_amount'),
        ];

        // Get last 6 months data for chart
        $chartData = $this->getInvoiceChartData($businessId);

        return view('dashboard.invoices.index', [
            'invoices' => $invoices,
            'stats' => $stats,
            'chartData' => $chartData,
        ]);
    }

    public function create(): View
    {
        Gate::authorize('create', Invoice::class);

        $clients = auth()->user()->business?->clients ?? collect();

        return view('dashboard.invoices.create', ['clients' => $clients]);
    }

    public function store(StoreInvoiceRequest $request): RedirectResponse
    {
        Gate::authorize('create', Invoice::class);

        $this->createInvoice->execute(auth()->user()->business, $request->validated());

        return to_route('invoices.index')->with('success', 'Invoice created successfully.');
    }

    public function show(Invoice $invoice): View
    {
        Gate::authorize('view', $invoice);

        $invoice->load(['client', 'items']);

        return view('dashboard.invoices.show', ['invoice' => $invoice]);
    }

    public function edit(Invoice $invoice): View
    {
        Gate::authorize('update', $invoice);

        $invoice->load(['items']);
        $clients = auth()->user()->business?->clients ?? collect();

        return view('dashboard.invoices.edit', ['invoice' => $invoice, 'clients' => $clients]);
    }

    public function update(UpdateInvoiceRequest $request, Invoice $invoice, UpdateInvoiceAction $action): RedirectResponse
    {
        Gate::authorize('update', $invoice);

        $action->execute($invoice, $request->validated());

        return to_route('invoices.show', $invoice)->with('success', 'Invoice updated successfully.');
    }

    public function preview(Request $request): View
    {
        // Get form data from request
        $data = $request->all();

        // Get client information if client_id is provided
        $client = null;
        if (! empty($data['client_id'])) {
            $client = Client::query()->find($data['client_id']);
        }

        // Get business information
        $business = auth()->user()->business;

        // Prepare invoice data object for the view
        $invoiceData = (object) [
            'invoice_number' => $data['invoice_number'] ?? 'PREVIEW',
            'issue_date' => $data['issue_date'] ?? now()->format('Y-m-d'),
            'due_date' => $data['due_date'] ?? now()->addDays(30)->format('Y-m-d'),
            'currency' => $data['currency'] ?? 'USD',
            'currency_symbol' => $data['currency_symbol'] ?? '$',
            'subtotal' => $data['subtotal'] ?? 0,
            'discount_amount' => $data['discount_amount'] ?? 0,
            'tax_amount' => $data['tax_amount'] ?? 0,
            'total_amount' => $data['total'] ?? 0,
            'note' => $data['note'] ?? '',
            'items' => collect($data['items'] ?? []),
            'billing_from' => [
                'name' => $data['billing_from_name'] ?? $business?->business_name,
                'address' => $data['billing_from_address'] ?? '',
                'email' => $data['billing_from_email'] ?? $business?->email,
                'phone' => $data['billing_from_phone'] ?? $business?->whatsapp_number,
                'subject' => $data['billing_subject'] ?? '',
            ],
            'billing_to' => [
                'name' => $client?->name ?? '',
                'email' => $client?->email ?? '',
                'phone' => $client?->whatsapp_number ?? '',
            ],
        ];

        return view('dashboard.invoices.preview', ['invoiceData' => $invoiceData]);
    }

    public function destroy(Request $request, Invoice $invoice): RedirectResponse|JsonResponse
    {
        Gate::authorize('delete', $invoice);

        $this->deleteInvoice->execute($invoice);

        if ($request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Invoice deleted successfully!',
                'redirect' => route('invoices.index'),
            ]);
        }

        return to_route('invoices.index')->with('success', 'Invoice deleted successfully.');
    }

    public function allForBusiness(Request $request)
    {
        Gate::authorize('viewAny', Invoice::class);

        $userId = auth()->id();

        // Get email templates from email_templates table
        $emailTemplates = EmailTemplate::query()
            ->where('user_id', $userId)
            ->where('is_active', true)
            ->get()
            ->map(function ($template): array {
                // Extract content from template_html or template_json
                $content = $template->template_html;
                if (! $content && $template->template_json) {
                    // If template_json is an array, try to extract text
                    $content = is_array($template->template_json) ? json_encode($template->template_json) : $template->template_json;
                }

                return [
                    'id' => $template->id,
                    'name' => $template->name,
                    'subject' => $template->subject,
                    'content' => $content ?? 'Email template content',
                    'channel' => 'email',
                    'is_default' => $template->is_default,
                ];
            });

        // Get WhatsApp/SMS templates from message_templates table
        $messageTemplates = MessageTemplates::query()
            ->where('user_id', $userId)
            ->where('is_active', true)
            ->whereIn('channel', ['whatsapp', 'sms'])
            ->get()
            ->map(fn ($template): array => [
                'id' => $template->id,
                'name' => $template->name,
                'content' => $template->content,
                'channel' => $template->channel,
                'is_default' => $template->is_default,
            ]);

        // Combine both template types
        $allTemplates = $emailTemplates->merge($messageTemplates);

        return response()->json($allTemplates->values());
    }

    /**
     * Get invoice statistics for last 6 months for chart
     *
     * @return array{categories: array<int, string>, total: array<int, int>, paid: array<int, int>, pending: array<int, int>, overdue: array<int, int>}
     */
    private function getInvoiceChartData(string $businessId): array
    {
        $months = [];
        $total = [];
        $paid = [];
        $pending = [];
        $overdue = [];

        // Get last 6 months
        for ($i = 5; $i >= 0; $i--) {
            $date = now()->subMonths($i);
            $startOfMonth = $date->copy()->startOfMonth();
            $endOfMonth = $date->copy()->endOfMonth();

            $months[] = $date->format('M');

            // Total invoices count for the month
            $total[] = Invoice::query()->where('business_id', $businessId)
                ->whereBetween('created_at', [$startOfMonth, $endOfMonth])
                ->count();

            // Paid invoices count
            $paid[] = Invoice::query()->where('business_id', $businessId)
                ->where('status', 'paid')
                ->whereBetween('created_at', [$startOfMonth, $endOfMonth])
                ->count();

            // Pending invoices count
            $pending[] = Invoice::query()->where('business_id', $businessId)
                ->where('status', 'sent')
                ->whereBetween('created_at', [$startOfMonth, $endOfMonth])
                ->count();

            // Overdue invoices count
            $overdue[] = Invoice::query()->where('business_id', $businessId)
                ->where('status', 'sent')
                ->where('due_date', '<', now())
                ->whereBetween('created_at', [$startOfMonth, $endOfMonth])
                ->count();
        }

        return [
            'categories' => $months,
            'total' => $total,
            'paid' => $paid,
            'pending' => $pending,
            'overdue' => $overdue,
        ];
    }
}
