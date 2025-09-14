<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Carbon;
use App\Mail\PasswordOtpMail;

class AuthController extends Controller
{
    public function showLoginForm()
    {
        return view('login');
    }

    public function login(Request $request)
    {
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

            $authUser = Auth::user();
            if ($authUser && ($authUser->role === 'Primary Administrator')) {
                return redirect()->intended('/admin/dashboard');
            }
            return redirect()->intended('/staff/dashboard');
        }

        // Fallback (should rarely occur)
        return back()
            ->withErrors(['email' => 'Unable to sign in. Please try again.'])
            ->withInput($request->only('email', 'remember'));
    }

    public function logout(Request $request)
    {
        Auth::logout();
        
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        
        return redirect('/login');
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
}
