<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\BillingCycle;
use App\Enums\SubscriptionStatus;
use Illuminate\Database\Eloquent\Attributes\Scope;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Date;

final class Subscription extends Model
{
    use HasFactory;
    use HasUuids;
    use SoftDeletes;

    protected $casts = [
        'billing_cycle' => BillingCycle::class,
        'status' => SubscriptionStatus::class,
        'amount' => 'decimal:2',
        'trial_ends_at' => 'datetime',
        'starts_at' => 'datetime',
        'ends_at' => 'datetime',
        'renews_at' => 'datetime',
        'canceled_at' => 'datetime',
    ];

    /**
     * @return BelongsTo<User, Subscription>
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * @return BelongsTo<SubscriptionPlan, Subscription>
     */
    public function plan(): BelongsTo
    {
        return $this->belongsTo(SubscriptionPlan::class, 'plan_id');
    }

    /**
     * @return HasMany<SubscriptionInvoice>
     */
    public function invoices(): HasMany
    {
        return $this->hasMany(SubscriptionInvoice::class);
    }

    /**
     * @return HasMany<BillingTransaction>
     */
    public function transactions(): HasMany
    {
        return $this->hasMany(BillingTransaction::class);
    }

    /**
     * Check if subscription is active or trialing
     */
    public function isActive(): bool
    {
        return $this->status->isActive();
    }

    /**
     * Check if subscription is on trial
     */
    public function onTrial(): bool
    {
        return $this->status === SubscriptionStatus::TRIALING
            && $this->trial_ends_at
            && $this->trial_ends_at->isFuture();
    }

    /**
     * Check if subscription is canceled
     */
    public function isCanceled(): bool
    {
        return $this->status === SubscriptionStatus::CANCELED;
    }

    /**
     * Check if subscription is expired
     */
    public function isExpired(): bool
    {
        return $this->status === SubscriptionStatus::EXPIRED;
    }

    /**
     * Get days until renewal
     */
    public function daysUntilRenewal(): int
    {
        if (! $this->renews_at) {
            return 0;
        }

        return max(0, (int) Date::now()->diffInDays($this->renews_at, false));
    }

    /**
     * Get days remaining in trial
     */
    public function daysRemainingInTrial(): int
    {
        if (! $this->trial_ends_at) {
            return 0;
        }

        return max(0, (int) Date::now()->diffInDays($this->trial_ends_at, false));
    }

    /**
     * Scope to get active subscriptions
     */
    #[Scope]
    protected function active(Builder $query): Builder
    {
        return $query->where('status', SubscriptionStatus::ACTIVE);
    }

    /**
     * Scope to get trialing subscriptions
     */
    #[Scope]
    protected function trialing(Builder $query): Builder
    {
        return $query->where('status', SubscriptionStatus::TRIALING);
    }
}
