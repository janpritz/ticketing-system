<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\RoleRequest;
use Illuminate\Http\Request;
use App\Models\Role;
use App\Models\Department;
use App\Services\Admin\RoleService;

class RolesController extends Controller
{
    /**
     * Display a listing of roles and available departments.
     */
    public function index(Request $request, RoleService $service)
    {
        $roles = $service->getPaginatedRoles($request->integer('per_page', 25));
        $departments = $service->getAllDepartments();

        return view('dashboards.admin.roles.index', compact('roles', 'departments'));
    }

    public function create()
    {
        $departments = Department::orderBy('name')->get();
        return view('dashboards.admin.roles.create', compact('departments'));
    }

    /**
     * Store a newly created role in storage.
     */
    /**
     * Store a newly created role in storage.
     */
    public function store(RoleRequest $request, RoleService $service)
    {
        $role = $service->createRole($request->validated());

        if ($request->ajax() || $request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Role created successfully.',
                'data'    => $role->load('department')
            ]);
        }

        return redirect()->route('admin.roles.index')->with('status', 'Role created.');
    }

    /**
     * Show the form for editing the specified role.
     */
    public function edit(Role $role)
    {
        $departments = Department::orderBy('name')->get();
        return view('dashboards.admin.roles.edit', compact('role', 'departments'));
    }

    /**
     * Update the specified role in storage.
     */
    /**
     * Update the specified role in storage.
     */
    public function update(RoleRequest $request, Role $role, RoleService $service)
    {
        $updatedRole = $service->updateRole($role, $request->validated());

        if ($request->ajax() || $request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Role updated successfully.',
                'data'    => $updatedRole->load('department')
            ]);
        }

        return redirect()->route('admin.roles.index')->with('status', 'Role updated.');
    }

    /**
     * Remove the specified role from storage.
     */
    public function destroy(Role $role, RoleService $service)
    {
        $result = $service->deleteRole($role);

        return response()->json(
            $result,
            $result['success'] ? 200 : 422
        );
    }
    public function all()
    {
        $roles = Role::with('department')
            ->orderBy('department_id')
            ->orderBy('name')->get();

        return response()->json($roles);
    }

    /**
     * Get roles by department (for department-roles dropdown).
     */
    public function byDepartment($departmentId)
    {
        $roles = Role::where('department_id', $departmentId)
            ->with('department')
            ->orderBy('name')
            ->get();

        return response()->json($roles);
    }
}
