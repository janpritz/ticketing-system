<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Facades\Event;
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

    }
}
