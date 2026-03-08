<?php

namespace App\Services\Auth;

use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\{DB, Hash, Mail, Log};
use App\Mail\{PasswordOtpMail};
use App\Models\{User, Ticket, OTP};
use Illuminate\Validation\ValidationException;


class OTPService
{
    public function resolveEmailFromIdentifier(string $identifier): ?string
    {
        // 1. If the identifier is already an email, return it
        if (filter_var($identifier, FILTER_VALIDATE_EMAIL)) {
            return $identifier;
        }

        // 2. Otherwise, treat it as a recipient_id and look up the latest ticket
        return Ticket::where('recepient_id', $identifier)
            ->orderBy('date_created', 'desc')
            ->value('email');
    }

    /**
     * Verifies the 6-digit code against the database.
     */
    public function verifyOtp(string $email, string $code): array
    {
        $otp = Otp::where('email', $email)
            ->where('verified_at', null)
            ->latest()
            ->first();

        if (!$otp) {
            return ['success' => false, 'message' => 'No active OTP found.'];
        }

        if (now()->isAfter($otp->expires_at)) {
            return ['success' => false, 'message' => 'This code has expired.'];
        }

        // Use a secure comparison (especially if you hash your OTPs)
        if (!hash_equals((string)$otp->code, (string)$code)) {
            return ['success' => false, 'message' => 'The code you entered is incorrect.'];
        }

        // Mark as used or delete
        $otp->update(['verified_at' => now()]);
        // $otp->delete(); // Alternative: delete it immediately

        return ['success' => true];
    }
    public function sendPasswordOtp(string $email): string
    {
        // 1. Generate OTP
        $otp = str_pad((string) random_int(0, 999999), 6, '0', STR_PAD_LEFT);

        // 2. Automate Token Storage (Upsert)
        DB::table('password_reset_tokens')->updateOrInsert(
            ['email' => $email],
            [
                'token' => Hash::make($otp),
                'created_at' => now()
            ]
        );
        // 3. Send Mail (Wrapped in try-catch to prevent app crash)
        try {
            Mail::to($email)->send(new PasswordOtpMail($otp));
        } catch (\Throwable $e) {
            throw ValidationException::withMessages([
                'email' => 'Failed to send OTP email. Please try again later.'
            ]);
        }
        return $email; // Return email for redirection purposes
    }

    public function resetPasswordWithOtp(array $data): void
    {
        // 1. Fetch the token record
        $record = DB::table('password_reset_tokens')
            ->where('email', $data['email'])
            ->first();

        if (!$record) {
            throw ValidationException::withMessages(['otp' => 'Invalid OTP or email.']);
        }

        // 2. Automate Expiration Check (10 mins)
        if (Carbon::parse($record->created_at)->addMinutes(10)->isPast()) {
            DB::table('password_reset_tokens')->where('email', $data['email'])->delete();
            throw ValidationException::withMessages(['otp' => 'OTP expired. Please request a new one.']);
        }

        // 3. Verify OTP Hash
        if (!Hash::check($data['otp'], $record->token)) {
            throw ValidationException::withMessages(['otp' => 'Invalid OTP.']);
        }

        // 4. Update User
        $user = User::where('email', $data['email'])->firstOrFail();
        $user->update([
            'password' => Hash::make($data['password'])
        ]);

        // 5. Cleanup
        DB::table('password_reset_tokens')->where('email', $data['email'])->delete();
    }
}
