<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

final class ReminderRuleStepTemplate extends Model
{
    use HasFactory;
    use HasUuids;

    protected $fillable = [
        'reminder_rule_step_id',
        'message_template_id',
        'channel',
    ];

    public function step()
    {
        return $this->belongsTo(ReminderRuleStep::class, 'reminder_rule_step_id');
    }

    public function messageTemplate()
    {
        return $this->belongsTo(MessageTemplates::class);
    }
}
