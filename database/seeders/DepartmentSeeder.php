<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use App\Models\Department;

class DepartmentSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $departments = [
            [
                'name' => 'Enrollment',
                'description' => 'Handles enrollment, registration, and course-related inquiries'
            ],
            [
                'name' => 'Finance and Payments',
                'description' => 'Handles tuition fees, payments, refunds, and billing'
            ],
            [
                'name' => 'Scholarships',
                'description' => 'Handles scholarship applications and financial aid'
            ],
            [
                'name' => 'Academic Concerns',
                'description' => 'Handles grades, transcripts, graduation, and academic issues'
            ],
            [
                'name' => 'Exams',
                'description' => 'Handles exam schedules, results, and accommodations'
            ],
            [
                'name' => 'Student Services',
                'description' => 'Handles student life, counseling, activities, and support services'
            ],
            [
                'name' => 'Library Services',
                'description' => 'Handles library resources, borrowing, and research assistance'
            ],
            [
                'name' => 'IT Support',
                'description' => 'Handles technical support, Wi-Fi, software, and hardware issues'
            ],
            [
                'name' => 'Graduation',
                'description' => 'Handles commencement, diplomas, and graduation requirements'
            ],
            [
                'name' => 'Athletics and Sports',
                'description' => 'Handles sports clubs, PE classes, and athletic events'
            ],
            [
                'name' => 'Primary Administrator',
                'description' => 'System administration and overall management'
            ],
        ];

        foreach ($departments as $department) {
            Department::firstOrCreate(['name' => $department['name']], $department);
        }
    }
}
