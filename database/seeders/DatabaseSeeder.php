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

        // Ensure Primary Administrator exists (update or create to avoid duplicate seed failures)
        User::updateOrCreate(
            ['email' => 'acc.sangkaychatbot@gmail.com'],
            [
                'name' => 'Primary Administrator',
                'password' => Hash::make('ACCSangkay2025'),
                'role' => 'Primary Administrator',
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

        foreach ($roles as $role) {
            $email = strtolower(str_replace(' ', '.', $role)) . '@example.com';
            User::updateOrCreate(
                ['email' => $email],
                [
                    'name' => $role . ' Staff',
                    'password' => Hash::make('password123'),
                    'role' => $role,
                ]
            );
        }

        // Seed FAQs from response.yml (if present)
        if (class_exists(\Database\Seeders\FaqSeeder::class)) {
            $this->call(\Database\Seeders\FaqSeeder::class);
        }
    }
}
