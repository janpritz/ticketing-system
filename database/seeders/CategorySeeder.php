<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Category;
use App\Models\Role;

class CategorySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Extra categories for some well-known roles
        $extras = [
            'Enrollment' => [
                ['name' => 'Admission Requirements', 'description' => 'Questions about admission requirements.'],
                ['name' => 'Application Status', 'description' => 'Status of applications.'],
            ],
            'Finance and Payments' => [
                ['name' => 'Tuition and Fees', 'description' => 'Billing and payment queries.'],
            ],
            'IT Support' => [
                ['name' => 'System Access', 'description' => 'Login and access issues.'],
            ],
            'Student Services' => [
                ['name' => 'Counseling', 'description' => 'Student counseling and support services.'],
            ],
        ];

        // Ensure a default role-specific category exists for every role created by the roles seeder
        $roles = Role::orderBy('name')->get();
        foreach ($roles as $role) {
            // Use a role-specific default category name instead of a generic "General"
            $defaultName = $role->name . ' Inquiries';
            Category::updateOrCreate(
                ['name' => $defaultName, 'role_id' => $role->id],
                ['description' => 'Inquiries for the ' . $role->name . ' role.']
            );

            if (isset($extras[$role->name])) {
                foreach ($extras[$role->name] as $c) {
                    Category::updateOrCreate(
                        ['name' => $c['name'], 'role_id' => $role->id],
                        ['description' => $c['description'] ?? null]
                    );
                }
            }
        }
    }
}