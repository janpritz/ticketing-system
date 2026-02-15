<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;
use App\Models\Role;
use App\Models\Department;
use App\Models\User;

class RolesController extends Controller
{
    /**
     * Display a listing of roles.
     */
    public function index(Request $request)
    {
        $this->ensureAdmin();
        
        $perPage = (int)$request->query('per_page', 25);
        $roles = Role::with('department')
            ->orderBy('name')
            ->paginate($perPage);

        // Also load departments for the form
        $departments = Department::orderBy('name')->get();

        return view('dashboards.admin.roles.index', compact('roles', 'departments'));
    }

    /**
     * Show the form for creating a new role.
     */
    public function create()
    {
        $this->ensureAdmin();
        $departments = Department::orderBy('name')->get();
        return view('dashboards.admin.roles.create', compact('departments'));
    }

    /**
     * Store a newly created role in storage.
     */
    public function store(Request $request)
    {
        $this->ensureAdmin();
        
        $validated = $request->validate([
            'department_id' => ['required', 'exists:departments,id'],
            'name' => ['required', 'string', 'max:191', Rule::unique('roles', 'name')],
            'description' => ['nullable', 'string', 'max:1000'],
        ]);

        $role = Role::create([
            'department_id' => $validated['department_id'],
            'name' => $validated['name'],
            'description' => $validated['description'] ?? null,
        ]);

        if ($request->ajax() || $request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Role created successfully.',
                'data' => $role
            ]);
        }

        return redirect()->route('admin.roles.index')->with('status', 'Role created.');
    }

    /**
     * Show the form for editing the specified role.
     */
    public function edit(Role $role)
    {
        $this->ensureAdmin();
        $departments = Department::orderBy('name')->get();
        return view('dashboards.admin.roles.edit', compact('role', 'departments'));
    }

    /**
     * Update the specified role in storage.
     */
    public function update(Request $request, Role $role)
    {
        $this->ensureAdmin();
        
        $validated = $request->validate([
            'department_id' => ['required', 'exists:departments,id'],
            'name' => ['required', 'string', 'max:191', Rule::unique('roles', 'name')->ignore($role->id)],
            'description' => ['nullable', 'string', 'max:1000'],
        ]);

        $role->update([
            'department_id' => $validated['department_id'],
            'name' => $validated['name'],
            'description' => $validated['description'] ?? null,
        ]);

        if ($request->ajax() || $request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Role updated successfully.',
                'data' => $role
            ]);
        }

        return redirect()->route('admin.roles.index')->with('status', 'Role updated.');
    }

    /**
     * Remove the specified role from storage.
     */
    public function destroy(Role $role)
    {
        $this->ensureAdmin();

        // Check if any users are assigned to this role
        $userCount = User::whereHas('roles', function ($q) use ($role) {
            $q->where('roles.id', $role->id);
        })->count();
        
        if ($userCount > 0) {
            return response()->json([
                'success' => false,
                'message' => "Cannot delete role '{$role->name}' because {$userCount} user(s) are assigned to it. Please reassign or remove the users first."
            ], 422);
        }

        $role->delete();

        return response()->json([
            'success' => true,
            'message' => 'Role deleted successfully.'
        ]);
    }

    private function ensureAdmin(): void
    {
        $u = Auth::user();
        $isAdmin = $u && strtolower((string)($u->role ?? '')) === 'primary administrator';
        abort_unless($isAdmin, 403, 'Unauthorized');
    }
}
