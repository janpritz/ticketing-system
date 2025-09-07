<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Models\Ticket;
use App\Models\User;
use Carbon\Carbon;

class AdminController extends Controller
{
    /**
     * Admin dashboard displaying system-wide metrics, charts and recent tickets.
     */
    public function index(Request $request)
    {
        // KPI metrics
        $openTickets = Ticket::where('status', 'Open')->count();
        $inProgressTickets = Ticket::where('status', 'In-Progress')->count();
        // Placeholder until a FAQs model/table exists
        $faqCount = 0;
        $userCount = User::count();

        // Weekly ticket analytics (current week Mon-Sun)
        $startOfWeek = Carbon::now()->startOfWeek();
        $endOfWeek = Carbon::now()->endOfWeek();

        $byDay = Ticket::select(DB::raw('DATE(date_created) as d'), DB::raw('COUNT(*) as c'))
            ->whereBetween('date_created', [$startOfWeek, $endOfWeek])
            ->groupBy('d')
            ->pluck('c', 'd')
            ->toArray();

        $weekLabels = [];
        $weekData = [];
        $cursor = $startOfWeek->copy();
        for ($i = 0; $i < 7; $i++) {
            $weekLabels[] = $cursor->format('D'); // Mon, Tue, ...
            $dateKey = $cursor->toDateString();
            $weekData[] = (int)($byDay[$dateKey] ?? 0);
            $cursor->addDay();
        }

        // Tickets by Category
        $categoryRows = Ticket::select('category', DB::raw('COUNT(*) as c'))
            ->groupBy('category')
            ->orderByDesc('c')
            ->get();

        $categoryLabels = $categoryRows->pluck('category')->map(function ($v) {
            return $v ?: 'Uncategorized';
        })->toArray();
        $categoryData = $categoryRows->pluck('c')->map(fn ($v) => (int)$v)->toArray();

        // Top senders (frequent ticket creators) - top 10
        $topSenders = Ticket::select('email', DB::raw('COUNT(*) as c'))
            ->groupBy('email')
            ->orderByDesc('c')
            ->limit(10)
            ->get();

        // Recent lists
        $openList = Ticket::with('staff')
            ->where('status', 'Open')
            ->orderByDesc('date_created')
            ->take(6)
            ->get();

        $inProgressList = Ticket::with('staff')
            ->where('status', 'In-Progress')
            ->orderByDesc('updated_at')
            ->take(6)
            ->get();

        return view('dashboards.admin.index', [
            'openTickets'       => $openTickets,
            'inProgressTickets' => $inProgressTickets,
            'faqCount'          => $faqCount,
            'userCount'         => $userCount,
            'weekLabels'        => $weekLabels,
            'weekData'          => $weekData,
            'categoryLabels'    => $categoryLabels,
            'categoryData'      => $categoryData,
            'topSenders'        => $topSenders,
            'openList'          => $openList,
            'inProgressList'    => $inProgressList,
        ]);
    }

    /**
     * Live data endpoint for admin dashboard auto-refresh.
     */
    public function data(Request $request)
    {
        // KPI metrics
        $openTickets = Ticket::where('status', 'Open')->count();
        $inProgressTickets = Ticket::where('status', 'In-Progress')->count();
        // Placeholder until a FAQs model/table exists
        $faqCount = 0;
        $userCount = User::count();

        // Weekly ticket analytics (current week Mon-Sun)
        $startOfWeek = Carbon::now()->startOfWeek();
        $endOfWeek = Carbon::now()->endOfWeek();

        $byDay = Ticket::select(DB::raw('DATE(date_created) as d'), DB::raw('COUNT(*) as c'))
            ->whereBetween('date_created', [$startOfWeek, $endOfWeek])
            ->groupBy('d')
            ->pluck('c', 'd')
            ->toArray();

        $weekLabels = [];
        $weekData = [];
        $cursor = $startOfWeek->copy();
        for ($i = 0; $i < 7; $i++) {
            $weekLabels[] = $cursor->format('D'); // Mon, Tue, ...
            $dateKey = $cursor->toDateString();
            $weekData[] = (int)($byDay[$dateKey] ?? 0);
            $cursor->addDay();
        }

        // Tickets by Category (all)
        $categoryRows = Ticket::select('category', DB::raw('COUNT(*) as c'))
            ->groupBy('category')
            ->orderByDesc('c')
            ->get();

        $categoryLabels = $categoryRows->pluck('category')->map(function ($v) {
            return $v ?: 'Uncategorized';
        })->values()->toArray();

        $categoryData = $categoryRows->pluck('c')->map(fn ($v) => (int)$v)->values()->toArray();

        // Top senders (frequent ticket creators) - top 10
        $topSenders = Ticket::select('email', DB::raw('COUNT(*) as c'))
            ->groupBy('email')
            ->orderByDesc('c')
            ->limit(10)
            ->get()
            ->map(function ($row) {
                return [
                    'email' => $row->email,
                    'count' => (int) $row->c,
                ];
            })
            ->values()
            ->toArray();

        // Recent lists for live admin table refresh (limit 6 like initial view)
        $openListArr = Ticket::with('staff')
            ->where('status', 'Open')
            ->orderByDesc('date_created')
            ->take(6)
            ->get()
            ->map(function ($t) {
                return [
                    'id'           => (int) $t->id,
                    'status'       => (string) $t->status,
                    'email'        => (string) ($t->email ?? ''),
                    'category'     => (string) ($t->category ?? ''),
                    'date_created' => optional($t->date_created ?? $t->created_at)->toDateTimeString(),
                    'created_at'   => optional($t->created_at)->toDateTimeString(),
                    'updated_at'   => optional($t->updated_at)->toDateTimeString(),
                    'staff'        => $t->staff ? ['name' => (string) $t->staff->name] : null,
                ];
            })
            ->values()
            ->toArray();

        $inProgressListArr = Ticket::with('staff')
            ->where('status', 'In-Progress')
            ->orderByDesc('updated_at')
            ->take(6)
            ->get()
            ->map(function ($t) {
                return [
                    'id'           => (int) $t->id,
                    'status'       => (string) $t->status,
                    'email'        => (string) ($t->email ?? ''),
                    'category'     => (string) ($t->category ?? ''),
                    'date_created' => optional($t->date_created ?? $t->created_at)->toDateTimeString(),
                    'created_at'   => optional($t->created_at)->toDateTimeString(),
                    'updated_at'   => optional($t->updated_at)->toDateTimeString(),
                    'staff'        => $t->staff ? ['name' => (string) $t->staff->name] : null,
                ];
            })
            ->values()
            ->toArray();

        return response()->json([
            'openTickets'       => (int) $openTickets,
            'inProgressTickets' => (int) $inProgressTickets,
            'faqCount'          => (int) $faqCount,
            'userCount'         => (int) $userCount,
            'weekLabels'        => $weekLabels,
            'weekData'          => $weekData,
            'categoryLabels'    => $categoryLabels,
            'categoryData'      => $categoryData,
            'topSenders'        => $topSenders,
            'openList'          => $openListArr,
            'inProgressList'    => $inProgressListArr,
        ])
        ->header('Cache-Control', 'no-store, no-cache, must-revalidate, max-age=0')
        ->header('Pragma', 'no-cache')
        ->header('Expires', '0');
    }
}