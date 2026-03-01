<?php

namespace App\Services\Admin;

use Illuminate\Support\Facades\{Auth, DB, Log, Http};
use App\Models\{Document, DocumentChange, Role};


class AnnouncementService
{
    public function createAnnouncementWithRasa(array $data): array
    {
        // 1. Availability Check
        if (!\App\Services\RasaServerService::isServerOnline()) {
            return [
                'success' => false,
                'message' => 'Rasa server is offline. Queued uploads are disabled.',
                'status'  => 503
            ];
        }

        try {
            $rasaConfig = config('services.faq_list_docs');
            $rasaUrl    = $rasaConfig['url'];
            $secret     = $rasaConfig['secret'];

            // 2. Determine Next ID & Current Content
            $downloadUrl = str_replace('/list-docs', '/download/Announcements.txt', $rasaUrl);
            $listUrl     = str_replace('/list-docs', '/download-announcements', $rasaUrl);

            $listResponse = Http::withHeaders(['X-FAQ-UPDATER-TOKEN' => $secret])->get($listUrl);

            $nextId = 1;
            $currentFileContent = "";

            if ($listResponse->successful()) {
                $listData = $listResponse->json();
                if ($listData['ok'] && !empty($listData['announcements'])) {
                    $nextId = max(array_column($listData['announcements'], 'id')) + 1;

                    // Fetch existing content to append
                    $downloadResponse = Http::withHeaders(['X-FAQ-UPDATER-TOKEN' => $secret])->get($downloadUrl);
                    if ($downloadResponse->successful()) {
                        $currentFileContent = $downloadResponse->body();
                    }
                }
            }

            // 3. Prepare New Content
            $newEntry = "id: {$nextId}\ntitle: {$data['title']}\n{$data['content']}\n---------\n";
            $finalContent = $currentFileContent . $newEntry;

            // 4. Upload to Rasa
            $uploadUrl = str_replace('/list-docs', '/update-document', $rasaUrl);
            $uploadResponse = Http::withHeaders(['X-FAQ-UPDATER-TOKEN' => $secret])->post($uploadUrl, [
                'file_name'    => 'Announcements.txt',
                'file_content' => $finalContent,
                'file_type'    => 'txt'
            ]);

            if (!$uploadResponse->successful() || !($uploadResponse->json()['ok'] ?? false)) {
                throw new \Exception('Rasa upload failed.');
            }

            // 5. Post-Upload Actions (Logging & Role Mapping)
            $this->logAnnouncementChange('Announcements.txt', 'created');
            $this->mapAnnouncementToAllRoles($nextId);

            return [
                'success' => true,
                'message' => 'Announcement added successfully',
                'announcement' => ['id' => $nextId, 'title' => $data['title']]
            ];
        } catch (\Exception $e) {
            Log::error('AnnouncementService Error: ' . $e->getMessage());
            return ['success' => false, 'message' => $e->getMessage(), 'status' => 500];
        }
    }

    private function logAnnouncementChange(string $file, string $action): void
    {
        DocumentChange::create([
            'file_name' => $file,
            'action'    => $action,
            'user_id'   => Auth::id(),
            'user_name' => Auth::user()->name ?? null,
            'training_required' => true,
            'training_completed' => false,
        ]);
    }
    private function mapAnnouncementToAllRoles(int $announcementId): void
    {
        $roleIds = Role::pluck('id');
        $rows = $roleIds->map(fn($roleId) => [
            'announcement_id' => $announcementId,
            'role_id'         => $roleId,
            'created_at'      => now(),
            'updated_at'      => now(),
        ])->toArray();

        DB::table('announcement_roles')->insert($rows);
    }

    public function getEnrichedAnnouncements(): array
    {
        try {
            $rasaConfig = config('services.faq_list_docs');
            $url = str_replace('/list-docs', '/download-announcements', $rasaConfig['url']);

            // 1. Fetch Remote Announcements
            $response = Http::withHeaders([
                'X-FAQ-UPDATER-TOKEN' => $rasaConfig['secret'],
                'X-Requested-With' => 'XMLHttpRequest'
            ])->get($url);

            if (!$response->successful() || !($response->json()['ok'] ?? false)) {
                throw new \Exception('Rasa connection failed');
            }

            $announcements = $response->json()['announcements'] ?? [];

            // Extract IDs for bulk DB lookups
            $ids = collect($announcements)->pluck('id')->filter()->map(fn($id) => (int)$id)->toArray();

            // 2. Fetch Local DB State
            $roleMap = $this->getRoleMapping($ids);
            $pinnedIds = DB::table('pinned_announcements')->pluck('announcement_id')->toArray();

            // 3. Enrich Data
            foreach ($announcements as &$ann) {
                $aid = (int)($ann['id'] ?? 0);
                $ann['assigned_roles'] = $roleMap[$aid] ?? [];
                $ann['pinned'] = in_array($aid, $pinnedIds);
            }

            // 4. Sort: Pinned first, then ID Descending
            usort($announcements, function ($a, $b) {
                if ($a['pinned'] !== $b['pinned']) {
                    return $b['pinned'] <=> $a['pinned'];
                }
                return $b['id'] <=> $a['id'];
            });

            return [
                'success' => true,
                'announcements' => $announcements
            ];
        } catch (\Exception $e) {
            Log::error('AnnouncementService List Error: ' . $e->getMessage());
            return ['success' => false, 'message' => 'Rasa server is offline.'];
        }
    }

    private function getRoleMapping(array $ids): array
    {
        if (empty($ids)) return [];

        return DB::table('announcement_roles')
            ->whereIn('announcement_id', $ids)
            ->get()
            ->groupBy('announcement_id')
            ->map(fn($group) => $group->pluck('role_id')->map(fn($id) => (int)$id)->toArray())
            ->toArray();
    }

    public function updateAnnouncementOnRasa(int $id, array $data): array
    {
        try {
            $rasaConfig = config('services.faq_list_docs');
            $secret = $rasaConfig['secret'];
            $announcementsUrl = str_replace('/list-docs', '/download-announcements', $rasaConfig['url']);

            // 1. Fetch current list to modify
            $listResponse = Http::withHeaders(['X-FAQ-UPDATER-TOKEN' => $secret])->get($announcementsUrl);

            if (!$listResponse->successful()) {
                throw new \Exception('Failed to fetch announcements from Rasa server.');
            }

            $announcements = $listResponse->json()['announcements'] ?? [];
            $found = false;

            // 2. Modify the specific entry in the array
            foreach ($announcements as &$ann) {
                if ((int)$ann['id'] === $id) {
                    $ann['title'] = $data['title'];
                    $ann['content'] = $data['content'];
                    $found = true;
                    break;
                }
            }

            if (!$found) {
                return ['success' => false, 'message' => 'Announcement not found', 'status' => 404];
            }

            // 3. Reconstruct the raw .txt content
            $rawContent = "";
            foreach ($announcements as $ann) {
                $cleanContent = $this->stripLeadingRolesLine((string)($ann['content'] ?? ''));
                $rawContent .= "id: {$ann['id']}\ntitle: {$ann['title']}\n{$cleanContent}\n---------\n";
            }

            // 4. Upload the full reconstructed file
            $uploadUrl = str_replace('/list-docs', '/update-document', $rasaConfig['url']);
            $uploadResponse = Http::withHeaders(['X-FAQ-UPDATER-TOKEN' => $secret])->post($uploadUrl, [
                'file_name' => 'Announcements.txt',
                'file_content' => $rawContent,
                'file_type' => 'txt'
            ]);

            if (!$uploadResponse->successful()) {
                throw new \Exception('Failed to upload updated file to Rasa.');
            }

            // 5. Post-Update: Sync Roles & Log Change
            $this->logAnnouncementChange('Announcements.txt', 'updated');
            $this->syncAnnouncementRoles($id);

            return ['success' => true, 'message' => 'Announcement updated successfully'];
        } catch (\Exception $e) {
            Log::error('AnnouncementService Update Error: ' . $e->getMessage());
            return ['success' => false, 'message' => $e->getMessage()];
        }
    }

    private function stripLeadingRolesLine(string $content): string
    {
        // Remove "roles: 1,2,3" (or similar) from the very start of the string
        // including the trailing newline.
        return preg_replace('/^roles:\s*[\d,]*\s*\n?/i', '', $content);
    }

    private function syncAnnouncementRoles(int $id): void
    {
        $roleIds = Role::pluck('id');

        DB::transaction(function () use ($id, $roleIds) {
            // Clear old mappings
            DB::table('announcement_roles')->where('announcement_id', $id)->delete();

            // Re-insert mappings for all roles
            $rows = $roleIds->map(fn($roleId) => [
                'announcement_id' => $id,
                'role_id' => $roleId,
                'created_at' => now(),
                'updated_at' => now(),
            ])->toArray();

            DB::table('announcement_roles')->insert($rows);
        });
    }

    public function deleteAnnouncementFromRasa(int $id): array
    {
        try {
            $rasaConfig = config('services.faq_list_docs');
            $secret = $rasaConfig['secret'];
            $announcementsUrl = str_replace('/list-docs', '/download-announcements', $rasaConfig['url']);

            // 1. Fetch current list
            $listResponse = Http::withHeaders(['X-FAQ-UPDATER-TOKEN' => $secret])->get($announcementsUrl);

            if (!$listResponse->successful()) {
                throw new \Exception('Failed to fetch announcements from Rasa server.');
            }

            $announcements = $listResponse->json()['announcements'] ?? [];

            // 2. Filter out the deleted ID
            $filtered = array_filter($announcements, fn($ann) => (int)$ann['id'] !== $id);

            // 3. Reconstruct the file content
            $rawContent = "";
            foreach ($filtered as $ann) {
                $cleanContent = $this->stripLeadingRolesLine((string)($ann['content'] ?? ''));
                $rawContent .= "id: {$ann['id']}\ntitle: {$ann['title']}\n{$cleanContent}\n---------\n";
            }

            // 4. Upload the updated file
            $uploadUrl = str_replace('/list-docs', '/update-document', $rasaConfig['url']);
            $uploadResponse = Http::withHeaders(['X-FAQ-UPDATER-TOKEN' => $secret])->post($uploadUrl, [
                'file_name' => 'Announcements.txt',
                'file_content' => $rawContent,
                'file_type' => 'txt'
            ]);

            if (!$uploadResponse->successful()) {
                throw new \Exception('Failed to sync deletion to Rasa server.');
            }

            // 5. Cleanup local DB (Roles and Pins)
            $this->cleanupLocalAnnouncementData($id);
            $this->logAnnouncementChange('Announcements.txt', 'deleted');

            return [
                'success' => true,
                'message' => 'Announcement deleted successfully'
            ];
        } catch (\Exception $e) {
            Log::error('AnnouncementService Delete Error: ' . $e->getMessage());
            return ['success' => false, 'message' => $e->getMessage()];
        }
    }
    private function cleanupLocalAnnouncementData(int $id): void
    {
        DB::transaction(function () use ($id) {
            // Remove role mappings
            DB::table('announcement_roles')->where('announcement_id', $id)->delete();

            // Remove pinning status
            DB::table('pinned_announcements')->where('announcement_id', $id)->delete();
        });
    }

    public function toggleAnnouncementPin(int $id): array
    {
        try {
            $table = DB::table('pinned_announcements');
            $existing = $table->where('announcement_id', $id)->first();

            if ($existing) {
                // Unpin logic
                $table->where('announcement_id', $id)->delete();
                $message = 'Announcement unpinned successfully';
            } else {
                // Pin logic
                $table->insert([
                    'announcement_id' => $id,
                    'created_at' => now(),
                    'updated_at' => now()
                ]);
                $message = 'Announcement pinned successfully';
            }

            return [
                'success' => true,
                'message' => $message
            ];
        } catch (\Exception $e) {
            Log::error('AnnouncementService Pin Error: ' . $e->getMessage());
            return [
                'success' => false,
                'message' => 'Database error during pin toggle.'
            ];
        }
    }
}
