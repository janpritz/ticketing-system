<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Facades\Event;
use App\Observers\AdminObserver;
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

        // Register AdminObserver for cache busting
        Ticket::observe(AdminObserver::class);
        User::observe(AdminObserver::class);
        DocumentChange::observe(AdminObserver::class);

        // Register event listeners for user events
        Event::listen(
            'eloquent.created: App\Models\User',
            [AdminObserver::class, 'userCreated']
        );
        Event::listen(
            'eloquent.updated: App\Models\User',
            [AdminObserver::class, 'userUpdated']
        );
        Event::listen(
            'eloquent.deleted: App\Models\User',
            [AdminObserver::class, 'userDeleted']
        );

    }
}
