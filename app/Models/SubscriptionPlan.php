<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Scope;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

final class SubscriptionPlan extends Model
{
    use HasFactory;
    use HasUuids;

    protected $table = 'subscription_plans';

    protected $casts = [
        'monthly_price' => 'decimal:2',
        'yearly_price' => 'decimal:2',
        'invoice_limit' => 'integer',
        'client_limit' => 'integer',
        'whatsapp_enabled' => 'boolean',
        'custom_message' => 'boolean',
        'email_support' => 'boolean',
        'priority_support' => 'boolean',
        'api_access' => 'boolean',
        'is_active' => 'boolean',
        'is_featured' => 'boolean',
        'sort_order' => 'integer',
    ];

    /**
     * @return HasMany<Subscription>
     */
    public function subscriptions(): HasMany
    {
        return $this->hasMany(Subscription::class, 'plan_id');
    }

    /**
     * Get price for a specific billing cycle
     *
     * @return array{amount: string, formatted: string}
     */
    public function getPriceForCycle(string $cycle): array
    {
        $amount = $cycle === 'yearly' ? $this->yearly_price : $this->monthly_price;

        return [
            'amount' => (string) $amount,
            'formatted' => $this->formatPrice($amount),
        ];
    }

    /**
     * Format price with currency
     */
    public function formatPrice(string|float $amount): string
    {
        return '$'.number_format((float) $amount, 2);
    }

    /**
     * Calculate yearly savings percentage
     */
    public function getYearlySavingsPercentage(): int
    {
        $monthlyTotal = $this->monthly_price * 12;
        if ($monthlyTotal <= 0) {
            return 0;
        }

        $savings = $monthlyTotal - $this->yearly_price;

        return (int) round(($savings / $monthlyTotal) * 100);
    }

    /**
     * Scope to get only active plans
     */
    #[Scope]
    protected function active(Builder $query): Builder
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope to get featured plans
     */
    #[Scope]
    protected function featured(Builder $query): Builder
    {
        return $query->where('is_featured', true);
    }
}
