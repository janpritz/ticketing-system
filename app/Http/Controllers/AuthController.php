<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use App\Models\UserRole;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Carbon;
use App\Mail\PasswordOtpMail;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Cookie;
use App\Events\ActiveStaffUpdated;

class AuthController extends Controller
{
    public function showLoginForm()
    {
        return view('login');
    }

    public function login(Request $request)
    {
        // Log::info('Login attempt', [
        //     'email' => $request->input('email'),
        //     'has_csrf' => $request->has('_token'),
        //     'csrf_token' => $request->input('_token'),
        //     'session_id' => $request->session()->getId(),
        //     'user_agent' => $request->userAgent(),
        // ]);

        $request->validate([
            'email'    => 'required|email',
            'password' => 'required',
            'remember' => 'nullable|boolean',
        ]);

        $email = $request->input('email');
        $password = $request->input('password');
        $remember = $request->boolean('remember');

        // 1) Specific error if email is not a valid user
        $user = User::where('email', $email)->first();
        if (!$user) {
            return back()
                ->withErrors(['email' => 'Email is not registered.'])
                ->withInput($request->only('email', 'remember'));
        }

        // 2) Specific error if password is invalid for an existing user
        if (!Hash::check($password, $user->password)) {
            return back()
                ->withErrors(['password' => 'Invalid password.'])
                ->withInput($request->only('email', 'remember'));
        }

        // 3) Credentials are valid — sign in and redirect based on role
        if (Auth::attempt(['email' => $email, 'password' => $password], $remember)) {
            $request->session()->regenerate();

            /** @var \App\Models\User|null $authUser */
            $authUser = Auth::user();
            // Check if user has role_id = 1 in user_roles table via UserRole model
            $isPrimaryAdmin = $authUser && UserRole::where('user_id', $authUser->id)
                ->where('role_id', 1)
                ->exists();
            if ($isPrimaryAdmin) {
                return redirect()->intended('/admin/dashboard');
            }

            // Broadcast active staff update for staff login
            $cutoff = now()->subMinutes(10)->getTimestamp();
            $activeStaffCount = DB::table('sessions')
                ->join('users', 'sessions.user_id', '=', 'users.id')
                ->leftJoin('user_roles', 'users.id', '=', 'user_roles.user_id')
                ->leftJoin('roles', 'user_roles.role_id', '=', 'roles.id')
                ->whereNotNull('sessions.user_id')
                ->where('sessions.last_activity', '>=', $cutoff)
                ->where(function ($qb) {
                    $qb->whereNull('roles.name')->orWhere('roles.name', '!=', 'Primary Administrator');
                })
                ->distinct('sessions.user_id')
                ->count('sessions.user_id');

            // Build full staff contacts with active flag
            $staffContacts = User::leftJoin('user_roles', 'users.id', '=', 'user_roles.user_id')
                ->leftJoin('roles', 'user_roles.role_id', '=', 'roles.id')
                ->where(function ($q) {
                    $q->whereNull('roles.name')->orWhere('roles.name', '!=', 'Primary Administrator');
                })
                ->leftJoin('sessions', 'sessions.user_id', '=', 'users.id')
                ->groupBy('users.id', 'users.name', 'users.email')
                ->select([
                    'users.id',
                    'users.name',
                    'users.email',
                    DB::raw('MAX(sessions.last_activity) as last_activity_ts')
                ])
                ->orderBy('users.name')
                ->get()
                ->map(function ($row) use ($cutoff) {
                    $ts = (int) ($row->last_activity_ts ?? 0);
                    return [
                        'id' => (int) $row->id,
                        'name' => (string) ($row->name ?? ''),
                        'email' => (string) ($row->email ?? ''),
                        'last_activity_ts' => $ts,
                        'is_active' => $ts >= $cutoff,
                    ];
                })
                ->values()
                ->toArray();

            dispatch(function () use ($activeStaffCount, $staffContacts) {
                broadcast(new ActiveStaffUpdated($activeStaffCount, $staffContacts));
            })->afterResponse();

            return redirect()->intended('/staff/dashboard');
        }

        // Fallback (should rarely occur)
        return back()
            ->withErrors(['email' => 'Unable to sign in. Please try again.'])
            ->withInput($request->only('email', 'remember'));
    }

    public function logout(Request $request)
    {
        // Log the logout attempt for debugging
        Log::info('Logout attempt', [
            'user_id' => $request->user() ? $request->user()->id : 'guest',
            'session_id' => $request->session()->getId(),
            'has_csrf' => $request->has('_token'),
        ]);

        try {
            // Clear any cached data
            $request->session()->flush();
            
            // Invalidate the session
            $request->session()->invalidate();
            
            // Regenerate the CSRF token
            $request->session()->regenerateToken();
            
            // Clear remember me cookie if exists
            if ($request->hasCookie('remember_web_59ba36addc2b2f9401580f014c7f58ea4e30989d')) {
                Cookie::queue(Cookie::forget('remember_web_59ba36addc2b2f9401580f014c7f58ea4e30989d'));
            }
            
            // Logout the user
            Auth::logout();

            // Broadcast active staff update
            $cutoff = now()->subMinutes(10)->getTimestamp();
            $activeStaffCount = DB::table('sessions')
                ->join('users', 'sessions.user_id', '=', 'users.id')
                ->leftJoin('user_roles', 'users.id', '=', 'user_roles.user_id')
                ->leftJoin('roles', 'user_roles.role_id', '=', 'roles.id')
                ->whereNotNull('sessions.user_id')
                ->where('sessions.last_activity', '>=', $cutoff)
                ->where(function ($qb) {
                    $qb->whereNull('roles.name')->orWhere('roles.name', '!=', 'Primary Administrator');
                })
                ->distinct('sessions.user_id')
                ->count('sessions.user_id');

            // Build full staff contacts with active flag
            $staffContacts = User::leftJoin('user_roles', 'users.id', '=', 'user_roles.user_id')
                ->leftJoin('roles', 'user_roles.role_id', '=', 'roles.id')
                ->where(function ($q) {
                    $q->whereNull('roles.name')->orWhere('roles.name', '!=', 'Primary Administrator');
                })
                ->leftJoin('sessions', 'sessions.user_id', '=', 'users.id')
                ->groupBy('users.id', 'users.name', 'users.email')
                ->select([
                    'users.id',
                    'users.name',
                    'users.email',
                    DB::raw('MAX(sessions.last_activity) as last_activity_ts')
                ])
                ->orderBy('users.name')
                ->get()
                ->map(function ($row) use ($cutoff) {
                    $ts = (int) ($row->last_activity_ts ?? 0);
                    return [
                        'id' => (int) $row->id,
                        'name' => (string) ($row->name ?? ''),
                        'email' => (string) ($row->email ?? ''),
                        'last_activity_ts' => $ts,
                        'is_active' => $ts >= $cutoff,
                    ];
                })
                ->values()
                ->toArray();

            dispatch(function () use ($activeStaffCount, $staffContacts) {
                broadcast(new ActiveStaffUpdated($activeStaffCount, $staffContacts));
            })->afterResponse();

            Log::info('Logout successful', ['session_id' => $request->session()->getId()]);

            return redirect('/login')->with('status', 'You have been logged out successfully.');
            
        } catch (\Exception $e) {
            Log::error('Logout error', [
                'error' => $e->getMessage(),
                'session_id' => $request->session()->getId(),
            ]);
            
            // Force logout even if there's an error
            Auth::logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();

            // Broadcast active staff update
            $cutoff = now()->subMinutes(10)->getTimestamp();
            $activeStaffCount = DB::table('sessions')
                ->join('users', 'sessions.user_id', '=', 'users.id')
                ->leftJoin('user_roles', 'users.id', '=', 'user_roles.user_id')
                ->leftJoin('roles', 'user_roles.role_id', '=', 'roles.id')
                ->whereNotNull('sessions.user_id')
                ->where('sessions.last_activity', '>=', $cutoff)
                ->where(function ($qb) {
                    $qb->whereNull('roles.name')->orWhere('roles.name', '!=', 'Primary Administrator');
                })
                ->distinct('sessions.user_id')
                ->count('sessions.user_id');

            // Build full staff contacts with active flag
            $staffContacts = User::leftJoin('user_roles', 'users.id', '=', 'user_roles.user_id')
                ->leftJoin('roles', 'user_roles.role_id', '=', 'roles.id')
                ->where(function ($q) {
                    $q->whereNull('roles.name')->orWhere('roles.name', '!=', 'Primary Administrator');
                })
                ->leftJoin('sessions', 'sessions.user_id', '=', 'users.id')
                ->groupBy('users.id', 'users.name', 'users.email')
                ->select([
                    'users.id',
                    'users.name',
                    'users.email',
                    DB::raw('MAX(sessions.last_activity) as last_activity_ts')
                ])
                ->orderBy('users.name')
                ->get()
                ->map(function ($row) use ($cutoff) {
                    $ts = (int) ($row->last_activity_ts ?? 0);
                    return [
                        'id' => (int) $row->id,
                        'name' => (string) ($row->name ?? ''),
                        'email' => (string) ($row->email ?? ''),
                        'last_activity_ts' => $ts,
                        'is_active' => $ts >= $cutoff,
                    ];
                })
                ->values()
                ->toArray();

            dispatch(function () use ($activeStaffCount, $staffContacts) {
                broadcast(new ActiveStaffUpdated($activeStaffCount, $staffContacts));
            })->afterResponse();

            return redirect('/login')->with('error', 'Logout completed. Please try again if you experience issues.');
        }
    }

    /**
     * Show the "Forgot Password" form (request OTP).
     */
    public function showForgotForm()
    {
        return view('auth.forgot');
    }

    /**
     * Handle sending OTP to the user's email.
     */
    public function sendOtp(Request $request)
    {
        $request->validate([
            'email' => 'required|email|exists:users,email'
        ]);

        $email = $request->input('email');
        $user = User::where('email', $email)->first();

        if (!$user) {
            return back()->withErrors(['email' => 'Email is not registered.'])->withInput($request->only('email'));
        }

        // Generate a 6-digit OTP
        $otp = str_pad((string) random_int(0, 999999), 6, '0', STR_PAD_LEFT);

        // Upsert into password_reset_tokens (email is primary)
        DB::table('password_reset_tokens')->updateOrInsert(
            ['email' => $email],
            [
                'token' => Hash::make($otp),
                'created_at' => now()
            ]
        );

        try {
            Mail::to($email)->send(new PasswordOtpMail($otp));
        } catch (\Throwable $e) {
            // swallow delivery error; user can retry
        }

        return redirect()->route('password.reset.form', ['email' => $email])
            ->with('status', 'An OTP has been sent to your email. It will expire in 10 minutes.');
    }

    /**
     * Show the "Enter OTP + New Password" form.
     */
    public function showResetForm(Request $request)
    {
        $email = $request->query('email', '');
        return view('auth.reset-otp', ['email' => $email]);
    }

    /**
     * Verify OTP and update password.
     */
    public function resetWithOtp(Request $request)
    {
        $request->validate([
            'email'    => 'required|email',
            'otp'      => 'required|digits:6',
            'password' => 'required|min:8|confirmed',
        ]);

        $email = $request->input('email');
        $otp   = $request->input('otp');

        $record = DB::table('password_reset_tokens')->where('email', $email)->first();

        if (!$record) {
            return back()->withErrors(['otp' => 'Invalid OTP or email.'])->withInput($request->only('email'));
        }

        // Expire after 10 minutes
        $created = Carbon::parse($record->created_at);
        if ($created->lt(now()->subMinutes(10))) {
            return back()->withErrors(['otp' => 'OTP expired. Please request a new one.'])->withInput($request->only('email'));
        }

        if (!Hash::check($otp, $record->token)) {
            return back()->withErrors(['otp' => 'Invalid OTP.'])->withInput($request->only('email'));
        }

        $user = User::where('email', $email)->first();
        if (!$user) {
            return back()->withErrors(['email' => 'User not found.'])->withInput($request->only('email'));
        }

        $user->password = Hash::make($request->input('password'));
        $user->save();

        // Invalidate token
        DB::table('password_reset_tokens')->where('email', $email)->delete();

        return redirect()->route('login')->with('status', 'Password updated. You can now sign in.');
    }

    /**
     * Show the account verification form (set password).
     */
    public function showVerifyAccountForm(Request $request, $token)
    {
        // Find user with this verification token
        $user = User::where('verification_token', $token)->first();

        if (!$user) {
            return redirect()->route('login')->with('error', 'Invalid or expired verification link.');
        }

        // If user already has a verified email (password already set), redirect to login
        if ($user->email_verified_at) {
            return redirect()->route('login')->with('status', 'Account already verified. Please login.');
        }

        return view('auth.verify-account', ['token' => $token]);
    }

    /**
     * Process the account verification (set password).
     */
    public function verifyAccount(Request $request)
    {
        $request->validate([
            'token' => 'required|string',
            'password' => 'required|string|min:8|confirmed',
        ]);

        $token = $request->input('token');
        $password = $request->input('password');

        // Find user with this verification token
        $user = User::where('verification_token', $token)->first();

        if (!$user) {
            return back()->withErrors(['token' => 'Invalid or expired verification link.'])->withInput();
        }

        // If user already has a verified email, redirect to login
        if ($user->email_verified_at) {
            return redirect()->route('login')->with('status', 'Account already verified. Please login.');
        }

        // Set the password and mark email as verified
        $user->password = Hash::make($password);
        $user->email_verified_at = now();
        $user->verification_token = null;
        $user->save();

        // Redirect with success message that triggers SweetAlert
        return redirect()->route('login')->with('success', 'Account verified successfully! You can now login with your password.');
    }
}
