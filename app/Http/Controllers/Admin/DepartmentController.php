<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Department;
use App\Models\Role;
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
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:191|unique:departments,name',
            'description' => 'nullable|string',
            'roles' => 'nullable|array',
            'roles.*' => 'nullable|string|max:191',
        ]);

        // Create department
        $department = Department::create([
            'name' => $request->name,
            'description' => $request->description,
        ]);

        // Create roles if provided
        if ($request->has('roles') && is_array($request->roles)) {
            foreach ($request->roles as $roleName) {
                if (!empty(trim($roleName))) {
                    Role::create([
                        'department_id' => $department->id,
                        'name' => trim($roleName),
                        'description' => null,
                    ]);
                }
            }
        }

        if ($request->ajax() || $request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Department created successfully with roles.',
                'data' => $department
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
    public function update(Request $request, Department $department)
    {
        $request->validate([
            'name' => 'required|string|max:191|unique:departments,name,' . $department->id,
            'description' => 'nullable|string',
        ]);

        $department->update([
            'name' => $request->name,
            'description' => $request->description,
        ]);

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
    public function destroy(Department $department)
    {
        // Check if department has roles
        if ($department->roles()->count() > 0) {
            if (request()->ajax() || request()->wantsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Cannot delete department with associated roles.'
                ], 422);
            }
            return redirect()->route('admin.departments.index')
                ->with('error', 'Cannot delete department with associated roles.');
        }

        // Check if any users are assigned to this department
        $userCount = $department->users()->count();
        if ($userCount > 0) {
            if (request()->ajax() || request()->wantsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => "Cannot delete department because {$userCount} user(s) are assigned to it. Please reassign or remove the users first."
                ], 422);
            }
            return redirect()->route('admin.departments.index')
                ->with('error', "Cannot delete department because {$userCount} user(s) are assigned to it. Please reassign or remove the users first.");
        }

        $department->delete();

        if (request()->ajax() || request()->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Department deleted successfully.'
            ]);
        }

        return redirect()->route('admin.departments.index')
            ->with('success', 'Department deleted successfully.');
    }
}
