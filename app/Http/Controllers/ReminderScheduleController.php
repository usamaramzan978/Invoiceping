<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Actions\ReminderSchedule\CancelReminderScheduleAction;
use App\Actions\ReminderSchedule\CreateReminderScheduleAction;
use App\Actions\ReminderSchedule\RescheduleReminderAction;
use App\Actions\ReminderSchedule\UpdateReminderScheduleAction;
use App\Http\Requests\RescheduleReminderRequest;
use App\Http\Requests\StoreReminderScheduleRequest;
use App\Http\Requests\UpdateReminderScheduleRequest;
use App\Models\EmailTemplate;
use App\Models\Invoice;
use App\Models\MessageTemplates;
use App\Models\ReminderRule;
use App\Models\ReminderSchedule;
use Exception;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Date;
use Illuminate\Support\Facades\Gate;
use Illuminate\View\View;
use InvalidArgumentException;

final class ReminderScheduleController extends Controller
{
    public function __construct(
        private readonly CreateReminderScheduleAction $createAction,
        private readonly UpdateReminderScheduleAction $updateAction,
        private readonly CancelReminderScheduleAction $cancelAction,
        private readonly RescheduleReminderAction $rescheduleAction
    ) {}

    /**
     * Display a listing of reminder schedules.
     */
    public function index(): View
    {
        Gate::authorize('viewAny', ReminderSchedule::class);

        $user = auth()->user();
        $business = $user->business;

        abort_unless($business, 404, 'Business profile not found');

        /**
         * Get ONE representative row per logical reminder group
         * Only show schedules for invoices belonging to user's business
         */
        $subquery = ReminderSchedule::query()
            ->join('invoices', 'reminder_schedules.invoice_id', '=', 'invoices.id')
            ->where('invoices.business_id', $business->id)
            ->selectRaw('MIN(reminder_schedules.id) as id')
            ->groupByRaw('
                COALESCE(reminder_schedules.bulk_group_id, CAST(reminder_schedules.id AS CHAR)),
                reminder_schedules.reminder_rule_step_id,
                COALESCE(CAST(reminder_schedules.email_template_id AS CHAR), reminder_schedules.message_template_id),
                reminder_schedules.channel
            ');

        $query = ReminderSchedule::query()
            ->whereIn('id', $subquery)
            ->with([
                'invoice.client',
                'invoice.business',
                'rule',
                'step',
                'messageTemplate',
                'emailTemplate',
            ])
            ->latest('scheduled_at');

        /**
         * Paginate representative rows
         */
        $schedules = $query->paginate(15);

        /**
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

    /**
     * Show the form for creating a new reminder schedule.
     */
    public function create(): View
    {
        Gate::authorize('create', ReminderSchedule::class);

        $user = auth()->user();
        $business = $user->business;

        abort_unless($business, 404, 'Business profile not found');

        $invoices = Invoice::query()
            ->where('business_id', $business->id)
            ->latest()
            ->get();

        $rules = ReminderRule::query()
            ->where('user_id', $user->id)
            ->with(['steps.templates.emailTemplate', 'steps.templates.messageTemplate'])
            ->get();

        $emailTemplates = EmailTemplate::query()
            ->where('user_id', $user->id)
            ->where('is_active', true)
            ->get();

        $messageTemplates = MessageTemplates::query()
            ->where('user_id', $user->id)
            ->where('is_active', true)
            ->get();

        // Pass email templates with template_json for JavaScript InvoiceBlock detection
        $emailTemplatesWithJson = $emailTemplates->map(function ($template) {
            return [
                'id' => $template->id,
                'name' => $template->name,
                'template_json' => $template->template_json,
            ];
        });

        return view('dashboard.schedule-reminders.create', [
            'invoices' => $invoices,
            'rules' => $rules,
            'emailTemplates' => $emailTemplates,
            'emailTemplatesJson' => $emailTemplatesWithJson, // For JavaScript
            'messageTemplates' => $messageTemplates,
        ]);
    }

    /**
     * Store a newly created reminder schedule.
     */
    public function store(StoreReminderScheduleRequest $request): RedirectResponse
    {
        Gate::authorize('create', ReminderSchedule::class);

        try {
            $result = $this->createAction->execute($request->validated(), auth()->id());

            $message = $result['source_type'] === 'rule'
                ? 'Rule scheduled successfully.'
                : 'Reminder(s) scheduled successfully.';

            return to_route('schedule-reminders.index')->with('success', $message);
        } catch (Exception $exception) {
            return back()
                ->withInput()
                ->with('error', 'Failed to create reminder schedule: '.$exception->getMessage());
        }
    }

    /**
     * Show the form for editing the specified reminder schedule.
     */
    public function edit(ReminderSchedule $schedule): View
    {
        Gate::authorize('update', $schedule);

        $user = auth()->user();
        $business = $user->business;

        abort_unless($business, 404, 'Business profile not found');

        $invoices = Invoice::query()
            ->where('business_id', $business->id)
            ->latest()
            ->get();

        $rules = ReminderRule::query()
            ->where('user_id', $user->id)
            ->with(['steps.templates.emailTemplate', 'steps.templates.messageTemplate'])
            ->get();

        $emailTemplates = EmailTemplate::query()
            ->where('user_id', $user->id)
            ->where('is_active', true)
            ->get();

        $messageTemplates = MessageTemplates::query()
            ->where('user_id', $user->id)
            ->where('is_active', true)
            ->get();

        // Get related schedules for bulk group (if any)
        $relatedSchedules = $schedule->bulk_group_id
            ? ReminderSchedule::query()
                ->where('bulk_group_id', $schedule->bulk_group_id)
                ->with('invoice')
                ->get()
            : collect([$schedule]);

        return view('dashboard.schedule-reminders.edit', [
            'schedule' => $schedule,
            'invoices' => $invoices,
            'rules' => $rules,
            'emailTemplates' => $emailTemplates,
            'messageTemplates' => $messageTemplates,
            'relatedSchedules' => $relatedSchedules,
        ]);
    }

    /**
     * Update the specified reminder schedule.
     */
    public function update(UpdateReminderScheduleRequest $request, ReminderSchedule $schedule): RedirectResponse
    {
        Gate::authorize('update', $schedule);

        try {
            $result = $this->updateAction->execute($schedule, $request->validated(), auth()->id());

            $message = $result['source_type'] === 'rule'
                ? 'Rule updated successfully.'
                : 'Reminder(s) updated successfully.';

            return to_route('schedule-reminders.index')->with('success', $message);
        } catch (Exception $exception) {
            return back()
                ->withInput()
                ->with('error', 'Failed to update reminder schedule: '.$exception->getMessage());
        }
    }

    /**
     * Cancel the specified reminder schedule.
     */
    public function cancel(ReminderSchedule $schedule): RedirectResponse
    {
        Gate::authorize('cancel', $schedule);

        try {
            $cancelledCount = $this->cancelAction->execute($schedule, auth()->id());

            $message = $cancelledCount > 1
                ? $cancelledCount.' reminder(s) cancelled successfully.'
                : 'Reminder cancelled successfully.';

            return back()->with('success', $message);
        } catch (InvalidArgumentException $e) {
            return back()->with('error', $e->getMessage());
        } catch (Exception $e) {
            return back()->with('error', 'Failed to cancel reminder: '.$e->getMessage());
        }
    }

    /**
     * Reschedule the specified reminder schedule.
     */
    public function reschedule(RescheduleReminderRequest $request, ReminderSchedule $schedule): RedirectResponse
    {
        Gate::authorize('reschedule', $schedule);

        try {
            $rescheduledCount = $this->rescheduleAction->execute(
                $schedule,
                Date::parse($request->validated()['scheduled_at']),
                auth()->id()
            );

            $message = $rescheduledCount > 1
                ? $rescheduledCount.' reminder(s) rescheduled successfully.'
                : 'Reminder rescheduled successfully.';

            return back()->with('success', $message);
        } catch (InvalidArgumentException $e) {
            return back()->with('error', $e->getMessage());
        } catch (Exception $e) {
            return back()->with('error', 'Failed to reschedule reminder: '.$e->getMessage());
        }
    }
}
