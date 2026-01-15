<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * @property string $id
 * @property string $reminder_rule_id
 * @property string $reminder_type
 * @property int $offset_days
 * @property int $sort_order
 * @property-read ReminderRule $rule
 * @property-read HasMany<ReminderRuleStepTemplate> $templates
 */
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

    /**
     * @return BelongsTo<ReminderRule, ReminderRuleStep>
     */
    public function rule(): BelongsTo
    {
        return $this->belongsTo(ReminderRule::class, 'reminder_rule_id');
    }

    /**
     * @return HasMany<ReminderRuleStepTemplate>
     */
    public function templates(): HasMany
    {
        return $this->hasMany(ReminderRuleStepTemplate::class);
    }
}
