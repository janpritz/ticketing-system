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
     * This will attempt to populate roles from existing users.role values (legacy).
     * If the users.role column is already removed (migration ran), fall back to a sensible default list.
     */
    public function run()
    {
        $roleDepartmentMap = [
            'Primary Administrator' => 'Primary Administrator',
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
            } catch (\Throwable $e) {
                // In case of any unexpected error, fall back to defaults below.
                $roles = [];
            }
        }

        if (empty($roles)) {
            $roles = array_keys($roleDepartmentMap);
        }

        foreach ($roles as $r) {
            $departmentName = $roleDepartmentMap[$r] ?? null;
            $departmentId = null;
            
            if ($departmentName) {
                $department = Department::where('name', $departmentName)->first();
                if ($department) {
                    $departmentId = $department->id;
                }
            }

            Role::firstOrCreate(
                ['name' => $r], 
                ['description' => null, 'department_id' => $departmentId]
            );
        }
    }
}
