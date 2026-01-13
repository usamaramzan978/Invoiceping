<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\BillingTransaction;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Gate;

final class BillingController extends Controller
{

    /**
     * Display billing history
     */
    public function index(): View
    {
        Gate::authorize('viewAny', BillingTransaction::class);

        $user = auth()->user();

        $transactions = BillingTransaction::query()->where('user_id', $user->id)
            ->with('subscription.plan')->latest()
            ->paginate(15);

        $totalSpent = BillingTransaction::query()->where('user_id', $user->id)
            ->completed()
            ->payments()
            ->sum('amount');

        return view('dashboard.billing.index', [
            'transactions' => $transactions,
            'totalSpent' => $totalSpent,
        ]);
    }

    /**
     * Show single transaction details
     */
    public function show(BillingTransaction $transaction): View
    {
        Gate::authorize('view', $transaction);

        return view('dashboard.billing.show', [
            'transaction' => $transaction->load('subscription.plan'),
        ]);
    }
}
