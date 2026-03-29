<?php

namespace App\Services\Staff;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\{Storage, Hash, Log, DB};
use Illuminate\Support\Carbon;
use App\Models\{User, Ticket};

class UserService
{
    public function updateStaffProfile(User $user, array $data, ?UploadedFile $photo): User
    {
        $user->name = $data['name'];

        if ($photo) {
            $ext = strtolower($photo->getClientOriginalExtension());
            $filename = 'user_' . $user->id . '.' . $ext;
            $dir = 'profile_photos';
            $newPath = $dir . '/' . $filename;

            // Delete old photo if it exists and is a different path
            if ($user->profile_photo && $user->profile_photo !== $newPath) {
                Storage::disk('public')->delete($user->profile_photo);
            }

            // Store new file
            Storage::disk('public')->putFileAs($dir, $photo, $filename);
            $user->profile_photo = $newPath;
        }

        $user->save();
        return $user;
    }

    public function toggleNotifications(int $userId, bool $enabled): bool
    {
        User::where('id', $userId)->update([
            'email_notifications' => $enabled
        ]);

        return (bool) User::where('id', $userId)->value('email_notifications');
    }

    public function updateUserPassword(User $user, string $plainPassword): void
    {
        $user->password = Hash::make($plainPassword);
        $user->save();

        Log::info("Password updated for User ID: {$user->id}");
    }

    private function buildWeeklyThroughput(int $staffId): array
    {
        // Weekly analytics (Mon–Sun) scoped to the signed-in staff's tickets
        $startOfWeek = Carbon::now()->startOfWeek();
        $endOfWeek = Carbon::now()->endOfWeek();

        $rows = Ticket::where('staff_id', $staffId)
            ->whereBetween('date_created', [$startOfWeek, $endOfWeek])
            ->selectRaw('DATE(date_created) as d, COUNT(*) as c')
            ->groupBy('d')
            ->orderBy('d')
            ->pluck('c', 'd')
            ->toArray();

        $series = [];
        $labels = [];
        $max = 0;

        $cursor = $startOfWeek->copy();
        for ($i = 0; $i < 7; $i++) {
            $dayKey = $cursor->toDateString();
            $count = (int)($rows[$dayKey] ?? 0);
            $series[] = $count;
            $labels[] = $cursor->format('D'); // Mon, Tue, ...
            if ($count > $max) {
                $max = $count;
            }
            $cursor->addDay();
        }

        return [
            'series' => $series,
            'labels' => $labels,
            'max' => $max,
        ];
    }

   
}
