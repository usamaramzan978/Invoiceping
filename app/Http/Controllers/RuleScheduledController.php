<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Actions\ReminderSchedule\CreateRuleScheduledAction;
use App\Actions\ReminderSchedule\UpdateRuleScheduledAction;
use App\Http\Requests\StoreRuleScheduledRequest;
use App\Http\Requests\UpdateRuleScheduledRequest;
use App\Models\BusinessProfile;
use App\Models\Invoice;
use App\Models\ReminderRule;
use App\Models\ReminderSchedule;
use Exception;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Gate;
use Illuminate\View\View;
use InvalidArgumentException;

final class RuleScheduledController extends Controller
{
    public function __construct(
        private readonly CreateRuleScheduledAction $createAction,
        private readonly UpdateRuleScheduledAction $updateAction
    ) {}

    /**
     * Show the form for creating a new rule-based reminder schedule.
     */
    public function create(): View
    {
        Gate::authorize('create', ReminderSchedule::class);

        $user = auth()->user();
        /** @var BusinessProfile|null $business */
        $business = $user->business;

        abort_unless($business, 404, 'Business profile not found');
        $invoices = Invoice::query()
            ->where('business_id', $business->id)
            ->with('client')
            ->latest()
            ->get();

        $rules = ReminderRule::query()
            ->where('user_id', $user->id)
            ->with(['steps.templates.emailTemplate', 'steps.templates.messageTemplate'])
            ->get();

        return view('dashboard.rule-scheduled.create', [
            'invoices' => $invoices,
            'rules' => $rules,
        ]);
    }

    /**
     * Store a newly created rule-based reminder schedule.
     */
    public function store(StoreRuleScheduledRequest $request): RedirectResponse
    {
        Gate::authorize('create', ReminderSchedule::class);

        try {
            $result = $this->createAction->execute($request->validated(), auth()->id());

            $message = $result['created_count'] > 1
                ? 'Rule scheduled successfully for '.$result['created_count'].' reminder(s).'
                : 'Rule scheduled successfully.';

            return to_route('schedule-reminders.index')->with('success', $message);
        } catch (Exception $exception) {
            return back()
                ->withInput()
                ->with('error', 'Failed to schedule reminder: '.$exception->getMessage());
        }
    }

    /**
     * Show the form for editing the specified rule-based reminder schedule.
     */
    public function edit(ReminderSchedule $schedule): View
    {
        Gate::authorize('update', $schedule);

        $user = auth()->user();
        $business = $user->business;

        abort_unless($business, 404, 'Business profile not found');
        abort_unless(
            $schedule->source_type->value === 'rule',
            404,
            'This reminder schedule is not rule-based.'
        );

        $invoices = Invoice::query()
            ->where('business_id', $business->id)
            ->with('client')
            ->latest()
            ->get();

        $rules = ReminderRule::query()
            ->where('user_id', $user->id)
            ->with(['steps.templates.emailTemplate', 'steps.templates.messageTemplate'])
            ->get();

        // Get related schedules for bulk group (if any)
        $relatedSchedules = $schedule->bulk_group_id
            ? ReminderSchedule::query()
                ->where('bulk_group_id', $schedule->bulk_group_id)
                ->with('invoice')
                ->get()
            : collect([$schedule]);

        return view('dashboard.rule-scheduled.edit', [
            'schedule' => $schedule,
            'invoices' => $invoices,
            'rules' => $rules,
            'relatedSchedules' => $relatedSchedules,
        ]);
    }

    /**
     * Update the specified rule-based reminder schedule.
     */
    public function update(UpdateRuleScheduledRequest $request, ReminderSchedule $schedule): RedirectResponse
    {
        Gate::authorize('update', $schedule);

        try {
            $result = $this->updateAction->execute($schedule, $request->validated(), auth()->id());

            $message = $result['created_count'] > 1
                ? 'Rule updated successfully for '.$result['created_count'].' reminder(s).'
                : 'Rule updated successfully.';

            return to_route('schedule-reminders.index')->with('success', $message);
        } catch (InvalidArgumentException $e) {
            return back()
                ->withInput()
                ->with('error', $e->getMessage());
        } catch (Exception $exception) {
            return back()
                ->withInput()
                ->with('error', 'Failed to update reminder schedule: '.$exception->getMessage());
        }
    }
}
