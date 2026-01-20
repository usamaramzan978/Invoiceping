<?php

declare(strict_types=1);

use App\Http\Controllers\Admin\AdminSubscriptionController;
use App\Http\Controllers\BillingController;
use App\Http\Controllers\BusinessProfileController;
use App\Http\Controllers\ClientController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\EmailTemplateController;
use App\Http\Controllers\InvoiceController;
use App\Http\Controllers\LogController;
use App\Http\Controllers\ManualScheduledController;
use App\Http\Controllers\MessageTemplateController;
use App\Http\Controllers\ReminderRuleController;
use App\Http\Controllers\ReminderScheduleController;
use App\Http\Controllers\RuleScheduledController;
use App\Http\Controllers\SendInvoiceMessageController;
use App\Http\Controllers\SubscriptionController;
use App\Http\Controllers\SubscriptionInvoiceController;
use App\Http\Controllers\SupportTicketController;
use App\Http\Controllers\SupportTicketReplyController;
use App\Http\Controllers\SubscriptionPlanController;
use App\Http\Controllers\WhatsAppProviderController;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth'])->group(function (): void {

    Route::get('/home', [DashboardController::class, 'index'])->name('home');

    Route::resource('clients', ClientController::class);
    Route::resource('business-profile', BusinessProfileController::class)->only(['index', 'create', 'store', 'show', 'edit', 'update']);
    Route::resource('support-tickets', SupportTicketController::class)->parameters([
        'support-tickets' => 'support_ticket',
    ]);
    Route::post('support-tickets/{support_ticket}/replies', [SupportTicketReplyController::class, 'store'])
        ->name('support-tickets.replies.store');
    //    Invoice Routes
    Route::post('invoices/preview', [InvoiceController::class, 'preview'])->name('invoices.preview');
    Route::get('invoice/{invoice}/download', [InvoiceController::class, 'download'])->name('invoice.download');
    Route::resource('invoices', InvoiceController::class);
    Route::get('message-templates-invoice', [InvoiceController::class, 'allForBusiness'])->name('message-templates-invoice');
    Route::post('invoices/send-message', [SendInvoiceMessageController::class, 'send'])->name('invoices.send-message');

    // Reminder Rules
    Route::patch('reminder-rules/{rule}/toggle-status', [ReminderRuleController::class, 'toggleStatus'])->name('reminder-rules.toggle-status');
    Route::resource('reminder-rules', ReminderRuleController::class)->parameters([
        'reminder-rules' => 'rule',
    ]);

    // Schedule Reminders - All Scheduled (index only, for viewing all)
    Route::post('schedule-reminders/{schedule}/cancel', [ReminderScheduleController::class, 'cancel'])->name('schedule-reminders.cancel');
    Route::post('schedule-reminders/{schedule}/reschedule', [ReminderScheduleController::class, 'reschedule'])->name('schedule-reminders.reschedule');
    Route::get('schedule-reminders', [ReminderScheduleController::class, 'index'])->name('schedule-reminders.index');

    // Rule-Based Scheduled Reminders
    Route::resource('rule-scheduled', RuleScheduledController::class)->only(['create', 'store', 'edit', 'update'])->parameters([
        'rule-scheduled' => 'schedule',
    ]);

    // Manual Scheduled Reminders
    Route::resource('manual-scheduled', ManualScheduledController::class)->only(['create', 'store', 'edit', 'update'])->parameters([
        'manual-scheduled' => 'schedule',
    ]);

    // Route::get('email/templates', [EmailTemplateController::class, 'indexView'])->name('email.templates.index');
    Route::resource('message/templates', MessageTemplateController::class);
    Route::get('email/templates/create', [EmailTemplateController::class, 'create'])->name('email.templates.create');

    // Email Template API Routes
    Route::prefix('api/email-templates')->name('api.email-templates.')->group(function (): void {
        Route::get('/', [EmailTemplateController::class, 'index'])->name('index');
        Route::get('available-variables', [EmailTemplateController::class, 'getAvailableVariables'])->name('available-variables');
        Route::post('/', [EmailTemplateController::class, 'store'])->name('store');
        Route::get('{email_template}', [EmailTemplateController::class, 'show'])->name('show');
        Route::patch('{email_template}', [EmailTemplateController::class, 'update'])->name('update');
        Route::delete('{email_template}', [EmailTemplateController::class, 'destroy'])->name('destroy');
        Route::post('{email_template}/set-default', [EmailTemplateController::class, 'setDefault'])->name('set-default');
        Route::post('{email_template}/toggle-active', [EmailTemplateController::class, 'toggleActive'])->name('toggle-active');
    });

    // ================= SUBSCRIPTION & BILLING ROUTES =================
    Route::prefix('subscriptions')->name('subscriptions.')->group(function (): void {
        // Subscription Plans
        Route::get('plans', [SubscriptionPlanController::class, 'index'])->name('plans.index');

        // User Subscriptions
        Route::get('/', [SubscriptionController::class, 'index'])->name('index');
        Route::post('/', [SubscriptionController::class, 'store'])->name('store');
        Route::patch('{subscription}', [SubscriptionController::class, 'update'])->name('update');
        Route::post('{subscription}/cancel', [SubscriptionController::class, 'cancel'])->name('cancel');
        Route::post('{subscription}/resume', [SubscriptionController::class, 'resume'])->name('resume');
        Route::post('{subscription}/switch-cycle', [SubscriptionController::class, 'switchCycle'])->name('switch-cycle');
    });

    // Billing Routes
    Route::prefix('billing')->name('billing.')->group(function (): void {
        // Billing History
        Route::get('/', [BillingController::class, 'index'])->name('index');
        Route::get('transactions/{transaction}', [BillingController::class, 'show'])->name('transactions.show');

        // Subscription Invoices
        Route::get('invoices', [SubscriptionInvoiceController::class, 'index'])->name('invoices.index');
        Route::get('invoices/{invoice}', [SubscriptionInvoiceController::class, 'show'])->name('invoices.show');
        Route::get('invoices/{invoice}/download', [SubscriptionInvoiceController::class, 'download'])->name('invoices.download');
    });

    // ================= LOGS ROUTES =================
    Route::get('logs', [LogController::class, 'index'])->name('logs.index');

    // ================= WHATSAPP PROVIDER ROUTES =================
    Route::post('whatsapp-providers/{whatsapp_provider}/toggle-status', [WhatsAppProviderController::class, 'toggleStatus'])->name('whatsapp-providers.toggle-status');
    Route::post('whatsapp-providers/{whatsapp_provider}/set-default', [WhatsAppProviderController::class, 'setDefault'])->name('whatsapp-providers.set-default');
    Route::resource('whatsapp-providers', WhatsAppProviderController::class);

    // ================= ADMIN ROUTES =================
    Route::prefix('admin')->name('admin.')->group(function (): void {
        // Admin Subscription Management (Manual CRUD)
        Route::resource('subscriptions', AdminSubscriptionController::class);
        Route::get('support-tickets', [SupportTicketController::class, 'index'])->name('support-tickets.index');
        Route::post('support-tickets/{support_ticket}/replies', [SupportTicketReplyController::class, 'storeAdmin'])
            ->name('support-tickets.replies.store');
    });
});
