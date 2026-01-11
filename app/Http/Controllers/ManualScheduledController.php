<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Actions\ReminderSchedule\CreateManualScheduledAction;
use App\Actions\ReminderSchedule\UpdateManualScheduledAction;
use App\Http\Requests\StoreManualScheduledRequest;
use App\Http\Requests\UpdateManualScheduledRequest;
use App\Models\EmailTemplate;
use App\Models\Invoice;
use App\Models\MessageTemplates;
use App\Models\ReminderSchedule;
use Exception;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;
use InvalidArgumentException;

final class ManualScheduledController extends Controller
{
    public function __construct(
        private readonly CreateManualScheduledAction $createAction,
        private readonly UpdateManualScheduledAction $updateAction
    ) {}

    /**
     * Show the form for creating a new manual reminder schedule.
     */
    public function create(): View
    {
        $user = Auth::user();
        $business = $user->business;

        abort_unless($business, 404, 'Business profile not found');

        $invoices = Invoice::query()
            ->where('business_id', $business->id)
            ->latest()
            ->get();

        $emailTemplates = EmailTemplate::query()
            ->where('user_id', $user->id)
            ->where('is_active', true)
            ->get();

        $messageTemplates = MessageTemplates::query()
            ->where('user_id', $user->id)
            ->where('is_active', true)
            ->get();

        return view('dashboard.manual-scheduled.create', [
            'invoices' => $invoices,
            'emailTemplates' => $emailTemplates,
            'messageTemplates' => $messageTemplates,
        ]);
    }

    /**
     * Store a newly created manual reminder schedule.
     */
    public function store(StoreManualScheduledRequest $request): RedirectResponse
    {
        try {
            $result = $this->createAction->execute($request->validated(), Auth::id());

            $message = $result['created_count'] > 1
                ? $result['created_count'].' reminder(s) scheduled successfully.'
                : 'Reminder scheduled successfully.';

            return to_route('schedule-reminders.index')->with('success', $message);
        } catch (Exception $exception) {
            return back()
                ->withInput()
                ->with('error', 'Failed to schedule reminder: '.$exception->getMessage());
        }
    }

    /**
     * Show the form for editing the specified manual reminder schedule.
     */
    public function edit(ReminderSchedule $schedule): View
    {
        $user = Auth::user();
        $business = $user->business;

        abort_unless($business, 404, 'Business profile not found');

        // Verify schedule ownership and that it's manual
        $schedule->loadMissing('invoice.business');
        abort_unless(
            $schedule->invoice && $schedule->invoice->business_id === $business->id,
            403,
            'You do not have permission to edit this reminder schedule.'
        );

        abort_unless(
            $schedule->source_type->value === 'manual',
            404,
            'This reminder schedule is not manual.'
        );

        $invoices = Invoice::query()
            ->where('business_id', $business->id)
            ->latest()
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

        return view('dashboard.manual-scheduled.edit', [
            'schedule' => $schedule,
            'invoices' => $invoices,
            'emailTemplates' => $emailTemplates,
            'messageTemplates' => $messageTemplates,
            'relatedSchedules' => $relatedSchedules,
        ]);
    }

    /**
     * Update the specified manual reminder schedule.
     */
    public function update(UpdateManualScheduledRequest $request, ReminderSchedule $schedule): RedirectResponse
    {
        try {
            $result = $this->updateAction->execute($schedule, $request->validated(), Auth::id());

            $message = $result['created_count'] > 1
                ? $result['created_count'].' reminder(s) updated successfully.'
                : 'Reminder updated successfully.';

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
