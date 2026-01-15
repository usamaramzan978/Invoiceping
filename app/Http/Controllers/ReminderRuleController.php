<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Actions\ReminderRule\CreateReminderRuleAction;
use App\Actions\ReminderRule\DeleteReminderRuleAction;
use App\Actions\ReminderRule\ToggleStatusReminderRuleAction;
use App\Actions\ReminderRule\UpdateReminderRuleAction;
use App\Http\Requests\StoreReminderRuleRequest;
use App\Http\Requests\UpdateReminderRuleRequest;
use App\Models\EmailTemplate;
use App\Models\MessageTemplates;
use App\Models\ReminderRule;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\View\View;

final class ReminderRuleController extends Controller
{
    public function __construct(
        private readonly CreateReminderRuleAction $createAction,
        private readonly UpdateReminderRuleAction $updateAction,
        private readonly DeleteReminderRuleAction $deleteAction,
        private readonly ToggleStatusReminderRuleAction $toggleStatusAction
    ) {}

    public function index(): View
    {
        Gate::authorize('viewAny', ReminderRule::class);

        $rules = ReminderRule::with('steps.templates')
            ->where('user_id', auth()->id())
            ->latest()
            ->get();

        return view('dashboard.reminder-rules.index', ['rules' => $rules]);
    }

    public function create(): View
    {
        Gate::authorize('create', ReminderRule::class);

        $userId = auth()->user()->id;

        // Get WhatsApp & SMS templates from message_templates table
        $messageTemplates = MessageTemplates::query()
            ->where('user_id', $userId)
            ->where('is_active', true)
            ->get();

        // Get Email templates from email_templates table
        $emailTemplates = EmailTemplate::query()
            ->where('user_id', $userId)
            ->where('is_active', true)
            ->get();

        // Pass email templates with template_json for JavaScript InvoiceBlock detection
        $emailTemplatesWithJson = $emailTemplates->map(fn($template): array => [
            'id' => $template->id,
            'name' => $template->name,
            'template_json' => $template->template_json,
        ]);

        return view('dashboard.reminder-rules.create', [
            'messageTemplates' => $messageTemplates,
            'emailTemplates' => $emailTemplates,
            'emailTemplatesJson' => $emailTemplatesWithJson, // For JavaScript
        ]);
    }

    public function store(StoreReminderRuleRequest $request): RedirectResponse
    {
        Gate::authorize('create', ReminderRule::class);

        $this->createAction->execute(auth()->id(), $request->validated());

        return to_route('reminder-rules.index')->with('success', 'Reminder rule created successfully.');
    }

    public function edit(ReminderRule $rule): View
    {
        Gate::authorize('update', $rule);

        $rule->load('steps.templates');
        $userId = auth()->user()->id;

        // Get WhatsApp & SMS templates from message_templates table
        $messageTemplates = MessageTemplates::query()
            ->where('user_id', $userId)
            ->where('is_active', true)
            ->get();

        // Get Email templates from email_templates table
        $emailTemplates = EmailTemplate::query()
            ->where('user_id', $userId)
            ->where('is_active', true)
            ->get();

        // Pass email templates with template_json for JavaScript InvoiceBlock detection
        $emailTemplatesWithJson = $emailTemplates->map(fn($template): array => [
            'id' => $template->id,
            'name' => $template->name,
            'template_json' => $template->template_json,
        ]);

        return view('dashboard.reminder-rules.edit', [
            'rule' => $rule,
            'messageTemplates' => $messageTemplates,
            'emailTemplates' => $emailTemplates,
            'emailTemplatesJson' => $emailTemplatesWithJson, // For JavaScript
        ]);
    }

    public function update(UpdateReminderRuleRequest $request, ReminderRule $rule): RedirectResponse
    {
        Gate::authorize('update', $rule);

        $this->updateAction->execute($rule, $request->validated());

        return to_route('reminder-rules.index')->with('success', 'Reminder rule updated successfully.');
    }

    public function destroy(Request $request, ReminderRule $rule): RedirectResponse|JsonResponse
    {
        Gate::authorize('delete', $rule);

        $this->deleteAction->execute($rule);

        if ($request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Reminder rule deleted successfully!',
                'redirect' => route('reminder-rules.index'),
            ]);
        }

        return to_route('reminder-rules.index')->with('success', 'Reminder rule deleted successfully.');
    }

    public function toggleStatus(ReminderRule $rule): RedirectResponse
    {
        Gate::authorize('toggleStatus', $rule);

        $this->toggleStatusAction->execute($rule);

        return back()->with('success', 'Status updated successfully.');
    }
}
