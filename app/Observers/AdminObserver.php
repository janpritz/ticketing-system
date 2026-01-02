<?php

namespace App\Observers;

use Illuminate\Support\Facades\Cache;
use App\Models\Ticket;
use App\Models\User;
use App\Models\DocumentChange;

class AdminObserver
{
    /**
     * Cache key for admin dashboard data
     */
    const CACHE_KEY = 'admin_dashboard_data';
    
    /**
     * Cache TTL in every reload
     */
    const CACHE_TTL = 0;

    /**
     * Clear admin dashboard cache when a ticket is created
     */
    public function created(Ticket $ticket)
    {
        $this->clearAdminDashboardCache();
    }

    /**
     * Clear admin dashboard cache when a ticket is updated
     */
    public function updated(Ticket $ticket)
    {
        $this->clearAdminDashboardCache();
    }

    /**
     * Clear admin dashboard cache when a ticket is deleted
     */
    public function deleted(Ticket $ticket)
    {
        $this->clearAdminDashboardCache();
    }

    /**
     * Clear admin dashboard cache when a user is created
     */
    public function userCreated(User $user)
    {
        $this->clearAdminDashboardCache();
    }

    /**
     * Clear admin dashboard cache when a user is updated
     */
    public function userUpdated(User $user)
    {
        $this->clearAdminDashboardCache();
    }

    /**
     * Clear admin dashboard cache when a user is deleted
     */
    public function userDeleted(User $user)
    {
        $this->clearAdminDashboardCache();
    }

    /**
     * Clear admin dashboard cache when document changes occur
     */
    public function documentChanged(DocumentChange $documentChange)
    {
        $this->clearAdminDashboardCache();
    }

    /**
     * Clear the admin dashboard cache
     */
    protected function clearAdminDashboardCache()
    {
        Cache::forget(self::CACHE_KEY);
        \Illuminate\Support\Facades\Log::info('Admin dashboard cache cleared');
    }

    /**
     * Get cached admin dashboard data or generate fresh data
     */
    public static function getCachedAdminDashboardData(callable $dataCallback)
    {
        return Cache::remember(self::CACHE_KEY, self::CACHE_TTL, $dataCallback);
    }
}