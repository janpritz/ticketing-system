<?php

namespace Database\Seeders;

use App\Models\Ticket;
use App\Models\StagedFaq;
use App\Models\Role;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class TicketSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Get some roles for the tickets
        $roles = Role::all();
        
        if ($roles->isEmpty()) {
            $this->command->warn('No roles found. Please run RolesSeeder first.');
            return;
        }

        // Get a staff user to assign tickets to
        $staffUser = User::where('id', '>', 1)->first();

        // Create sample tickets with staged FAQs
        $ticketsData = [
            [
                'role' => 'Enrollment',
                'question' => 'How do I enroll for the next semester?',
                'response' => 'You can enroll through the student portal during the enrollment period.',
                'email' => 'student1@example.com',
                'status' => 'closed',
                'staged_faqs' => [
                    [
                        'general_topic' => 'Enrollment',
                        'semantic_key' => 'enrollment_process',
                        'suggested_q' => 'How do I enroll for the next semester?',
                        'suggested_a' => 'You can enroll through the student portal during the enrollment period. Visit the enrollment section and follow the guided steps.',
                        'status' => 'approved'
                    ],
                    [
                        'general_topic' => 'Enrollment',
                        'semantic_key' => 'enrollment_requirements',
                        'suggested_q' => 'What are the requirements for enrollment?',
                        'suggested_a' => 'You need your student ID, previous grades, and proof of payment for the enrollment fee.',
                        'status' => 'approved'
                    ]
                ]
            ],
            [
                'role' => 'Finance and Payments',
                'question' => 'Where can I pay my tuition fees?',
                'response' => 'Tuition fees can be paid at the finance office or online through the payment portal.',
                'email' => 'student2@example.com',
                'status' => 'closed',
                'staged_faqs' => [
                    [
                        'general_topic' => 'Payments',
                        'semantic_key' => 'tuition_payment',
                        'suggested_q' => 'Where can I pay my tuition fees?',
                        'suggested_a' => 'Tuition fees can be paid at the finance office or online through the payment portal using credit/debit card.',
                        'status' => 'approved'
                    ]
                ]
            ],
            [
                'role' => 'Scholarships',
                'question' => 'What scholarships are available for new students?',
                'response' => 'We offer merit-based and need-based scholarships for eligible students.',
                'email' => 'student3@example.com',
                'status' => 'closed',
                'staged_faqs' => [
                    [
                        'general_topic' => 'Scholarships',
                        'semantic_key' => 'new_student_scholarships',
                        'suggested_q' => 'What scholarships are available for new students?',
                        'suggested_a' => 'We offer merit-based scholarships for high achievers and need-based scholarships for students with financial difficulties.',
                        'status' => 'approved'
                    ],
                    [
                        'general_topic' => 'Scholarships',
                        'semantic_key' => 'scholarship_requirements',
                        'suggested_q' => 'What are the requirements for scholarships?',
                        'suggested_a' => 'Requirements vary by scholarship. Generally, you need a good academic record and meet the specific criteria for each scholarship program.',
                        'status' => 'pending'
                    ]
                ]
            ],
            [
                'role' => 'Academic Concerns',
                'question' => 'How can I drop a subject?',
                'response' => 'You can drop a subject within the first two weeks of the semester without penalty.',
                'email' => 'student4@example.com',
                'status' => 'closed',
                'staged_faqs' => [
                    [
                        'general_topic' => 'Academics',
                        'semantic_key' => 'drop_subject',
                        'suggested_q' => 'How can I drop a subject?',
                        'suggested_a' => 'You can drop a subject within the first two weeks of the semester without penalty. After that period, grades may apply.',
                        'status' => 'approved'
                    ]
                ]
            ],
            [
                'role' => 'Exams',
                'question' => 'When are the final exams scheduled?',
                'response' => 'Final exams are scheduled during the last week of the semester.',
                'email' => 'student5@example.com',
                'status' => 'closed',
                'staged_faqs' => [
                    [
                        'general_topic' => 'Exams',
                        'semantic_key' => 'final_exams_schedule',
                        'suggested_q' => 'When are the final exams scheduled?',
                        'suggested_a' => 'Final exams are scheduled during the last week of the semester. Check the academic calendar for specific dates.',
                        'status' => 'approved'
                    ]
                ]
            ],
            [
                'role' => 'IT Support',
                'question' => 'How do I reset my student email password?',
                'response' => 'You can reset your password through the IT portal or contact IT support.',
                'email' => 'student6@example.com',
                'status' => 'closed',
                'staged_faqs' => [
                    [
                        'general_topic' => 'IT Support',
                        'semantic_key' => 'email_password_reset',
                        'suggested_q' => 'How do I reset my student email password?',
                        'suggested_a' => 'You can reset your password through the IT portal at it-support.edu/reset or contact IT support at it-helpdesk@example.edu.',
                        'status' => 'approved'
                    ]
                ]
            ]
        ];

        foreach ($ticketsData as $ticketData) {
            $roleName = $ticketData['role'];
            $role = $roles->firstWhere('name', $roleName);
            
            if (!$role) {
                continue;
            }

            // Create ticket
            $ticket = Ticket::create([
                'role_id' => $role->id,
                'question' => $ticketData['question'],
                'response' => $ticketData['response'],
                'recepient_id' => $role->id, // Use role_id as recipient
                'email' => $ticketData['email'],
                'status' => $ticketData['status'],
                'staff_id' => $staffUser ? $staffUser->id : null,
                'date_created' => now(),
                'date_closed' => now(),
            ]);

            // Create staged FAQs for this ticket
            if (isset($ticketData['staged_faqs'])) {
                foreach ($ticketData['staged_faqs'] as $faqData) {
                    StagedFaq::create([
                        'ticket_id' => $ticket->id,
                        'general_topic' => $faqData['general_topic'],
                        'semantic_key' => $faqData['semantic_key'],
                        'suggested_q' => $faqData['suggested_q'],
                        'suggested_a' => $faqData['suggested_a'],
                        'status' => $faqData['status'],
                    ]);
                }
            }
        }

        $this->command->info('Ticket seeder completed successfully!');
    }
}
