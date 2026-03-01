<?php

namespace App\Services\Auth;

use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\{DB, Hash, Mail, Log};
use App\Mail\PasswordOtpMail;
use App\Models\User;
use Illuminate\Validation\ValidationException;


class OTPService
{
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
