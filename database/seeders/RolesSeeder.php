<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use App\Models\Role;
use App\Models\Department;

class RolesSeeder extends Seeder
{
    /**
     * Run the roles seeder.
     *
     * This will:
     * 1. Delete any "Unassigned" role
     * 2. Ensure "Primary Administrator" gets ID 1
     * 3. Create other roles as needed
     */
    public function run()
    {
        // First, delete any "Unassigned" role that may exist
        DB::table('roles')->where('name', 'Unassigned')->delete();

        // Get the Primary Administrator department
        $primaryAdminDept = Department::where('name', 'Primary Administrator')->first();
        $primaryAdminDeptId = $primaryAdminDept ? $primaryAdminDept->id : null;

        // Check if Primary Administrator already exists with any ID
        $existingPrimaryAdmin = Role::where('name', 'Primary Administrator')->first();
        
        if ($existingPrimaryAdmin) {
            // If it's not ID 1, we need to handle this
            if ($existingPrimaryAdmin->id != 1) {
                // Update the role at ID 1 to be the Primary Administrator
                $roleAtId1 = Role::find(1);
                if ($roleAtId1) {
                    // Delete the role at ID 1 (should be Unassigned after our delete above)
                    $roleAtId1->delete();
                }
                // Update the existing Primary Administrator to ID 1
                $existingPrimaryAdmin->update(['id' => 1, 'department_id' => $primaryAdminDeptId]);
            } else {
                // It's already ID 1, just ensure department is set correctly
                $existingPrimaryAdmin->update(['department_id' => $primaryAdminDeptId]);
            }
        } else {
            // No Primary Administrator exists, create with ID 1
            // First ensure ID 1 is empty
            $roleAtId1 = Role::find(1);
            if ($roleAtId1) {
                $roleAtId1->delete();
            }
            
            Role::create([
                'id' => 1,
                'name' => 'Primary Administrator',
                'department_id' => $primaryAdminDeptId,
                'description' => 'System administrator with full access'
            ]);
        }

        $roleDepartmentMap = [
            'Enrollment' => 'Enrollment',
            'Finance and Payments' => 'Finance and Payments',
            'Scholarships' => 'Scholarships',
            'Academic Concerns' => 'Academic Concerns',
            'Exams' => 'Exams',
            'Student Services' => 'Student Services',
            'Library Services' => 'Library Services',
            'IT Support' => 'IT Support',
            'Graduation' => 'Graduation',
            'Athletics and Sports' => 'Athletics and Sports'
        ];

        $roles = [];

        // Only attempt to read legacy `users.role` when the column still exists.
        if (Schema::hasColumn('users', 'role')) {
            try {
                $roles = DB::table('users')->whereNotNull('role')->distinct()->pluck('role')->toArray();
                // Filter out "Unassigned" from legacy roles
                $roles = array_filter($roles, fn($r) => $r !== 'Unassigned');
            } catch (\Throwable $e) {
                // In case of any unexpected error, fall back to defaults below.
                $roles = [];
            }
        }

        if (empty($roles)) {
            $roles = array_keys($roleDepartmentMap);
        }

        foreach ($roles as $r) {
            // Skip if Primary Administrator (already handled above)
            if ($r === 'Primary Administrator') {
                continue;
            }

            // Skip if role already exists
            if (Role::where('name', $r)->exists()) {
                continue;
            }

            $departmentName = $roleDepartmentMap[$r] ?? null;
            $departmentId = null;
            
            if ($departmentName) {
                $department = Department::where('name', $departmentName)->first();
                if ($department) {
                    $departmentId = $department->id;
                }
            }

            Role::create([
                'name' => $r, 
                'description' => null, 
                'department_id' => $departmentId
            ]);
        }

        // Ensure Primary Administrator user (ID 1) has the Primary Administrator role
        $primaryAdminRole = Role::where('name', 'Primary Administrator')->first();
        if ($primaryAdminRole) {
            // Check if user ID 1 exists
            $adminUser = DB::table('users')->where('id', 1)->first();
            if ($adminUser) {
                // Check if user_roles entry already exists
                $existingUserRole = DB::table('user_roles')
                    ->where('user_id', 1)
                    ->where('role_id', $primaryAdminRole->id)
                    ->first();
                
                if (!$existingUserRole) {
                    DB::table('user_roles')->insert([
                        'user_id' => 1,
                        'role_id' => $primaryAdminRole->id,
                        'department_id' => $primaryAdminRole->department_id,
                        'created_at' => now(),
                        'updated_at' => now()
                    ]);
                } else {
                    // Update existing entry with department_id
                    DB::table('user_roles')
                        ->where('user_id', 1)
                        ->where('role_id', $primaryAdminRole->id)
                        ->update(['department_id' => $primaryAdminRole->department_id]);
                }
            }
        }
    }
}
