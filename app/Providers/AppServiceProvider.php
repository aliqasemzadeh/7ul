<?php

namespace App\Providers;

use App\Settings\AuthSettings;
use App\Settings\SiteSettings;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
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
        Gate::before(function ($user, $ability) {
            return $user->hasRole('admin') ? true : null;
        });

        View::composer('*', function ($view): void {
            if (! $view->offsetExists('siteSettings')) {
                $view->with('siteSettings', app(SiteSettings::class));
            }

            if (! $view->offsetExists('authSettings')) {
                $view->with('authSettings', app(AuthSettings::class));
            }
        });
    }
}
