<?php

namespace App\Providers;

use App\Events\Security\EmergencyEventCreated;
use App\Listeners\Security\SendEmergencyNotifications;
use App\Services\TenantContext;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Vite;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->singleton(TenantContext::class);
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Vite::prefetch(concurrency: 3);

        Event::listen(
            EmergencyEventCreated::class,
            SendEmergencyNotifications::class,
        );
    }
}
