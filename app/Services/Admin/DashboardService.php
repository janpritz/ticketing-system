<?php

namespace App\Services\Admin;

use App\Models\Ticket;
use App\Models\User;
use App\Models\DocumentChange;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

class DashboardService
{
    /**
     * Get all dashboard data formatted for both View and JSON responses.
     */
    public function getAdminDashboardData(): array
    {
        $cutoff = now()->subMinutes(10)->getTimestamp();
        $startOfWeek = Carbon::now()->startOfWeek();
        $endOfWeek = Carbon::now()->endOfWeek();

        return [
            // KPI metrics
            'openTickets'       => (int) Ticket::where('status', 'Open')->count(),
            'forwardedTickets'  => (int) Ticket::where('status', 'Forwarded')->count(),
            'userCount'         => (int) User::count(),

            // Training Status
            'lastTraining'      => $this->getLastTrainingFormatted(),

            // Staff Logic (Strictly following your session/role query)
            'activeStaffCount'  => $this->getActiveStaffCount($cutoff),
            'activeStaff'       => $this->getActiveStaffList($cutoff),
            'staffContacts'     => $this->getStaffContacts($cutoff),

            // Analytics
            'weekLabels'        => $this->getWeeklyAnalytics($startOfWeek, $endOfWeek)['labels'],
            'weekData'          => $this->getWeeklyAnalytics($startOfWeek, $endOfWeek)['data'],
            'categoryLabels'    => $this->getCategoryAnalytics()['labels'],
            'categoryData'      => $this->getCategoryAnalytics()['data'],
            'topSenders'        => $this->getTopSenders(),

            // Ticket Lists
            'unassignedTickets' => $this->getUnassignedTicketsMapped(),

            // Config/Misc
            'faqUpdaterSecret'  => env('RASA_SECRET'),
            'faqUpdaterUrl'     => env('RASA_SERVER_CHECKER'),
            'users'             => User::orderBy('name')->get(['id', 'name']),
        ];
    }

    private function getLastTrainingFormatted(): string
    {
        $lastTraining = DocumentChange::getLastTrainingTimestamp();
        return $lastTraining ? $lastTraining->format('M j, Y g:i A') : 'Never';
    }

    private function getActiveStaffCount(int $cutoff): int
    {
        return DB::table('sessions')
            ->join('users', 'sessions.user_id', '=', 'users.id')
            ->leftJoin('user_roles', 'users.id', '=', 'user_roles.user_id')
            ->leftJoin('roles', 'user_roles.role_id', '=', 'roles.id')
            ->whereNotNull('sessions.user_id')
            ->where('sessions.last_activity', '>=', $cutoff)
            ->where(function ($qb) {
                $qb->whereNull('roles.name')->orWhere('roles.name', '!=', 'Primary Administrator');
            })
            ->distinct('sessions.user_id')
            ->count('sessions.user_id');
    }

    private function getActiveStaffList(int $cutoff): array
    {
        return DB::table('sessions')
            ->join('users', 'sessions.user_id', '=', 'users.id')
            ->leftJoin('user_roles', 'users.id', '=', 'user_roles.user_id')
            ->leftJoin('roles', 'user_roles.role_id', '=', 'roles.id')
            ->whereNotNull('sessions.user_id')
            ->where('sessions.last_activity', '>=', $cutoff)
            ->where(function ($qb) {
                $qb->whereNull('roles.name')->orWhere('roles.name', '!=', 'Primary Administrator');
            })
            ->groupBy('users.id', 'users.name', 'users.email')
            ->select([
                'users.id',
                'users.name',
                'users.email',
                DB::raw('MAX(sessions.last_activity) as last_activity_ts')
            ])
            ->orderByDesc('last_activity_ts')
            ->get()
            ->map(fn($row) => [
                'id' => (int) $row->id,
                'name' => (string) ($row->name ?? ''),
                'email' => (string) ($row->email ?? ''),
                'last_activity_ts' => (int) ($row->last_activity_ts ?? 0),
            ])
            ->values()
            ->toArray();
    }

    private function getStaffContacts(int $cutoff): array
    {
        return User::leftJoin('user_roles', 'users.id', '=', 'user_roles.user_id')
            ->leftJoin('roles', 'user_roles.role_id', '=', 'roles.id')
            ->where(function ($q) {
                $q->whereNull('roles.name')->orWhere('roles.name', '!=', 'Primary Administrator');
            })
            ->leftJoin('sessions', 'sessions.user_id', '=', 'users.id')
            ->groupBy('users.id', 'users.name', 'users.email')
            ->select([
                'users.id',
                'users.name',
                'users.email',
                DB::raw('MAX(sessions.last_activity) as last_activity_ts')
            ])
            ->orderBy('users.name')
            ->get()
            ->map(function ($row) use ($cutoff) {
                $ts = (int) ($row->last_activity_ts ?? 0);
                return [
                    'id' => (int) $row->id,
                    'name' => (string) ($row->name ?? ''),
                    'email' => (string) ($row->email ?? ''),
                    'last_activity_ts' => $ts,
                    'is_active' => $ts >= $cutoff,
                ];
            })
            ->values()
            ->toArray();
    }

    private function getWeeklyAnalytics(Carbon $start, Carbon $end): array
    {
        $byDay = Ticket::select(DB::raw('DATE(date_created) as d'), DB::raw('COUNT(*) as c'))
            ->whereBetween('date_created', [$start, $end])
            ->groupBy('d')
            ->pluck('c', 'd')
            ->toArray();

        $labels = [];
        $data = [];
        $cursor = $start->copy();

        for ($i = 0; $i < 7; $i++) {
            $labels[] = $cursor->format('D');
            $dateKey = $cursor->toDateString();
            $data[] = (int)($byDay[$dateKey] ?? 0);
            $cursor->addDay();
        }

        return ['labels' => $labels, 'data' => $data];
    }

    private function getCategoryAnalytics(): array
    {
        $rows = Ticket::leftJoin('categories', 'tickets.category_id', '=', 'categories.id')
            ->select(DB::raw("COALESCE(categories.name, 'Uncategorized') as category_name"), DB::raw('COUNT(*) as c'))
            ->groupBy('category_name')
            ->orderByDesc('c')
            ->get();

        return [
            'labels' => $rows->pluck('category_name')->values()->toArray(),
            'data'   => $rows->pluck('c')->map(fn($v) => (int)$v)->values()->toArray(),
        ];
    }

    private function getTopSenders(): array
    {
        return Ticket::select('email', DB::raw('COUNT(*) as c'))
            ->groupBy('email')
            ->orderByDesc('c')
            ->limit(10)
            ->get()
            ->map(fn($row) => [
                'email' => $row->email,
                'count' => (int) $row->c,
            ])
            ->values()
            ->toArray();
    }

    private function getUnassignedTicketsMapped(): array
    {
        $tickets = Ticket::with('staff')
            ->where(function ($query) {
                $query->whereNull('staff_id')->orWhere('staff_id', 1);
            })
            ->where('status', 'Open')
            ->orderByDesc('updated_at')
            ->take(6)
            ->get();

        Log::info('Unassigned tickets count: ' . $tickets->count());

        return $tickets->map(function ($t) {
            return [
                'id'           => (int) $t->id,
                'status'       => (string) $t->status,
                'email'        => (string) ($t->email ?? ''),
                'category'     => (string) (is_object($t->category) ? ($t->category->name ?? '') : ($t->getAttribute('category') ?? '')),
                'date_created' => optional($t->date_created ?? $t->created_at)->format('Y-m-d h:i a'),
                'created_at'   => optional($t->created_at)->format('Y-m-d h:i a'),
                'updated_at'   => optional($t->updated_at)->format('Y-m-d h:i a'),
                'staff'        => $t->staff ? ['name' => (string) $t->staff->name] : null,
            ];
        })->values()->toArray();
    }
}
