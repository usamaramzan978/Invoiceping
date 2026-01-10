<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\SubscriptionPlan;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Auth;

final class SubscriptionPlanController extends Controller
{
    /**
     * Display available subscription plans
     */
    public function index(): View
    {
        $plans = SubscriptionPlan::active()
            ->orderBy('sort_order')
            ->orderBy('monthly_price')
            ->get();

        $user = Auth::user();
        $currentSubscription = $user?->activeSubscription;

        return view('dashboard.subscriptions.plans', [
            'plans' => $plans,
            'currentSubscription' => $currentSubscription,
        ]);
    }
}
