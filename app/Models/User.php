<?php

declare(strict_types=1);

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

final class User extends Authenticatable
{
    use HasFactory;
    use HasUuids;
    use Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    public function business(): HasOne
    {
        return $this->hasOne(BusinessProfile::class);
    }

    /**
     * @return HasMany<Subscription>
     */
    public function subscriptions(): HasMany
    {
        return $this->hasMany(Subscription::class);
    }

    /**
     * Get active subscription
     *
     * @return HasOne<Subscription>
     */
    public function activeSubscription(): HasOne
    {
        return $this->hasOne(Subscription::class)
            ->whereIn('status', ['active', 'trialing'])
            ->latest();
    }

    /**
     * @return HasMany<BillingTransaction>
     */
    public function billingTransactions(): HasMany
    {
        return $this->hasMany(BillingTransaction::class);
    }

    /**
     * @return HasMany<SubscriptionInvoice>
     */
    public function subscriptionInvoices(): HasMany
    {
        return $this->hasMany(SubscriptionInvoice::class);
    }

    /**
     * @return HasMany<ReminderRule>
     */
    public function reminderRules(): HasMany
    {
        return $this->hasMany(ReminderRule::class);
    }

    /**
     * @return HasMany<EmailTemplate>
     */
    public function emailTemplates(): HasMany
    {
        return $this->hasMany(EmailTemplate::class);
    }

    /**
     * Check if user has an active subscription
     */
    public function hasActiveSubscription(): bool
    {
        return $this->activeSubscription()->exists();
    }

    /**
     * Check if user is subscribed to a specific plan
     */
    public function subscribedToPlan(string $planId): bool
    {
        return $this->activeSubscription()
            ->where('plan_id', $planId)
            ->exists();
    }

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }
}
