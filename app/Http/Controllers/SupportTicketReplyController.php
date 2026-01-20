<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\BusinessProfile;
use App\Models\SupportTicket;
use App\Models\SupportTicketReply;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;

final class SupportTicketReplyController extends Controller
{
    public function store(Request $request, SupportTicket $support_ticket): RedirectResponse
    {
        Gate::authorize('view', $support_ticket);

        $data = $request->validate([
            'message' => ['required', 'string', 'max:2000'],
        ]);

        /** @var BusinessProfile|null $business */
        $business = Auth::user()?->business;

        // Ensure ticket belongs to current business in non-admin context
        if ($business && $support_ticket->business_id !== $business->id) {
            abort(403);
        }

        SupportTicketReply::query()->create([
            'support_ticket_id' => $support_ticket->id,
            'user_id' => Auth::id(),
            'is_admin' => false,
            'message' => $data['message'],
        ]);

        return to_route('support-tickets.show', $support_ticket)
            ->with('success', 'Reply added successfully.');
    }

    public function storeAdmin(Request $request, SupportTicket $support_ticket): RedirectResponse
    {
        // Admin can reply to any ticket but still must be authenticated
        Gate::authorize('view', $support_ticket);

        $data = $request->validate([
            'message' => ['required', 'string', 'max:2000'],
        ]);

        SupportTicketReply::query()->create([
            'support_ticket_id' => $support_ticket->id,
            'user_id' => Auth::id(),
            'is_admin' => true,
            'message' => $data['message'],
        ]);

        return to_route('support-tickets.show', $support_ticket)
            ->with('success', 'Admin reply added successfully.');
    }
}

