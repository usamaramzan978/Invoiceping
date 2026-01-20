<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Actions\SupportTicket\CreateSupportTicketAction;
use App\Actions\SupportTicket\DeleteSupportTicketAction;
use App\Actions\SupportTicket\UpdateSupportTicketAction;
use App\Enums\SupportTicketPriority;
use App\Enums\SupportTicketStatus;
use App\Http\Requests\StoreSupportTicketRequest;
use App\Http\Requests\UpdateSupportTicketRequest;
use App\Models\BusinessProfile;
use App\Models\SupportTicket;
use App\Models\SupportTicketReply;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;

final class SupportTicketController extends Controller
{
    public function __construct(
        private readonly CreateSupportTicketAction $createTicket,
        private readonly UpdateSupportTicketAction $updateTicket,
        private readonly DeleteSupportTicketAction $deleteTicket,
    ) {}

    public function index(Request $request): View
    {
        Gate::authorize('viewAny', SupportTicket::class);

        $routeName = (string) $request->route()?->getName();
        $isAdminRoute = str_starts_with($routeName, 'admin.');

        $query = SupportTicket::query()
            ->with(['client', 'invoice'])
            ->latest();

        if (! $isAdminRoute) {
            /** @var BusinessProfile|null $business */
            $business = Auth::user()?->business;
            $businessId = $business?->id;
            $query->where('business_id', $businessId);
        }

        if ($search = $request->get('search')) {
            $query->where(function ($q) use ($search): void {
                $q->where('subject', 'like', "%{$search}%")
                    ->orWhere('message', 'like', "%{$search}%");
            });
        }

        if ($status = $request->get('status')) {
            $query->where('status', $status);
        }

        if ($priority = $request->get('priority')) {
            $query->where('priority', $priority);
        }

        $tickets = $query->paginate(15);

        return view('dashboard.support-tickets.index', [
            'tickets' => $tickets,
            'statuses' => SupportTicketStatus::cases(),
            'priorities' => SupportTicketPriority::cases(),
        ]);
    }

    public function create(): View
    {
        Gate::authorize('create', SupportTicket::class);

        /** @var BusinessProfile|null $business */
        $business = Auth::user()?->business;
        $clients = $business?->clients()->orderBy('name')->get() ?? collect();
        $invoices = $business?->invoices()->orderBy('created_at', 'desc')->limit(50)->get() ?? collect();

        return view('dashboard.support-tickets.create', [
            'clients' => $clients,
            'invoices' => $invoices,
            'priorities' => SupportTicketPriority::cases(),
        ]);
    }

    public function store(StoreSupportTicketRequest $request): RedirectResponse
    {
        Gate::authorize('create', SupportTicket::class);

        try {
            /** @var BusinessProfile|null $business */
            $business = Auth::user()?->business;

            $data = $request->validated();

            if ($request->hasFile('attachment')) {
                $data['attachment_path'] = $request->file('attachment')->store('support-attachments', 'public');
            }
            $data['business_id'] = $business?->id;
            $data['created_by'] = Auth::id();

            $ticket = $this->createTicket->handle($data);

            return to_route('support-tickets.show', $ticket)->with('success', 'Support ticket created successfully.');
        } catch (Exception $exception) {
            return back()->withInput()->with('error', 'Failed to create support ticket: '.$exception->getMessage());
        }
    }

    public function show(SupportTicket $support_ticket): View
    {
        Gate::authorize('view', $support_ticket);

        $support_ticket->load(['client', 'invoice', 'creator', 'replies.user']);

        return view('dashboard.support-tickets.show', [
            'ticket' => $support_ticket,
        ]);
    }

    public function edit(Request $request, SupportTicket $support_ticket): View|RedirectResponse
    {
        Gate::authorize('update', $support_ticket);

        // Business users cannot edit ticket content after an admin reply
        $routeName = (string) $request->route()?->getName();
        $isAdminRoute = str_starts_with($routeName, 'admin.');

        if (! $isAdminRoute && $support_ticket->replies()->where('is_admin', true)->exists()) {
            return to_route('support-tickets.show', $support_ticket)
                ->with('error', 'You cannot edit this ticket after an admin has replied.');
        }

        /** @var BusinessProfile|null $business */
        $business = Auth::user()?->business;
        $clients = $business?->clients()->orderBy('name')->get() ?? collect();
        $invoices = $business?->invoices()->orderBy('created_at', 'desc')->limit(50)->get() ?? collect();

        return view('dashboard.support-tickets.edit', [
            'ticket' => $support_ticket,
            'clients' => $clients,
            'invoices' => $invoices,
            'statuses' => SupportTicketStatus::cases(),
            'priorities' => SupportTicketPriority::cases(),
        ]);
    }

    public function update(UpdateSupportTicketRequest $request, SupportTicket $support_ticket): RedirectResponse
    {
        Gate::authorize('update', $support_ticket);

        try {
            // Prevent business users from editing ticket content after an admin reply
            $routeName = (string) $request->route()?->getName();
            $isAdminRoute = str_starts_with($routeName, 'admin.');

            if (! $isAdminRoute && $support_ticket->replies()->where('is_admin', true)->exists()) {
                return to_route('support-tickets.show', $support_ticket)
                    ->with('error', 'You cannot edit this ticket after an admin has replied.');
            }

            $data = $request->validated();

            if ($request->hasFile('attachment')) {
                if ($support_ticket->attachment_path) {
                    Storage::disk('public')->delete($support_ticket->attachment_path);
                }

                $data['attachment_path'] = $request->file('attachment')->store('support-attachments', 'public');
            }

            $this->updateTicket->handle($support_ticket, $data);

            return to_route('support-tickets.show', $support_ticket)
                ->with('success', 'Support ticket updated successfully.');
        } catch (Exception $exception) {
            return back()->withInput()->with('error', 'Failed to update support ticket: '.$exception->getMessage());
        }
    }

    public function destroy(Request $request, SupportTicket $support_ticket): RedirectResponse|JsonResponse
    {
        Gate::authorize('delete', $support_ticket);

        $this->deleteTicket->handle($support_ticket);

        if ($request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Support ticket deleted successfully!',
                'redirect' => route('support-tickets.index'),
            ]);
        }

        return to_route('support-tickets.index')->with('success', 'Support ticket deleted successfully.');
    }
}

