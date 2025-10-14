<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use App\Models\Role;

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
            $roles = [
                'Primary Administrator',
                'Enrollment',
                'Finance and Payments',
                'Scholarships',
                'Academic Concerns',
                'Exams',
                'Student Services',
                'Library Services',
                'IT Support',
                'Graduation',
                'Athletics and Sports'
            ];
        }

        foreach ($roles as $r) {
            Role::firstOrCreate(['name' => $r], ['description' => null]);
        }
    }
}