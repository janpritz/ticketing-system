<?php

namespace App\Http\Controllers;

use App\Events\ActiveStaffUpdated;
use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\{AuthRequest, OTPRequest, ResetPasswordRequest};
use App\Mail\PasswordOtpMail;
use App\Models\User;
use App\Models\UserRole;
use App\Services\Auth\{AuthService, OTPService};
use Illuminate\Http\{RedirectResponse, Request};
use Illuminate\Support\Facades\{Auth, Cookie, DB, Hash, Log, Mail};
use Illuminate\Validation\ValidationException;

class AuthController extends Controller
{
    public function showLoginForm()
    {
        return view('login');
    }

    public function login(AuthRequest $request, AuthService $authService): RedirectResponse
    {
        $redirectPath = $authService->login($request->validated());
        return redirect()->intended($redirectPath);
    }
    public function logout(AuthService $authService): RedirectResponse
    {
        try {
            $authService->logout();

            return redirect('/login');
        } catch (\Exception $e) {
            Log::error('Logout failed', ['error' => $e->getMessage()]);

            // Even if DB fails, we want the user session gone
            Auth::logout();
            return redirect('/login');
        }
    }

    public function showForgotForm()
    {
        return view('auth.forgot');
    }

    public function sendOtp(OTPRequest $request, OTPService $authService): RedirectResponse
    {
        // The 'exists' check in OtpRequest ensures we don't need to fetch the User model manually here.
        $email = $authService->sendPasswordOtp($request->validated('email'));

        return redirect()->route('password.reset.form', ['email' => $email])
            ->with('status', 'An OTP has been sent to your email. It will expire in 10 minutes.');
    }

    public function showResetForm(OTPRequest $request)
    {
        $validatedEmail = $request->validated('email');
        return view('auth.reset-otp', ['email' => $validatedEmail]);
    }

    public function resetWithOtp(ResetPasswordRequest $request, OTPService $authService): RedirectResponse
    {
        // 1. Validation happens automatically via ResetPasswordRequest
        // 2. Business logic happens in the Service
        $authService->resetPasswordWithOtp($request->validated());

        // 3. Success Response
        return redirect()->route('login')
            ->with('status', 'Password updated. You can now sign in.');
    }

    public function showVerifyAccountForm(string $token, AuthService $authService)
    {
        try {
            // The service automates the check
            $authService->validateVerificationToken($token);
            return view('auth.verify-account', compact('token'));
        } catch (ValidationException $e) {
            return redirect()->route('login')->with('error', $e->getMessage());
        } catch (\Exception $e) {
            return redirect()->route('login')->with('status', $e->getMessage());
        }
    }  
}
