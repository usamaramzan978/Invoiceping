<?php

declare(strict_types=1);

namespace App\Providers;

use App\Models\BillingTransaction;
use App\Models\BusinessProfile;
use App\Models\Client;
use App\Models\Invoice;
use App\Models\MessageTemplates;
use App\Models\ReminderRule;
use App\Models\ReminderSchedule;
use App\Models\Subscription;
use App\Models\SubscriptionInvoice;
use App\Models\WhatsAppProvider;
use App\Policies\BillingTransactionPolicy;
use App\Policies\BusinessProfilePolicy;
use App\Policies\ClientPolicy;
use App\Policies\InvoicePolicy;
use App\Policies\MessageTemplatePolicy;
use App\Policies\ReminderRulePolicy;
use App\Policies\ReminderSchedulePolicy;
use App\Policies\SubscriptionInvoicePolicy;
use App\Policies\SubscriptionPolicy;
use App\Policies\WhatsAppProviderPolicy;
use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;

final class AuthServiceProvider extends ServiceProvider
{
    /**
     * The policy mappings for the application.
     *
     * @var array<class-string, class-string>
     */
    protected $policies = [
        WhatsAppProvider::class => WhatsAppProviderPolicy::class,
        Subscription::class => SubscriptionPolicy::class,
        SubscriptionInvoice::class => SubscriptionInvoicePolicy::class,
        ReminderSchedule::class => ReminderSchedulePolicy::class,
        ReminderRule::class => ReminderRulePolicy::class,
        MessageTemplates::class => MessageTemplatePolicy::class,
        Invoice::class => InvoicePolicy::class,
        Client::class => ClientPolicy::class,
        BusinessProfile::class => BusinessProfilePolicy::class,
        BillingTransaction::class => BillingTransactionPolicy::class,
    ];

    /**
     * Register any authentication / authorization services.
     */
    public function boot(): void
    {
        $this->registerPolicies();
    }
}
