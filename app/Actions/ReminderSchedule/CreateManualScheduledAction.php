<?php

declare(strict_types=1);

namespace App\Actions\ReminderSchedule;

use App\Enums\ReminderSourceTypeEnum;
use App\Enums\ReminderStatusEnum;
use App\Models\Invoice;
use App\Models\ReminderSchedule;
use App\Models\User;
use Illuminate\Support\Facades\Date;
use Illuminate\Support\Str;
use InvalidArgumentException;

final readonly class CreateManualScheduledAction
{
    /**
     * Create manual reminder schedules for invoices.
     *
     * @param  array<string, mixed>  $validated
     * @return array<string, mixed> Created schedules count
     *
     * @throws InvalidArgumentException
     */
    public function execute(array $validated, string $userId): array
    {
        $invoiceIds = $validated['invoice_ids'];
        $businessId = $this->getUserBusinessId($userId);
        $bulkGroupId = count($invoiceIds) > 1 ? Str::uuid()->toString() : null;
        $createdCount = 0;

        // Verify all invoices belong to user's business
        $this->verifyInvoiceOwnership($invoiceIds, $businessId);

        foreach ($invoiceIds as $invoiceId) {
            $invoice = Invoice::query()->findOrFail($invoiceId);
            $createdCount += $this->createManualSchedule(
                $invoice,
                $validated,
                $bulkGroupId
            );
        }

        return [
            'created_count' => $createdCount,
            'source_type' => 'manual',
        ];
    }

    /**
     * Create a manual reminder schedule.
     */
    private function createManualSchedule(
        Invoice $invoice,
        array $validated,
        ?string $bulkGroupId
    ): int {
        ReminderSchedule::query()->create([
            'invoice_id' => $invoice->id,
            'source_type' => ReminderSourceTypeEnum::MANUAL,
            'channel' => $validated['channel'],
            'email_template_id' => $validated['channel'] === 'email' ? $validated['email_template_id'] : null,
            'message_template_id' => in_array($validated['channel'], ['whatsapp', 'sms']) ? $validated['message_template_id'] : null,
            'include_pdf' => (bool) ($validated['include_pdf'] ?? false),
            'bulk_group_id' => $bulkGroupId,
            'scheduled_at' => Date::parse($validated['scheduled_at']),
            'status' => ReminderStatusEnum::PENDING,
        ]);

        return 1;
    }

    /**
     * Get user's business ID.
     */
    private function getUserBusinessId(string $userId): ?string
    {
        $user = User::query()->with('business')->findOrFail($userId);

        throw_unless($user->business, InvalidArgumentException::class, 'Business profile not found. Please create a business profile first.');

        return $user->business->id;
    }

    /**
     * Verify that all invoices belong to the user's business.
     */
    private function verifyInvoiceOwnership(array $invoiceIds, ?string $businessId): void
    {
        throw_unless($businessId, InvalidArgumentException::class, 'Business profile not found.');

        $count = Invoice::query()
            ->whereIn('id', $invoiceIds)
            ->where('business_id', $businessId)
            ->count();

        throw_if($count !== count($invoiceIds), InvalidArgumentException::class, 'One or more invoices do not belong to your business.');
    }
}
