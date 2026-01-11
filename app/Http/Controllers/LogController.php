<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\Log;
use App\Services\LogService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

final class LogController extends Controller
{
    public function __construct(
        private readonly LogService $logService
    ) {}

    /**
     * Display a listing of logs.
     */
    public function index(Request $request): View
    {
        $userId = Auth::id();
        $query = Log::query()->forUser($userId)->with(['invoice', 'user'])->latest();

        // Filter by type
        if ($request->filled('type')) {
            $query->ofType($request->string('type')->toString());
        }

        // Filter by status
        if ($request->filled('status')) {
            $query->withStatus($request->string('status')->toString());
        }

        // Filter by channel
        if ($request->filled('channel')) {
            $query->forChannel($request->string('channel')->toString());
        }

        // Filter by date range
        if ($request->filled('date_from')) {
            $query->whereDate('created_at', '>=', $request->date('date_from'));
        }

        if ($request->filled('date_to')) {
            $query->whereDate('created_at', '<=', $request->date('date_to'));
        }

        // Search in content, recipient, or error message
        if ($request->filled('search')) {
            $search = $request->string('search')->toString();
            $query->where(function ($q) use ($search): void {
                $q->where('content', 'like', sprintf('%%%s%%', $search))
                    ->orWhere('recipient', 'like', sprintf('%%%s%%', $search))
                    ->orWhere('error_message', 'like', sprintf('%%%s%%', $search))
                    ->orWhere('subject', 'like', sprintf('%%%s%%', $search));
            });
        }

        $logs = $query->paginate(20)->withQueryString();

        // Get statistics
        $stats = $this->logService->getStatistics($userId);

        return view('dashboard.logs.index', [
            'logs' => $logs,
            'stats' => $stats,
            'filters' => $request->only(['type', 'status', 'channel', 'date_from', 'date_to', 'search']),
        ]);
    }
}
