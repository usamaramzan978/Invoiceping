<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

final class Client extends Model
{
    use HasFactory;
    use HasUuids;

    protected $fillable = [
        'business_id',
        'name',
        'whatsapp_number',
        'email',
        'status',
    ];

    public function business(): BelongsTo
    {
        return $this->belongsTo(BusinessProfile::class, 'business_id');
    }

    public function invoices(): HasMany
    {
        return $this->hasMany(Invoice::class);
    }
}
