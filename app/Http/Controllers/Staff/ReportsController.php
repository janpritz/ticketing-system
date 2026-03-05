<?php

namespace App\Http\Controllers\Staff;

// use App\Models\Faq;
// use App\Models\ProcessedFaq;
use App\Models\Ticket;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;

class ReportsController extends Controller
{
    public function index(Request $request)
    {
        $staffId = Auth::id();
        $days = (int) $request->get('days', 7);

        // Normalize allowed days to 7,30,90
        if (!in_array($days, [7, 30, 90])) {
            $days = 7;
        }

        $performanceMetrics = $this->getPerformanceMetrics($staffId, $days);
        $overdueTickets = $this->getOverdueTickets($staffId, $days);
        $faqAnalysis = ['processed_faqs' => 0, 'total_faqs' => 0]; // Default values since FAQ processing is commented out
        $weeklyThroughput = $this->buildThroughput($staffId, $days);

        // Build a simple, robust recently forwarded list (forwarder name + ticket category)
        $recentForwarders = [];
        $limit = 10;

        $recentRouting = \App\Models\TicketRoutingHistory::where('staff_id', $staffId)
            ->orderByDesc('routed_at')
            ->with(['ticket.category'])
            ->take(30)
            ->get();

        $prevStaffIds = [];
        $prevMap = [];
        foreach ($recentRouting as $entry) {
            $prev = \App\Models\TicketRoutingHistory::where('ticket_id', $entry->ticket_id)
                ->where('routed_at', '<', $entry->routed_at)
                ->orderByDesc('routed_at')
                ->first();

            if ($prev && $prev->staff_id) {
                $prevStaffIds[] = $prev->staff_id;
                $prevMap[$entry->id] = $prev->staff_id;
            } elseif ($entry->ticket && isset($entry->ticket->staff_id) && $entry->ticket->staff_id) {
                $prevStaffIds[] = $entry->ticket->staff_id;
                $prevMap[$entry->id] = $entry->ticket->staff_id;
            }
        }

        $userNames = [];
        if (!empty($prevStaffIds)) {
            $userNames = \App\Models\User::whereIn('id', array_values(array_unique($prevStaffIds)))->pluck('name', 'id')->toArray();
        }

        foreach ($recentRouting as $entry) {
            $forwarderName = 'Unknown';
            if (isset($prevMap[$entry->id]) && isset($userNames[$prevMap[$entry->id]])) {
                $forwarderName = $userNames[$prevMap[$entry->id]];
            }

            // Normalize category: category may be a related model object or a plain string in some records
            $categoryName = 'Uncategorized';
            if ($entry->ticket) {
                // Prefer the relation (Category model) if present
                if ($entry->ticket->category && is_object($entry->ticket->category)) {
                    $categoryName = $entry->ticket->category->name ?? 'Uncategorized';
                } else {
                    // Fallback to legacy category string column if present
                    $legacy = $entry->ticket->getAttribute('category');
                    if (is_string($legacy) && trim($legacy) !== '') {
                        $categoryName = $legacy;
                    }
                }
            }

            $recentForwarders[] = [
                'name' => $forwarderName,
                'category' => $categoryName,
            ];

            if (count($recentForwarders) >= $limit) break;
        }

        $totalForwardsCount = \App\Models\TicketRoutingHistory::where('staff_id', $staffId)->count();

        return view('staff.reports.index', compact('performanceMetrics', 'overdueTickets', 'faqAnalysis', 'weeklyThroughput', 'recentForwarders', 'totalForwardsCount', 'days'));
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

    // private function getFaqAnalysis($staffId)
    // {
    //     $processedFaqs = ProcessedFaq::where('staff_id', $staffId)->count();
    //     $totalFaqs = Faq::count();

    //     return [
    //         'processed_faqs' => $processedFaqs,
    //         'total_faqs' => $totalFaqs,
    //     ];
    // }

    /**
     * Build dynamic weekly throughput (last 7 days) for a staff user.
     * Returns:
     * [
     *   'series' => [c1,...,c7],
     *   'labels' => ['Sun','Mon',...],
     *   'max'    => maxCount
     * ]
     */
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
