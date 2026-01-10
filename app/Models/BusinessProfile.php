<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

final class BusinessProfile extends Model
{
    use HasFactory;
    use HasUuids;

    protected $fillable = [
        'user_id',
        'business_name',
        'tax_id',
        'whatsapp_number',
        'email',
        'address',
        'image',
        'currency',
        'timezone',
        'default_reminder_rule_id',
        'auto_apply_reminders',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function invoices(): HasMany
    {
        return $this->hasMany(Invoice::class, 'business_id');
    }

    public function clients(): HasMany
    {
        return $this->hasMany(Client::class, 'business_id');
    }
}
