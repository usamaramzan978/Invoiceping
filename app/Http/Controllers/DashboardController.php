<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Actions\Dashboard\GetDashboardAnalyticsAction;
use Illuminate\Contracts\View\Factory;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;

final class DashboardController extends Controller
{
    public function __construct(
        private readonly GetDashboardAnalyticsAction $getDashboardAnalyticsAction
    ) {}

    public function index(Request $request): Factory|View
    {
        $analytics = $this->getDashboardAnalyticsAction->execute(
            $request->user()->id
        );

        return view('welcome', compact('analytics'));
    }

}
