<?php

namespace App\Providers;

use App\Models\Booking;
use App\Models\Facility;
use App\Models\MaintenanceOrder;
use App\Models\Resource;
use App\Observers\AuditObserver;
use Illuminate\Auth\Notifications\ResetPassword;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->bind(\App\Strategies\SectorStrategyInterface::class, function ($app) {
            if ($app->bound(\App\Models\Tenant::class)) {
                $tenant = $app->make(\App\Models\Tenant::class);
                return match ($tenant->sector ?? 'university') {
                    'healthcare' => new \App\Strategies\HealthcareStrategy(),
                    'corporate'  => new \App\Strategies\CorporateStrategy(),
                    'government' => new \App\Strategies\GovernmentStrategy(),
                    default      => new \App\Strategies\UniversityStrategy(),
                };
            }
            return new \App\Strategies\UniversityStrategy();
        });
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        ResetPassword::createUrlUsing(function (object $notifiable, string $token) {
            return config('app.frontend_url')."/password-reset/$token?email={$notifiable->getEmailForPasswordReset()}";
        });

        // Register global audit observers
        Booking::observe(AuditObserver::class);
        Resource::observe(AuditObserver::class);
        Facility::observe(AuditObserver::class);
        MaintenanceOrder::observe(AuditObserver::class);
    }
}
