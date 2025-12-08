<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Models\Ticket;
use App\Models\AnalyticsTicketSnapshot;
use Carbon\Carbon;

class ReportsController extends Controller
{
    public function index()
    {
        // Get current open tickets count
        $currentOpenTickets = $this->getCurrentOpenTicketsCount();

        // Get backlog trend data for default 30 days
        $backlogTrendData = $this->getBacklogTrendData(30);

        // Staff Performance and Workload Analysis

        // 2.1 Tickets Assigned (Current Workload)
        $ticketsAssigned = Ticket::whereIn('status', ['Open', 'Forwarded'])
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

        // 2.2 Tickets Solved/Closed in last 30 days
        $thirtyDaysAgo = now()->subDays(30);
        $ticketsSolved = Ticket::whereNotNull('date_closed')
            ->where('date_closed', '>=', $thirtyDaysAgo)
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

        // 2.3 Workload Distribution
        $totalOpenTickets = Ticket::whereIn('status', ['Open', 'Forwarded'])->count();
        $workloadDistribution = [];
        if ($totalOpenTickets > 0) {
            $workloadDistribution = Ticket::whereIn('status', ['Open', 'Forwarded'])
                ->whereNotNull('staff_id')
                ->with('staff')
                ->select('staff_id', DB::raw('COUNT(*) as count'))
                ->groupBy('staff_id')
                ->orderByDesc('count')
                ->get()
                ->map(function ($item) use ($totalOpenTickets) {
                    $percentage = round(($item->count / $totalOpenTickets) * 100, 1);
                    return [
                        'name' => $item->staff ? $item->staff->name : 'Unknown',
                        'count' => (int) $item->count,
                        'percentage' => $percentage,
                    ];
                })
                ->toArray();
        }

        // Calculate average resolution time
        $avgResolutionTime = $this->getAverageResolutionTime();

        // Total tickets this month
        $totalTicketsThisMonth = $this->getTotalTicketsThisMonth();

        // Overdue tickets (open tickets older than 1 day)
        $overdueTickets = $this->getOverdueTicketsCount();

        // Top ticket drivers (last 30 days)
        $topTicketDrivers = $this->getTopTicketDriversForPeriod(30);

        // Tickets by org (last 90 days)
        $ticketsByOrg = $this->getTicketsByOrgForPeriod(90);

        return view('dashboards.admin.reports.index', compact('currentOpenTickets', 'backlogTrendData', 'ticketsAssigned', 'ticketsSolved', 'workloadDistribution', 'avgResolutionTime', 'totalTicketsThisMonth', 'overdueTickets', 'topTicketDrivers', 'ticketsByOrg'));
    }

    public function getBacklogTrendDataAjax(Request $request)
    {
        $days = $request->get('days', 30);
        $data = $this->getBacklogTrendData($days);

        return response()->json($data);
    }

    public function getDynamicDataAjax(Request $request)
    {
        $days = $request->get('days', 30);
        $data = [
            'avgResolutionTime' => $this->getAvgResolutionTimeForPeriod($days),
            'totalTickets' => $this->getTotalTicketsForPeriod($days),
            'ticketsSolved' => $this->getTicketsSolvedForPeriod($days),
            'topTicketDrivers' => $this->getTopTicketDriversForPeriod($days),
            'ticketsByOrg' => $this->getTicketsByOrgForPeriod($days),
        ];
        return response()->json($data);
    }

    private function getCurrentOpenTicketsCount()
    {
        return Ticket::whereIn('status', ['Open', 'Forwarded'])->count();
    }

    private function getBacklogTrendData($days)
    {
        $endDate = Carbon::today();
        $startDate = $endDate->copy()->subDays($days - 1);

        // Get daily counts from snapshots
        $dailyCounts = AnalyticsTicketSnapshot::selectRaw('snapshot_date, COUNT(*) as open_count')
            ->whereIn('status', ['Open', 'Forwarded'])
            ->whereBetween('snapshot_date', [$startDate->toDateString(), $endDate->toDateString()])
            ->groupBy('snapshot_date')
            ->orderBy('snapshot_date')
            ->pluck('open_count', 'snapshot_date')
            ->toArray();

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

    private function getAverageResolutionTime()
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

    private function getTotalTicketsThisMonth()
    {
        $startOfMonth = now()->startOfMonth();
        $endOfMonth = now()->endOfMonth();

        return Ticket::whereBetween('created_at', [$startOfMonth, $endOfMonth])->count();
    }

    private function getOverdueTicketsCount()
    {
        $oneDayAgo = now()->subDay();
        return Ticket::whereIn('status', ['Open', 'Forwarded'])
            ->where('created_at', '<', $oneDayAgo)
            ->count();
    }

    private function getAvgResolutionTimeForPeriod($days)
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

    private function getTotalTicketsForPeriod($days)
    {
        $startDate = now()->subDays($days);
        return Ticket::where('created_at', '>=', $startDate)->count();
    }

    private function getTicketsSolvedForPeriod($days)
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

    private function getTopTicketDriversForPeriod($days)
    {
        $startDate = now()->subDays($days);
        // Assuming category is a field, but in the model, perhaps it's category_id
        // For simplicity, let's assume category is a string field
        // If it's id, need to join
        return Ticket::where('created_at', '>=', $startDate)
            ->select('category', DB::raw('COUNT(*) as count'))
            ->groupBy('category')
            ->orderByDesc('count')
            ->get()
            ->map(function ($item) {
                return [
                    'label' => $item->category ?: 'Unknown',
                    'count' => (int) $item->count,
                ];
            })
            ->toArray();
    }

    private function getTicketsByOrgForPeriod($days)
    {
        $startDate = now()->subDays($days);
        // Group by recepient_id (user who created the ticket)
        return Ticket::where('created_at', '>=', $startDate)
            ->select('recepient_id', DB::raw('COUNT(*) as count'))
            ->groupBy('recepient_id')
            ->orderByDesc('count')
            ->limit(10)
            ->get()
            ->map(function ($item) {
                $user = \App\Models\User::find($item->recepient_id);
                return [
                    'name' => $user ? $user->name : 'Unknown',
                    'count' => (int) $item->count,
                ];
            })
            ->toArray();
    }
}
