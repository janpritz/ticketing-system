<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;
use App\Models\Role;
use App\Models\Category;
use App\Models\User;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

class RolesController extends Controller
{
    /**
     * List roles (admin only).
     */
    public function index(Request $request)
    {
        $this->ensureAdmin();

        $perPage = (int) $request->query('per_page', 25);

        // Select only needed columns to reduce payload
        $roles = Role::select('id','name','description','updated_at')
            ->orderBy('name')
            ->paginate($perPage);

        // Cache a "last changed" timestamp to avoid repeated MAX() scans
        $lastChanged = Cache::get('roles_last_changed');
        if (!$lastChanged) {
            $maxUpdated = Role::max('updated_at');
            $lastChanged = $maxUpdated ? strtotime($maxUpdated) : time();
            Cache::put('roles_last_changed', $lastChanged, 3600);
        }

        return view('dashboards.admin.roles.index', compact('roles'))
            ->with('lastChanged', $lastChanged);
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

        // Require at least 2 categories when creating a role
        $validated = $request->validate([
            'name' => ['required','string','max:191', Rule::unique('roles','name')],
            'description' => ['nullable','string','max:1000'],
            'categories' => ['required','array','min:2'],
            'categories.*' => ['required','string','max:191'],
        ]);

        // Create role
        $role = Role::create([
            'name' => $validated['name'],
            'description' => $validated['description'] ?? null,
        ]);

        // Create categories and attach to role
        foreach ($validated['categories'] as $catName) {
            $catName = trim((string)$catName);
            if ($catName === '') {
                continue;
            }
            try {
                Category::create([
                    'role_id' => $role->id,
                    'name' => $catName,
                    'description' => null,
                ]);
            } catch (\Throwable $e) {
                // Log but continue; category uniqueness or other DB issues shouldn't block role creation here
                Log::warning('Failed to create category "' . $catName . '" for role ' . $role->id . ': ' . $e->getMessage());
            }
        }

        // bump roles last-changed cache
        try {
            Cache::put('roles_last_changed', time(), 3600);
        } catch (\Throwable $cacheEx) {
            Log::warning('Failed to update roles_last_changed cache: ' . $cacheEx->getMessage());
        }

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

        // bump roles last-changed cache
        try {
            Cache::put('roles_last_changed', time(), 3600);
        } catch (\Throwable $cacheEx) {
            Log::warning('Failed to update roles_last_changed cache: ' . $cacheEx->getMessage());
        }

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

        // bump roles last-changed cache
        try {
            Cache::put('roles_last_changed', time(), 3600);
        } catch (\Throwable $cacheEx) {
            Log::warning('Failed to update roles_last_changed cache: ' . $cacheEx->getMessage());
        }

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