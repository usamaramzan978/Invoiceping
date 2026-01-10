<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

final class ReminderRuleStep extends Model
{
    use HasFactory;
    use HasUuids;

    protected $fillable = [
        'reminder_rule_id',
        'reminder_type',
        'offset_days',
        'sort_order',
    ];

    public function rule()
    {
        return $this->belongsTo(ReminderRule::class, 'reminder_rule_id');
    }

    public function templates()
    {
        return $this->hasMany(ReminderRuleStepTemplate::class);
    }
}
