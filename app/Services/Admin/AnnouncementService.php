<?php

namespace App\Services\Admin;

use Illuminate\Support\Facades\{Auth, DB, Log};
use App\Models\{Announcement, Document, DocumentChange};
use Carbon\Carbon;

class AnnouncementService
{
    public function createAnnouncement(array $data): array
    {
        return DB::transaction(function () use ($data) {
            try {
                // 1. ⏱️ Calculate the 3-day rule validation check
                $start = Carbon::parse($data['starts_at']);
                $end = Carbon::parse($data['expires_at']);

                // Force ABSOLUTE (positive) flat integer comparison
                $daysDifference = (int) $start->diffInDays($end, true);

                if ($daysDifference < 3) {
                    throw new \Exception("Announcements must stay active for a minimum of 3 days. Your dates compute to only {$daysDifference} days.");
                }

                // 2. 💾 Standard Relational Database Insertion
                $announcement = Announcement::create([
                    'title'      => $data['title'],
                    'content'    => $data['content'],
                    'starts_at'  => $start,
                    'expires_at' => $end,
                    'pinned'     => false,
                    'created_by' => Auth::id() ?? 1, // Fallback to System ID if null
                ]);

                // 3. 📝 Push state changes to log table to trigger yellow dashboard banners
                $this->logChange('announcements_table', 'created');

                // // Your legacy mapper (keeping it intact based on your old code)
                // if (method_exists($this, 'mapAnnouncementToAllRoles')) {
                //     $this->mapAnnouncementToAllRoles($announcement->id);
                // }

                return [
                    'success'      => true,
                    'message'      => 'Announcement added successfully.',
                    'announcement' => [
                        'id'    => $announcement->id,
                        'title' => $announcement->title
                    ]
                ];
            } catch (\Exception $e) {
                Log::error('Local Announcement Storage Error: ' . $e->getMessage());
                return [
                    'success' => false,
                    'message' => $e->getMessage(),
                    'status'  => 500
                ];
            }
        });
    }

    private function logChange(string $fileName, string $action): void
    {
        try {
            DocumentChange::create([
                'file_name'          => $fileName,
                'action'             => $action,
                'user_id'            => Auth::id(),
                'user_name'          => Auth::user()->name ?? 'System',
                'training_required'  => true,
                'training_completed' => false,
            ]);
        } catch (\Exception $e) {
            Log::error("KnowledgebaseService Error ($action): " . $e->getMessage());
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


    public function getEnrichedAnnouncements(): array
    {
        try {
            // 🚀 Fetch directly from the database table
            $announcements = Announcement::query()
                ->with(['creator'])
                // 📌 Sort by Pinned first, then by the newest created_at date
                ->orderBy('pinned', 'desc')
                ->orderBy('created_at', 'desc')
                ->get();

            // Map it to match the exact JSON array structure your frontend expects
            $formattedAnnouncements = $announcements->map(fn($ann) => [
                'id'             => $ann->id,
                'title'          => $ann->title,
                'content'        => $ann->content,
                'starts_at'      => $ann->starts_at?->toDateTimeString(),
                'expires_at'     => $ann->expires_at?->toDateTimeString(),
                'pinned'         => (bool) $ann->pinned,
                'created_at'     => $ann->created_at?->toDateTimeString(),
                'role_id'        => $ann->role_id,
                'created_by'     => $ann->creator?->name ?? 'Unknown',

                // Assigned roles (empty for now)
                'assigned_roles' => [],
            ])->values()->toArray();

            return [
                'success'       => true,
                'announcements' => $formattedAnnouncements
            ];
        } catch (\Throwable $e) {
            Log::error('Failed to fetch rich announcements from DB: ' . $e->getMessage());
            return [
                'success' => false,
                'message' => 'Failed to load announcements from the database.'
            ];
        }
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

    public function deleteAnnouncement(int $id): array
    {
        return DB::transaction(function () use ($id) {
            try {
                $announcement = Announcement::findOrFail($id);

                // 🗑️ Soft delete the announcement row
                $announcement->delete();

                // 📝 Log change to notify Rasa retraining engine
                $this->logChange('announcements_table', 'deleted');

                return [
                    'success' => true,
                    'message' => 'Announcement soft-deleted successfully.'
                ];
            } catch (\Exception $e) {
                Log::error('Announcement Delete Error: ' . $e->getMessage());
                return ['success' => false, 'message' => 'Failed to delete: ' . $e->getMessage(), 'status' => 500];
            }
        });
    }
}
