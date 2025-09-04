<?php

namespace App\Http\Controllers;

use App\Models\Ticket;
use Illuminate\Http\Request;
use App\Models\User;


class TicketController extends Controller
{
    // Show the ticket creation form
    public function showCreateForm($recepient_id = null)
    {
        return view('tickets.create', compact('recepient_id')); // returns the Blade view for the form
    }

    public function store(Request $request)
    {
        // Dump and die to inspect request data
        //dd($request->all());  // This will stop execution and dump the data to the browser

        $request->validate([
            'category' => 'required|string|max:255',
            'question' => 'required|string',
            'recepient_id' => ['required', 'string', 'regex:/^[0-9]+$/'],  // Only numbers allowed as string
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

        // Map categories to staff roles
        $categoryToRoleMap = [
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

        // Determine the role based on category
        $role = $categoryToRoleMap[$request->category] ?? 'Primary Administrator'; // Default to Student Services if no match

        // Find an available staff member with the required role
        $staff = User::where('role', $role)->inRandomOrder()->first();

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

        // For API requests, return JSON
        if ($request->wantsJson()) {
            return response()->json($ticket, 201);
        }

        // For web requests, redirect to index page with success message
        return redirect()->route('tickets.index', ['recepient_id' => $request->recepient_id])->with('success', 'Ticket created successfully! Please wait for a response, which will be sent to your email.');
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
            'status' => 'required|in:open,closed,in-progress'
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
        $request->validate([
            'category' => 'required|string|max:255',
            'question' => 'required|string',
        ]);

        $ticket = Ticket::findOrFail($id);

        $ticket->update([
            'category' => $request->category,
            'question' => $request->question,
        ]);

        return redirect()->back()->with('success', 'Ticket updated successfully!');
    }

    public function destroy(Request $request, $id)
    {
        $ticket = Ticket::findOrFail($id);

        // Delete the ticket
        $ticket->delete();

        return redirect()->back()->with('success', 'Ticket deleted successfully!');
    }
}
