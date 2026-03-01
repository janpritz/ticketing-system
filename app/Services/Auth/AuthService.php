<?php

namespace App\Services\Auth;

use App\Models\User;
use App\Models\UserRole;
use App\Events\ActiveStaffUpdated;
use Illuminate\Support\Facades\{Auth, Hash, DB};
use Illuminate\Validation\ValidationException;

class AuthService
{
    public function login(array $data): string
    {
        $user = User::where('email', $data['email'])->first();

        // 1. Manual Validation Checks
        if (!$user) {
            throw ValidationException::withMessages(['email' => 'Email is not registered.']);
        }

        if (!Hash::check($data['password'], $user->password)) {
            throw ValidationException::withMessages(['password' => 'Invalid password.']);
        }

        // 2. Attempt Login
        if (Auth::attempt(['email' => $data['email'], 'password' => $data['password']], $data['remember'] ?? false)) {
            request()->session()->regenerate();

            $this->handlePostLoginStaffUpdates();

            return $this->determineRedirectPath(Auth::user());
        }

        throw ValidationException::withMessages(['email' => 'Unable to sign in.']);
    }

    protected function determineRedirectPath($user): string
    {
        $isPrimaryAdmin = UserRole::where('user_id', $user->id)->where('role_id', 1)->exists();
        return $isPrimaryAdmin ? '/admin/dashboard' : '/staff/dashboard';
    }

    protected function handlePostLoginStaffUpdates(): void
    {
        $cutoff = now()->subMinutes(10)->getTimestamp();

        // Logic moved from Controller to here
        $staffContacts = $this->getStaffContacts($cutoff);
        $activeCount = collect($staffContacts)->where('is_active', true)->count();

        // Use dispatch()->afterResponse() to keep the login fast
        dispatch(fn() => broadcast(new ActiveStaffUpdated($activeCount, $staffContacts)))->afterResponse();
    }

    protected function getStaffContacts(int $cutoff): array
    {
        return User::leftJoin('user_roles', 'users.id', '=', 'user_roles.user_id')
            ->leftJoin('roles', 'user_roles.role_id', '=', 'roles.id')
            ->where(fn($q) => $q->whereNull('roles.name')->orWhere('roles.name', '!=', 'Primary Administrator'))
            ->leftJoin('sessions', 'sessions.user_id', '=', 'users.id')
            ->groupBy('users.id', 'users.name', 'users.email')
            ->select(['users.id', 'users.name', 'users.email', DB::raw('MAX(sessions.last_activity) as last_activity_ts')])
            ->get()
            ->map(fn($row) => [
                'id' => (int) $row->id,
                'name' => $row->name,
                'is_active' => (int)$row->last_activity_ts >= $cutoff,
            ])->toArray();
    }

    public function validateVerificationToken(string $token): User
    {
        $user = User::where('verification_token', $token)->first();

        if (!$user) {
            // We throw a custom exception or a ValidationException to automate the redirect
            throw ValidationException::withMessages(['token' => 'Invalid or expired verification link.']);
        }

        if ($user->email_verified_at) {
            // You can also throw an exception here with a specific "status" message
            throw new \Exception('Account already verified.');
        }

        return $user;
    }

    public function logout(): void
    {
        Auth::logout();
        request()->session()->invalidate();
        request()->session()->regenerateToken();

        $this->handlePostLoginStaffUpdates();
    }
}
