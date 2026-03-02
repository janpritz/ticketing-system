<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\DepartmentRequest;
use App\Http\Requests\Admin\DepartmentUpdateRequest;
use App\Models\Department;
use App\Models\Role;
use App\Services\Admin\DepartmentService;
use Illuminate\Http\Request;

class DepartmentController extends Controller
{
    /**
     * Display a listing of the departments.
     */
    public function index()
    {
        $departments = Department::with('roles')->orderBy('name')->paginate(10);
        return view('dashboards.admin.departments.index', compact('departments'));
    }

    /**
     * Show the form for creating a new department.
     */
    public function create()
    {
        return view('dashboards.admin.departments.create');
    }

    /**
     * Store a newly created department in storage.
     */
    /**
     * Store a newly created department and its associated roles.
     */
    public function store(DepartmentRequest $request, DepartmentService $service)
    {
        $department = $service->createDepartmentWithRoles($request->validated());

        if ($request->ajax() || $request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Department created successfully with roles.',
                'data'    => $department
            ]);
        }

        return redirect()->route('admin.departments.index')
            ->with('success', 'Department created successfully with roles.');
    }

    /**
     * Show the form for editing the specified department.
     */
    public function edit(Department $department)
    {
        return view('dashboards.admin.departments.edit', compact('department'));
    }

    /**
     * Update the specified department in storage.
     */
    public function update(DepartmentUpdateRequest $request, Department $department, DepartmentService $service)
    {
        $service->updateDepartment($department, $request->validated());

        if ($request->ajax() || $request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Department updated successfully.',
                'data' => $department
            ]);
        }

        return redirect()->route('admin.departments.index')
            ->with('success', 'Department updated successfully.');
    }

    /**
     * Remove the specified department from storage.
     */
    public function destroy(Department $department, DepartmentService $service)
    {
        $result = $service->deleteDepartment($department);

        if (request()->ajax() || request()->wantsJson()) {
            return response()->json([
                'success' => $result['success'],
                'message' => $result['message']
            ], $result['success'] ? 200 : 422);
        }

        $type = $result['success'] ? 'success' : 'error';
        return redirect()->route('admin.departments.index')->with($type, $result['message']);
    }
}
