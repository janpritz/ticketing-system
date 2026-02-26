<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Role;
use App\Models\Department;
// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // Seed departments first
        if (class_exists(\Database\Seeders\DepartmentSeeder::class)) {
            $this->call(\Database\Seeders\DepartmentSeeder::class);
        }

        // Seed roles
        if (class_exists(\Database\Seeders\RolesSeeder::class)) {
            $this->call(\Database\Seeders\RolesSeeder::class);
        }

        // Update existing user_roles entries that don't have is_primary_role set
        // Set the first role for each user as primary
        $userRoleGroups = DB::table('user_roles')
            ->select('user_id', DB::raw('MIN(id) as first_role_id'))
            ->groupBy('user_id')
            ->get();

        foreach ($userRoleGroups as $group) {
            DB::table('user_roles')
                ->where('user_id', $group->user_id)
                ->where('id', $group->first_role_id)
                ->update(['is_primary_role' => true]);
        }

        // Ensure Primary Administrator role exists and Primary Administrator user is present with id 1
        $adminRole = Role::where('name', 'Primary Administrator')->first();

        if (!$adminRole) {
            $adminRole = Role::create(['name' => 'Primary Administrator']);
        }

        // Get Primary Administrator department
        $adminDepartment = Department::where('name', 'Primary Administrator')->first();

        // Ensure admin user has id 1
        $adminUser = User::find(1);
        if (!$adminUser) {
            DB::table('users')->insert([
                'id' => 1,
                'name' => 'Primary Administrator',
                'email' => 'acc.sangkaychatbot@gmail.com',
                'password' => Hash::make('ACCSangkay2025'),
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        } else {
            $adminUser->update([
                'name' => 'Primary Administrator',
                'password' => Hash::make('ACCSangkay2025'),
            ]);
        }

        // Create user_roles entry for admin with role_id = 1
        if ($adminUser && $adminRole) {
            // Get Primary Administrator department ID
            $adminDeptId = $adminDepartment ? $adminDepartment->id : null;
            
            // First check if the user already has role_id = 1
            $existingRole = DB::table('user_roles')
                ->where('user_id', $adminUser->id)
                ->where('role_id', 1)
                ->first();
            
            if (!$existingRole) {
                DB::table('user_roles')->insert([
                    'user_id' => $adminUser->id,
                    'role_id' => 1,
                    'department_id' => $adminDeptId,
                    'is_primary_role' => true,
                    'created_at' => now(),
                    'updated_at' => now()
                ]);
            } else {
                // Update existing entry to set is_primary_role and department_id
                DB::table('user_roles')
                    ->where('user_id', $adminUser->id)
                    ->where('role_id', 1)
                    ->update([
                        'department_id' => $adminDeptId,
                        'is_primary_role' => true
                    ]);
            }
        }

        // Create or update sample staff users with different roles
        $staffData = [
            'Enrollment' => ['name' => 'Maria Santos', 'email' => 'maria.santos@example.com'],
            'Finance and Payments' => ['name' => 'Juan Dela Cruz', 'email' => 'juan.delacruz@example.com'],
            'Scholarships' => ['name' => 'Ana Reyes', 'email' => 'ana.reyes@example.com'],
            'Academic Concerns' => ['name' => 'Pedro Gonzales', 'email' => 'pedro.gonzales@example.com'],
            'Exams' => ['name' => 'Luisa Mendoza', 'email' => 'luisa.mendoza@example.com'],
            'Student Services' => ['name' => 'Carlos Bautista', 'email' => 'carlos.bautista@example.com'],
            'Library Services' => ['name' => 'Rosa Lim', 'email' => 'rosa.lim@example.com'],
            'IT Support' => ['name' => 'Jose Santos', 'email' => 'jose.santos@example.com'],
            'Student Affairs' => ['name' => 'Carmen Torres', 'email' => 'carmen.torres@example.com'],
            'Graduation' => ['name' => 'Ramon Cruz', 'email' => 'ramon.cruz@example.com'],
            'Athletics and Sports' => ['name' => 'Mariano Reyes', 'email' => 'mariano.reyes@example.com']
        ];

        foreach ($staffData as $roleName => $staffInfo) {
            // Ensure role record exists
            $r = Role::firstOrCreate(['name' => $roleName]);
            
            // Get department for this role
            $department = Department::where('name', $roleName)->first();

            $user = User::updateOrCreate(
                ['email' => $staffInfo['email']],
                [
                    'name' => $staffInfo['name'],
                    'password' => Hash::make('password123'),
                ]
            );

            // Create user_roles entry with department_id and is_primary_role
            if ($user && $r && $department) {
                DB::table('user_roles')->updateOrInsert(
                    ['user_id' => $user->id, 'role_id' => $r->id],
                    [
                        'department_id' => $department->id,
                        'is_primary_role' => true,
                        'created_at' => now(), 
                        'updated_at' => now()
                    ]
                );
            }
        }
    }
}
