<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Services\Admin\ReportService;


class ReportsController extends Controller
{
    /**
     * Display the high-level administrative reports dashboard.
     */
    public function index(ReportService $service)
    {

        // 1. Fetch all metrics from the service
        $stats = $service->getAdminDashboardStats();

        // 2. Return view with organized data
        return view('dashboards.admin.reports.page', $stats);
    }

    public function getBacklogTrendDataAjax(Request $request, ReportService $service)
    {
        $days = $request->get('days', 30);
        $data = $service->getBacklogTrendData($days);

        return response()->json($data);
    }

    public function getClosedTicketsTrendDataAjax(Request $request, ReportService $service)
    {
        $days = $request->get('days', 30);
        $data = $service->getClosedTicketsTrendData($days);

        return response()->json($data);
    }

    public function getDynamicDataAjax(Request $request, ReportService $service)
    {
        $days = $request->get('days', 30);
        $data = [
            'avgResolutionTime' => $service->getAvgResolutionTimeForPeriod($days),
            'totalTickets' => $service->getTotalTicketsForPeriod($days),
            'ticketsSolved' => $service->getTicketsSolvedForPeriod($days),
            'topTicketDrivers' => $service->getTopTicketDriversForPeriod($days),
            'ticketsByOrg' => $service->getTicketsByOrgForPeriod($days),
        ];
        return response()->json($data);
    }

    /**
     * Get detailed data on how a specific staff member forwards tickets to others.
     */
    public function getForwardsByStaff($staffId, Request $request, ReportService $service)
    {
        $days = $request->integer('days', 30);
        $result = $service->getForwardingReportForStaff((int)$staffId, $days);

        return response()->json($result);
    }
}
