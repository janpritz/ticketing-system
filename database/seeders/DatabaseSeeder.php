<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Role;
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
        // Seed roles first
        if (class_exists(\Database\Seeders\RolesSeeder::class)) {
            $this->call(\Database\Seeders\RolesSeeder::class);
        }

        // Seed categories
        if (class_exists(\Database\Seeders\CategorySeeder::class)) {
            $this->call(\Database\Seeders\CategorySeeder::class);
        }

        // Ensure Primary Administrator role exists and Primary Administrator user is present with id 1
        $adminRole = Role::where('name', 'Primary Administrator')->first();

        if (!$adminRole) {
            $adminRole = Role::create(['name' => 'Primary Administrator']);
        }

        // Ensure admin user has id 1
        $adminUser = User::find(1);
        if (!$adminUser) {
            DB::table('users')->insert([
                'id' => 1,
                'name' => 'Primary Administrator',
                'email' => 'acc.sangkaychatbot@gmail.com',
                'password' => Hash::make('ACCSangkay2025'),
                'role_id' => $adminRole->id,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        } else {
            $adminUser->update([
                'name' => 'Primary Administrator',
                'password' => Hash::make('ACCSangkay2025'),
                'role_id' => $adminRole->id,
            ]);
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

            User::updateOrCreate(
                ['email' => $staffInfo['email']],
                [
                    'name' => $staffInfo['name'],
                    'password' => Hash::make('password123'),
                    'role_id' => $r->id,
                ]
            );
        }
    }
}
