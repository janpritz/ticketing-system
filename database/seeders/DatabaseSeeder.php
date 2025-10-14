<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Role;
// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // User::factory(10)->create();

        // Ensure Primary Administrator role exists and Primary Administrator user is present
        $adminRole = Role::firstOrCreate(['name' => 'Primary Administrator']);

        User::updateOrCreate(
            ['email' => 'acc.sangkaychatbot@gmail.com'],
            [
                'name' => 'Primary Administrator',
                'password' => Hash::make('ACCSangkay2025'),
                'role_id' => $adminRole->id,
            ]
        );

        // Create or update sample staff users with different roles
        $roles = [
            'Enrollment',
            'Finance and Payments',
            'Scholarships',
            'Academic Concerns',
            'Exams',
            'Student Services',
            'Library Services',
            'IT Support',
            'Student Affairs',
            'Graduation',
            'Athletics and Sports'
        ];

        foreach ($roles as $roleName) {
            $email = strtolower(str_replace(' ', '.', $roleName)) . '@example.com';

            // Ensure role record exists
            $r = Role::firstOrCreate(['name' => $roleName]);

            User::updateOrCreate(
                ['email' => $email],
                [
                    'name' => $roleName . ' Staff',
                    'password' => Hash::make('password123'),
                    'role_id' => $r->id,
                ]
            );
        }

        // Seed FAQs from response.yml (if present)
        if (class_exists(\Database\Seeders\FaqSeeder::class)) {
            $this->call(\Database\Seeders\FaqSeeder::class);
        }
    }
}
