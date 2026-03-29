<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
    // SweetAlert helpers
    @include('dashboards.staff.announcements.scripts.sweetalert-helpers')

    (function() {
        const $ = (sel, root = document) => root.querySelector(sel);
        const $$ = (sel, root = document) => Array.from(root.querySelectorAll(sel));

        if (!document.getElementById('announcementsList')) {
            return;
        }

        // Global data
        let announcementsData = [];
        let currentAnnouncementId = null;
        let currentSearchTerm = '';

        // Modal elements
        const addAnnouncementModal = $('#addAnnouncementModal');
        const addAnnouncementBtn = $('#addAnnouncementBtn');
        const addAnnouncementForm = $('#addAnnouncementForm');
        const addAnnouncementSubmit = $('#addAnnouncementSubmit');
        const announcementTitle = $('#announcementTitle');
        const announcementContent = $('#announcementContent');
        const closeButtons = $$('[data-close="announcement"]');

        // View announcement modal elements
        const viewAnnouncementModal = $('#viewAnnouncementModal');
        const viewAnnouncementTitle = $('#viewAnnouncementTitle');
        const viewAnnouncementContent = $('#viewAnnouncementContent');
        const viewCloseButtons = $$('[data-close="view-announcement"]');
        const announcementMenuBtn = $('#announcementMenuBtn');
        const announcementMenu = $('#announcementMenu');

        // Search elements
        const announcementSearch = $('#announcementSearch');

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
                addAnnouncementForm.removeAttribute('data-editing-id');
                $('#addModalTitle').textContent = 'Add Announcement';
                addAnnouncementSubmit.innerHTML = 'Add';
                $('#title_error').classList.add('hidden');
                $('#announcement_error').classList.add('hidden');
            });
        });

        // View modal close handlers
        viewCloseButtons.forEach(btn => {
            btn.addEventListener('click', () => {
                viewAnnouncementModal.classList.add('hidden');
                announcementMenu.classList.add('hidden'); // Hide menu on close
            });
        });

        // Menu button handler
        if (announcementMenuBtn) {
            announcementMenuBtn.addEventListener('click', () => {
                announcementMenu.classList.toggle('hidden');
            });
        }

        // Search handler
        if (announcementSearch) {
            announcementSearch.addEventListener('input', (e) => {
                currentSearchTerm = e.target.value.toLowerCase().trim();
                renderFilteredAnnouncements();
            });
        }

        // Menu item handlers
        if ($('#editAnnouncementMenu')) {
            $('#editAnnouncementMenu').addEventListener('click', () => {
                const ann = announcementsData.find(a => a.id === currentAnnouncementId);
                if (ann) {
                    $('#addModalTitle').textContent = 'Edit Announcement';
                    announcementTitle.value = ann.title;
                    announcementContent.value = ann.content;
                    addAnnouncementSubmit.innerHTML = 'Update';
                    addAnnouncementForm.setAttribute('data-editing-id', currentAnnouncementId);
                    addAnnouncementModal.classList.remove('hidden');
                    viewAnnouncementModal.classList.add('hidden');
                    announcementMenu.classList.add('hidden');
                }
            });
        }

        if ($('#deleteAnnouncementMenu')) {
            $('#deleteAnnouncementMenu').addEventListener('click', () => {
                Swal.fire({
                    title: 'Are you sure?',
                    text: 'This announcement will be permanently deleted.',
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#d33',
                    cancelButtonColor: '#3085d6',
                    confirmButtonText: 'Yes, delete it!'
                }).then((result) => {
                    if (result.isConfirmed) {
                        fetch('/staff/announcements/' + currentAnnouncementId, {
                                method: 'DELETE',
                                headers: {
                                    'X-CSRF-TOKEN': document.querySelector(
                                        'meta[name="csrf-token"]').getAttribute('content'),
                                    'X-Requested-With': 'XMLHttpRequest'
                                }
                            })
                            .then(response => response.json())
                            .then(result => {
                                if (result.success) {
                                    showToast('success', 'Announcement deleted successfully');
                                    loadAnnouncements();
                                    viewAnnouncementModal.classList.add('hidden');
                                } else {
                                    showToast('error', result.message ||
                                        'Failed to delete announcement');
                                }
                            })
                            .catch(error => {
                                console.error('Error deleting announcement:', error);
                                showToast('error', 'Failed to delete announcement');
                            });
                    }
                });
                announcementMenu.classList.add('hidden');
            });
        }

        if ($('#pinAnnouncementMenu')) {
            $('#pinAnnouncementMenu').addEventListener('click', () => {
                fetch('/staff/announcements/' + currentAnnouncementId + '/pin', {
                        method: 'POST',
                        headers: {
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')
                                .getAttribute('content'),
                            'X-Requested-With': 'XMLHttpRequest'
                        }
                    })
                    .then(response => response.json())
                    .then(result => {
                        if (result.success) {
                            showToast('success', 'Announcement pinned successfully');
                            loadAnnouncements();
                            viewAnnouncementModal.classList.add('hidden');
                        } else {
                            showToast('error', result.message || 'Failed to pin announcement');
                        }
                    })
                    .catch(error => {
                        console.error('Error pinning announcement:', error);
                        showToast('error', 'Failed to pin announcement');
                    });
                announcementMenu.classList.add('hidden');
            });
        }

        // Form submission
        if (addAnnouncementSubmit) {
            addAnnouncementSubmit.addEventListener('click', async () => {
                const title = announcementTitle.value.trim();
                const content = announcementContent.value.trim();
                const startsAt = $('#announcementStartsAt')?.value || '';
                const expiresAt = $('#announcementExpiresAt')?.value || '';

                if (!title) {
                    $('#title_error').textContent = 'Please enter announcement title';
                    $('#title_error').classList.remove('hidden');
                    return;
                }

                if (!content) {
                    $('#announcement_error').textContent = 'Please enter announcement content';
                    $('#announcement_error').classList.remove('hidden');
                    return;
                }

                // Basic client-side date checks (server still validates)
                if (!startsAt) {
                    $('#date_error').textContent = 'Please select a visible starting date.';
                    $('#date_error').classList.remove('hidden');
                    return;
                }

                if (!expiresAt) {
                    $('#date_error').textContent = 'Please select an auto-expire date.';
                    $('#date_error').classList.remove('hidden');
                    return;
                }

                $('#title_error').classList.add('hidden');
                $('#announcement_error').classList.add('hidden');
                $('#date_error').classList.add('hidden');

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

                const editingId = addAnnouncementForm.getAttribute('data-editing-id');
                const url = editingId ? '/staff/announcements/' + editingId : '/staff/announcements';
                const method = editingId ? 'PUT' : 'POST';

                try {
                    const response = await fetch(url, {
                        method: method,
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')
                                .getAttribute('content'),
                            'X-Requested-With': 'XMLHttpRequest'
                        },
                        body: JSON.stringify({
                            title,
                            content,
                            starts_at: startsAt,
                            expires_at: expiresAt
                        })
                    });

                    const result = await response.json();

                    if (!response.ok || !result.success) {
                        if (result.errors) {
                            showFieldErrors(result.errors);
                        } else {
                            showToast('error', result.message || 'Failed to save announcement');
                        }
                        return;
                    }

                    showToast('success', editingId ? 'Announcement updated successfully' :
                        'Announcement added successfully');
                    addAnnouncementModal.classList.add('hidden');
                    addAnnouncementForm.reset();
                    addAnnouncementForm.removeAttribute('data-editing-id');
                    addAnnouncementSubmit.innerHTML = 'Add Announcement';
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

        // Show announcement modal
        window.showAnnouncementModal = function(id) {
            const announcement = announcementsData.find(a => a.id === id);
            if (announcement) {
                currentAnnouncementId = id;
                $('#viewAnnouncementTitleDisplay').textContent = announcement.title || 'Announcement ' + id;
                $('#viewAnnouncementContentDisplay').textContent = announcement.content;
                // Update menu text based on pinned status
                const pinMenu = $('#pinAnnouncementMenu');
                if (pinMenu) {
                    const isPinned = announcement.staff_pinned || announcement.pinned;
                    pinMenu.textContent = isPinned ? 'Unpin' : 'Pin';
                }
                viewAnnouncementModal.classList.remove('hidden');
            }
        }

        // Load announcements function
        async function loadAnnouncements() {
            const announcementsList = $('#announcementsList');
            if (!announcementsList) {
                return;
            }
            try {
                announcementsList.innerHTML =
                    '<div class="text-center text-sm text-gray-500">Loading announcements...</div>';

                const response = await fetch('/staff/announcements', {
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest'
                    }
                });

                const result = await response.json();

                if (!response.ok || !result.success) {
                    throw new Error(result.message || 'Failed to load announcements');
                }

                announcementsData = result.announcements || [];
                currentSearchTerm = '';
                if (announcementSearch) {
                    announcementSearch.value = '';
                }
                renderFilteredAnnouncements();

            } catch (error) {
                console.error('Error loading announcements:', error);
                announcementsList.innerHTML =
                    `<div class="text-center text-sm text-red-600">Error loading announcements: ${error.message}</div>`;
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
                <div class="p-4 border border-gray-200 rounded-lg cursor-pointer hover:bg-gray-50 transition-colors" onclick="showAnnouncementModal(${announcement.id})">
                    <div class="flex items-center gap-3">
                        <div class="flex-1 min-w-0">
                            <div class="text-sm font-medium text-gray-900 truncate">${escapeHtml(announcement.title || 'Announcement ' + announcement.id)}</div>
                        </div>
                        <div class="flex-shrink-0 flex items-center gap-2">
                            ${((announcement.staff_pinned !== null && announcement.staff_pinned !== undefined) ? announcement.staff_pinned : announcement.pinned) ? '<span class="text-yellow-500">📌</span>' : ''}
                            <svg class="w-4 h-4 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                            </svg>
                        </div>
                    </div>
                </div>
            `).join('');
        }

        // Render filtered announcements
        function renderFilteredAnnouncements() {
            let filteredAnnouncements = announcementsData;

            if (currentSearchTerm) {
                filteredAnnouncements = announcementsData.filter(announcement => {
                    const title = (announcement.title || '').toLowerCase();
                    const content = (announcement.content || '').toLowerCase();
                    return title.includes(currentSearchTerm) || content.includes(currentSearchTerm);
                });
            }

            renderAnnouncements(filteredAnnouncements);
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
