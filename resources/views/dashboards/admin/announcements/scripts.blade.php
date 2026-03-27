<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
    // 🚀 GLOBAL SCOPE: Moved outside IIFE so Deleted Announcements page can access it!
    let announcementsData = []; 

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

    // Modal display function (scoped globally so both views can trigger it)
    window.showAnnouncementModal = function(id) {
        const announcement = announcementsData.find(a => a.id == id);
        if (announcement) {
            currentAnnouncementId = id;
            document.getElementById('viewAnnouncementTitleDisplay').textContent = announcement.title || 'Announcement ' + id;
            document.getElementById('viewAnnouncementContentDisplay').textContent = announcement.content;
            document.getElementById('viewAnnouncementStartsAt').textContent = announcement.starts_at ? new Date(announcement.starts_at).toLocaleString() : 'N/A';
            document.getElementById('viewAnnouncementExpiresAt').textContent = announcement.expires_at ? new Date(announcement.expires_at).toLocaleString() : 'N/A';
            document.getElementById('viewAnnouncementCreatedAt').textContent = announcement.created_at ? new Date(announcement.created_at).toLocaleString() : 'N/A';

            // Update menu text based on pinned status
            const pinMenu = document.getElementById('pinAnnouncementMenu');
            if (pinMenu) {
                pinMenu.textContent = announcement.pinned ? 'Unpin' : 'Pin';
            }

            // Show restore button if the announcement is soft-deleted
            const restoreContainer = document.getElementById('restoreButtonContainer');
            if (restoreContainer) {
                if (announcement.deleted_at) {
                    restoreContainer.classList.remove('hidden');
                    document.getElementById('restoreAnnouncementBtn').onclick = () => restoreAnnouncement(id);
                    // Hide the menu for deleted announcements
                    document.getElementById('announcementMenuBtn').style.display = 'none';
                } else {
                    restoreContainer.classList.add('hidden');
                    document.getElementById('announcementMenuBtn').style.display = 'block';
                }
            }

            document.getElementById('viewAnnouncementModal').classList.remove('hidden');
        }
    };

    let currentAnnouncementId = null; // Tracked globally for menu actions

    (function() {
        const $ = (sel, root = document) => root.querySelector(sel);
        const $$ = (sel, root = document) => Array.from(root.querySelectorAll(sel));

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
                $('#date_error').classList.add('hidden');
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
                const ann = announcementsData.find(a => a.id == currentAnnouncementId);
                if (ann) {
                    $('#addModalTitle').textContent = 'Edit Announcement';
                    announcementTitle.value = ann.title;
                    announcementContent.value = ann.content;
                    
                    if ($('#announcementStartsAt')) $('#announcementStartsAt').value = ann.starts_at ? ann.starts_at.substring(0, 16) : '';
                    if ($('#announcementExpiresAt')) $('#announcementExpiresAt').value = ann.expires_at ? ann.expires_at.substring(0, 16) : '';

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
                    text: 'This announcement will be soft-deleted.',
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#d33',
                    cancelButtonColor: '#3085d6',
                    confirmButtonText: 'Yes, delete it!'
                }).then((result) => {
                    if (result.isConfirmed) {
                        fetch('/admin/announcements/' + currentAnnouncementId, {
                            method: 'DELETE',
                            headers: {
                                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
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
                                showToast('error', result.message || 'Failed to delete announcement');
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
                fetch('/admin/announcements/pin/' + currentAnnouncementId, {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                        'X-Requested-With': 'XMLHttpRequest'
                    }
                })
                .then(response => response.json())
                .then(result => {
                    if (result.success) {
                        showToast('success', 'Announcement status changed successfully');
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
                const startsAt = $('#announcementStartsAt') ? $('#announcementStartsAt').value : null;
                const expiresAt = $('#announcementExpiresAt') ? $('#announcementExpiresAt').value : null;

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

                if (!startsAt || !expiresAt) {
                    $('#date_error').textContent = 'Both start and expiration dates are required';
                    $('#date_error').classList.remove('hidden');
                    return;
                }

                $('#title_error').classList.add('hidden');
                $('#announcement_error').classList.add('hidden');
                $('#date_error').classList.add('hidden');

                const originalHTML = addAnnouncementSubmit.innerHTML;
                addAnnouncementSubmit.disabled = true;
                addAnnouncementSubmit.innerHTML = `
                    <svg class="animate-spin h-4 w-4 mx-auto" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                    </svg>
                    <span class="ml-2">Saving...</span>
                `;

                const editingId = addAnnouncementForm.getAttribute('data-editing-id');
                const url = editingId ? '/admin/announcements/' + editingId : '/admin/announcements';
                const method = editingId ? 'PUT' : 'POST';

                try {
                    const response = await fetch(url, {
                        method: method,
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                            'X-Requested-With': 'XMLHttpRequest'
                        },
                        body: JSON.stringify({ title, content, starts_at: startsAt, expires_at: expiresAt })
                    });

                    const result = await response.json();

                    if (!response.ok || !result.success) {
                        throw new Error(result.message || 'Failed to save announcement');
                    }

                    showToast('success', editingId ? 'Announcement updated' : 'Announcement added');
                    addAnnouncementModal.classList.add('hidden');
                    addAnnouncementForm.reset();
                    addAnnouncementForm.removeAttribute('data-editing-id');
                    addAnnouncementSubmit.innerHTML = 'Add';
                    loadAnnouncements();

                } catch (error) {
                    console.error('Error adding announcement:', error);
                    showToast('error', error.message || 'Failed to add announcement');
                } finally {
                    addAnnouncementSubmit.disabled = false;
                    addAnnouncementSubmit.innerHTML = originalHTML;
                }
            });
        }

        // Load active announcements
        async function loadAnnouncements() {
            const announcementsList = $('#announcementsList');
            if (!announcementsList) return; // Skip if on the deleted page

            try {
                announcementsList.innerHTML = '<div class="text-center text-sm text-gray-500">Loading announcements...</div>';

                const response = await fetch('/admin/announcements/list', {
                    headers: { 'X-Requested-With': 'XMLHttpRequest' }
                });

                const result = await response.json();

                if (!response.ok || !result.success) {
                    throw new Error(result.message || 'Failed to load announcements');
                }

                announcementsData = result.announcements || [];
                currentSearchTerm = '';
                if (announcementSearch) announcementSearch.value = '';
                renderFilteredAnnouncements();

            } catch (error) {
                console.error('Error loading announcements:', error);
                announcementsList.innerHTML = `<div class="text-center text-sm text-red-600">Error: ${error.message}</div>`;
            }
        }

        // Render standard announcements
        function renderAnnouncements(announcements) {
            const announcementsList = $('#announcementsList');
            if (!announcementsList) return;

            if (!announcements || announcements.length === 0) {
                announcementsList.innerHTML = `
                    <div class="text-center text-gray-500">
                        <svg xmlns="http://www.w3.org/2000/svg" class="mx-auto h-12 w-12 text-gray-400" fill="currentColor" viewBox="0 0 16 16">
                            <path d="M13 2.5a1.5 1.5 0 0 1 3 0v11a1.5 1.5 0 0 1-3 0zm-1 .724c-2.067.95-4.539 1.481-7 1.656v6.237a25 25 0 0 1 1.088.085c2.053.204 4.038.668 5.912 1.56zm-8 7.841V4.934c-.68.027-1.399.043-2.008.053A2.02 2.02 0 0 0 0 7v2c0 1.106.896 1.996 1.994 2.009l.496.008a64 64 0 0 1 1.51.048m1.39 1.081q.428.032.85.078l.253 1.69a1 1 0 0 1-.983 1.187h-.548a1 1 0 0 1-.916-.599l-1.314-2.48a66 66 0 0 1 1.692.064q.491.026.966.06"/>
                        </svg>
                        <h3 class="mt-2 text-sm font-medium text-gray-900">No active announcements yet</h3>
                    </div>`;
                return;
            }

            announcementsList.innerHTML = announcements.map(announcement => `
                <div class="p-4 border border-gray-200 rounded-lg cursor-pointer hover:bg-gray-50 transition-colors" onclick="showAnnouncementModal(${announcement.id})">
                    <div class="flex items-center gap-3">
                        <div class="flex-1 min-w-0">
                            <div class="text-sm font-medium text-gray-900 truncate">${escapeHtml(announcement.title || 'Announcement ' + announcement.id)}</div>
                        </div>
                        <div class="flex-shrink-0 flex items-center gap-2">
                            ${announcement.pinned ? '<span class="text-yellow-500">📌</span>' : ''}
                            <svg class="w-4 h-4 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                            </svg>
                        </div>
                    </div>
                </div>
            `).join('');
        }

        function renderFilteredAnnouncements() {
            let filtered = announcementsData;
            if (currentSearchTerm) {
                filtered = announcementsData.filter(ann => {
                    const title = (ann.title || '').toLowerCase();
                    const content = (ann.content || '').toLowerCase();
                    return title.includes(currentSearchTerm) || content.includes(currentSearchTerm);
                });
            }
            renderAnnouncements(filtered);
        }

        function escapeHtml(text) {
            const div = document.createElement('div');
            div.textContent = text;
            return div.innerHTML;
        }

        // Initialize loading
        const deletedListView = document.getElementById('deletedAnnouncementsList');
        if (deletedListView) {
            if (typeof loadDeletedAnnouncements === 'function') {
                loadDeletedAnnouncements(); // Let the page file handle its own population
            }
        } else {
            loadAnnouncements();
        }

        // --- Rasa Training Banner State ---
        async function checkTrainingStatus() {
            try {
                const res = await fetch('/admin/document-changes/training-status', { headers: { 'X-Requested-With': 'XMLHttpRequest' } });
                if (res.ok) {
                    const data = await res.json();
                    const alertEl = $('#trainingAlert');
                    if (alertEl) {
                        if (data.requires_training) {
                            alertEl.classList.remove('hidden');
                        } else {
                            alertEl.classList.add('hidden');
                        }
                    }
                }
            } catch (err) {
                console.error('Error checking training status:', err);
            }
        }

        const trainBtn = $('#trainRasaBtn');
        if (trainBtn) {
            trainBtn.addEventListener('click', trainRasa);
        }

        checkTrainingStatus();

        async function trainRasa() {
            const btn = $('#trainRasaBtn');
            const spinner = $('#trainSpinner');
            const btnText = $('#trainBtnText');

            btn.disabled = true;
            spinner.classList.remove('hidden');
            btnText.textContent = 'Training...';

            try {
                const res = await fetch('/admin/document-changes/train-rasa', {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                        'X-Requested-With': 'XMLHttpRequest'
                    }
                });

                const data = await res.json();
                if (!res.ok || !data.success) throw new Error(data.message || 'Training failed');

                showToast('success', 'Rasa training completed successfully!');
                const alertEl = $('#trainingAlert');
                if (alertEl) alertEl.classList.add('hidden');

            } catch (err) {
                console.error('Training error:', err);
                Swal.fire({ icon: 'error', title: 'Training Failed', text: err.message });
            } finally {
                btn.disabled = false;
                spinner.classList.add('hidden');
                btnText.textContent = 'Train Rasa';
            }
        }
    })();
</script>