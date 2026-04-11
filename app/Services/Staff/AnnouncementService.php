<?php

namespace App\Services\Staff;

use App\Models\{Announcement, DocumentChange, User};
use Illuminate\Support\Facades\{DB, Log};
use Carbon\Carbon;

class AnnouncementService
{
    public function createAnnouncement(array $data, User $user): Announcement
    {
        $start = Carbon::parse($data['starts_at']);
        $end = Carbon::parse($data['expires_at']);

        // Must stay active for at least 3 days (admin logic equivalent)
        $daysDifference = (int) $start->diffInDays($end, true);
        if ($daysDifference < 3) {
            throw new \Exception(
                "Announcements must stay active for a minimum of 3 days. Your dates compute to only {$daysDifference} days."
            );
        }

        $announcement = Announcement::create([
            'title'      => $data['title'],
            'content'    => $data['content'],
            'starts_at'  => $start,
            'expires_at' => $end,
            'role_id'    => $user->role_id ?: null,
            'created_by' => $user->id,
            'pinned'     => false,
        ]);

        // Log to DocumentChange for global training alert
        DocumentChange::create([
            'file_name'          => 'announcements_table',
            'action'             => 'created',
            'user_id'            => $user->id,
            'user_name'          => $user->name,
            'training_required'  => true,
            'training_completed' => false,
        ]);

        return $announcement;
    }

    /**
     * Formats all announcements into a single text block for Rasa.
     */
    protected function rebuildAnnouncementsMasterContent(): string
    {
        return Announcement::orderByDesc('pinned')
            ->orderByDesc('id')
            ->get()
            ->map(function ($a) {
                return "id: {$a->id}\ntitle: {$a->title}\n{$a->content}\n---------";
            })
            ->implode("\n");
    }

    
    /**
     * Check if an announcement title already exists (case-insensitive and trimmed).
     *
     * @param string $title
     * @param int|null $excludeId Use this when updating to ignore the current record.
     * @return bool
     */
    public function isDuplicateTitle(string $title, ?int $excludeId = null): bool
    {
        $query = Announcement::whereRaw(
            'LOWER(TRIM(title)) = ?',
            [strtolower(trim($title))]
        );

        if ($excludeId) {
            $query->where('id', '!=', $excludeId);
        }

        return $query->exists();
    }

    /**
     * Fetch and format announcements based on user role.
     */
    public function getAnnouncementsForUser(User $user): array
    {
        $query = Announcement::query()
            ->active()
            ->with(['creator'])
            ->orderBy('pinned', 'desc')
            ->orderBy('created_at', 'desc');

        if (!$user->isPrimaryAdmin()) {
            $query->where('created_by', $user->id);
        }

        $announcements = $query->get();

        $formattedAnnouncements = $announcements->map(function ($ann) {
            return [
                'id' => $ann->id,
                'title' => $ann->title,
                'content' => $ann->content,
                'starts_at' => $ann->starts_at?->toDateTimeString(),
                'expires_at' => $ann->expires_at?->toDateTimeString(),
                // Admin pin uses legacy `pinned`; staff pin should use `staff_pinned` (nullable)
                'pinned' => (bool) $ann->pinned,
                'staff_pinned' => $ann->staff_pinned,
                'created_at' => $ann->created_at?->toDateTimeString(),
                'role_id' => $ann->role_id,
                'created_by' => $ann->creator?->name ?? 'Unknown',
                'assigned_roles' => $ann->role_id ? [(int) $ann->role_id] : [],
            ];
        })->values()->toArray();

        return $formattedAnnouncements;
    }

    public function toggleStaffPin(Announcement $announcement): bool
    {
        // staff_pinned is nullable boolean; treat null as not pinned
        $announcement->staff_pinned = !($announcement->staff_pinned ?? false);
        $announcement->save();

        return (bool) $announcement->staff_pinned;
    }

    /**
     * Helper to resolve roles from either the pivot table or the legacy column.
     */
    protected function getAssignedRoleIds(Announcement $announcement): array
    {
        $ids = $announcement->roles->pluck('id')->toArray();

        if (empty($ids) && $announcement->role_id) {
            return [(int) $announcement->role_id];
        }

        return $ids;
    }

    public function updateAnnouncement(Announcement $announcement, array $data, User $user): void
    {
        $announcement->update([
            'title' => $data['title'],
            'content' => $data['content'],
        ]);

        // Log to DocumentChange for global training alert
        DocumentChange::create([
            'file_name'          => 'announcements_table',
            'action'             => 'updated',
            'user_id'            => $user->id,
            'user_name'          => $user->name,
            'training_required'  => true,
            'training_completed' => false,
        ]);
    }

    public function deleteAnnouncement(Announcement $announcement, User $user): void
    {
        $announcement->delete();

        // Log to DocumentChange for global training alert
        DocumentChange::create([
            'file_name'          => 'announcements_table',
            'action'             => 'deleted',
            'user_id'            => $user->id,
            'user_name'          => $user->name,
            'training_required'  => true,
            'training_completed' => false,
        ]);
    }

    /**
     * Restore a soft-deleted announcement and log the change to DocumentChange.
     */
    public function restoreAnnouncement(int $id, User $user): bool
    {
        $announcement = Announcement::onlyTrashed()->find($id);
        if (!$announcement) {
            return false;
        }

        $announcement->restore();

        // Log to DocumentChange for global training alert
        DocumentChange::create([
            'file_name'          => 'announcements_table',
            'action'             => 'restored',
            'user_id'            => $user->id,
            'user_name'          => $user->name,
            'training_required'  => true,
            'training_completed' => false,
        ]);

        return true;
    }
    /**
     * Toggle pin status, rebuild the master file, and sync to Rasa.
     */
    public function toggleAnnouncementPin(Announcement $announcement, User $user): bool
    {
        return DB::transaction(function () use ($announcement, $user) {
            $pivotTable = 'pinned_announcements';

            $existing = DB::table($pivotTable)
                ->where('announcement_id', $announcement->id)
                ->exists();

            if ($existing) {
                DB::table($pivotTable)->where('announcement_id', $announcement->id)->delete();
                $pinned = false;
            } else {
                DB::table($pivotTable)->insert([
                    'announcement_id' => $announcement->id,
                    'created_at'      => now(),
                    'updated_at'      => now()
                ]);
                $pinned = true;
            }

            // Re-sync with Rasa because order has changed
            $masterContent = $this->rebuildAnnouncementsMasterContent();
            //$this->syncAnnouncementsToRasa($masterContent);

            // Log the change
            DocumentChange::create([
                'file_name'          => 'Announcements.txt',
                'action'             => 'updated', // Re-ordering is an update
                'user_id'            => $user->id,
                'user_name'          => $user->name,
                'training_required'  => true,
                'training_completed' => false,
            ]);

            return $pinned;
        });
    }
}
