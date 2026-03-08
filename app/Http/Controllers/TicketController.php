<?php

namespace App\Http\Controllers;

use App\Http\Requests\Admin\SendOTPRequest;
use App\Http\Requests\Admin\StoreTicketRequest;
use App\Http\Requests\Admin\SubmitTicketRequest;
use App\Http\Requests\Admin\UpdateStatusRequest;
use App\Http\Requests\Admin\UpdateTicketRequest1;
use App\Http\Requests\Admin\VerifyTicketOTPRequest;
use App\Models\Ticket;
use Illuminate\Http\Request;
use App\Models\User;
use App\Models\Role;
use App\Models\Department;
use App\Models\TicketRoutingHistory;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Mail;
use App\Services\Admin\TicketService;

use App\Jobs\ProcessTicketCreation;
use App\Services\Admin\OtpService;
use App\Services\Auth\OTPService as AuthOTPService;

class TicketController extends Controller
{

    /**
     * Securely serve ticket attachments.
     */
    public function serveAttachment(string $path, TicketService $service)
    {
        // The path is already coming from the route parameter
        $fileData = $service->resolveAttachmentPath($path);

        if (!$fileData['exists']) {
            abort(404);
        }

        return response()->file($fileData['full_path']);
    }

    // Show the ticket creation form
    public function showCreateForm(Request $request, AuthOTPService $service, $recepient_id = null)
    {
        // Use your service to get the email regardless of what was passed (ID or Email)
        $email = $service->resolveEmailFromIdentifier($recepient_id ?? '');
        $roles = Role::pluck('name', 'id');

        // Security: Check the cookie against the URL identifier
        $cookieEmail = $request->cookie('verified_email');

        // If they have no cookie, or the URL identifier resolves to a different email
        if (!$cookieEmail || ($email && $cookieEmail !== $email)) {
            return redirect()->route('tickets.verify-otp')
                ->with('error', 'Please verify your identity.');
        }

        return view('tickets.create.page', [
            'recepient_id' => $recepient_id,
            'email' => $cookieEmail // Always use the verified cookie email for the form
        ], compact('roles'));
    }

    /**
     * Show ticket submission form after OTP verification
     */
    public function showTicketSubmitForm(Request $request, $recepient_id = null, $email)
    {
        // Fetch roles from DB (id => name) so the form submits role_id correctly
        $roles = Role::orderBy('name')->pluck('name', 'id')->toArray();

        return view('tickets.submit', compact('recepient_id', 'roles', 'email'));
    }

    /**
     * Handle ticket submission after OTP verification
     */
    /**
     * Submit and process a new support ticket using a Form Request.
     */
    public function submitTicket(SubmitTicketRequest $request, TicketService $service)
    {
        // If we are here, validation has already passed.
        $validated = $request->validated();

        // Delegate logic to the service
        $ticket = $service->createAndProcessTicket($validated, $request->file('attachments', []));

        // Handle JSON/API Response
        if ($request->wantsJson()) {
            return response()->json([
                'ticket'   => $ticket,
                'staff_id' => $ticket->staff_id,
                'message'  => $ticket->staff_id
                    ? 'Ticket created and assigned successfully!'
                    : 'Ticket created; assignment is pending.'
            ], 201);
        }

        // Handle Web Redirect
        $statusMessage = $ticket->staff_id
            ? 'Ticket created and assigned successfully!'
            : 'Ticket created successfully! Assignment is being processed.';

        $redirectUrl = url('/tickets/' . $validated['recepient_id']) . '?email=' . rawurlencode($ticket->email);

        return redirect()->to($redirectUrl)->with('success', $statusMessage);
    }


    /**
     * Store a newly created ticket in storage using verified session email.
     */
    public function store(StoreTicketRequest $request, TicketService $service)
    {
        $email = $request->cookie('verified_email');

        // Pass the email explicitly to ensure the service has it
        $ticket = $service->createAndProcessTicket(
            array_merge($request->validated(), [
                'email' => $email,
                'recepient_id' => $request->route('recepient_id') // Get from URL if present
            ]),
            $request->file('attachments', [])
        );

        return redirect()->route('tickets.index', ['recepient_id' => $email])
            ->with('success', 'Ticket created!');
    }

    /**
     * Display a listing of tickets for the verified guest.
     */
    public function index(Request $request, TicketService $service)
    {
        // 1. Get verified email from cookie
        $email = session('verified_email') ?? $request->cookie('verified_email');

        // 2. Retrieve tickets and user list from Service
        $tickets = $service->getTicketsByEmail($email);

        // 3. Handle JSON/API Response
        if ($request->wantsJson()) {
            return response()->json($tickets);
        }

        // 4. Handle Web View
        return view('tickets.index.page', [
            'tickets'    => $tickets,
            'identifier' => $email,
            'isEmail'    => true,
            'isStaff'    => false,
            'users'      => $service->getSimpleUserList(),
        ]);
    }
    /**
     * Update the status of a specific ticket.
     */
    public function updateStatus(UpdateStatusRequest $request, int $id, TicketService $service)
    {
        $user = $request->user();

        // The service handles finding the ticket and ensuring ownership
        $ticket = $service->updateTicketStatus($id, $user->id, $request->status);

        if ($request->wantsJson()) {
            return response()->json([
                'success' => true,
                'ticket'  => $ticket,
                'message' => "Ticket status updated to {$request->status}."
            ]);
        }

        return redirect()->back()->with('success', 'Ticket status updated.');
    }

    /**
     * Update the specified ticket in storage.
     */
    public function update(UpdateTicketRequest1 $request, Ticket $ticket, TicketService $service)
    {
        // Data is already validated and authorized here
        $service->updateTicket(
            $ticket,
            $request->safe()->except('attachments'),
            $request->file('attachments', []),
            $request->user() // Passes null if it's a guest
        );

        return redirect()->back()->with('success', 'Changes saved successfully.');
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
        return view('tickets.verify-email.page', compact('email'));
    }

    /**
     * Show OTP verification page for ticket access
     */
    public function showVerifyOtp(Request $request, $identifier = null)
    {
        // 1. Check if the user is already verified via cookie
        $verifiedEmail = $request->cookie('verified_email');

        if ($verifiedEmail && $verifiedEmail !== 'deleted') {
            // If already verified, redirect to tickets with their email
            return redirect()->route('tickets.index', ['email' => $verifiedEmail]);
        }

        // 2. If no identifier in the URL, redirect to a version with a random one
        // This keeps your "tickets/verify-otp/{id}" requirement intact
        if (!$identifier) {
            $randomId = rand(100000, 999999);
            return redirect()->route('tickets.verify-otp', ['identifier' => $randomId]);
        }

        return view('tickets.verify-otp.page', compact('identifier'));
    }

    /**
     * Send OTP to email for ticket access verification
     */
    /**
     * Handle the request to send an OTP for ticket access.
     */
    /**
     * Handle the request to send an OTP for ticket access.
     */
    public function sendTicketOtp(SendOTPRequest $request, OtpService $otpService)
    {
        try {
            // 1. Resolve the target email
            $identifier = $request->validated('identifier');
            $email = $otpService->resolveEmailFromIdentifier($identifier);

            if (!$email) {
                return response()->json([
                    'success' => false,
                    'message' => 'No tickets found for this identifier.'
                ], 422);
            }

            // 2. Generate and Send OTP
            $sent = $otpService->sendOtp($email);

            if ($sent) {
                return response()->json([
                    'success' => true,
                    'message' => 'OTP sent successfully to your email.',
                    'email'   => $email,
                ]);
            }

            // If $sent is false but no exception was thrown (logic-level failure)
            throw new \Exception("The OTP service could not complete the request.");
        } catch (\Throwable $e) {
            // Log the specific error for debugging
            Log::error("OTP Generation Error: " . $e->getMessage(), [
                'identifier' => $request->input('identifier'),
                'exception'  => get_class($e)
            ]);

            return response()->json([
                'success' => false,
                'message' => 'An unexpected error occurred. Please try again later.'
            ], 500);
        }
    }

    /**
     * Verify OTP for ticket access
     */
    /**
     * Verify the OTP code and establish a session.
     */
    public function verifyTicketOtp(VerifyTicketOTPRequest $request, OtpService $otpService)
    {
        try {
            // 1. Resolve Email (Fixed the $$ syntax error)
            $email = $otpService->resolveEmailFromIdentifier($request->validated('identifier'));

            if (!$email) {
                return response()->json(['success' => false, 'message' => 'Invalid identifier.'], 422);
            }

            // 2. Verify via Service
            $verification = $otpService->verifyOtp($email, $request->input('otp_code'));

            if (!$verification['success']) {
                return response()->json([
                    'success' => false,
                    'message' => $verification['message']
                ], 422);
            }

            // Use the request instance to ensure the session is bound to this specific call
            $request->session()->put('otp_verified', true);
            $request->session()->put('verified_email', $email);

            // Force the session to write to the driver (file/database) immediately
            $request->session()->save();

            $cookie = cookie('verified_email', $email, 60, '/', null, false, false);
            // --- NEW: Store in Session for Middleware Security ---
            session([
                'otp_verified' => true,
                'verified_email' => $email,
                'otp_verified_at' => now(),
            ]);

            $cookie = cookie('verified_email', $email, 60, '/', null, true, false);

            return response()->json([
                'success'      => true,
                'message'      => 'OTP verified successfully!',
                'redirect_url' => route('tickets.index', ['email' => $email])
            ])->withCookie($cookie);
        } catch (\Throwable $e) {
            // This is where that "Variable variable" error was being caught
            Log::error("OTP Verification Error: " . $e->getMessage());

            // During development, you can return $e->getMessage() here to see errors instantly
            return response()->json(['success' => false, 'message' => 'An error occurred during verification.'], 500);
        }
    }

    /**
     * Resend OTP for ticket access (with 1 minute throttle)
     */
    /**
     * Handle the request to resend an OTP code.
     */
    public function resendTicketOtp(Request $request, OtpService $otpService)
    {
        $request->validate(['identifier' => 'required|string']);

        try {
            $identifier = $request->input('identifier');
            $email = $otpService->resolveEmailFromIdentifier($identifier);

            if (!$email) {
                return response()->json(['success' => false, 'message' => 'No tickets found.'], 422);
            }

            // 1. Check Cooldown (1-minute throttle)
            $cooldown = $otpService->getResendCooldown($email);
            if ($cooldown > 0) {
                return response()->json([
                    'success' => false,
                    'message' => "Please wait {$cooldown} seconds before requesting a new OTP.",
                    'retry_after' => $cooldown
                ], 429);
            }

            // 2. Reuse the sendOtp logic from the service
            if ($otpService->sendOtp($email)) {
                return response()->json(['success' => true, 'message' => 'OTP resent successfully!']);
            }

            throw new \Exception("The OTP service could not resend the code.");
        } catch (\Throwable $e) {
            Log::error("OTP Resend Error: " . $e->getMessage());
            return response()->json(['success' => false, 'message' => 'Failed to resend OTP.'], 500);
        }
    }
}
