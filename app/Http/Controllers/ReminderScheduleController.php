<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Enums\ReminderSourceTypeEnum;
use App\Enums\ReminderStatusEnum;
use App\Models\Invoice;
use App\Models\MessageTemplates;
use App\Models\ReminderRule;
use App\Models\ReminderSchedule;
use Carbon\Carbon;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Date;
use Illuminate\Support\Str;
use Illuminate\View\View;

final class ReminderScheduleController
{
    public function index(Request $request): View
    {
        /**
         * STEP 1:
         * Get ONE representative row per logical reminder group
         */
        $subquery = ReminderSchedule::query()
            ->selectRaw('MIN(id) as id')
            ->groupByRaw('
            COALESCE(bulk_group_id, CAST(id AS CHAR)),
            reminder_rule_step_id,
            message_template_id,
            channel
        ');

        $query = ReminderSchedule::query()
            ->whereIn('id', $subquery)
            ->with([
                'invoice.client',
                'rule',
                'step',
                'messageTemplate',
            ])
            ->latest('scheduled_at');

        /**
         * STEP 2:
         * Filters
         */
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('invoice_id')) {
            $query->where('invoice_id', $request->invoice_id);
        }

        /**
         * STEP 3:
         * Paginate representative rows
         */
        $schedules = $query->paginate(15);

        /**
         * STEP 4:
         * Load all related schedules for bulk groups (single query)
         */
        $bulkGroupIds = $schedules
            ->pluck('bulk_group_id')
            ->filter()
            ->unique()
            ->values();

        $relatedSchedules = $bulkGroupIds->isNotEmpty()
            ? ReminderSchedule::query()
                ->whereIn('bulk_group_id', $bulkGroupIds)
                ->with('invoice')
                ->get()
                ->groupBy(fn (ReminderSchedule $item): string => $item->groupKey())
            : collect();

        /**
         * STEP 5:
         * Attach grouped schedules to each row
         */
        foreach ($schedules as $schedule) {
            if ($schedule->bulk_group_id) {
                $schedule->setRelation(
                    'all_schedules_in_group',
                    $relatedSchedules->get(
                        $schedule->groupKey(),
                        collect([$schedule])
                    )
                );
            } else {
                // Manual / single reminders
                $schedule->setRelation(
                    'all_schedules_in_group',
                    collect([$schedule])
                );
            }
        }

        return view('dashboard.schedule-reminders.index', [
            'schedules' => $schedules,
        ]);
    }

    public function create(): View
    {
        $business = auth()->user()->business;
        $invoices = Invoice::query()->where('business_id', $business->id)->latest()->get();
        $rules = ReminderRule::query()->where('user_id', auth()->id())
            ->with(['steps.templates.messageTemplate'])
            ->get();
        $templates = MessageTemplates::query()->where('business_id', $business->id)->get();

        return view('dashboard.schedule-reminders.create', ['invoices' => $invoices, 'rules' => $rules, 'templates' => $templates]);
    }

    public function store(Request $request): RedirectResponse
    {
        $request->validate([
            'invoice_ids' => ['required', 'array'],
            'invoice_ids.*' => ['exists:invoices,id'], // make sure IDs exist and match type
            'source_type' => ['required', 'in:manual,rule'],
            'channel' => ['nullable', 'required_if:source_type,manual', 'string'],
            'message_template_id' => ['nullable', 'required_if:source_type,manual', 'exists:message_templates,id'],
            'reminder_rule_id' => ['nullable', 'required_if:source_type,rule', 'exists:reminder_rules,id'],
            'scheduled_at' => ['required', 'date_format:Y-m-d\TH:i', 'after:now'], // <- handle T
        ]);

        $invoiceIds = $request->input('invoice_ids', []);
        $sourceType = $request->source_type;
        $bulkGroupId = Str::uuid(); // Always use a bulk group for consistency when scheduling a rule or multiple invoices

        foreach ($invoiceIds as $invoiceId) {
            $invoice = Invoice::query()->findOrFail($invoiceId);

            if ($sourceType === 'manual') {
                ReminderSchedule::query()->create([
                    'invoice_id' => $invoiceId,
                    'source_type' => ReminderSourceTypeEnum::MANUAL,
                    'channel' => $request->channel,
                    'message_template_id' => $request->message_template_id,
                    'bulk_group_id' => count($invoiceIds) > 1 ? $bulkGroupId : null,
                    'scheduled_at' => $request->scheduled_at,
                    'status' => ReminderStatusEnum::PENDING,
                ]);
            } else {
                $rule = ReminderRule::with('steps.templates')->findOrFail($request->reminder_rule_id);
                $referenceDate = Date::parse($request->scheduled_at);

                foreach ($rule->steps as $step) {
                    // Calculate scheduled_at based on referenceDate and step offset
                    $scheduledAt = $this->calculateScheduledAt($referenceDate, $step);

                    foreach ($step->templates as $template) {
                        ReminderSchedule::query()->create([
                            'invoice_id' => $invoiceId,
                            'source_type' => 'rule',
                            'channel' => $template->channel,
                            'message_template_id' => $template->message_template_id,
                            'reminder_rule_id' => $rule->id,
                            'reminder_rule_step_id' => $step->id,
                            'bulk_group_id' => $bulkGroupId,
                            'scheduled_at' => $scheduledAt,
                            'status' => ReminderStatusEnum::PENDING,
                        ]);
                    }
                }
            }
        }

        $message = $sourceType === 'rule' ? 'Rule scheduled successfully.' : 'Reminder(s) scheduled successfully.';

        return to_route('schedule-reminders.index')->with('success', $message);
    }

    public function edit(ReminderSchedule $schedule): View
    {
        $business = auth()->user()->business;
        $invoices = Invoice::query()->where('business_id', $business->id)->latest()->get();
        $rules = ReminderRule::query()->where('user_id', auth()->id())
            ->with(['steps.templates.messageTemplate'])
            ->get();
        $templates = MessageTemplates::query()->where('business_id', $business->id)->get();

        // Get related invoices for bulk group (if any)
        $relatedSchedules = ReminderSchedule::query()->where('bulk_group_id', $schedule->bulk_group_id)
            ->get();

        return view('dashboard.schedule-reminders.edit', ['schedule' => $schedule, 'invoices' => $invoices, 'rules' => $rules, 'templates' => $templates, 'relatedSchedules' => $relatedSchedules]);
    }

    public function update(Request $request, ReminderSchedule $schedule): RedirectResponse
    {
        $request->validate([
            'invoice_ids' => ['required', 'array'],
            'invoice_ids.*' => ['exists:invoices,id'],
            'source_type' => ['required', 'in:manual,rule'],
            'channel' => ['required_if:source_type,manual', 'string'],
            'message_template_id' => ['required_if:source_type,manual', 'exists:message_templates,id'],
            'reminder_rule_id' => ['required_if:source_type,rule', 'exists:reminder_rules,id'],
            'scheduled_at' => ['required', 'datetime'],
        ]);

        $invoiceIds = $request->input('invoice_ids', []);
        $sourceType = $request->source_type;

        // Determine if this is a bulk group update or single update
        $bulkGroupId = $schedule->bulk_group_id;

        // Delete existing schedules in the group (or just this one)
        if ($bulkGroupId) {
            ReminderSchedule::query()->where('bulk_group_id', $bulkGroupId)->delete();
        } else {
            $schedule->delete();
        }

        // Generate a new bulk group ID for the updated set
        $newBulkGroupId = Str::uuid();

        foreach ($invoiceIds as $invoiceId) {
            $invoice = Invoice::query()->findOrFail($invoiceId);

            if ($sourceType === 'manual') {
                ReminderSchedule::query()->create([
                    'invoice_id' => $invoiceId,
                    'source_type' => ReminderSourceTypeEnum::MANUAL,
                    'channel' => $request->channel,
                    'message_template_id' => $request->message_template_id,
                    'bulk_group_id' => count($invoiceIds) > 1 ? $newBulkGroupId : null,
                    'scheduled_at' => $request->scheduled_at,
                    'status' => ReminderStatusEnum::PENDING,
                ]);
            } else {
                $rule = ReminderRule::with('steps.templates')->findOrFail($request->reminder_rule_id);
                $referenceDate = Date::parse($request->scheduled_at);

                foreach ($rule->steps as $step) {
                    $scheduledAt = $this->calculateScheduledAt($referenceDate, $step);

                    foreach ($step->templates as $template) {
                        ReminderSchedule::query()->create([
                            'invoice_id' => $invoiceId,
                            'source_type' => ReminderSourceTypeEnum::RULE,
                            'channel' => $template->channel,
                            'message_template_id' => $template->message_template_id,
                            'reminder_rule_id' => $rule->id,
                            'reminder_rule_step_id' => $step->id,
                            'bulk_group_id' => $newBulkGroupId,
                            'scheduled_at' => $scheduledAt,
                            'status' => ReminderStatusEnum::PENDING,
                        ]);
                    }
                }
            }
        }

        $message = $sourceType === 'rule' ? 'Rule updated successfully.' : 'Reminder(s) updated successfully.';

        return to_route('schedule-reminders.index')->with('success', $message);
    }

    public function cancel(ReminderSchedule $schedule): RedirectResponse
    {
        if ($schedule->status !== ReminderStatusEnum::PENDING) {
            return back()->with('error', 'Only pending reminders can be cancelled.');
        }

        // Cancel related schedules if bulk group exists
        if ($schedule->bulk_group_id) {
            ReminderSchedule::query()->where('bulk_group_id', $schedule->bulk_group_id)
                ->where('reminder_rule_step_id', $schedule->reminder_rule_step_id)
                ->where('message_template_id', $schedule->message_template_id)
                ->where('channel', $schedule->channel)
                ->update(['status' => ReminderStatusEnum::CANCELLED]);
        } else {
            $schedule->update(['status' => ReminderStatusEnum::CANCELLED]);
        }

        return back()->with('success', 'Reminder(s) cancelled successfully.');
    }

    public function reschedule(Request $request, ReminderSchedule $schedule): RedirectResponse
    {
        $request->validate([
            'scheduled_at' => ['required', 'date', 'after:now'],
        ]);

        if ($schedule->status !== ReminderStatusEnum::PENDING) {
            return back()->with('error', 'Only pending reminders can be rescheduled.');
        }

        $scheduledAt = $request->scheduled_at;

        // Reschedule related schedules if bulk group exists
        if ($schedule->bulk_group_id) {
            ReminderSchedule::query()->where('bulk_group_id', $schedule->bulk_group_id)
                ->where('reminder_rule_step_id', $schedule->reminder_rule_step_id)
                ->where('message_template_id', $schedule->message_template_id)
                ->where('channel', $schedule->channel)
                ->update(['scheduled_at' => $scheduledAt]);
        } else {
            $schedule->update(['scheduled_at' => $scheduledAt]);
        }

        return back()->with('success', 'Reminder(s) rescheduled successfully.');
    }

    private function calculateScheduledAt(Carbon $referenceDate, $step)
    {
        $date = clone $referenceDate;
        $offset = (int) $step->offset_days;

        if ($step->reminder_type === 'before_due') {
            $date->subDays($offset);
        } elseif ($step->reminder_type === 'after_due') {
            $date->addDays($offset);
        }

        // 'on_due' doesn't need offset adjustment

        // Set a default time (e.g., 09:00 AM) or use current time if it's today
        $date->setTime(9, 0, 0);

        return $date;
    }
}
