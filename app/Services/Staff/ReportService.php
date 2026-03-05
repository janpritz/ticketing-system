<?php

namespace App\Services\Staff;

use App\Models\User;
use App\Models\{TicketRoutingHistory, Ticket};

class ReportService
{
    public function getStaffReportData(int $staffId, int $days): array
    {
        return [
            'performanceMetrics' => $this->getPerformanceMetrics($staffId, $days),
            'overdueTickets'     => $this->getOverdueTickets($staffId, $days),
            'faqAnalysis'        => ['processed_faqs' => 0, 'total_faqs' => 0],
            'weeklyThroughput'   => $this->buildThroughput($staffId, $days),
            'recentForwarders'   => $this->getRecentForwarders($staffId),
            'totalForwardsCount' => TicketRoutingHistory::where('staff_id', $staffId)->count(),
        ];
    }

    protected function getRecentForwarders(int $staffId, int $limit = 10): array
    {
        // 1. Get recent routing history with ticket and category relationships pre-loaded
        $recentRouting = TicketRoutingHistory::where('staff_id', $staffId)
            ->with(['ticket.category', 'ticket.routingHistory'])
            ->orderByDesc('routed_at')
            ->take($limit * 2) // Take a few extra to ensure we find enough with "previous" staff
            ->get();

        $forwarders = [];

        foreach ($recentRouting as $entry) {
            // 2. Find the previous staff member directly from the loaded relationship 
            // instead of a fresh DB query inside the loop.
            $prev = $entry->ticket->routingHistory
                ->where('routed_at', '<', $entry->routed_at)
                ->sortByDesc('routed_at')
                ->first();

            $forwarderName = $prev?->staff?->name ?? $entry->ticket?->staff?->name ?? 'Unknown';

            // 3. Resolve Category Name
            $categoryName = $entry->ticket?->category?->name
                ?? (is_string($entry->ticket?->category) ? $entry->ticket->category : 'Uncategorized');

            $forwarders[] = [
                'name'     => $forwarderName,
                'category' => $categoryName,
            ];

            if (count($forwarders) >= $limit) break;
        }

        return $forwarders;
    }

    private function getPerformanceMetrics($staffId, int $days)
    {
        $start = now()->subDays($days)->startOfDay();

        // Example: tickets resolved by staff within range
        $resolvedTickets = Ticket::where('staff_id', $staffId)
            ->where('status', 'Closed')
            ->where('created_at', '>=', $start)
            ->count();

        $totalTickets = Ticket::where('staff_id', $staffId)
            ->where('created_at', '>=', $start)
            ->count();

        $avgResolutionTime = Ticket::where('staff_id', $staffId)
            ->where('status', 'Closed')
            ->where('created_at', '>=', $start)
            ->selectRaw('AVG(TIMESTAMPDIFF(HOUR, created_at, updated_at)) as avg_time')
            ->first()->avg_time ?? 0;

        return [
            'resolved_tickets' => $resolvedTickets,
            'total_tickets' => $totalTickets,
            'avg_resolution_time' => $avgResolutionTime,
        ];
    }

    private function getOverdueTickets($staffId, int $days)
    {
        // Overdue: tickets not closed, older than 24 hours, and within the selected window
        $start = now()->subDays($days)->startOfDay();

        $overdue = Ticket::where('staff_id', $staffId)
            ->where('status', '!=', 'Closed')
            ->where('created_at', '<', now()->subHours(24))
            ->where('created_at', '>=', $start)
            ->get();

        return $overdue;
    }

    private function buildThroughput(int $staffId, int $days): array
    {
        // Build throughput for the last N days (inclusive), ending today
        $end = \Illuminate\Support\Carbon::now()->endOfDay();
        $start = \Illuminate\Support\Carbon::now()->subDays($days - 1)->startOfDay();

        $rows = \App\Models\Ticket::where('staff_id', $staffId)
            ->whereBetween('date_created', [$start, $end])
            ->selectRaw('DATE(date_created) as d, COUNT(*) as c')
            ->groupBy('d')
            ->orderBy('d')
            ->pluck('c', 'd')
            ->toArray();

        $series = [];
        $labels = [];
        $max = 0;

        $cursor = $start->copy();
        for ($i = 0; $i < $days; $i++) {
            $dayKey = $cursor->toDateString();
            $count = (int)($rows[$dayKey] ?? 0);
            $series[] = $count;
            $labels[] = $cursor->format('M j'); // e.g., Jan 5
            if ($count > $max) {
                $max = $count;
            }
            $cursor->addDay();
        }

        return [
            'series' => $series,
            'labels' => $labels,
            'max' => $max,
        ];
    }
}
