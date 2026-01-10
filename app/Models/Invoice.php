<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\DiscountType;
use App\Enums\InvoiceStatus;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

final class Invoice extends Model
{
    use HasFactory;
    use HasUuids;

    protected $fillable = [
        'business_id',
        'client_id',
        'invoice_number',
        'issue_date',
        'due_date',
        'total_amount',
        'discount_type',
        'discount_amount',
        'tax_amount',
        'currency',
        'status',
        'public_token',
        'paid_at',
        'sent_at',
        'notes',
        'pdf_path',
    ];

    protected $casts = [
        'issue_date' => 'date',
        'due_date' => 'date',
        'paid_at' => 'datetime',
        'status' => InvoiceStatus::class,
        'discount_type' => DiscountType::class,
        'total_amount' => 'decimal:2',
    ];

    public function business(): BelongsTo
    {
        return $this->belongsTo(BusinessProfile::class, 'business_id');
    }

    public function client(): BelongsTo
    {
        return $this->belongsTo(Client::class);
    }

    public function items(): HasMany
    {
        return $this->hasMany(InvoiceItem::class);
    }

    public function reminderSchedules()
    {
        return $this->hasMany(ReminderSchedule::class);
    }

    /**
     * Check if invoice is overdue.
     * An invoice is overdue if:
     * - Due date has passed
     * - Status is SENT (not DRAFT or PAID)
     */
    public function isOverdue(): bool
    {
        return $this->due_date->isPast()
            && $this->status === InvoiceStatus::SENT
            && ! $this->paid_at;
    }

    /**
     * Check if invoice is paid.
     */
    public function isPaid(): bool
    {
        return $this->status === InvoiceStatus::PAID || $this->paid_at !== null;
    }

    /**
     * Get the computed status.
     * This returns OVERDUE if the invoice meets overdue criteria,
     * otherwise returns the actual database status.
     */
    public function getComputedStatus(): InvoiceStatus
    {
        if ($this->isOverdue()) {
            return InvoiceStatus::OVERDUE;
        }

        return $this->status;
    }

    /**
     * Mark invoice as paid.
     */
    public function markAsPaid(): void
    {
        $this->update([
            'status' => InvoiceStatus::PAID,
            'paid_at' => now(),
        ]);
    }

    /**
     * Mark invoice as sent.
     */
    public function markAsSent(): void
    {
        $this->update([
            'status' => InvoiceStatus::SENT,
            'sent_at' => now(),
        ]);
    }
}
