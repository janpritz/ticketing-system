<?php

namespace App\Services\Admin;

use App\Models\{Role, Department, User};
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;

class RoleService
{
    /**
     * Get roles with their associated departments, paginated.
     */
    public function getPaginatedRoles(int $perPage = 25): LengthAwarePaginator
    {
        return Role::with('department')
            ->orderBy('name')
            ->paginate($perPage);
    }

    /**
     * Get a list of all departments for selection in forms.
     */
    public function getAllDepartments(): Collection
    {
        return Department::orderBy('name')->get();
    }

    public function createRole(array $data): Role
    {
        return Role::create([
            'department_id' => $data['department_id'],
            'name'          => $data['name'],
            'description'   => $data['description'] ?? null,
        ]);
    }

    /**
     * Update an existing role record.
     */
    public function updateRole(Role $role, array $data): Role
    {
        $role->update([
            'department_id' => $data['department_id'],
            'name'          => $data['name'],
            'description'   => $data['description'] ?? null,
        ]);

        return $role;
    }

    /**
     * Attempt to delete a role after verifying it is not in use.
     */
    public function deleteRole(Role $role): array
    {
        // 1. Safety Check: Are users still attached?
        $userCount = User::whereHas('roles', function ($q) use ($role) {
            $q->where('roles.id', $role->id);
        })->count();

        if ($userCount > 0) {
            return [
                'success' => false,
                'message' => "Cannot delete role '{$role->name}' because {$userCount} user(s) are assigned to it. Please reassign or remove the users first."
            ];
        }

        // 2. Perform the deletion
        $role->delete();

        return [
            'success' => true,
            'message' => 'Role deleted successfully.'
        ];
    }
}
