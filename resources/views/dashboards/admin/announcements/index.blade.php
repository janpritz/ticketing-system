@extends('layouts.admin')

@section('title', 'Announcements')

@section('admin-content')
    <div class="sm:px-2">
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
            <div>
                <h1 class="text-xl sm:text-2xl font-semibold text-slate-900">Announcements</h1>
                <p class="text-sm text-gray-600 mt-1">Manage dynamic chatbot responses.</p>
            </div>
            <!-- Add Announcement Button -->
            <button id="addAnnouncementBtn" type="button"
                class="inline-flex items-center justify-center sm:justify-start gap-2 rounded-lg bg-blue-600 hover:bg-blue-700 text-white text-sm font-medium px-4 py-2 w-full sm:w-auto mt-2 sm:mt-0">
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                </svg>
                <span>Add Announcement</span>
            </button>
        </div>

        <!-- Search Bar -->
        <div class="mt-4">
            <div class="relative">
                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                    <svg class="h-5 w-5 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                    </svg>
                </div>
                <input type="text" id="announcementSearch" placeholder="Search announcements..."
                       class="block w-full pl-10 pr-3 py-2 border border-gray-300 rounded-md leading-5 bg-white placeholder-gray-500 focus:outline-none focus:placeholder-gray-400 focus:ring-1 focus:ring-blue-500 focus:border-blue-500 text-sm">
            </div>
        </div>

        <!-- Document Training Alert -->
        <div id="trainingAlert" class="hidden bg-orange-50 border-l-4 border-orange-400 p-4 mb-4 mt-4">
            <div class="flex items-center justify-between">
                <div class="flex items-center">
                    <div class="flex-shrink-0">
                        <svg class="h-5 w-5 text-orange-400" viewBox="0 0 20 20" fill="currentColor">
                            <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd" />
                        </svg>
                    </div>
                    <div class="ml-3">
                        <p class="text-sm text-orange-700">
                            <strong>Training Required:</strong> Documents have been modified and need Rasa retraining.
                        </p>
                    </div>
                </div>
                <div class="ml-4">
                    <button id="trainRasaBtn"
                            class="inline-flex items-center px-3 py-2 border border-transparent text-sm leading-4 font-medium rounded-md text-orange-700 bg-orange-100 hover:bg-orange-200 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-orange-500 transition-colors duration-200">
                        <svg class="mr-2 h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z" />
                        </svg>
                        <span id="trainBtnText">Train Rasa</span>
                        <svg class="ml-2 h-4 w-4 hidden" id="trainSpinner" fill="none" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                        </svg>
                    </button>
                </div>
            </div>
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
    <div class="w-full max-w-full sm:max-w-4xl bg-white rounded-lg shadow border border-gray-200 overflow-auto h-[80vh] mx-4 sm:mx-0">
        <div class="h-12 flex items-center justify-between px-4 border-b">
            <div class="text-sm font-semibold text-slate-800" id="addModalTitle">Add Announcement</div>
            <button type="button" class="text-slate-500 hover:text-slate-700" data-close="announcement"
                    aria-label="Close">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 24 24" fill="currentColor">
                    <path d="M6 18 18 6M6 6l12 12" />
                </svg>
            </button>
        </div>
        <form id="addAnnouncementForm" class="p-4 space-y-4">
            <div>
                <label class="block text-sm font-medium text-slate-700">Announcement Title</label>
                <input type="text" id="announcementTitle" required
                       class="mt-1 w-full rounded-md border border-gray-300 px-3 py-2 text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                       placeholder="Enter announcement title...">
                <p id="title_error" class="mt-1 text-xs text-red-600 hidden"></p>
            </div>
            <div>
                <label class="block text-sm font-medium text-slate-700">Announcement Content</label>
                <textarea id="announcementContent" rows="8" required
                          class="mt-1 w-full rounded-md border border-gray-300 px-3 py-2 text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                          placeholder="Enter your announcement here..."></textarea>
                <p class="mt-1 text-xs text-slate-500">Announcements are for short-term, constantly changing information like enrollment schedules and document releases.</p>
                <p id="announcement_error" class="mt-1 text-xs text-red-600 hidden"></p>
            </div>
            <div class="mt-auto pt-0 flex flex-col sm:flex-row sm:items-center justify-end gap-3">
                <button type="button" class="rounded-md border border-gray-300 bg-white hover:bg-gray-50 text-sm px-6 py-3 order-2 sm:order-1"
                         data-close="announcement">Cancel</button>
                <button id="addAnnouncementSubmit" type="button"
                         class="rounded-md bg-blue-600 hover:bg-blue-700 text-white text-sm font-medium px-6 py-3 order-1 sm:order-2 w-full sm:w-auto">Add</button>
            </div>
        </form>
    </div>
</div>
</div>

<!-- View Announcement Modal -->
<div id="viewAnnouncementModal" class="fixed inset-0 z-50 hidden">
<div class="absolute inset-0 bg-black/40" data-close="view-announcement"></div>
<div class="absolute inset-0 flex items-center justify-center p-4">
    <div class="relative w-full max-w-full sm:max-w-4xl bg-white rounded-lg shadow border border-gray-200 overflow-auto h-[80vh] mx-4 sm:mx-0">
        <div class="h-12 flex items-center justify-between px-4 border-b">
            <div class="text-sm font-semibold text-slate-800" id="viewAnnouncementTitle">Announcement</div>
            <div class="flex items-center gap-2">
                <button id="announcementMenuBtn" class="text-slate-500 hover:text-slate-700" aria-label="Menu">
                    <svg class="h-5 w-5" fill="currentColor" viewBox="0 0 20 20">
                        <path d="M10 6a2 2 0 110-4 2 2 0 010 4zM10 12a2 2 0 110-4 2 2 0 010 4zM10 18a2 2 0 110-4 2 2 0 010 4z" />
                    </svg>
                </button>
                <button type="button" class="text-gray-700 hover:text-gray-900 p-1 rounded hover:bg-gray-100" data-close="view-announcement" aria-label="Close">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <path d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>
        </div>
        <div id="announcementMenu" class="hidden absolute right-4 top-12 bg-white border border-gray-200 rounded shadow-lg z-10 w-32">
            <button id="editAnnouncementMenu" class="block w-full text-left px-4 py-2 text-sm hover:bg-gray-100">Edit</button>
            <button id="deleteAnnouncementMenu" class="block w-full text-left px-4 py-2 text-sm hover:bg-gray-100">Delete</button>
            <button id="pinAnnouncementMenu" class="block w-full text-left px-4 py-2 text-sm hover:bg-gray-100">Pin</button>
        </div>
        <div class="p-6">
            <h2 class="text-lg font-semibold text-gray-900 mb-4" id="viewAnnouncementTitleDisplay"></h2>
            <div class="text-sm text-gray-700 whitespace-pre-line break-words" id="viewAnnouncementContentDisplay"></div>
        </div>
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

                $('#title_error').classList.add('hidden');
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
                        body: JSON.stringify({ title, content })
                    });

                    const result = await response.json();

                    if (!response.ok || !result.success) {
                        throw new Error(result.message || 'Failed to save announcement');
                    }

                    showToast('success', editingId ? 'Announcement updated successfully' : 'Announcement added successfully');
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
                    pinMenu.textContent = announcement.pinned ? 'Unpin' : 'Pin';
                }
                viewAnnouncementModal.classList.remove('hidden');
            }
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

                announcementsData = result.announcements || [];
                currentSearchTerm = '';
                if (announcementSearch) {
                    announcementSearch.value = '';
                }
                renderFilteredAnnouncements();

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

        // Document Training Alert Management
        async function trainRasa() {
            const btn = document.getElementById('trainRasaBtn');
            const spinner = document.getElementById('trainSpinner');
            const btnText = document.getElementById('trainBtnText');

            // Show loading state
            btn.disabled = true;
            spinner.classList.remove('hidden');
            btnText.textContent = 'Training...';

            try {
                const csrf = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
                const res = await fetch('/admin/document-changes/train-rasa', {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': csrf,
                        'X-Requested-With': 'XMLHttpRequest'
                    }
                });

                const data = await res.json();

                if (!res.ok || !data.success) {
                    throw new Error(data.message || 'Training failed');
                }

                // Show success message
                Swal.fire({
                    toast: true,
                    position: 'top-end',
                    icon: 'success',
                    title: 'Rasa training completed successfully!',
                    showConfirmButton: false,
                    timer: 3000,
                    timerProgressBar: true
                });

                // Hide the training alert
                document.getElementById('trainingAlert').classList.add('hidden');

            } catch (err) {
                console.error('[DEBUG] Training error:', err);
                Swal.fire({
                    icon: 'error',
                    title: 'Training Failed',
                    text: `Training failed: ${err.message}`,
                    confirmButtonText: 'OK'
                });
            } finally {
                // Reset button state
                btn.disabled = false;
                spinner.classList.add('hidden');
                btnText.textContent = 'Train Rasa';
            }
        }

        async function checkTrainingStatus() {
            try {
                const res = await fetch('/admin/document-changes/training-status', {
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest'
                    }
                });

                if (res.ok) {
                    const data = await res.json();
                    const alertEl = document.getElementById('trainingAlert');

                    if (data.requires_training) {
                        // Show training alert
                        alertEl.classList.remove('hidden');
                    } else {
                        // Hide training alert
                        alertEl.classList.add('hidden');
                    }
                }
            } catch (err) {
                console.error('[DEBUG] Error checking training status:', err);
            }
        }

        // Add event listener for train button
        const trainBtn = document.getElementById('trainRasaBtn');
        if (trainBtn) {
            trainBtn.addEventListener('click', trainRasa);
        }

        // Check training status on page load
        checkTrainingStatus();
    })();
</script>

@endsection