<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Http\Request;
use Illuminate\Cache\RateLimiting\Limit;
use App\Models\Ticket;
use App\Models\User;
use App\Models\DocumentChange;

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
        // Register NoCaptcha service provider for reCAPTCHA
        $this->app->register(\Anhskohbo\NoCaptcha\NoCaptchaServiceProvider::class);

        // Configure rate limiters
        $this->configureRateLimiting();
    }

    /**
     * Configure rate limiters for the application.
     */
    protected function configureRateLimiting(): void
    {
        // User-specific rate limiter for Sanctum throttle middleware
        RateLimiter::for('App\\Models\\User::10:1', function (Request $request) {
            $user = $request->user();
            if ($user) {
                return Limit::perMinute(10)->by('user:' . $user->id);
            }
            return Limit::perMinute(10)->by('guest');
        });
    }
}
