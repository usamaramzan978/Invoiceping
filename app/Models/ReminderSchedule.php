<?php

declare(strict_types=1);

namespace App\Models;

// app/Models/ReminderSchedule.php
use App\Enums\MessageChannel;
use App\Enums\ReminderSourceTypeEnum;
use App\Enums\ReminderStatusEnum;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;

/**
 * @property string $id
 * @property string $invoice_id
 * @property string|null $reminder_rule_id
 * @property string|null $reminder_rule_step_id
 * @property string|null $message_template_id
 * @property int|null $email_template_id
 * @property bool $include_pdf
 * @property string $channel
 * @property Carbon $scheduled_at
 * @property ReminderStatusEnum $status
 * @property ReminderSourceTypeEnum $source_type
 * @property Carbon|null $sent_at
 * @property string|null $failure_reason
 * @property string|null $bulk_group_id
 * @property-read Invoice $invoice
 * @property-read ReminderRule|null $rule
 * @property-read ReminderRuleStep|null $step
 * @property-read MessageTemplates|null $messageTemplate
 * @property-read EmailTemplate|null $emailTemplate
 */
final class ReminderSchedule extends Model
{
    use HasFactory;
    use HasUuids;

    protected $fillable = [
        'invoice_id',
        'reminder_rule_id',
        'reminder_rule_step_id',
        'message_template_id',
        'email_template_id',
        'include_pdf',
        'channel',
        'scheduled_at',
        'status',
        'source_type',
        'sent_at',
        'failure_reason',
        'bulk_group_id',
    ];

    protected $casts = [
        'scheduled_at' => 'datetime',
        'sent_at' => 'datetime',
        'status' => ReminderStatusEnum::class,
        'channel' => MessageChannel::class,
        'source_type' => ReminderSourceTypeEnum::class,
        'include_pdf' => 'boolean',
    ];

    public function groupKey(): string
    {
        $channelValue = $this->channel instanceof MessageChannel ? $this->channel->value : (string) $this->channel;
        $templateId = $channelValue === 'email'
            ? ($this->email_template_id ?? 'none')
            : ($this->message_template_id ?? 'none');

        return implode('-', [
            $this->bulk_group_id ?? $this->id,
            $this->reminder_rule_step_id ?? 'none',
            $templateId,
            $channelValue,
        ]);
    }

    /**
     * Get the invoice that this reminder is for.
     *
     * @return BelongsTo<Invoice, ReminderSchedule>
     */
    public function invoice(): BelongsTo
    {
        return $this->belongsTo(Invoice::class);
    }

    /**
     * Get the reminder rule (if scheduled from a rule).
     *
     * @return BelongsTo<ReminderRule, ReminderSchedule>
     */
    public function rule(): BelongsTo
    {
        return $this->belongsTo(ReminderRule::class, 'reminder_rule_id');
    }

    /**
     * Get the reminder rule step (if scheduled from a rule).
     *
     * @return BelongsTo<ReminderRuleStep, ReminderSchedule>
     */
    public function step(): BelongsTo
    {
        return $this->belongsTo(ReminderRuleStep::class, 'reminder_rule_step_id');
    }

    /**
     * Get the message template (WhatsApp/SMS).
     *
     * @return BelongsTo<MessageTemplates, ReminderSchedule>
     */
    public function messageTemplate(): BelongsTo
    {
        return $this->belongsTo(MessageTemplates::class, 'message_template_id');
    }

    /**
     * Get the email template.
     *
     * @return BelongsTo<EmailTemplate, ReminderSchedule>
     */
    public function emailTemplate(): BelongsTo
    {
        return $this->belongsTo(EmailTemplate::class, 'email_template_id');
    }

    /**
     * Check if this is an email channel.
     */
    public function isEmailChannel(): bool
    {
        return $this->channel === MessageChannel::EMAIL;
    }

    /**
     * Check if this is a WhatsApp channel.
     */
    public function isWhatsAppChannel(): bool
    {
        return $this->channel === MessageChannel::WHATSAPP;
    }

    /**
     * Check if this is an SMS channel.
     */
    public function isSMSChannel(): bool
    {
        return $this->channel === MessageChannel::SMS;
    }
}
