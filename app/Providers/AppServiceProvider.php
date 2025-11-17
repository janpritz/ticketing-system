<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Facades\Event;
use App\Models\Faq;
use App\Observers\FaqObserver;
use App\Events\FaqCreated;
use App\Events\FaqUpdated;
use App\Events\FaqDeleted;
use App\Events\FaqEnabled;
use App\Events\FaqDisabled;
use App\Listeners\QueueFaqSync;

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
        // Register model observers
        Faq::observe(FaqObserver::class);

        // Register NoCaptcha service provider for reCAPTCHA
        $this->app->register(\Anhskohbo\NoCaptcha\NoCaptchaServiceProvider::class);

        // Register FAQ sync event listeners
        Event::listen(FaqCreated::class, QueueFaqSync::class);
        Event::listen(FaqUpdated::class, QueueFaqSync::class);
        Event::listen(FaqDeleted::class, QueueFaqSync::class);
        Event::listen(FaqEnabled::class, QueueFaqSync::class);
        Event::listen(FaqDisabled::class, QueueFaqSync::class);
    }
}
