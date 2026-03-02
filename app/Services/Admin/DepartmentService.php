<?php

namespace App\Services\Admin;

use App\Models\Department;
use App\Models\Role;
use Illuminate\Support\Facades\DB;

class DepartmentService
{
    /**
     * Create a department and batch-create its associated roles.
     */
    public function createDepartmentWithRoles(array $data): Department
    {
        return DB::transaction(function () use ($data) {
            // 1. Create the Department
            $department = Department::create([
                'name'        => $data['name'],
                'description' => $data['description'] ?? null,
            ]);

            // 2. Create Roles (if provided)
            if (!empty($data['roles']) && is_array($data['roles'])) {
                foreach ($data['roles'] as $roleName) {
                    $trimmedName = trim($roleName);

                    if (!empty($trimmedName)) {
                        $department->roles()->create([
                            'name'        => $trimmedName,
                            'description' => null,
                        ]);
                    }
                }
            }

            return $department;
        });
    }

    public function updateDepartment(Department $department, array $data): bool
    {
        return $department->update([
            'name' => $data['name'],
            'description' => $data['description'] ?? $department->description,
        ]);
    }

    /**
     * Safely delete a department after checking for dependencies.
     */
    public function deleteDepartment(Department $department): array
    {
        // 1. Check for associated Roles
        if ($department->roles()->exists()) {
            return [
                'success' => false,
                'message' => 'Cannot delete department with associated roles.'
            ];
        }

        // 2. Check for assigned Users
        $userCount = $department->users()->count();
        if ($userCount > 0) {
            return [
                'success' => false,
                'message' => "Cannot delete department because {$userCount} user(s) are assigned to it. Please reassign them first."
            ];
        }

        // 3. Perform Deletion
        $department->delete();

        return [
            'success' => true,
            'message' => 'Department deleted successfully.'
        ];
    }
}
