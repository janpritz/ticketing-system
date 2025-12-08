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
            ->with('staff')
            ->select('staff_id', DB::raw('COUNT(*) as count'))
            ->groupBy('staff_id')
            ->having('staff_id', '!=', null)
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
            ->with('staff')
            ->select('staff_id', DB::raw('COUNT(*) as count'))
            ->groupBy('staff_id')
            ->having('staff_id', '!=', null)
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
                ->with('staff')
                ->select('staff_id', DB::raw('COUNT(*) as count'))
                ->groupBy('staff_id')
                ->having('staff_id', '!=', null)
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

        return view('dashboards.admin.reports.index', compact('currentOpenTickets', 'backlogTrendData', 'ticketsAssigned', 'ticketsSolved', 'workloadDistribution', 'avgResolutionTime', 'totalTicketsThisMonth', 'overdueTickets'));
    }

    public function getBacklogTrendDataAjax(Request $request)
    {
        $days = $request->get('days', 30);
        $data = $this->getBacklogTrendData($days);

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
}
