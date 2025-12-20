<?php

namespace App\Http\Controllers;

use App\Models\Faq;
use App\Models\ProcessedFaq;
use App\Models\Ticket;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class StaffReportsController extends Controller
{
    public function index()
    {
        $staffId = Auth::id();

        $performanceMetrics = $this->getPerformanceMetrics($staffId);
        $overdueTickets = $this->getOverdueTickets($staffId);
        $faqAnalysis = $this->getFaqAnalysis($staffId);
        $weeklyThroughput = $this->buildWeeklyThroughput($staffId);

        return view('staff.reports.index', compact('performanceMetrics', 'overdueTickets', 'faqAnalysis', 'weeklyThroughput'));
    }

    private function getPerformanceMetrics($staffId)
    {
        // Example: tickets resolved by staff
        $resolvedTickets = Ticket::where('staff_id', $staffId)
            ->where('status', 'Closed')
            ->count();

        $totalTickets = Ticket::where('staff_id', $staffId)->count();

        $avgResolutionTime = Ticket::where('staff_id', $staffId)
            ->where('status', 'Closed')
            ->selectRaw('AVG(TIMESTAMPDIFF(HOUR, created_at, updated_at)) as avg_time')
            ->first()->avg_time ?? 0;

        return [
            'resolved_tickets' => $resolvedTickets,
            'total_tickets' => $totalTickets,
            'avg_resolution_time' => $avgResolutionTime,
        ];
    }

    private function getOverdueTickets($staffId)
    {
        // Assuming tickets with status not closed and created more than 24 hours ago
        $overdue = Ticket::where('staff_id', $staffId)
            ->where('status', '!=', 'Closed')
            ->where('created_at', '<', now()->subHours(24))
            ->get();

        return $overdue;
    }

    private function getFaqAnalysis($staffId)
    {
        $processedFaqs = ProcessedFaq::where('staff_id', $staffId)->count();
        $totalFaqs = Faq::count();

        return [
            'processed_faqs' => $processedFaqs,
            'total_faqs' => $totalFaqs,
        ];
    }

    /**
     * Build dynamic weekly throughput (last 7 days) for a staff user.
     * Returns:
     * [
     *   'series' => [c1,...,c7],
     *   'labels' => ['Sun','Mon',...],
     *   'max'    => maxCount
     * ]
     */
    private function buildWeeklyThroughput(int $staffId): array
    {
        // Weekly analytics (Mon–Sun) scoped to the signed-in staff's tickets
        $startOfWeek = \Illuminate\Support\Carbon::now()->startOfWeek();
        $endOfWeek = \Illuminate\Support\Carbon::now()->endOfWeek();

        $rows = \App\Models\Ticket::where('staff_id', $staffId)
            ->whereBetween('date_created', [$startOfWeek, $endOfWeek])
            ->selectRaw('DATE(date_created) as d, COUNT(*) as c')
            ->groupBy('d')
            ->orderBy('d')
            ->pluck('c', 'd')
            ->toArray();

        $series = [];
        $labels = [];
        $max = 0;

        $cursor = $startOfWeek->copy();
        for ($i = 0; $i < 7; $i++) {
            $dayKey = $cursor->toDateString();
            $count = (int)($rows[$dayKey] ?? 0);
            $series[] = $count;
            $labels[] = $cursor->format('D'); // Mon, Tue, ...
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