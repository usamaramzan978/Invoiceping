<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\SubscriptionInvoice;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Gate;

final class SubscriptionInvoiceController extends Controller
{
    /**
     * Display subscription invoices
     */
    public function index(): View
    {
        Gate::authorize('viewAny', SubscriptionInvoice::class);

        $user = auth()->user();

        $invoices = SubscriptionInvoice::query()->where('user_id', $user->id)
            ->with('subscription.plan')->latest()
            ->paginate(15);

        $paidCount = SubscriptionInvoice::query()->where('user_id', $user->id)
            ->paid()
            ->count();

        $pendingCount = SubscriptionInvoice::query()->where('user_id', $user->id)
            ->pending()
            ->count();

        $overdueCount = SubscriptionInvoice::query()->where('user_id', $user->id)
            ->overdue()
            ->count();

        return view('dashboard.billing.invoices.index', [
            'invoices' => $invoices,
            'paidCount' => $paidCount,
            'pendingCount' => $pendingCount,
            'overdueCount' => $overdueCount,
        ]);
    }

    /**
     * Show invoice details
     */
    public function show(SubscriptionInvoice $invoice): View
    {
        Gate::authorize('view', $invoice);

        return view('dashboard.billing.invoices.show', [
            'invoice' => $invoice->load('subscription.plan', 'user'),
        ]);
    }

    /**
     * Download invoice as PDF
     */
    public function download(SubscriptionInvoice $invoice): Response
    {
        Gate::authorize('download', $invoice);

        $pdf = Pdf::loadView('dashboard.billing.invoices.pdf', [
            'invoice' => $invoice->load('subscription.plan', 'user'),
        ]);

        return $pdf->download(sprintf('invoice-%s.pdf', $invoice->invoice_number));
    }
}
