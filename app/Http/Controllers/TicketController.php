<?php

namespace App\Http\Controllers;

use App\Models\Ticket;
use App\Models\Otp;
use Illuminate\Http\Request;
use App\Models\User;
use App\Models\Role;
use App\Models\Category;
use App\Models\TicketRoutingHistory;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Mail;
use App\Jobs\ProcessTicketCreation;
use App\Mail\OtpMail;

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

        // Check if email is already verified
        $emailVerified = false;
        if ($email) {
            $emailVerified = Otp::where('email', $email)
                               ->whereNotNull('verified_at')
                               ->exists();
            
            // If email is already verified, redirect to submission page
            if ($emailVerified) {
                return redirect()->route('tickets.submit.form', [$recepient_id, $email]);
            }
        }

        return view('tickets.create', compact('recepient_id', 'categories', 'email', 'emailVerified'));
    }

    /**
     * Show ticket submission form after OTP verification
     */
    public function showTicketSubmitForm(Request $request, $recepient_id = null, $email)
    {
        // Check if the user has reached the maximum number of tickets (4 tickets max)
        $ticketCount = Ticket::where('email', $email)->count();
        if ($ticketCount >= 4) {
            return redirect()->to(url('/tickets/' . urlencode($email)))->with('error', 'You have reached the maximum number of tickets (4). Please wait for responses to your existing tickets.');
        }

        // Verify email is actually verified
        $emailVerified = Otp::where('email', $email)
                           ->whereNotNull('verified_at')
                           ->exists();

        if (!$emailVerified) {
            return redirect()->route('tickets.create', $recepient_id)->with('error', 'Email verification required.');
        }

        // Fetch categories from DB
        $categories = Category::orderBy('name')->pluck('name')->toArray();

        return view('tickets.submit', compact('recepient_id', 'categories', 'email'));
    }

    /**
     * Handle ticket submission after OTP verification
     */
    public function submitTicket(Request $request)
    {
        $request->validate([
            'category' => 'required|string|max:255',
            'question' => 'required|string',
            'recepient_id' => ['required'],
            'email' => 'required|email|max:255',
            'attachments' => 'nullable|array|max:5',
            'attachments.*' => 'image|mimes:jpeg,png,jpg,gif|max:5120', // 5MB max
            'g-recaptcha-response' => 'required|captcha',
        ]);

        // Verify email is verified
        $emailVerified = Otp::where('email', $request->email)
                           ->whereNotNull('verified_at')
                           ->exists();

        if (!$emailVerified) {
            return redirect()->route('tickets.create', $request->recepient_id)->with('error', 'Email verification required.');
        }

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

        // For web requests, redirect to tickets page for the recipient id.
        // Generate a full URL using the configured app URL so it becomes {APP_URL}/tickets/{recipient_id}
        return redirect()->to(url('/tickets/' . $request->recepient_id))
            ->with('success', 'Ticket created successfully! Please wait for a response, which will be sent to your email.');
    }

    /**
     * Send OTP to email address
     */
    public function sendOtp(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'is_resend' => 'boolean'
        ]);

        $email = $request->email;
        $isResend = $request->boolean('is_resend', false);

        // Generate 6-digit OTP
        $otpCode = str_pad(random_int(0, 999999), 6, '0', STR_PAD_LEFT);

        // Delete any existing unverified OTPs for this email
        Otp::where('email', $email)
           ->whereNull('verified_at')
           ->delete();

        // Set expiry time: 15 minutes for initial, 1 minute for resend
        $expiresInMinutes = $isResend ? 1 : 15;
        $expiresAt = now()->addMinutes($expiresInMinutes);

        // Create new OTP record
        Otp::create([
            'email' => $email,
            'otp_code' => $otpCode,
            'expires_at' => $expiresAt,
        ]);

        // Send OTP via email
        Mail::to($email)->send(new OtpMail($otpCode));

        return response()->json([
            'success' => true,
            'message' => 'OTP sent successfully!',
            'expires_at' => $expiresAt->timestamp
        ]);
    }

    /**
     * Verify OTP
     */
    public function verifyOtp(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'otp_code' => 'required|string|size:6',
        ]);

        $email = $request->email;
        $otpCode = $request->otp_code;

        // Find the OTP record
        $otp = Otp::where('email', $email)
                  ->where('otp_code', $otpCode)
                  ->first();

        if (!$otp) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid OTP code.'
            ], 400);
        }

        if (!$otp->isValid()) {
            return response()->json([
                'success' => false,
                'message' => 'OTP has expired. Please request a new one.'
            ], 400);
        }

        // Mark as verified
        $otp->verified_at = now();
        $otp->save();

        return response()->json([
            'success' => true,
            'message' => 'Email verified successfully!',
            'verified' => true
        ]);
    }

    /**
     * Check if email is verified
     */
    public function checkEmailVerification(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
        ]);

        $email = $request->email;

        $verified = Otp::where('email', $email)
                      ->whereNotNull('verified_at')
                      ->exists();

        return response()->json([
            'verified' => $verified
        ]);
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

        // If it's an email, verify that the email has been verified
        if ($isEmail) {
            $emailVerified = Otp::where('email', $identifier)
                               ->whereNotNull('verified_at')
                               ->exists();

            if (!$emailVerified) {
                // Email not verified - redirect to verification page
                return redirect()->route('tickets.verify', ['email' => $identifier])
                               ->with('error', 'Email verification required to view tickets.');
            }
        }

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

    /**
     * Show the ticket status verification form
     */
    public function showStatusForm()
    {
        return view('tickets.status');
    }

    /**
     * Send OTP for ticket status verification
     */
    public function sendOtpStatus(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
        ]);

        $email = $request->email;

        // Check if email has any tickets
        $ticketCount = Ticket::where('email', $email)->count();
        if ($ticketCount === 0) {
            return response()->json([
                'success' => false,
                'message' => 'No tickets found for this email address.'
            ], 400);
        }

        // Generate 6-digit OTP
        $otpCode = str_pad(random_int(0, 999999), 6, '0', STR_PAD_LEFT);

        // Delete any existing unverified OTPs for this email
        Otp::where('email', $email)
           ->whereNull('verified_at')
           ->delete();

        // Set expiry time: 15 minutes
        $expiresAt = now()->addMinutes(15);

        // Create new OTP record
        Otp::create([
            'email' => $email,
            'otp_code' => $otpCode,
            'expires_at' => $expiresAt,
        ]);

        // Send OTP via email
        Mail::to($email)->send(new OtpMail($otpCode));

        return response()->json([
            'success' => true,
            'message' => 'Verification code sent successfully!',
            'expires_at' => $expiresAt->timestamp
        ]);
    }

    /**
     * Verify OTP for ticket status and redirect to tickets index
     */
    public function verifyOtpStatus(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'otp_code' => 'required|string|size:6',
        ]);

        $email = $request->email;
        $otpCode = $request->otp_code;

        // Find the OTP record
        $otp = Otp::where('email', $email)
                  ->where('otp_code', $otpCode)
                  ->first();

        if (!$otp) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid verification code.'
            ], 400);
        }

        if (!$otp->isValid()) {
            return response()->json([
                'success' => false,
                'message' => 'Verification code has expired. Please request a new one.'
            ], 400);
        }

        // Mark as verified
        $otp->verified_at = now();
        $otp->save();

        return response()->json([
            'success' => true,
            'message' => 'Email verified successfully!',
            'verified' => true,
            'redirect_url' => url('/tickets/' . urlencode($email))
        ]);
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
