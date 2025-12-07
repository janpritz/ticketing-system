<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
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

        return view('dashboards.admin.reports.index', compact('currentOpenTickets', 'backlogTrendData'));
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
}
