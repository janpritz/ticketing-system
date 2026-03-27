@extends('layouts.admin')

@section('title', 'Deleted Announcements')

@section('admin-content')
    <div class="sm:px-2">
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
            <div>
                <h1 class="text-xl sm:text-2xl font-semibold text-slate-900">Deleted Announcements</h1>
                <p class="text-sm text-gray-600 mt-1">Manage deleted announcements.</p>
            </div>
            <!-- Buttons -->
            @include('dashboards.admin.announcements.components.add-announcement-btn', [
                'isDeletedView' => true,
            ])
        </div>

        <!-- Deleted Announcements List -->
        <div id="deletedAnnouncementsList" class="mt-6 space-y-4">
            <!-- Loaded via AJAX -->
        </div>
    </div>

    <!-- View Deleted Announcement Modal -->
    @include('dashboards.admin.announcements.components.view-announcement-modal')

    @push('scripts')
        @include('dashboards.admin.announcements.scripts')
    @endpush

    <script>
        async function loadDeletedAnnouncements() {
            const list = document.getElementById('deletedAnnouncementsList');
            try {
                list.innerHTML =
                '<div class="text-center text-sm text-gray-500">Loading deleted announcements...</div>';

                const response = await fetch('/admin/announcements/deleted/list');
                const result = await response.json();

                if (!response.ok || !result.announcements) {
                    throw new Error('Failed to load deleted announcements');
                }

                // 🚀 Fix 1: Share the deleted data with the global script variable!
                if (typeof announcementsData !== 'undefined') {
                    announcementsData = result.announcements;
                } else {
                    // Fallback if the script scoping isn't global yet
                    window.announcementsData = result.announcements;
                }

                const announcements = result.announcements;

                if (announcements.length === 0) {
                    list.innerHTML = `
                        <div class="text-center text-gray-500">
                            <svg xmlns="http://www.w3.org/2000/svg" class="mx-auto h-12 w-12 text-gray-400" fill="currentColor" viewBox="0 0 16 16">
                                <path d="M13 2.5a1.5 1.5 0 0 1 3 0v11a1.5 1.5 0 0 1-3 0zm-1 .724c-2.067.95-4.539 1.481-7 1.656v6.237a25 25 0 0 1 1.088.085c2.053.204 4.038.668 5.912 1.56zm-8 7.841V4.934c-.68.027-1.399.043-2.008.053A2.02 2.02 0 0 0 0 7v2c0 1.106.896 1.996 1.994 2.009l.496.008a64 64 0 0 1 1.51.048m1.39 1.081q.428.032.85.078l.253 1.69a1 1 0 0 1-.983 1.187h-.548a1 1 0 0 1-.916-.599l-1.314-2.48a66 66 0 0 1 1.692.064q.491.026.966.06"/>
                            </svg>
                            <h3 class="mt-2 text-sm font-medium text-gray-900">No deleted announcements</h3>
                            <p class="mt-1 text-sm text-gray-500">Deleted announcements will appear here.</p>
                        </div>
                    `;
                    return;
                }

                list.innerHTML = announcements.map(ann => `
                    <div class="p-4 border border-gray-200 rounded-lg bg-gray-50">
                        <div class="flex items-center justify-between">
                            <div class="flex-1 min-w-0">
                                <div class="text-sm font-medium text-gray-900 truncate">${ann.title}</div>
                                <div class="text-xs text-gray-500 mt-1">
                                    Deleted by ${ann.created_by} on ${new Date(ann.deleted_at).toLocaleDateString()}
                                </div>
                            </div>
                            <button onclick="showAnnouncementModal('${ann.id}')"
                                    class="inline-flex items-center px-3 py-1 bg-blue-600 hover:bg-blue-700 text-white text-xs font-medium rounded-md mr-2">
                                View
                            </button>
                        </div>
                    </div>
                `).join('');
            } catch (error) {
                console.error('Error loading deleted announcements:', error);
                list.innerHTML =
                    `<div class="text-center text-sm text-red-600">Error loading deleted announcements: ${error.message}</div>`;
            }
        }

        async function restoreAnnouncement(id) {
            Swal.fire({
                title: 'Are you sure?',
                text: 'This will restore the announcement.',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#3085d6',
                cancelButtonColor: '#d33',
                confirmButtonText: 'Yes, restore it!'
            }).then(async (result) => {
                if (result.isConfirmed) {
                    try {
                        const response = await fetch('/admin/announcements/' + id + '/restore', {
                            method: 'POST',
                            headers: {
                                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute(
                                    'content'),
                                'X-Requested-With': 'XMLHttpRequest'
                            }
                        });

                        const result = await response.json();

                        if (result.success) {
                            Swal.fire('Restored!', 'Announcement has been restored.', 'success');
                            loadDeletedAnnouncements();
                        } else {
                            Swal.fire('Error!', 'Failed to restore announcement.', 'error');
                        }
                    } catch (error) {
                        console.error('Error restoring announcement:', error);
                        Swal.fire('Error!', 'Failed to restore announcement.', 'error');
                    }
                }
            });
        }

        loadDeletedAnnouncements();
    </script>
@endsection
