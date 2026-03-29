<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
    @include('dashboards.staff.announcements.scripts.sweetalert-helpers')

    (function() {
        const list = document.getElementById('deletedAnnouncementsList');
        const modal = document.getElementById('viewAnnouncementModal');
        const titleDisplay = document.getElementById('viewAnnouncementTitleDisplay');
        const contentDisplay = document.getElementById('viewAnnouncementContentDisplay');
        const closeButtons = Array.from(document.querySelectorAll('[data-close="view-announcement"]'));
        const announcementMenu = document.getElementById('announcementMenu');
        const announcementMenuBtn = document.getElementById('announcementMenuBtn');
        const editMenu = document.getElementById('editAnnouncementMenu');
        const pinMenu = document.getElementById('pinAnnouncementMenu');
        const deleteMenu = document.getElementById('deleteAnnouncementMenu');
        const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');

        let deletedAnnouncements = [];
        let currentAnnouncementId = null;

        if (!list) {
            return;
        }

        if (announcementMenuBtn) {
            announcementMenuBtn.addEventListener('click', () => {
                announcementMenu?.classList.toggle('hidden');
            });
        }

        closeButtons.forEach((button) => {
            button.addEventListener('click', () => {
                modal?.classList.add('hidden');
                announcementMenu?.classList.add('hidden');
            });
        });

        editMenu?.classList.add('hidden');
        pinMenu?.classList.add('hidden');

        if (deleteMenu) {
            deleteMenu.textContent = 'Restore';
            deleteMenu.addEventListener('click', async () => {
                if (currentAnnouncementId !== null) {
                    await restoreAnnouncement(currentAnnouncementId, true);
                }
            });
        }

        function escapeHtml(text) {
            const div = document.createElement('div');
            div.textContent = text ?? '';
            return div.innerHTML;
        }

        function formatDeletedDate(value) {
            if (!value) {
                return 'Unknown date';
            }

            const date = new Date(value);
            return Number.isNaN(date.getTime()) ? 'Unknown date' : date.toLocaleDateString();
        }

        function renderEmptyState() {
            list.innerHTML = `
                <div class="text-center text-gray-500">
                    <svg xmlns="http://www.w3.org/2000/svg" class="mx-auto h-12 w-12 text-gray-400" fill="currentColor" viewBox="0 0 16 16">
                        <path d="M13 2.5a1.5 1.5 0 0 1 3 0v11a1.5 1.5 0 0 1-3 0zm-1 .724c-2.067.95-4.539 1.481-7 1.656v6.237a25 25 0 0 1 1.088.085c2.053.204 4.038.668 5.912 1.56zm-8 7.841V4.934c-.68.027-1.399.043-2.008.053A2.02 2.02 0 0 0 0 7v2c0 1.106.896 1.996 1.994 2.009l.496.008a64 64 0 0 1 1.51.048m1.39 1.081q.428.032.85.078l.253 1.69a1 1 0 0 1-.983 1.187h-.548a1 1 0 0 1-.916-.599l1.314-2.48a66 66 0 0 1 1.692.064q.491.026.966.06"/>
                    </svg>
                    <h3 class="mt-2 text-sm font-medium text-gray-900">No deleted announcements</h3>
                    <p class="mt-1 text-sm text-gray-500">Deleted announcements will appear here.</p>
                </div>
            `;
        }

        function renderDeletedAnnouncements(items) {
            if (!items.length) {
                renderEmptyState();
                return;
            }

            list.innerHTML = items.map((announcement) => `
                <div class="p-4 border border-gray-200 rounded-lg bg-gray-50">
                    <div class="flex items-center justify-between gap-4">
                        <div class="flex-1 min-w-0">
                            <div class="text-sm font-medium text-gray-900 truncate">${escapeHtml(announcement.title)}</div>
                            <div class="text-xs text-gray-500 mt-1">
                                Deleted by ${escapeHtml(announcement.created_by || announcement.creator_name || 'Unknown')} on ${formatDeletedDate(announcement.deleted_at)}
                            </div>
                        </div>
                        <div class="flex gap-2">
                            <button type="button" data-view-id="${announcement.id}"
                                class="inline-flex items-center px-3 py-1 bg-blue-600 hover:bg-blue-700 text-white text-xs font-medium rounded-md">
                                View
                            </button>
                            <button type="button" data-restore-id="${announcement.id}"
                                class="inline-flex items-center px-3 py-1 bg-green-600 hover:bg-green-700 text-white text-xs font-medium rounded-md">
                                Restore
                            </button>
                        </div>
                    </div>
                </div>
            `).join('');

            list.querySelectorAll('[data-view-id]').forEach((button) => {
                button.addEventListener('click', () => {
                    showDeletedAnnouncementModal(Number(button.dataset.viewId));
                });
            });

            list.querySelectorAll('[data-restore-id]').forEach((button) => {
                button.addEventListener('click', async () => {
                    await restoreAnnouncement(Number(button.dataset.restoreId));
                });
            });
        }

        function showDeletedAnnouncementModal(id) {
            const announcement = deletedAnnouncements.find((item) => Number(item.id) === Number(id));

            if (!announcement || !modal || !titleDisplay || !contentDisplay) {
                return;
            }

            currentAnnouncementId = Number(id);
            titleDisplay.textContent = announcement.title || `Announcement ${id}`;
            contentDisplay.textContent = announcement.content || 'No content available.';
            modal.classList.remove('hidden');
        }

        async function loadDeletedAnnouncements() {
            try {
                list.innerHTML = '<div class="text-center text-sm text-gray-500">Loading deleted announcements...</div>';

                const response = await fetch('/staff/announcements/deleted/list', {
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest'
                    }
                });

                const result = await response.json();

                if (!response.ok || !result.success) {
                    throw new Error(result.message || 'Failed to load deleted announcements');
                }

                deletedAnnouncements = result.announcements || [];
                renderDeletedAnnouncements(deletedAnnouncements);
            } catch (error) {
                console.error('Error loading deleted announcements:', error);
                list.innerHTML = `<div class="text-center text-sm text-red-600">Error loading deleted announcements: ${error.message}</div>`;
            }
        }

        async function restoreAnnouncement(id, fromModal = false) {
            const confirmation = await Swal.fire({
                title: 'Are you sure?',
                text: 'This will restore the announcement.',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#3085d6',
                cancelButtonColor: '#d33',
                confirmButtonText: 'Yes, restore it!'
            });

            if (!confirmation.isConfirmed) {
                return;
            }

            try {
                const response = await fetch('/staff/announcements/' + id + '/restore', {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': csrfToken,
                        'X-Requested-With': 'XMLHttpRequest'
                    }
                });

                const result = await response.json();

                if (!response.ok || !result.success) {
                    throw new Error(result.message || 'Failed to restore announcement');
                }

                await Swal.fire('Restored!', 'Announcement has been restored.', 'success');

                if (fromModal) {
                    modal?.classList.add('hidden');
                }

                announcementMenu?.classList.add('hidden');
                await loadDeletedAnnouncements();
            } catch (error) {
                console.error('Error restoring announcement:', error);
                Swal.fire('Error!', error.message || 'Failed to restore announcement.', 'error');
            }
        }

        loadDeletedAnnouncements();
    })();
</script>
