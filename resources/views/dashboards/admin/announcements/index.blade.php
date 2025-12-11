@extends('layouts.admin')

@section('title', 'Announcements')

@section('admin-content')
    <div class="sm:px-2">
        <div class="flex items-center justify-between gap-4">
            <div>
                <h1 class="text-2xl font-semibold text-slate-900">Announcements</h1>
                <p class="text-sm text-gray-600 mt-1">Manage system announcements and notifications.</p>
            </div>
            <!-- Add Announcement Button -->
            <button id="addAnnouncementBtn" type="button"
                class="inline-flex items-center gap-2 rounded-lg bg-blue-600 hover:bg-blue-700 text-white text-sm font-medium px-4 py-2">
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                </svg>
                Add Announcement
            </button>
        </div>
<!-- Announcements List -->
<div class="mt-4 bg-white rounded-xl border border-gray-200 overflow-hidden">
    <div class="p-6">
        <div id="announcementsList" class="space-y-4">
            <div class="text-center text-sm text-gray-500">Loading announcements...</div>
        </div>
    </div>
</div>
</div>

<!-- Add Announcement Modal -->
<div id="addAnnouncementModal" class="fixed inset-0 z-50 hidden">
<div class="absolute inset-0 bg-black/40" data-close="announcement"></div>
<div class="absolute inset-0 flex items-center justify-center p-4">
    <div class="w-full max-w-full sm:max-w-lg bg-white rounded-none sm:rounded-lg shadow border border-gray-200 overflow-auto max-h-[90vh]">
        <div class="h-12 flex items-center justify-between px-4 border-b">
            <div class="text-sm font-semibold text-slate-800">Add Announcement</div>
            <button type="button" class="text-slate-500 hover:text-slate-700" data-close="announcement"
                    aria-label="Close">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 24 24" fill="currentColor">
                    <path d="M6 18 18 6M6 6l12 12" />
                </svg>
            </button>
        </div>
        <form id="addAnnouncementForm" class="p-4 space-y-4">
            <div>
                <label class="block text-sm font-medium text-slate-700">Announcement Content</label>
                <textarea id="announcementContent" rows="6" required
                          class="mt-1 w-full rounded-md border border-gray-300 px-3 py-2 text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                          placeholder="Enter your announcement here..."></textarea>
                <p class="mt-1 text-xs text-slate-500">Announcements are for short-term, constantly changing information like enrollment schedules and document releases.</p>
                <p id="announcement_error" class="mt-1 text-xs text-red-600 hidden"></p>
            </div>
            <div class="pt-2 flex items-center justify-end gap-3">
                <button type="button" class="rounded-md border border-gray-300 bg-white hover:bg-gray-50 text-sm px-4 py-2"
                        data-close="announcement">Cancel</button>
                <button id="addAnnouncementSubmit" type="button"
                        class="rounded-md bg-blue-600 hover:bg-blue-700 text-white text-sm font-medium px-4 py-2">Add Announcement</button>
            </div>
        </form>
    </div>
</div>
</div>

@endsection

@section('admin-scripts')
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
    // SweetAlert helpers
    function showToast(type, message) {
        Swal.fire({
            toast: true,
            position: 'top-end',
            icon: type === 'error' ? 'error' : (type === 'success' ? 'success' : 'info'),
            title: message,
            showConfirmButton: false,
            timer: 3000,
            timerProgressBar: true
        });
    }

    (function() {
        const $ = (sel, root = document) => root.querySelector(sel);
        const $$ = (sel, root = document) => Array.from(root.querySelectorAll(sel));

        // Modal elements
        const addAnnouncementModal = $('#addAnnouncementModal');
        const addAnnouncementBtn = $('#addAnnouncementBtn');
        const addAnnouncementForm = $('#addAnnouncementForm');
        const addAnnouncementSubmit = $('#addAnnouncementSubmit');
        const announcementContent = $('#announcementContent');
        const closeButtons = $$('[data-close="announcement"]');

        // Modal handlers
        if (addAnnouncementBtn) {
            addAnnouncementBtn.addEventListener('click', () => {
                addAnnouncementModal.classList.remove('hidden');
            });
        }

        closeButtons.forEach(btn => {
            btn.addEventListener('click', () => {
                addAnnouncementModal.classList.add('hidden');
                addAnnouncementForm.reset();
                $('#announcement_error').classList.add('hidden');
            });
        });

        // Form submission
        if (addAnnouncementSubmit) {
            addAnnouncementSubmit.addEventListener('click', async () => {
                const content = announcementContent.value.trim();

                if (!content) {
                    $('#announcement_error').textContent = 'Please enter announcement content';
                    $('#announcement_error').classList.remove('hidden');
                    return;
                }

                $('#announcement_error').classList.add('hidden');

                // Show loading state
                const originalHTML = addAnnouncementSubmit.innerHTML;
                addAnnouncementSubmit.disabled = true;
                addAnnouncementSubmit.innerHTML = `
                    <svg class="animate-spin h-4 w-4 mx-auto" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                    </svg>
                    <span class="ml-2">Adding...</span>
                `;

                try {
                    const response = await fetch('/admin/announcements', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                            'X-Requested-With': 'XMLHttpRequest'
                        },
                        body: JSON.stringify({ content })
                    });

                    const result = await response.json();

                    if (!response.ok || !result.success) {
                        throw new Error(result.message || 'Failed to add announcement');
                    }

                    showToast('success', 'Announcement added successfully');
                    addAnnouncementModal.classList.add('hidden');
                    addAnnouncementForm.reset();
                    loadAnnouncements(); // Refresh the list

                } catch (error) {
                    console.error('Error adding announcement:', error);
                    showToast('error', error.message || 'Failed to add announcement');
                } finally {
                    // Reset button state
                    addAnnouncementSubmit.disabled = false;
                    addAnnouncementSubmit.innerHTML = originalHTML;
                }
            });
        }

        // Load announcements function
        async function loadAnnouncements() {
            const announcementsList = $('#announcementsList');
            try {
                announcementsList.innerHTML = '<div class="text-center text-sm text-gray-500">Loading announcements...</div>';

                const response = await fetch('/admin/announcements/list', {
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest'
                    }
                });

                const result = await response.json();

                if (!response.ok || !result.success) {
                    throw new Error(result.message || 'Failed to load announcements');
                }

                renderAnnouncements(result.announcements || []);

            } catch (error) {
                console.error('Error loading announcements:', error);
                announcementsList.innerHTML = `<div class="text-center text-sm text-red-600">Error loading announcements: ${error.message}</div>`;
            }
        }

        // Render announcements
        function renderAnnouncements(announcements) {
            const announcementsList = $('#announcementsList');

            if (!announcements || announcements.length === 0) {
                announcementsList.innerHTML = `
                    <div class="text-center text-gray-500">
                        <svg xmlns="http://www.w3.org/2000/svg" class="mx-auto h-12 w-12 text-gray-400" fill="currentColor" viewBox="0 0 16 16">
                            <path d="M13 2.5a1.5 1.5 0 0 1 3 0v11a1.5 1.5 0 0 1-3 0zm-1 .724c-2.067.95-4.539 1.481-7 1.656v6.237a25 25 0 0 1 1.088.085c2.053.204 4.038.668 5.912 1.56zm-8 7.841V4.934c-.68.027-1.399.043-2.008.053A2.02 2.02 0 0 0 0 7v2c0 1.106.896 1.996 1.994 2.009l.496.008a64 64 0 0 1 1.51.048m1.39 1.081q.428.032.85.078l.253 1.69a1 1 0 0 1-.983 1.187h-.548a1 1 0 0 1-.916-.599l-1.314-2.48a66 66 0 0 1 1.692.064q.491.026.966.06"/>
                        </svg>
                        <h3 class="mt-2 text-sm font-medium text-gray-900">No announcements yet</h3>
                        <p class="mt-1 text-sm text-gray-500">Get started by creating your first announcement.</p>
                    </div>
                `;
                return;
            }

            announcementsList.innerHTML = announcements.map(announcement => `
                <div class="flex items-start gap-4 p-4 border border-gray-200 rounded-lg">
                    <div class="flex-shrink-0 w-8 h-8 bg-blue-100 rounded-full flex items-center justify-center">
                        <span class="text-sm font-medium text-blue-600">${announcement.id}</span>
                    </div>
                    <div class="flex-1">
                        <div class="text-sm text-gray-900 whitespace-pre-line">${escapeHtml(announcement.content)}</div>
                    </div>
                </div>
            `).join('');
        }

        // Utility function to escape HTML
        function escapeHtml(text) {
            const div = document.createElement('div');
            div.textContent = text;
            return div.innerHTML;
        }

        // Initialize - load announcements on page load
        loadAnnouncements();
    })();
</script>

@endsection