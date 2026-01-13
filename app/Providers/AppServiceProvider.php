<?php

declare(strict_types=1);

namespace App\Providers;

use App\Models\WhatsAppProvider;
use App\Policies\WhatsAppProviderPolicy;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Foundation\Support\Providers\AuthServiceProvider;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\ServiceProvider;

final class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        /** @var Application $this->app */
        DB::prohibitDestructiveCommands($this->app->isProduction());
        Model::shouldBeStrict(! $this->app->isProduction());

        Paginator::useBootstrapFive();
    }
}
