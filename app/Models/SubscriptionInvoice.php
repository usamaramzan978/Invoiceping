<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\SubscriptionInvoiceStatus;
use Illuminate\Database\Eloquent\Attributes\Scope;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

final class SubscriptionInvoice extends Model
{
    use HasFactory;
    use HasUuids;

    protected $casts = [
        'status' => SubscriptionInvoiceStatus::class,
        'subtotal' => 'decimal:2',
        'tax' => 'decimal:2',
        'discount' => 'decimal:2',
        'total' => 'decimal:2',
        'period_start' => 'date',
        'period_end' => 'date',
        'due_date' => 'date',
        'paid_at' => 'datetime',
    ];

    /**
     * @return BelongsTo<User, SubscriptionInvoice>
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * @return BelongsTo<Subscription, SubscriptionInvoice>
     */
    public function subscription(): BelongsTo
    {
        return $this->belongsTo(Subscription::class);
    }

    /**
     * Check if invoice is paid
     */
    public function isPaid(): bool
    {
        return $this->status === SubscriptionInvoiceStatus::PAID;
    }

    /**
     * Check if invoice is overdue
     */
    public function isOverdue(): bool
    {
        return $this->status === SubscriptionInvoiceStatus::PENDING
            && $this->due_date->isPast();
    }

    /**
     * Format amount with currency
     */
    public function formattedTotal(): string
    {
        return '$'.number_format((float) $this->total, 2);
    }

    /**
     * Get download URL for PDF
     */
    public function downloadUrl(): string
    {
        return route('billing.invoices.download', $this->id);
    }

    /**
     * Scope to get paid invoices
     */
    #[Scope]
    protected function paid(Builder $query): Builder
    {
        return $query->where('status', SubscriptionInvoiceStatus::PAID);
    }

    /**
     * Scope to get pending invoices
     */
    #[Scope]
    protected function pending(Builder $query): Builder
    {
        return $query->where('status', SubscriptionInvoiceStatus::PENDING);
    }

    /**
     * Scope to get overdue invoices
     */
    #[Scope]
    protected function overdue(Builder $query): Builder
    {
        return $query->where('status', SubscriptionInvoiceStatus::PENDING)
            ->where('due_date', '<', now());
    }
}
