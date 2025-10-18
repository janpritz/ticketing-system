<?php

namespace App\Http\Controllers;

use App\Models\Ticket;
use Illuminate\Http\Request;
use App\Models\User;
use App\Models\Role;
use App\Models\Category;
use App\Models\TicketRoutingHistory;
use Illuminate\Support\Facades\Log;



class TicketController extends Controller
{
    // Show the ticket creation form
    public function showCreateForm($recepient_id = null)
    {
        // Fetch categories from DB at page load (we show categories only; role is resolved from category)
        $categories = Category::orderBy('name')->pluck('name')->toArray();

        return view('tickets.create', compact('recepient_id', 'categories'));
    }

    public function store(Request $request)
    {
        // Dump and die to inspect request data
        //dd($request->all());  // This will stop execution and dump the data to the browser

        $request->validate([
            'category' => 'required|string|max:255',
            'question' => 'required|string',
            'recepient_id' => ['required'],
            'email' => 'required|email|max:255',
        ]);

        // Check if the user already has an open ticket
        $existingOpenTicket = Ticket::where('recepient_id', $request->recepient_id)
            ->where('status', 'Open')
            ->first();

        if ($existingOpenTicket) {
            // If there is already an open ticket, prevent creating a new one and return an error
            if ($request->wantsJson()) {
                return response()->json(['error' => 'You already have an open ticket. Please wait for the response.'], 400);
            } else {
                return redirect()->route('tickets.index', ['recepient_id' => $request->recepient_id])->with('error', 'You already have an open ticket. Please wait for a response.');
            }
        }

        // Determine role based on the selected category (lookup from DB).
        // Role selection via the form has been removed; we resolve role by the category chosen.
        $roleModel = null;
        $categoryModel = Category::where('name', $request->category)->with('role')->first();
        if ($categoryModel && $categoryModel->role) {
            $roleModel = $categoryModel->role;
        } else {
            // Fallback to Primary Administrator if no mapping exists.
            $roleModel = Role::where('name', 'Primary Administrator')->first();
        }

        // Find staff with the lowest open-ticket load within the selected role
        $staff = null;
        if ($roleModel) {
            $candidates = User::where('role_id', $roleModel->id)
                ->withCount(['assignedTickets as open_tickets_count' => function ($q) {
                    $q->where('status', 'Open');
                }])
                ->get();

            if ($candidates->isNotEmpty()) {
                // pick the minimum load then randomize among equals to avoid hot-spotting a single user
                $min = $candidates->min('open_tickets_count');
                $ties = $candidates->where('open_tickets_count', $min);
                $staff = $ties->count() ? $ties->random() : $ties->first();
            }
        }

        $ticket = Ticket::create([
            'category' => $request->category,
            'question' => $request->question,
            'recepient_id' => $request->recepient_id,
            'email' => $request->email,
            'status' => 'Open',
            'staff_id' => $staff ? $staff->id : null,
            'date_created' => now(),
            'date_closed' => null,
        ]);

        // Record initial routing history at ticket creation
        TicketRoutingHistory::create([
            'ticket_id' => $ticket->id,
            'staff_id' => $ticket->staff_id, // may be null if not assigned
            'status' => 'Open',
            'routed_at' => now(),
            'notes' => 'Ticket created' . ($ticket->staff_id ? ' and assigned' : ''),
        ]);
        
        // Send push notification to the assigned staff (if any)
        if ($ticket->staff_id) {
            try {
                $payload = [
                    'title' => 'New ticket assigned',
                    'body'  => 'A new ticket (ID: ' . $ticket->id . ') has been assigned to you.',
                    'data'  => ['url' => '/staff/dashboard']
                ];
                // Use the PushService to deliver the notification (non-blocking on failure)
                app(\App\Services\PushService::class)->sendToUser($ticket->staff_id, $payload);
            } catch (\Throwable $e) {
                // Log and continue — notification failure must not block ticket creation
                Log::warning('Push send failed for ticket assignment: ' . $e->getMessage());
            }
        }
        
        // For API requests, return JSON
        if ($request->wantsJson()) {
            return response()->json($ticket, 201);
        }
        
        // For web requests, redirect to index page with success message
        return redirect()->route('tickets.index', ['recepient_id' => $request->recepient_id])->with('success', 'Ticket created successfully! Please wait for a response, which will be sent to your email.');
    }


    private function getCategoryToRoleMap(): array
    {
        // Centralised mapping between categories and role names.
        // Keep this in sync with any admin-managed 'roles' entries.
        return [
            // Enrollment-related categories
            'Course Registration' => 'Enrollment',
            'Add or Drop Classes' => 'Enrollment',
            'Late Enrollment' => 'Enrollment',
            'Shifting to a Different Program' => 'Enrollment',
            'Transferring Between Schools' => 'Enrollment',
            'Schedule Conflicts' => 'Enrollment',
            'Class Schedules' => 'Enrollment',
            'Course Prerequisites' => 'Enrollment',

            // Finance-related categories
            'Tuition Fee Inquiries' => 'Finance and Payments',
            'Payment Methods (Online, Bank, etc.)' => 'Finance and Payments',
            'Refund Issues' => 'Finance and Payments',
            'Billing and Invoice Problems' => 'Finance and Payments',

            // Scholarship-related categories
            'Scholarships & Financial Aid' => 'Scholarships',
            'Merit-Based Scholarships' => 'Scholarships',
            'Need-Based Scholarships' => 'Scholarships',
            'Scholarship Application Status' => 'Scholarships',
            'Eligibility and Deadlines for Scholarships' => 'Scholarships',
            'Scholarships for International Students' => 'Scholarships',
            'Sports Scholarships' => 'Scholarships',

            // Academic-related categories
            'Grades and Transcript Requests' => 'Academic Concerns',
            'Academic Probation or Warnings' => 'Academic Concerns',
            'Graduation Requirements' => 'Academic Concerns',
            'Thesis/Dissertation Submission' => 'Academic Concerns',

            // Exam-related categories
            'Exam Schedules' => 'Exams',
            'Exam Results' => 'Exams',
            'Re-scheduling Exams' => 'Exams',
            'Special Exam Accommodations' => 'Exams',

            // Student Services-related categories
            'Career Counseling' => 'Student Services',
            'Student Organizations & Activities' => 'Student Services',
            'Mental Health Support' => 'Student Services',
            'Peer Mentoring' => 'Student Services',
            'Internship Assistance' => 'Student Services',
            'Student Life Events' => 'Student Services',
            'Student Rights and Responsibilities' => 'Student Services',
            'Code of Conduct Violations' => 'Student Services',
            'Disciplinary Actions' => 'Student Services',
            'Visa Assistance' => 'Student Services',
            'Cultural Integration Support' => 'Student Services',
            'Study Abroad Programs' => 'Student Services',
            'Alumni Services' => 'Student Services',

            // Library Services-related categories
            'Book Borrowing' => 'Library Services',
            'Access to Digital Resources' => 'Library Services',
            'Study Room Reservations' => 'Library Services',
            'Library Fees and Fines' => 'Library Services',
            'Research Assistance' => 'Library Services',

            // IT Support-related categories
            'Wi-Fi Issues' => 'IT Support',
            'Software Installation' => 'IT Support',
            'Email Issues' => 'IT Support',
            'Computer Lab Problems' => 'IT Support',
            'Learning Management System (LMS) Issues' => 'IT Support',

            // Graduation-related categories
            'Commencement Exercises' => 'Graduation',
            'Diploma Requests' => 'Graduation',

            // Athletics and Sports-related categories
            'Sports Club Registration' => 'Athletics and Sports',
            'Physical Education Classes' => 'Athletics and Sports',
            'Sports Event Tickets' => 'Athletics and Sports',
        ];
    }

    public function index(Request $request)
    {
        $recepient_id = $request->recepient_id;

        // For API requests, return JSON
        if ($request->wantsJson()) {
            // Retrieve all tickets for the specified recepient_id
            $tickets = Ticket::where('recepient_id', $recepient_id)->get();
            return response()->json($tickets);
        }

        // For web requests, return a view with the tickets
        $tickets = Ticket::where('recepient_id', $recepient_id)->get();
        return view('tickets.index', compact('tickets'));
    }


    public function updateStatus(Request $request, $id)
    {
        $request->validate([
            'status' => 'required|in:Open,Closed,Re-Routed'
        ]);

        $user = request()->user();
        $ticket = Ticket::where('user_id', $user->id)->findOrFail($id);

        $ticket->update(['status' => $request->status]);

        // For API requests, return JSON
        if ($request->wantsJson()) {
            return response()->json($ticket);
        }

        // For web requests, redirect back
        return redirect()->back()->with('success', 'Ticket status updated successfully!');
    }

    public function update(Request $request, $id)
    {
        // Only allow editing of the question; category must remain unchanged
        $request->validate([
            'question' => 'required|string',
        ]);

        $ticket = Ticket::findOrFail($id);

        $ticket->update([
            'question' => $request->question,
        ]);

        if ($request->wantsJson()) {
            return response()->json($ticket);
        }

        return redirect()->back()->with('success', 'Ticket updated successfully!');
    }

    public function destroy(Request $request, $id)
    {
        $ticket = Ticket::findOrFail($id);

        // Delete the ticket
        $ticket->delete();

        if ($request->wantsJson()) {
            return response()->json(['deleted' => true]);
        }

        return redirect()->back()->with('success', 'Ticket deleted successfully!');
    }
}
