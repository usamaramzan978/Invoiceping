<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\TransactionStatus;
use App\Enums\TransactionType;
use Illuminate\Database\Eloquent\Attributes\Scope;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

final class BillingTransaction extends Model
{
    use HasFactory;
    use HasUuids;

    protected $casts = [
        'type' => TransactionType::class,
        'status' => TransactionStatus::class,
        'amount' => 'decimal:2',
        'metadata' => 'array',
        'processed_at' => 'datetime',
    ];

    /**
     * @return BelongsTo<User, BillingTransaction>
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * @return BelongsTo<Subscription, BillingTransaction>
     */
    public function subscription(): BelongsTo
    {
        return $this->belongsTo(Subscription::class);
    }

    /**
     * Check if transaction is completed
     */
    public function isCompleted(): bool
    {
        return $this->status === TransactionStatus::COMPLETED;
    }

    /**
     * Check if transaction is pending
     */
    public function isPending(): bool
    {
        return $this->status === TransactionStatus::PENDING;
    }

    /**
     * Format amount with currency
     */
    public function formattedAmount(): string
    {
        return '$'.number_format((float) $this->amount, 2);
    }

    /**
     * Scope to get completed transactions
     */
    #[Scope]
    protected function completed(Builder $query): Builder
    {
        return $query->where('status', TransactionStatus::COMPLETED);
    }

    /**
     * Scope to get payments
     */
    #[Scope]
    protected function payments(Builder $query): Builder
    {
        return $query->where('type', TransactionType::PAYMENT);
    }

    /**
     * Scope to get refunds
     */
    #[Scope]
    protected function refunds(Builder $query): Builder
    {
        return $query->where('type', TransactionType::REFUND);
    }
}
