<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Ticket;
use Illuminate\Support\Str;
use Illuminate\Support\Carbon;

class TicketSeeder extends Seeder
{
    public function run()
    {
        for ($i = 0; $i < 30; $i++) {
            list($question, $response) = $this->generateRandomQuestionAndResponse();
            
            Ticket::create([
                'category_id' => 1,  // Assuming category ID 1 exists; adjust if needed
                'question' => $question,
                'response' => $response,
                'recepient_id' => 1,  // Assuming a default recipient ID; adjust if needed
                'email' => 'user' . $i . '@campus.edu',
                'status' => 'Closed',
                'staff_id' => 1,  // Assuming staff ID 1 exists; adjust if needed
                'date_created' => Carbon::now()->subDays(rand(1, 30)),
                'date_closed' => Carbon::now()->subDays(rand(0, 10)),
                'attachments' => null,  // No attachments for these seeds
            ]);
        }
    }

    private function generateRandomQuestionAndResponse()
    {
        $topics = [
            'Issue with dorm WiFi connectivity' => 'WiFi issue in dorms has been fixed by resetting the network and checking connections.',
            'Late library book return policy' => 'Late returns will incur a fine of $1 per day; extensions can be requested via the library portal.',
            'Campus shuttle schedule changes' => 'Shuttle schedule updated; new timings are available on the campus app for better routing.',
            'Problems with cafeteria meal options' => 'Meal options expanded based on feedback; vegetarian alternatives now include salads and wraps.',
            'Request for extension on assignment deadlines' => 'Extension granted for assignments due to unforeseen events; submit requests through the academic office.',
            'Noise complaints in student housing' => 'Noise levels monitored; quiet hours enforced from 10 PM to 8 AM as per housing regulations.',
            'Access issues to campus gym facilities' => 'Gym access restored after maintenance; ID cards required for entry starting next week.',
            'Questions about upcoming campus events' => 'Event details confirmed; registration open via the student portal for workshops and seminars.',
            'Technical problems with online learning portal' => 'Portal bugs resolved; users can now access courses without login errors.',
            'Concerns regarding campus security measures' => 'Security patrols increased around key areas; report incidents via the safety hotline for immediate action.'
        ];

        $key = array_rand($topics);
        return [$key . ' for building ' . Str::random(3), $topics[$key]];  // Append random string to question for uniqueness
    }
}
