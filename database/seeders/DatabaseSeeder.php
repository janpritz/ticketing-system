<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Role;
use App\Models\Department;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // 1. Seed departments and roles first
        if (class_exists(DepartmentSeeder::class)) {
            $this->call(DepartmentSeeder::class);
        }

        if (class_exists(RolesSeeder::class)) {
            $this->call(RolesSeeder::class);
        }

        // 2. Ensure "Primary Administrator" Department exists
        // This is the missing piece in many setups
        $adminDepartment = Department::firstOrCreate(['name' => 'Primary Administrator']);

        // 3. Ensure "Primary Administrator" Role exists
        $adminRole = Role::firstOrCreate(['name' => 'Primary Administrator']);

        // 4. Seed documents from docs folder
        if (class_exists(DocumentSeeder::class)) {
            $this->call(DocumentSeeder::class);
        }

        // 4. Create/Update the Primary Administrator User (ID: 1)
        // Using updateOrInsert to force ID 1 specifically
        DB::table('users')->updateOrInsert(
            ['id' => 1],
            [
                'name' => 'Primary Administrator',
                'email' => 'acc.sangkaychatbot@gmail.com',
                'password' => Hash::make('ACCSangkay2025'),
                'email_verified_at' => now(),
                'created_at' => now(),
                'updated_at' => now(),
            ]
        );

        // 5. Link Admin User to Admin Role & Department
        DB::table('user_roles')->updateOrInsert(
            ['user_id' => 1, 'role_id' => $adminRole->id],
            [
                'department_id' => $adminDepartment->id,
                'is_primary_role' => true,
                'created_at' => now(),
                'updated_at' => now()
            ]
        );

        // 6. Fix legacy primary roles for other users
        $this->fixPrimaryRoles();

        // 7. Seed Staff Data
        //$this->seedStaffUsers();
    }

    private function fixPrimaryRoles(): void
    {
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
    }

    private function seedStaffUsers(): void
    {
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
            $role = Role::firstOrCreate(['name' => $roleName]);
            $dept = Department::where('name', $roleName)->first();

            $user = User::updateOrCreate(
                ['email' => $staffInfo['email']],
                [
                    'name' => $staffInfo['name'],
                    'password' => Hash::make('password123'),
                ]
            );

            if ($dept) {
                DB::table('user_roles')->updateOrInsert(
                    ['user_id' => $user->id, 'role_id' => $role->id],
                    [
                        'department_id' => $dept->id,
                        'is_primary_role' => true,
                        'created_at' => now(),
                        'updated_at' => now()
                    ]
                );
            }
        }
    }
}
