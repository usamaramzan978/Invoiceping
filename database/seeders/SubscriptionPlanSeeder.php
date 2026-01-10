<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\SubscriptionPlan;
use Illuminate\Database\Seeder;

final class SubscriptionPlanSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $plans = [
            [
                'name' => 'Starter',
                'slug' => 'starter',
                'description' => 'Perfect for freelancers and small businesses just getting started with invoicing.',
                'monthly_price' => 9.99,
                'yearly_price' => 99.00,
                'currency' => 'USD',
                'invoice_limit' => 50,
                'client_limit' => 25,
                'whatsapp_enabled' => true,
                'custom_message' => false,
                'email_support' => true,
                'priority_support' => false,
                'api_access' => false,
                'is_active' => true,
                'is_featured' => false,
                'sort_order' => 1,
            ],
            [
                'name' => 'Professional',
                'slug' => 'professional',
                'description' => 'Ideal for growing businesses that need more invoicing power and customization.',
                'monthly_price' => 29.99,
                'yearly_price' => 299.00,
                'currency' => 'USD',
                'invoice_limit' => 200,
                'client_limit' => 100,
                'whatsapp_enabled' => true,
                'custom_message' => true,
                'email_support' => true,
                'priority_support' => false,
                'api_access' => true,
                'is_active' => true,
                'is_featured' => true,
                'sort_order' => 2,
            ],
            [
                'name' => 'Business',
                'slug' => 'business',
                'description' => 'For established businesses requiring unlimited invoicing and premium features.',
                'monthly_price' => 59.99,
                'yearly_price' => 599.00,
                'currency' => 'USD',
                'invoice_limit' => 1000,
                'client_limit' => 500,
                'whatsapp_enabled' => true,
                'custom_message' => true,
                'email_support' => true,
                'priority_support' => true,
                'api_access' => true,
                'is_active' => true,
                'is_featured' => false,
                'sort_order' => 3,
            ],
            [
                'name' => 'Enterprise',
                'slug' => 'enterprise',
                'description' => 'Custom solution for large organizations with unique requirements.',
                'monthly_price' => 149.99,
                'yearly_price' => 1499.00,
                'currency' => 'USD',
                'invoice_limit' => null, // Unlimited
                'client_limit' => null, // Unlimited
                'whatsapp_enabled' => true,
                'custom_message' => true,
                'email_support' => true,
                'priority_support' => true,
                'api_access' => true,
                'is_active' => true,
                'is_featured' => false,
                'sort_order' => 4,
            ],
        ];

        foreach ($plans as $planData) {
            SubscriptionPlan::query()->create($planData);
        }
    }
}
