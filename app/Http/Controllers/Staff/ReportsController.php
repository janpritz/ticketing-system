<?php

namespace App\Http\Controllers\Staff;

// use App\Models\Faq;
// use App\Models\ProcessedFaq;
use App\Models\Ticket;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use App\Services\Staff\ReportService;

class ReportsController extends Controller
{
    public function index(Request $request, ReportService $service)
    {
        $staffId = Auth::id();

        // Normalize allowed days
        $days = in_array($request->get('days'), [7, 30, 90])
            ? (int) $request->days
            : 7;

        // Get all report data via the Service
        $data = $service->getStaffReportData($staffId, $days);

        return view('staff.reports.index', array_merge($data, ['days' => $days]));
    }
}
