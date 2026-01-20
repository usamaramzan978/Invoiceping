<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\SupportTicketPriority;
use App\Enums\SupportTicketStatus;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

final class SupportTicket extends Model
{
    use HasFactory;
    use HasUuids;

    protected $fillable = [
        'business_id',
        'client_id',
        'invoice_id',
        'created_by',
        'subject',
        'message',
        'status',
        'priority',
        'attachment_path',
        'resolved_at',
        'closed_at',
    ];

    protected $casts = [
        'status' => SupportTicketStatus::class,
        'priority' => SupportTicketPriority::class,
        'resolved_at' => 'datetime',
        'closed_at' => 'datetime',
    ];

    public function business(): BelongsTo
    {
        return $this->belongsTo(BusinessProfile::class, 'business_id');
    }

    public function client(): BelongsTo
    {
        return $this->belongsTo(Client::class);
    }

    public function invoice(): BelongsTo
    {
        return $this->belongsTo(Invoice::class);
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * @return HasMany<SupportTicketReply>
     */
    public function replies(): HasMany
    {
        return $this->hasMany(SupportTicketReply::class, 'support_ticket_id');
    }
}

