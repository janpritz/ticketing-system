<?php

namespace App\Services\Admin;

use App\Models\AnalyticsTicketSnapshot;
use App\Models\{Ticket, TicketRoutingHistory, User};
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class ReportService
{
    /**
     * Gather all statistics required for the Admin Reports Dashboard.
     */
    public function getAdminDashboardStats(): array
    {
        $thirtyDaysAgo = now()->subDays(30);

        return [
            'currentOpenTickets'     => $this->getCurrentOpenTicketsCount(),
            'closedTicketsTrendData' => $this->getClosedTicketsTrendData(30),
            'ticketsAssigned'        => $this->getStaffWorkload(),
            'ticketsSolved'          => $this->getStaffPerformance($thirtyDaysAgo),
            'workloadDistribution'   => $this->getWorkloadDistribution(),
            'avgResolutionTime'      => $this->getAverageResolutionTime(),
            'totalTicketsThisMonth'  => $this->getTotalTicketsThisMonth(),
            'overdueTickets'         => $this->getOverdueTicketsCount(),
            'topTicketDrivers'       => $this->getTopTicketDriversForPeriod(30),
            'ticketsByOrg'           => $this->getTicketsByOrgForPeriod(90),
        ];
    }

    /**
     * Analysis of Resolved/Closed tickets per staff member for a specific period.
     */
    public function getStaffPerformance(\DateTimeInterface $startDate): array
    {
        return Ticket::whereNotNull('date_closed')
            ->where('date_closed', '>=', $startDate)
            ->whereNotNull('staff_id')
            ->with('staff:id,name')
            ->select('staff_id', DB::raw('COUNT(*) as count'))
            ->groupBy('staff_id')
            ->orderByDesc('count')
            ->get()
            ->map(fn($item) => [
                'name'  => $item->staff->name ?? 'Unknown',
                'count' => (int) $item->count,
            ])->toArray();
    }

    /**
     * Analysis of Current Workload (Open/Forwarded tickets per staff)
     */
    public function getStaffWorkload(): array
    {
        return Ticket::whereIn('status', ['Open', 'Forwarded'])
            ->whereNotNull('staff_id')
            ->with('staff:id,name')
            ->select('staff_id', DB::raw('COUNT(*) as count'))
            ->groupBy('staff_id')
            ->orderByDesc('count')
            ->get()
            ->map(fn($item) => [
                'name'  => $item->staff->name ?? 'Unknown',
                'count' => (int) $item->count,
            ])->toArray();
    }

    /**
     * Workload Distribution Percentage
     */
    public function getWorkloadDistribution(): array
    {
        $totalOpen = Ticket::whereIn('status', ['Open', 'Forwarded'])->count();

        if ($totalOpen === 0) return [];

        return collect($this->getStaffWorkload())->map(function ($item) use ($totalOpen) {
            $item['percentage'] = round(($item['count'] / $totalOpen) * 100, 1);
            return $item;
        })->toArray();
    }

    protected function getOverdueTicketsCount()
    {
        $oneDayAgo = now()->subDay();
        return Ticket::whereIn('status', ['Open', 'Forwarded'])
            ->where('created_at', '<', $oneDayAgo)
            ->count();
    }

    public function getAvgResolutionTimeForPeriod($days)
    {
        $startDate = now()->subDays($days);
        $closedTickets = Ticket::whereNotNull('date_closed')
            ->where('date_closed', '>=', $startDate)
            ->whereNotNull('created_at')
            ->get(['created_at', 'date_closed']);

        if ($closedTickets->isEmpty()) {
            return 'N/A';
        }

        $totalHours = 0;
        $count = 0;
        foreach ($closedTickets as $ticket) {
            $created = Carbon::parse($ticket->created_at);
            $closed = Carbon::parse($ticket->date_closed);
            if ($closed->greaterThan($created)) {
                $hours = $created->diffInHours($closed);
                $totalHours += $hours;
                $count++;
            }
        }

        if ($count === 0) {
            return 'N/A';
        }

        $avgHours = $totalHours / $count;
        if ($avgHours >= 24) {
            $days = round($avgHours / 24, 1);
            return $days . 'd';
        } else {
            return round($avgHours, 1) . 'h';
        }
    }

    public function getTotalTicketsForPeriod($days)
    {
        $startDate = now()->subDays($days);
        return Ticket::where('created_at', '>=', $startDate)->count();
    }

    public function getTicketsSolvedForPeriod($days)
    {
        $startDate = now()->subDays($days);
        return Ticket::whereNotNull('date_closed')
            ->where('date_closed', '>=', $startDate)
            ->whereNotNull('staff_id')
            ->with('staff')
            ->select('staff_id', DB::raw('COUNT(*) as count'))
            ->groupBy('staff_id')
            ->orderByDesc('count')
            ->get()
            ->map(function ($item) {
                return [
                    'name' => $item->staff ? $item->staff->name : 'Unknown',
                    'count' => (int) $item->count,
                ];
            })
            ->toArray();
    }

    public function getTopTicketDriversForPeriod($days)
    {
        $startDate = now()->subDays($days);
        // Group by the roles table (use role_id as source of truth)
        $rows = Ticket::where('tickets.created_at', '>=', $startDate)
            ->leftJoin('roles', 'tickets.role_id', '=', 'roles.id')
            ->select(DB::raw("COALESCE(roles.name, 'Unknown') as role_name"), DB::raw('COUNT(*) as count'))
            ->groupBy('roles.name')
            ->orderByDesc('count')
            ->get()
            ->map(function ($item) {
                return [
                    'label' => $item->role_name ?: 'Unknown',
                    'count' => (int) $item->count,
                ];
            })
            ->toArray();

        return $rows;
    }

    public function getTicketsByOrgForPeriod($days)
    {
        $startDate = now()->subDays($days);

        // Only consider tickets that currently have status 'Forwarded'
        $ticketIds = Ticket::where('status', 'Forwarded')->pluck('id')->toArray();
        if (empty($ticketIds)) return [];

        // Fetch routing history for these tickets and group by ticket_id
        $histories = TicketRoutingHistory::whereIn('ticket_id', $ticketIds)
            ->orderBy('ticket_id')
            ->orderBy('routed_at')
            ->get();

        $groups = [];
        foreach ($histories as $h) {
            $groups[$h->ticket_id][] = $h;
        }

        $forwardCounts = [];
        foreach ($groups as $ticketId => $rows) {
            if (count($rows) < 2) continue;
            $first = $rows[0];
            $second = $rows[1];
            // Only count the forward if the forwarding (second routed_at) occurred within the period
            if (Carbon::parse($second->routed_at)->lt($startDate)) continue;
            if ($first->staff_id) {
                $fid = $first->staff_id;
                if (!isset($forwardCounts[$fid])) $forwardCounts[$fid] = 0;
                $forwardCounts[$fid]++;
            }
        }

        if (empty($forwardCounts)) return [];

        arsort($forwardCounts);
        $top = array_slice($forwardCounts, 0, 10, true);
        $users = User::whereIn('id', array_keys($top))->get()->keyBy('id');

        $rowsOut = [];
        foreach ($top as $staffId => $count) {
            $user = $users->get($staffId);
            $rowsOut[] = [
                'id' => $staffId,
                'name' => $user ? $user->name : 'Unknown',
                'count' => (int)$count,
            ];
        }
        return $rowsOut;
    }

    protected function getTotalTicketsThisMonth()
    {
        $startOfMonth = now()->startOfMonth();
        $endOfMonth = now()->endOfMonth();

        return Ticket::whereBetween('created_at', [$startOfMonth, $endOfMonth])->count();
    }

    protected function getAverageResolutionTime()
    {
        $closedTickets = Ticket::whereNotNull('date_closed')
            ->whereNotNull('created_at')
            ->get(['created_at', 'date_closed']);

        if ($closedTickets->isEmpty()) {
            return 'N/A';
        }

        $totalHours = 0;
        $count = 0;

        foreach ($closedTickets as $ticket) {
            $created = Carbon::parse($ticket->created_at);
            $closed = Carbon::parse($ticket->date_closed);

            if ($closed->greaterThan($created)) {
                $hours = $created->diffInHours($closed);
                $totalHours += $hours;
                $count++;
            }
        }

        if ($count === 0) {
            return 'N/A';
        }

        $avgHours = $totalHours / $count;

        // Format as days or hours
        if ($avgHours >= 24) {
            $days = round($avgHours / 24, 1);
            return $days . 'd';
        } else {
            return round($avgHours, 1) . 'h';
        }
    }

    public function getClosedTicketsTrendData($days)
    {
        $days = (int) $days;
        if ($days <= 0) $days = 30;

        $endDate = Carbon::today();
        $startDate = $endDate->copy()->subDays($days - 1);

        // Aggregate by the date portion of date_closed.
        // NOTE: DATE() is supported by MySQL/MariaDB; if you ever switch DB engines,
        // this may need an adapter.
        $dailyClosed = Ticket::query()
            ->whereNotNull('date_closed')
            ->whereBetween('date_closed', [$startDate->copy()->startOfDay(), $endDate->copy()->endOfDay()])
            ->selectRaw('DATE(date_closed) as closed_date, COUNT(*) as closed_count')
            ->groupBy('closed_date')
            ->orderBy('closed_date')
            ->get()
            ->mapWithKeys(function ($row) {
                return [(string) $row->closed_date => (int) $row->closed_count];
            })
            ->toArray();

        $labels = [];
        $data = [];
        for ($date = $startDate->copy(); $date <= $endDate; $date->addDay()) {
            $dateStr = $date->toDateString();
            $labels[] = $date->format('M j');
            $data[] = $dailyClosed[$dateStr] ?? 0;
        }

        return [
            'labels' => $labels,
            'data' => $data,
        ];
    }

    protected function getCurrentOpenTicketsCount()
    {
        return Ticket::whereIn('status', ['Open', 'Forwarded'])->count();
    }

    public function getBacklogTrendData($days)
    {
        $endDate = Carbon::today();
        $startDate = $endDate->copy()->subDays($days - 1);

        // Get daily counts from snapshots. Use explicit mapping to Y-m-d keys to avoid
        // mismatches between date formats (Carbon vs string) when indexing the result.
        $dailyCounts = AnalyticsTicketSnapshot::selectRaw('snapshot_date, COUNT(*) as open_count')
            ->whereIn('status', ['Open', 'Forwarded'])
            ->whereBetween('snapshot_date', [$startDate->toDateString(), $endDate->toDateString()])
            ->groupBy('snapshot_date')
            ->orderBy('snapshot_date')
            ->get()
            ->mapWithKeys(function ($row) {
                // snapshot_date is cast to date on the model, ensure Y-m-d string key
                $key = $row->snapshot_date instanceof Carbon ? $row->snapshot_date->toDateString() : (string)$row->snapshot_date;
                return [$key => (int)$row->open_count];
            })
            ->toArray();

        // If there are no snapshot rows (or all counts are zero), fall back to
        // calculating open ticket counts from the tickets table so the chart
        // still shows meaningful data.
        if (empty($dailyCounts) || array_sum($dailyCounts) === 0) {
            $fallback = [];
            for ($date = $startDate->copy(); $date <= $endDate; $date->addDay()) {
                $endOfDay = $date->copy()->endOfDay();
                $count = Ticket::where('created_at', '<=', $endOfDay)
                    ->where(function ($q) use ($endOfDay) {
                        $q->whereNull('date_closed')
                            ->orWhere('date_closed', '>', $endOfDay);
                    })
                    ->count();
                $fallback[$date->toDateString()] = (int) $count;
            }
            $dailyCounts = $fallback;
        }

        // Fill missing dates with 0
        $labels = [];
        $data = [];

        for ($date = $startDate->copy(); $date <= $endDate; $date->addDay()) {
            $dateStr = $date->toDateString();
            $labels[] = $date->format('M j');
            $data[] = $dailyCounts[$dateStr] ?? 0;
        }

        return [
            'labels' => $labels,
            'data' => $data,
        ];
    }

    /**
     * Analyzes TicketRoutingHistory to see who a specific staff member forwards to.
     */
    public function getForwardingReportForStaff(int $staffId, int $days): array
    {
        $startDate = now()->subDays($days);

        // 1. Get IDs of tickets currently in Forwarded status
        $ticketIds = Ticket::where('status', 'Forwarded')->pluck('id')->toArray();

        if (empty($ticketIds)) {
            return ['forwarder' => null, 'recipients' => []];
        }

        // 2. Fetch routing history for these tickets
        $histories = TicketRoutingHistory::whereIn('ticket_id', $ticketIds)
            ->orderBy('ticket_id')
            ->orderBy('routed_at')
            ->get()
            ->groupBy('ticket_id');

        $recipients = [];
        $allInvolvedTicketIds = [];

        // 3. Identify the "First" (forwarder) and "Second" (recipient) in the chain
        foreach ($histories as $ticketId => $rows) {
            if ($rows->count() < 2) continue;

            $first = $rows[0];
            $second = $rows[1];

            // Filter by date and staff identity
            if (Carbon::parse($second->routed_at)->lt($startDate)) continue;
            if ($first->staff_id !== $staffId) continue;

            $rid = $second->staff_id;
            $recipients[$rid]['count'] = ($recipients[$rid]['count'] ?? 0) + 1;
            $recipients[$rid]['tickets'][] = $ticketId;
            $allInvolvedTicketIds[] = $ticketId;
        }

        if (empty($recipients)) {
            return ['forwarder' => null, 'recipients' => []];
        }

        // 4. Batch fetch Users and Ticket Questions to avoid N+1 queries
        $users = User::whereIn('id', array_keys($recipients))->get()->keyBy('id');
        $ticketMap = Ticket::whereIn('id', array_unique($allInvolvedTicketIds))
            ->get(['id', 'question'])
            ->keyBy('id');

        // 5. Build final response
        $formattedRecipients = [];
        foreach ($recipients as $rid => $meta) {
            $user = $users->get($rid);
            $formattedRecipients[] = [
                'id'      => $rid,
                'name'    => $user->name ?? 'Unknown',
                'count'   => (int)$meta['count'],
                'tickets' => collect($meta['tickets'])->map(fn($tid) => [
                    'id'       => $tid,
                    'question' => $ticketMap->get($tid)->question ?? ''
                ])->toArray(),
            ];
        }

        $forwarder = User::find($staffId);

        return [
            'forwarder'  => $forwarder->name ?? 'Unknown',
            'recipients' => $formattedRecipients
        ];
    }
}
