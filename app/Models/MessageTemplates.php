<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

final class MessageTemplates extends Model
{
    use HasFactory;
    use HasUuids;

    protected $fillable = [
        'business_id',
        'name',
        'channel',
        'content',
        'is_default',
        'is_active',
    ];

    public function business(): BelongsTo
    {
        return $this->belongsTo(BusinessProfile::class, 'business_id');
    }

    public function reminderSchedules()
    {
        return $this->hasMany(ReminderSchedule::class);
    }
}
