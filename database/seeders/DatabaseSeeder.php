<?php

namespace Database\Seeders;

use App\Models\User;
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

        User::factory()->create([
            'name' => 'Primary Administrator',
            'email' => 'acc.sangkaychatbot@gmail.com',
            'password' => Hash::make('ACCSangkay2025'),
            'role' => 'Primary Administrator',
        ]);

        // Create sample staff users with different roles
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

        foreach ($roles as $role) {
            User::factory()->create([
                'name' => $role . ' Staff',
                'email' => strtolower(str_replace(' ', '.', $role)) . '@example.com',
                'password' => Hash::make('password123'),
                'role' => $role,
            ]);
        }
    }
}
