<?php

namespace App\Http\Controllers;

use App\Models\Ticket;
use Illuminate\Http\Request;
use App\Models\User;
use App\Models\Role;
use App\Models\Category;
use App\Models\TicketRoutingHistory;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Cache;
use App\Jobs\ProcessTicketCreation;

class TicketController extends Controller
{
    // Show the ticket creation form
    public function showCreateForm(Request $request, $recepient_id = null)
    {
        // Check if the user has reached the maximum number of tickets (4 tickets max)
        // We need email from the request since it's not in the URL
        $email = $request->query('email');
        if ($email) {
            $ticketCount = Ticket::where('email', $email)->count();
            if ($ticketCount >= 4) {
                return redirect()->to(url('/tickets/' . urlencode($email)))->with('error', 'You have reached the maximum number of tickets (4). Please wait for responses to your existing tickets.');
            }
        }

        // Fetch categories from DB at page load (we show categories only; role is resolved from category)
        $categories = Category::orderBy('name')->pluck('name')->toArray();

        return view('tickets.create', compact('recepient_id', 'categories', 'email'));
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
            'attachments' => 'nullable|array|max:5',
            'attachments.*' => 'image|mimes:jpeg,png,jpg,gif|max:5120', // 5MB max
            'g-recaptcha-response' => 'required|captcha',
        ]);

        // Check if the user has reached the maximum number of tickets (4 tickets max per email)
        $ticketCount = Ticket::where('email', $request->email)->count();

        if ($ticketCount >= 4) {
            // If there are already 4 or more tickets, redirect to tickets page with email
            if ($request->wantsJson()) {
                return response()->json(['error' => 'You have reached the maximum number of tickets (4). Please wait for responses to your existing tickets.', 'redirect' => url('/tickets/' . urlencode($request->email))], 400);
            } else {
                return redirect()->to(url('/tickets/' . urlencode($request->email)))->with('error', 'You have reached the maximum number of tickets (4). Please wait for responses to your existing tickets.');
            }
        }

        // Handle attachments first
        $attachmentsPaths = [];
        if ($request->hasFile('attachments')) {
            foreach ($request->file('attachments') as $file) {
                $filename = time() . '_' . uniqid() . '.' . $file->getClientOriginalExtension();
                $path = $file->storeAs('attachments', $filename, 'public');
                $attachmentsPaths[] = $path;
            }
        }

        $ticket = Ticket::create([
            'category' => $request->category,
            'question' => $request->question,
            'recepient_id' => $request->recepient_id,
            'email' => $request->email,
            'status' => 'Open',
            'staff_id' => null, // will be set by job
            'date_created' => now(),
            'date_closed' => null,
            'attachments' => json_encode($attachmentsPaths),
        ]);

        // Clear tickets cache on creation
        Cache::flush();

        // Dispatch job to process the rest
        ProcessTicketCreation::dispatch($ticket->id, $request->category);

        // For API requests, return JSON
        if ($request->wantsJson()) {
            // Include assigned staff explicitly for client-side flows (AJAX form)
            return response()->json(['ticket' => $ticket, 'staff_id' => $ticket->staff_id], 201);
        }

        // For web requests, redirect to tickets page for the recepient id.
        // Generate a full URL using the configured app URL so it becomes {APP_URL}/tickets/{recepient_id}
        return redirect()->to(url('/tickets/' . $request->recepient_id))
            ->with('success', 'Ticket created successfully! Please wait for a response, which will be sent to your email.');
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

    public function index(Request $request, $identifier = null)
    {
        // Support both recepient_id and email as identifier
        $identifier = $identifier ?? $request->query('email') ?? $request->recepient_id;

        if (!$identifier) {
            return redirect()->route('login')->with('error', 'Invalid access. Please provide a valid identifier.');
        }

        // Determine if identifier is email or recepient_id
        $isEmail = filter_var($identifier, FILTER_VALIDATE_EMAIL);

        // Cache key for user tickets
        $cacheKey = 'user_tickets_' . ($isEmail ? 'email_' : 'recepient_') . $identifier;

        // For API requests, return JSON
        if ($request->wantsJson()) {
            // Retrieve all tickets for the specified identifier with caching
            $tickets = Cache::remember($cacheKey, 20, function () use ($identifier, $isEmail) {
                $query = Ticket::query();
                if ($isEmail) {
                    $query->where('email', $identifier);
                } else {
                    $query->where('recepient_id', $identifier);
                }
                return $query->orderBy('date_created', 'desc')->get();
            });
            return response()->json($tickets);
        }

        // For web requests, return a view with the tickets
        $tickets = Cache::remember($cacheKey, 20, function () use ($identifier, $isEmail) {
            $query = Ticket::query();
            if ($isEmail) {
                $query->where('email', $identifier);
            } else {
                $query->where('recepient_id', $identifier);
            }
            return $query->orderBy('date_created', 'desc')->get();
        });
        return view('tickets.index', compact('tickets', 'identifier', 'isEmail'));
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

        // Clear tickets cache on update
        Cache::flush();

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

        // Clear tickets cache on delete
        Cache::flush();

        if ($request->wantsJson()) {
            return response()->json(['deleted' => true]);
        }

        return redirect()->back()->with('success', 'Ticket deleted successfully!');
    }
}
