<?php

namespace App\Services\Admin;

use Illuminate\Support\Facades\{Mail, DB, Log};
use App\Models\{Ticket, Otp,};
use App\Mail\OtpMail;

class OtpService
{
    public function resolveEmailFromIdentifier(string $identifier): ?string
    {
        if (filter_var($identifier, FILTER_VALIDATE_EMAIL)) {
            return $identifier;
        }

        // Search for the most recent ticket associated with this recipient ID
        return Ticket::where('recepient_id', $identifier)
            ->orderBy('date_created', 'desc')
            ->value('email');
    }

    /**
     * Create, store, and mail an OTP code.
     */
    public function sendOtp(string $email): bool
    {
        try {
            $otpCode = str_pad((string) random_int(0, 999999), 6, '0', STR_PAD_LEFT);

            // Using a transaction ensures we don't delete an old OTP if the new one fails
            return DB::transaction(function () use ($email, $otpCode) {
                Otp::where('email', $email)->delete();

                Otp::create([
                    'email'      => $email,
                    'otp_code'   => $otpCode,
                    'expires_at' => now()->addMinutes(15),
                ]);

                Mail::to($email)->send(new OtpMail($otpCode));
                return true;
            });
        } catch (\Throwable $e) {
            Log::error("OTP Service Error for {$email}: " . $e->getMessage());
            return false;
        }
    }

    public function verifyOtp(string $email, string $code): array
    {
        $otp = Otp::where('email', $email)->latest()->first();

        if (!$otp) {
            return ['success' => false, 'message' => 'No OTP found. Please request a new one.'];
        }

        if (now()->isAfter($otp->expires_at)) {
            return ['success' => false, 'message' => 'OTP has expired.'];
        }

        // Use hash_equals to prevent timing attacks if you are storing codes as hashes
        if (!$otp->verify($code)) {
            return ['success' => false, 'message' => 'Invalid OTP code.'];
        }

        // Success: Cleanup
        $otp->delete();

        return ['success' => true];
    }

    public function getResendCooldown(string $email): int
    {
        $recentOtp = Otp::where('email', $email)
            ->where('created_at', '>', now()->subMinute())
            ->latest()
            ->first();

        if ($recentOtp) {
            return (int) now()->diffInSeconds($recentOtp->created_at->addMinute());
        }

        return 0;
    }
}
