<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;
use App\Models\Role;
use App\Models\User;

class RolesController extends Controller
{
    /**
     * List roles (admin only).
     */
    public function index(Request $request)
    {
        $this->ensureAdmin();

        $perPage = (int) $request->query('per_page', 25);
        $roles = Role::orderBy('name')->paginate($perPage);

        return view('dashboards.admin.roles.index', compact('roles'));
    }

    /**
     * Show create form.
     */
    public function create()
    {
        $this->ensureAdmin();
        return view('dashboards.admin.roles.create');
    }

    /**
     * Store a new role.
     */
    public function store(Request $request)
    {
        $this->ensureAdmin();

        $validated = $request->validate([
            'name' => ['required','string','max:191', Rule::unique('roles','name')],
            'description' => ['nullable','string','max:1000'],
        ]);

        Role::create([
            'name' => $validated['name'],
            'description' => $validated['description'] ?? null,
        ]);

        return redirect()->route('admin.roles.index')->with('status', 'Role created.');
    }

    /**
     * Show edit form.
     */
    public function edit(Role $role)
    {
        $this->ensureAdmin();

        // Protect the reserved Primary Administrator role from being edited here.
        if (strtolower((string)$role->name) === 'primary administrator') {
            abort(403, 'Cannot edit Primary Administrator role via this UI.');
        }

        return view('dashboards.admin.roles.edit', compact('role'));
    }

    /**
     * Update a role.
     */
    public function update(Request $request, Role $role)
    {
        $this->ensureAdmin();

        if (strtolower((string)$role->name) === 'primary administrator') {
            abort(403, 'Cannot modify Primary Administrator role.');
        }

        $validated = $request->validate([
            'name' => ['required','string','max:191', Rule::unique('roles','name')->ignore($role->id)],
            'description' => ['nullable','string','max:1000'],
        ]);

        $role->update([
            'name' => $validated['name'],
            'description' => $validated['description'] ?? null,
        ]);

        return redirect()->route('admin.roles.index')->with('status', 'Role updated.');
    }

    /**
     * Delete a role.
     */
    public function destroy(Role $role)
    {
        $this->ensureAdmin();

        if (strtolower((string)$role->name) === 'primary administrator') {
            return back()->withErrors(['delete' => 'Cannot delete Primary Administrator role.']);
        }

        // Unassign users that reference this role (set role_id to null)
        User::where('role_id', $role->id)->update(['role_id' => null]);

        $role->delete();

        return redirect()->route('admin.roles.index')->with('status', 'Role deleted.');
    }

    /**
     * Ensure only Primary Administrator may manage roles.
     */
    private function ensureAdmin(): void
    {
        $u = Auth::user();

        // Use the backwards-compatible accessor ($user->role) to determine admin status.
        // This avoids direct method calls that some static analyzers may not resolve during migration.
        $isAdmin = $u && strtolower((string)($u->role ?? '')) === 'primary administrator';

        abort_unless($isAdmin, 403, 'Unauthorized');
    }
}