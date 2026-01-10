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

final class ReminderSchedule extends Model
{
    use HasFactory;
    use HasUuids;

    protected $fillable = [
        'invoice_id',
        'reminder_rule_id',
        'reminder_rule_step_id',
        'message_template_id',
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
    ];

    public function groupKey(): string
    {
        return implode('-', [
            $this->bulk_group_id ?? $this->id,
            $this->reminder_rule_step_id ?? 'none',
            $this->message_template_id,
            $this->channel->value,
        ]);
    }

    public function invoice()
    {
        return $this->belongsTo(Invoice::class);
    }

    public function rule()
    {
        return $this->belongsTo(ReminderRule::class, 'reminder_rule_id');
    }

    public function step()
    {
        return $this->belongsTo(ReminderRuleStep::class, 'reminder_rule_step_id');
    }

    public function messageTemplate()
    {
        return $this->belongsTo(MessageTemplates::class);
    }
}
