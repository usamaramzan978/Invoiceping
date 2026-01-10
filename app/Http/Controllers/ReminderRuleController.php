<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\EmailTemplate;
use App\Models\MessageTemplates;
use App\Models\ReminderRule;
use App\Models\ReminderRuleStep;
use App\Models\ReminderRuleStepTemplate;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

final class ReminderRuleController extends Controller
{
    public function index(): View
    {
        $rules = ReminderRule::with('steps.templates')
            ->where('user_id', auth()->id())
            ->latest()
            ->get();

        return view('dashboard.reminder-rules.index', ['rules' => $rules]);
    }

    public function create(): View
    {
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

        return view('dashboard.reminder-rules.create', [
            'messageTemplates' => $messageTemplates,
            'emailTemplates' => $emailTemplates,
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'is_default' => ['boolean'],
            'steps' => ['required', 'array', 'min:1'],
            'steps.*.reminder_type' => ['required', 'string'],
            'steps.*.offset_days' => ['required', 'integer'],
            'steps.*.channels' => ['required', 'array'],
            'steps.*.channels.*.channel' => ['required', 'string'],
            'steps.*.channels.*.message_template_id' => ['nullable', 'uuid'],
            'steps.*.channels.*.email_template_id' => ['nullable', 'integer'],
        ]);

        DB::transaction(function () use ($request): void {
            if ($request->is_default) {
                ReminderRule::query()->where('user_id', auth()->id())->update(['is_default' => false]);
            }

            $rule = ReminderRule::query()->create([
                'user_id' => auth()->id(),
                'name' => $request->name,
                'is_default' => $request->is_default ?? false,
                'is_active' => true,
            ]);

            foreach ($request->steps as $index => $stepData) {
                $step = ReminderRuleStep::query()->create([
                    'reminder_rule_id' => $rule->id,
                    'reminder_type' => $stepData['reminder_type'],
                    'offset_days' => $stepData['offset_days'],
                    'sort_order' => $index,
                ]);

                foreach ($stepData['channels'] as $channelData) {
                    $templateData = [
                        'reminder_rule_step_id' => $step->id,
                        'channel' => $channelData['channel'],
                    ];

                    // Handle email templates (from email_templates table)
                    if ($channelData['channel'] === 'email' && ! empty($channelData['email_template_id'])) {
                        $templateData['email_template_id'] = $channelData['email_template_id'];
                        ReminderRuleStepTemplate::query()->create($templateData);
                    }
                    // Handle WhatsApp/SMS templates (from message_templates table)
                    elseif (in_array($channelData['channel'], ['whatsapp', 'sms']) && ! empty($channelData['message_template_id'])) {
                        $templateData['message_template_id'] = $channelData['message_template_id'];
                        ReminderRuleStepTemplate::query()->create($templateData);
                    }
                }
            }
        });

        return to_route('reminder-rules.index')->with('success', 'Reminder rule created successfully.');
    }

    public function edit(ReminderRule $rule): View
    {
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

        return view('dashboard.reminder-rules.edit', [
            'rule' => $rule,
            'messageTemplates' => $messageTemplates,
            'emailTemplates' => $emailTemplates,
        ]);
    }

    public function update(Request $request, ReminderRule $rule): RedirectResponse
    {
        $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'is_default' => ['boolean'],
            'steps' => ['required', 'array', 'min:1'],
            'steps.*.reminder_type' => ['required', 'string'],
            'steps.*.offset_days' => ['required', 'integer'],
            'steps.*.channels' => ['required', 'array'],
            'steps.*.channels.*.channel' => ['required', 'string'],
            'steps.*.channels.*.message_template_id' => ['nullable', 'uuid'],
            'steps.*.channels.*.email_template_id' => ['nullable', 'integer'],
        ]);

        DB::transaction(function () use ($request, $rule): void {
            if ($request->is_default) {
                ReminderRule::query()->where('user_id', auth()->id())
                    ->where('id', '!=', $rule->id)
                    ->update(['is_default' => false]);
            }

            $rule->update([
                'name' => $request->name,
                'is_default' => $request->is_default ?? false,
            ]);

            // Simple approach: delete and recreate steps
            $rule->steps()->each(function ($step): void {
                $step->templates()->delete();
                $step->delete();
            });

            foreach ($request->steps as $index => $stepData) {
                $step = ReminderRuleStep::query()->create([
                    'reminder_rule_id' => $rule->id,
                    'reminder_type' => $stepData['reminder_type'],
                    'offset_days' => $stepData['offset_days'],
                    'sort_order' => $index,
                ]);

                foreach ($stepData['channels'] as $channelData) {
                    $templateData = [
                        'reminder_rule_step_id' => $step->id,
                        'channel' => $channelData['channel'],
                    ];

                    // Handle email templates (from email_templates table)
                    if ($channelData['channel'] === 'email' && ! empty($channelData['email_template_id'])) {
                        $templateData['email_template_id'] = $channelData['email_template_id'];
                        ReminderRuleStepTemplate::query()->create($templateData);
                    }
                    // Handle WhatsApp/SMS templates (from message_templates table)
                    elseif (in_array($channelData['channel'], ['whatsapp', 'sms']) && ! empty($channelData['message_template_id'])) {
                        $templateData['message_template_id'] = $channelData['message_template_id'];
                        ReminderRuleStepTemplate::query()->create($templateData);
                    }
                }
            }
        });

        return to_route('reminder-rules.index')->with('success', 'Reminder rule updated successfully.');
    }

    public function destroy(Request $request, ReminderRule $rule): RedirectResponse|JsonResponse
    {
        $rule->delete();

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
        $rule->update(['is_active' => ! $rule->is_active]);

        return back()->with('success', 'Status updated successfully.');
    }
}
