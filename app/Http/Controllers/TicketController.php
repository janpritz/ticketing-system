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
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use App\Jobs\ProcessTicketCreation;

class TicketController extends Controller
{
    /**
     * Serve a stored ticket attachment from the public disk.
     *
     * This avoids relying on the `/public/storage` symlink (which is often missing
     * on shared hosting), preventing 404s when viewing attachments.
     */
    public function serveAttachment(Request $request, string $path)
    {
        // Only allow serving from the attachments directory
        if (!Str::startsWith($path, 'attachments/')) {
            abort(404);
        }

        $disk = Storage::disk('public');
        if (!$disk->exists($path)) {
            abort(404);
        }

        // Stream the file from storage without requiring the public/storage symlink.
        // (Using response()->file() keeps IDEs happy if they don't understand FilesystemAdapter::response())
        return response()->file($disk->path($path));
    }

    // Show the ticket creation form
    public function showCreateForm(Request $request, $recepient_id = null)
    {
        // Fetch categories from DB at page load (we show categories only; role is resolved from category)
        // Provide id => name pairs so the dropdown value is the category id
        $categories = Category::orderBy('name')->pluck('name', 'id')->toArray();

        $email = $request->query('email');

        return view('tickets.create', compact('recepient_id', 'categories', 'email'));
    }

    /**
     * Show ticket submission form after OTP verification
     */
    public function showTicketSubmitForm(Request $request, $recepient_id = null, $email)
    {
        // Fetch categories from DB (id => name) so the form submits category_id correctly
        $categories = Category::orderBy('name')->pluck('name', 'id')->toArray();

        return view('tickets.submit', compact('recepient_id', 'categories', 'email'));
    }

    /**
     * Handle ticket submission after OTP verification
     */
    public function submitTicket(Request $request)
    {
        $request->validate([
            'category_id' => 'nullable|integer|exists:categories,id',
            'question' => 'required|string',
            'recepient_id' => ['required'],
            'email' => 'required|email|max:255',
            'attachments' => 'nullable|array|max:5',
            'attachments.*' => 'image|mimes:jpeg,png,jpg,gif|max:5120', // 5MB max
            'g-recaptcha-response' => 'required|captcha',
        ]);

        // Handle attachments first
        $attachmentsPaths = [];
        if ($request->hasFile('attachments')) {
            foreach ($request->file('attachments') as $file) {
                $filename = time() . '_' . uniqid() . '.' . $file->getClientOriginalExtension();
                $path = $file->storeAs('attachments', $filename, 'public');
                $attachmentsPaths[] = $path;
            }
        }

        // Create ticket instance and set attributes explicitly so we can also persist the legacy
        // 'category' string for DB compatibility while using category_id as the source of truth.
        $ticket = new Ticket();
        $ticket->category_id = $request->input('category_id');
        $ticket->question = $request->question;
        $ticket->recepient_id = $request->recepient_id;
        $ticket->email = $request->email;
        $ticket->status = 'Open';
        $ticket->staff_id = null;
        $ticket->date_created = now();
        $ticket->date_closed = null;
        $ticket->attachments = json_encode($attachmentsPaths);

        // Legacy category string removed: category_id is the single source of truth.

        $ticket->save();


        // Process assignment synchronously so the page can show the ticket immediately
        try {
            (new ProcessTicketCreation($ticket->id, $request->input('category_id')))->handle();
        } catch (\Throwable $e) {
            // If synchronous processing fails, fall back to queueing the job and log the error
            \Illuminate\Support\Facades\Log::warning('Synchronous ticket processing failed; falling back to queued job: ' . $e->getMessage(), ['ticket_id' => $ticket->id]);
            ProcessTicketCreation::dispatch($ticket->id, $request->input('category_id'));
        }

        // For API requests, return JSON
        if ($request->wantsJson()) {
            // Include assigned staff explicitly for client-side flows (AJAX form)
            return response()->json([
                'ticket' => $ticket,
                'staff_id' => $ticket->staff_id,
                'message' => $ticket->staff_id
                    ? 'Ticket created and assigned to staff successfully!'
                    : 'Ticket created but assignment is pending. Staff will be assigned shortly.'
            ], 201);
        }

        // For web requests, redirect to tickets page for the recepient id.
        // Generate a full URL using the configured app URL so it becomes {APP_URL}/tickets/{recepient_id}
        $message = $ticket->staff_id
            ? 'Ticket created and assigned to staff successfully! Please wait for a response, which will be sent to your email.'
            : 'Ticket created successfully! Assignment is being processed and you will receive a response via email shortly.';
            
        // Include the creator email as a query parameter so the tickets index can immediately
        // resolve and display all tickets for that email without waiting for the background job.
        $redirectUrl = url('/tickets/' . $request->recepient_id) . '?email=' . rawurlencode($ticket->email);
        return redirect()->to($redirectUrl)
            ->with('success', $message);
    }


    public function store(Request $request)
    {
        // Dump and die to inspect request data
        //dd($request->all());  // This will stop execution and dump the data to the browser

        $request->validate([
            'category_id' => 'nullable|integer|exists:categories,id',
            'question' => 'required|string',
            'recepient_id' => ['required'],
            'email' => 'required|email|max:255',
            'attachments' => 'nullable|array|max:5',
            'attachments.*' => 'image|mimes:jpeg,png,jpg,gif|max:5120', // 5MB max
            'g-recaptcha-response' => 'required|captcha',
        ]);

        // Handle attachments first
        $attachmentsPaths = [];
        if ($request->hasFile('attachments')) {
            foreach ($request->file('attachments') as $file) {
                $filename = time() . '_' . uniqid() . '.' . $file->getClientOriginalExtension();
                $path = $file->storeAs('attachments', $filename, 'public');
                $attachmentsPaths[] = $path;
            }
        }

        $ticket = new Ticket();
        $ticket->category_id = $request->input('category_id');
        $ticket->question = $request->question;
        $ticket->recepient_id = $request->recepient_id;
        $ticket->email = $request->email;
        $ticket->status = 'Open';
        $ticket->staff_id = null;
        $ticket->date_created = now();
        $ticket->date_closed = null;
        $ticket->attachments = json_encode($attachmentsPaths);

        // Legacy category string removed: category_id is the single source of truth.

        $ticket->save();


        // Process assignment synchronously so the page can show the ticket immediately
        try {
            (new ProcessTicketCreation($ticket->id, $request->input('category_id')))->handle();
        } catch (\Throwable $e) {
            \Illuminate\Support\Facades\Log::warning('Synchronous ticket processing failed; falling back to queued job: ' . $e->getMessage(), ['ticket_id' => $ticket->id]);
            ProcessTicketCreation::dispatch($ticket->id, $request->input('category_id'));
        }

        // For API requests, return JSON
        if ($request->wantsJson()) {
            // Include assigned staff explicitly for client-side flows (AJAX form)
            return response()->json(['ticket' => $ticket, 'staff_id' => $ticket->staff_id], 201);
        }

        // For web requests, redirect to tickets page for the recepient id.
        // Generate a full URL using the configured app URL so it becomes {APP_URL}/tickets/{recepient_id}
        // Append the email so the tickets page shows the newly created ticket immediately
        $redirectUrl = url('/tickets/' . $request->recepient_id) . '?email=' . rawurlencode($ticket->email);
        return redirect()->to($redirectUrl)
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
        // Ticket CRUD is public/guest-facing; do not gate listing by authenticated staff users.
        // Treat all visitors as guests for the purposes of viewing tickets by recepient/email.
        $isStaff = false;

        // Not staff or not authenticated - use existing logic
        // Support both recepient_id and email as identifier
        $identifier = $identifier ?? $request->query('email') ?? $request->recepient_id;

        if (!$identifier) {
            return redirect()->route('login')->with('error', 'Invalid access. Please provide a valid identifier.');
        }

        // Determine if identifier is email or recepient_id
        $isEmail = filter_var($identifier, FILTER_VALIDATE_EMAIL);

        // If identifier looks like a recepient id (not an email), attempt to resolve it to an
        // email address by checking recent tickets created with that recepient_id. Keep the
        // resolved email separate so we can query BOTH recepient_id and email (if found).
        $resolvedEmail = null;
        if (!$isEmail && $identifier) {
            try {
                $resolvedEmail = Ticket::where('recepient_id', $identifier)
                    ->orderBy('date_created', 'desc')
                    ->value('email');
                if (!($resolvedEmail && filter_var($resolvedEmail, FILTER_VALIDATE_EMAIL))) {
                    $resolvedEmail = null;
                }
            } catch (\Throwable $e) {
                // Swallow and continue using the original identifier if lookup fails
                \Illuminate\Support\Facades\Log::warning('TicketController@index: failed to resolve recepient_id to email', ['recepient_id' => $identifier, 'error' => $e->getMessage()]);
                $resolvedEmail = null;
            }
        }

        // For API requests, return JSON
        if ($request->wantsJson()) {
            // Retrieve all tickets for the specified identifier
            $query = Ticket::query();
            // If the incoming identifier is an email, match by email. If it's a recepient id,
            // match by recepient_id and also include tickets that have the resolved email (if found).
            if ($isEmail) {
                // Case-insensitive email match
                $query->whereRaw('LOWER(email) = ?', [strval(strtolower($identifier))]);
            } else {
                if ($resolvedEmail) {
                    $query->where(function ($q) use ($identifier, $resolvedEmail) {
                        $q->where('recepient_id', $identifier)
                          ->orWhereRaw('LOWER(email) = ?', [strval(strtolower($resolvedEmail))]);
                    });
                } else {
                    $query->where('recepient_id', $identifier);
                }
            }
            // Ensure all tickets are returned regardless of status, but sort by priority:
            // Open first, then Forwarded, then Closed. Within each group sort by newest.
            $tickets = $query
                ->orderByRaw("FIELD(status, 'Open', 'Forwarded', 'Closed')")
                ->orderBy('date_created', 'desc')
                ->get();
            return response()->json($tickets);
        }

        // For web requests, return a view with the tickets
        $query = Ticket::query();
        if ($isEmail) {
            $query->whereRaw('LOWER(email) = ?', [strval(strtolower($identifier))]);
        } else {
            if ($resolvedEmail) {
                $query->where(function ($q) use ($identifier, $resolvedEmail) {
                    $q->where('recepient_id', $identifier)
                      ->orWhereRaw('LOWER(email) = ?', [strval(strtolower($resolvedEmail))]);
                });
            } else {
                $query->where('recepient_id', $identifier);
            }
        }
        // Return all tickets and sort by status priority (Open, Forwarded, Closed) then newest first
        $tickets = $query
            ->orderByRaw("FIELD(status, 'Open', 'Forwarded', 'Closed')")
            ->orderBy('date_created', 'desc')
            ->get();
        $users = User::orderBy('name')->get(['id', 'name']);
        return view('tickets.index', compact('tickets', 'identifier', 'isEmail', 'isStaff', 'users'));
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
        // Allow editing of the question and attachments; category must remain unchanged
        $request->validate([
            'question' => 'required|string',
            'attachments' => 'nullable|array|max:5',
            'attachments.*' => 'image|mimes:jpeg,png,jpg,gif|max:5120', // 5MB max
            'delete_attachments' => 'nullable|string',
        ]);

        $ticket = Ticket::findOrFail($id);

        // Handle attachments
        $currentAttachments = json_decode($ticket->attachments, true) ?? [];

        // Remove deleted attachments
        if ($request->delete_attachments) {
            $deleteList = json_decode($request->delete_attachments, true) ?? [];
            foreach ($deleteList as $path) {
                if (in_array($path, $currentAttachments)) {
                    Storage::disk('public')->delete($path);
                    $currentAttachments = array_diff($currentAttachments, [$path]);
                }
            }
        }

        // Add new attachments
        if ($request->hasFile('attachments')) {
            foreach ($request->file('attachments') as $file) {
                $filename = time() . '_' . uniqid() . '.' . $file->getClientOriginalExtension();
                $path = $file->storeAs('attachments', $filename, 'public');
                $currentAttachments[] = $path;
            }
        }

        $ticket->update([
            'question' => $request->question,
            'attachments' => json_encode(array_values($currentAttachments)),
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

    /**
     * Show the ticket status verification form
     */
    public function showStatusForm()
    {
        return view('tickets.status');
    }


    /**
     * Show email verification page
     */
    public function showVerifyEmail(Request $request)
    {
        $email = $request->query('email');
        return view('tickets.verify-email', compact('email'));
    }
}
