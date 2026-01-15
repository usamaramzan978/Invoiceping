<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;

/**
 * @property string $id
 * @property string $reminder_rule_step_id
 * @property string|null $message_template_id
 * @property int|null $email_template_id
 * @property bool $include_pdf
 * @property string $channel
 * @property Carbon $created_at
 * @property Carbon $updated_at
 * @property-read ReminderRuleStep $step
 * @property-read MessageTemplates|null $messageTemplate
 * @property-read EmailTemplate|null $emailTemplate
 */
final class ReminderRuleStepTemplate extends Model
{
    use HasFactory;
    use HasUuids;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'reminder_rule_step_id',
        'message_template_id',
        'email_template_id',
        'include_pdf',
        'channel',
    ];

    protected $casts = [
        'include_pdf' => 'boolean',
    ];

    /**
     * Get the reminder rule step that owns this template.
     *
     * @return BelongsTo<ReminderRuleStep, ReminderRuleStepTemplate>
     */
    public function step(): BelongsTo
    {
        return $this->belongsTo(ReminderRuleStep::class, 'reminder_rule_step_id');
    }

    /**
     * Get the message template (WhatsApp/SMS).
     *
     * @return BelongsTo<MessageTemplates, ReminderRuleStepTemplate>
     */
    public function messageTemplate(): BelongsTo
    {
        return $this->belongsTo(MessageTemplates::class, 'message_template_id');
    }

    /**
     * Get the email template.
     *
     * @return BelongsTo<EmailTemplate, ReminderRuleStepTemplate>
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
        return $this->channel === 'email';
    }

    /**
     * Check if this is a WhatsApp channel.
     */
    public function isWhatsAppChannel(): bool
    {
        return $this->channel === 'whatsapp';
    }

    /**
     * Check if this is an SMS channel.
     */
    public function isSMSChannel(): bool
    {
        return $this->channel === 'sms';
    }
}
