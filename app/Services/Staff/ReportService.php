<?php

namespace App\Services\Staff;

use App\Models\User;
use App\Models\{TicketRoutingHistory, Ticket};
use Illuminate\Support\Facades\Log;
use Exception;

class ReportService
{
    public function getStaffReportData(int $staffId, int $days): array
    {
        Log::info("ReportService::getStaffReportData - Fetching report data for staff_id: $staffId, days: $days");

        try {
            return [
                'performanceMetrics' => $this->getPerformanceMetrics($staffId, $days),
                'overdueTickets'     => $this->getOverdueTickets($staffId, $days),
                'faqAnalysis'        => ['processed_faqs' => 0, 'total_faqs' => 0],
                'weeklyThroughput'   => $this->buildThroughput($staffId, $days),
                'recentForwarders'   => $this->getRecentForwarders($staffId),
                'totalForwardsCount' => TicketRoutingHistory::where('staff_id', $staffId)->count(),
            ];
        } catch (Exception $e) {
            Log::error("ReportService::getStaffReportData failed: " . $e->getMessage(), [
                'staff_id' => $staffId,
                'exception' => $e
            ]);

            // Return a safe "Empty State" structure so the frontend doesn't break
            return [
                'performanceMetrics' => ['resolved_tickets' => 0, 'total_tickets' => 0, 'avg_resolution_time' => 0],
                'overdueTickets'     => [],
                'faqAnalysis'        => ['processed_faqs' => 0, 'total_faqs' => 0],
                'weeklyThroughput'   => ['series' => [], 'labels' => [], 'max' => 0],
                'recentForwarders'   => [],
                'totalForwardsCount' => 0,
                'error'              => 'Could not load some report data.'
            ];
        }
    }

    protected function getRecentForwarders(int $staffId, int $limit = 10): array
    {
        try {
            $recentRouting = TicketRoutingHistory::where('staff_id', $staffId)
                ->with(['ticket.role', 'ticket.routingHistories'])
                ->orderByDesc('routed_at')
                ->take($limit * 2)
                ->get();

            $forwarders = [];

            foreach ($recentRouting as $entry) {
                $prev = $entry->ticket->routingHistories
                    ->where('routed_at', '<', $entry->routed_at)
                    ->sortByDesc('routed_at')
                    ->first();

                $forwarderName = $prev?->staff?->name ?? $entry->ticket?->staff?->name ?? 'Unknown';
                $roleName = $entry->ticket?->role?->name ?? 'Uncategorized';

                $forwarders[] = [
                    'name'     => $forwarderName,
                    'category' => $roleName,
                ];

                if (count($forwarders) >= $limit) break;
            }

            return $forwarders;
        } catch (Exception $e) {
            Log::warning("Error fetching recent forwarders: " . $e->getMessage());
            return [];
        }
    }

    private function getPerformanceMetrics($staffId, int $days)
    {
        try {
            $start = now()->subDays($days)->startOfDay();

            $resolvedTickets = Ticket::where('staff_id', $staffId)
                ->where('status', 'Closed')
                ->where('updated_at', '>=', $start)
                ->count();

            $totalTickets = Ticket::where('staff_id', $staffId)
                ->where('updated_at', '>=', $start)
                ->count();

            $avgResolutionTime = Ticket::where('staff_id', $staffId)
                ->where('status', 'Closed')
                ->where('updated_at', '>=', $start)
                ->selectRaw('AVG(TIMESTAMPDIFF(HOUR, date_created, date_closed)) as avg_time')
                ->first()->avg_time ?? 0;

            return [
                'resolved_tickets' => $resolvedTickets,
                'total_tickets' => $totalTickets,
                'avg_resolution_time' => round($avgResolutionTime, 2),
            ];
        } catch (Exception $e) {
            Log::error("Error in getPerformanceMetrics: " . $e->getMessage());
            return ['resolved_tickets' => 0, 'total_tickets' => 0, 'avg_resolution_time' => 0];
        }
    }

    private function getOverdueTickets($staffId, int $days)
    {
        try {
            $start = now()->subDays($days)->startOfDay();

            return Ticket::where('staff_id', $staffId)
                ->where('status', '!=', 'Closed')
                ->where('date_created', '<', now()->subHours(24))
                ->where('date_created', '>=', $start)
                ->get();
        } catch (Exception $e) {
            Log::error("Error in getOverdueTickets: " . $e->getMessage());
            return collect(); // Return empty collection
        }
    }

    private function buildThroughput(int $staffId, int $days): array
    {
        try {
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
                $labels[] = $cursor->format('M j');
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
        } catch (Exception $e) {
            Log::error("Error in buildThroughput: " . $e->getMessage());
            return ['series' => [], 'labels' => [], 'max' => 0];
        }
    }
}   