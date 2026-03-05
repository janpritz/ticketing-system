<?php

namespace App\Services\Staff;

use App\Models\{Announcement, User, DocumentChange};
use Illuminate\Support\Facades\{DB, Http, Log};

class AnnouncementService
{
    /**
     * Create an announcement, rebuild the master file, and sync to Rasa.
     */
    public function createAnnouncementAndSync(array $data, User $user): Announcement
    {
        return DB::transaction(function () use ($data, $user) {
            // 1. Create the database record
            $announcement = Announcement::create([
                'title'      => $data['title'],
                'content'    => $data['content'],
                'role_id'    => $user->role_id ?: null,
                'created_by' => $user->id,
                'pinned'     => false,
            ]);

            // 2. Rebuild the consolidated Announcements.txt content
            $masterContent = $this->rebuildAnnouncementsMasterContent();

            // 3. Sync to Rasa (One file containing all announcements)
            $this->syncAnnouncementsToRasa($masterContent);

            // 4. Log change for training
            DocumentChange::create([
                'file_name'          => 'Announcements.txt',
                'action'             => 'created',
                'user_id'            => $user->id,
                'user_name'          => $user->name,
                'training_required'  => true,
                'training_completed' => false,
            ]);

            return $announcement;
        });
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
     * Handle the HTTP request to the Rasa server.
     */
    protected function syncAnnouncementsToRasa(string $content): void
    {
        $rasaUrl = config('services.faq_list_docs.url');
        $secret = config('services.faq_list_docs.secret');

        if ($rasaUrl && $secret) {
            $uploadUrl = str_replace('/list-docs', '/update-document', $rasaUrl);

            $response = Http::withHeaders([
                'X-FAQ-UPDATER-TOKEN' => $secret,
                'X-Requested-With'    => 'XMLHttpRequest'
            ])->post($uploadUrl, [
                'file_name'    => 'Announcements.txt',
                'file_content' => $content,
                'file_type'    => 'txt'
            ]);

            if (!$response->successful()) {
                Log::error('Rasa Announcements Sync Failed: ' . $response->status());
            }
        }
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
        // 1. Start the query with roles eager-loaded
        $query = Announcement::with('roles')
            ->orderByDesc('pinned')
            ->orderByDesc('id');

        // 2. Apply visibility filter for non-admins
        if (!$user->isPrimaryAdmin()) {
            $query->forRole($user->role_id);
        }

        // 3. Transform the collection into the desired JSON structure
        return $query->get()->map(function ($a) {
            return [
                'id'             => (int) $a->id,
                'title'          => (string) $a->title,
                'content'        => (string) $a->content,
                'pinned'         => (bool) $a->pinned,
                'assigned_roles' => $this->getAssignedRoleIds($a),
                'created_by'     => $a->created_by,
                'created_at'     => $a->created_at?->toDateTimeString(),
                'updated_at'     => $a->updated_at?->toDateTimeString(),
            ];
        })->toArray();
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

    /**
     * Update the record, ensure role mapping, and push the master file to Rasa.
     */
    public function updateAnnouncementAndSync(Announcement $announcement, array $data, User $user): void
    {
        DB::transaction(function () use ($announcement, $data, $user) {
            // 1. Update the DB record
            $announcement->update([
                'title'   => $data['title'],
                'content' => $data['content'],
            ]);

            // 2. Ensure role mapping exists for the current user's role
            if ($user->role_id) {
                $announcement->roles()->syncWithoutDetaching([$user->role_id]);
            }

            // 3. Rebuild the master block (stripping "Roles:" lines via model logic)
            $masterContent = $this->rebuildAnnouncementsMasterContent();

            // 4. Push to Rasa
            $this->syncAnnouncementsToRasa($masterContent);

            // 5. Log for Training Alert
            DocumentChange::create([
                'file_name'          => 'Announcements.txt',
                'action'             => 'updated',
                'user_id'            => $user->id,
                'user_name'          => $user->name,
                'training_required'  => true,
                'training_completed' => false,
            ]);
        });
    }

    /**
     * Delete the announcement, clean up roles, and sync the new state to Rasa.
     */
    public function deleteAnnouncementAndSync(Announcement $announcement, User $user): void
    {
        DB::transaction(function () use ($announcement, $user) {
            // 1. Remove pivot table mappings automatically
            $announcement->roles()->detach();

            // 2. Delete the DB record
            $announcement->delete();

            // 3. Rebuild the master block (now excluding the deleted item)
            $masterContent = $this->rebuildAnnouncementsMasterContent();

            // 4. Push updated consolidated file to Rasa
            $this->syncAnnouncementsToRasa($masterContent);

            // 5. Log change for training alert
            DocumentChange::create([
                'file_name'          => 'Announcements.txt',
                'action'             => 'deleted',
                'user_id'            => $user->id,
                'user_name'          => $user->name,
                'training_required'  => true,
                'training_completed' => false,
            ]);
        });
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
            $this->syncAnnouncementsToRasa($masterContent);

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
