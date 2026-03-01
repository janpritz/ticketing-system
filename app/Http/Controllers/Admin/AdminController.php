<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Services\Admin\{AdminService, DashboardService, UserService};

class AdminController extends Controller
{
    /**
     * Backwards-compatibility helper.
     * Older Announcements.txt entries may include a leading "roles:" line.
     * We do not store that in the file anymore (role scoping is handled in DB),
     * so strip it out when reconstructing or displaying.
     */
    private function stripLeadingRolesLine(string $content, AdminService $service): string
    {
        $result = $service->handleStripLeadingRolesLine($content);
        return $result;
    }

    /**
     * Admin dashboard displaying system-wide metrics, charts and recent tickets.
     */
    public function index(DashboardService $dashboardService)
    {
        $data = $dashboardService->getAdminDashboardData();

        return response()->view('dashboards.admin.index', $data);
    }

    /**
     * Live data endpoint for admin dashboard auto-refresh.
     */
    public function data(DashboardService $dashboardService)
    {
        $data = $dashboardService->getAdminDashboardData();
        return response()->json($data);
    }

    public function categoriesByRole(Request $request, DashboardService $service)
    {
        $roleName = $request->query('role_name');

        if (!$roleName) {
            return response()->json([
                'success' => false,
                'message' => 'No roles detected.'
            ], 400);
        }

        return response()->json($service->getCategoriesByRoleName($roleName));
    }
    public function logsIndex(Request $request, AdminService $service)
    {
        $filters = [
            'q' => trim((string) $request->query('q', '')),
            'per_page' => $request->integer('per_page', 25)
        ];

        // Validate per_page against allowed values
        if (!in_array($filters['per_page'], [25, 50, 100])) {
            $filters['per_page'] = 25;
        }

        $logs = $service->getDocumentLogs($filters);

        return view('dashboards.admin.logs.index', array_merge($filters, [
            'logs' => $logs
        ]));
    }
    public function faqsIndex()
    {
        return view('dashboards.admin.faqs.index');
    }
}
