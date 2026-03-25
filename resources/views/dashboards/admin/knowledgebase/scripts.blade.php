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
</script>
<script>
    console.log('[TEST] FAQ JavaScript loaded and executing');
    (function() {
        const stateEl = document.getElementById('admin-faqs-state');
        const LIST_URL = stateEl.getAttribute('data-list-url');
        const STORE_URL = stateEl.getAttribute('data-store-url');
        const SHOW_TEMPLATE = stateEl.getAttribute('data-show-url-template');
        const UPDATE_TEMPLATE = stateEl.getAttribute('data-update-url-template');
        const DESTROY_TEMPLATE = stateEl.getAttribute('data-destroy-url-template');
        const RESTORE_TEMPLATE = stateEl.getAttribute('data-restore-url-template');
        const ENABLE_TEMPLATE = stateEl.getAttribute('data-enable-url-template');
        const DISABLE_TEMPLATE = stateEl.getAttribute('data-disable-url-template');
        const HISTORY_URL = stateEl.getAttribute('data-history-url');
        const csrf = document.querySelector('meta[name="csrf-token"]').getAttribute('content');

        const $ = (sel, root = document) => root.querySelector(sel);
        const $$ = (sel, root = document) => Array.from(root.querySelectorAll(sel));

        // Prevent ReferenceErrors for optional mobile actions menu that may not exist
        const mobileActionsMenu = null;

        // Elements
        const qInput = $('#q');
        const perPageSelect = $('#per_page');
        const searchBtn = $('#searchBtn');
        const clearSearchBtn = $('#clearSearch');
        const showDeletedCheckbox = $('#show_deleted');
        const faqsTbody = $('#faqsTbody');
        const paginationControls = $('#paginationControls');

        const createModal = $('#createFaqModal');
        const createOpenBtn = $('#openCreateModalBtn');
        const createCloseEls = $$('[data-close="create"]', createModal || document);
        const createForm = $('#createFaqForm');
        const createSubmit = $('#createFaqSubmit');

        const uploadModal = $('#uploadFileModal');
        const uploadOpenBtn = $('#uploadFileBtn');
        const uploadCloseEls = $$('[data-close="upload"]', uploadModal || document);
        const uploadForm = $('#uploadFileForm');
        const uploadSubmit = $('#uploadFileSubmit');
        const faqFileInput = $('#faqFile');

        // Open upload modal when Upload button is clicked (DB-first flow uses staff.upload endpoint)
        if (uploadOpenBtn) {
            uploadOpenBtn.addEventListener('click', () => openModal(uploadModal));
        }

        // Upload file submit handler (DB-first; reuses staff upload endpoint to persist then forward to Rasa)
        if (uploadSubmit) {
            uploadSubmit.addEventListener('click', async () => {
                const file = faqFileInput.files[0];
                if (!file) {
                    $('#upload_file_error').textContent = 'Please select a file';
                    $('#upload_file_error').classList.remove('hidden');
                    return;
                }

                // Validate file type
                const allowedTypes = ['text/plain'];
                if (!allowedTypes.includes(file.type) && !file.name.match(/\.txt$/i)) {
                    $('#upload_file_error').textContent = 'Only .txt files are allowed';
                    $('#upload_file_error').classList.remove('hidden');
                    return;
                }

                $('#upload_file_error').classList.add('hidden');

                // Store original content and show loading spinner
                const originalHTML = uploadSubmit.innerHTML;
                uploadSubmit.disabled = true;
                uploadSubmit.innerHTML = `\
                        <svg class="animate-spin h-4 w-4 mx-auto" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">\
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>\
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>\
                        </svg>\
                        <span class="ml-2">Uploading...</span>`;

                try {
                    const fileContent = await file.text();

                    // Send file content to the application first (DB-first)
                    const uploadUrl = '{{ route('staff.document_management.upload') }}';
                    const res = await fetch(uploadUrl, {
                        method: 'POST',
                        headers: {
                            'X-CSRF-TOKEN': csrf,
                            'Content-Type': 'application/json',
                            'X-Requested-With': 'XMLHttpRequest'
                        },
                        body: JSON.stringify({
                            file_name: file.name,
                            file_type: 'txt',
                            file_content: fileContent,
                            file_size: file.size || null
                        })
                    });

                    const result = await (res.ok ? res.json() : {
                        success: false,
                        error: await res.text()
                    });

                    if (!res.ok || !result.success) {
                        console.error('[Admin File Upload] Upload failed:', result.error ||
                            'Unknown error');
                        throw new Error(result.error || 'File upload failed');
                    }

                    showToast('success', 'File saved locally and uploaded to Rasa successfully');

                    closeModal(uploadModal);
                    uploadForm.reset();

                    // Auto-refresh document list
                    fetchDocs();

                } catch (err) {
                    console.error('[Admin Upload] Upload error:', err);
                    showToast('error', err.message || 'Failed to upload file');
                } finally {
                    // Restore original content
                    uploadSubmit.innerHTML = originalHTML;
                    uploadSubmit.disabled = false;
                }
            });
        }

        const editDocumentModal = $('#editDocumentModal');
        const editDocumentCloseEls = $$('[data-close="edit-doc"]', editDocumentModal || document);
        const editDocumentForm = $('#editDocumentForm');
        const editDocumentSubmit = $('#editDocumentSubmit');

        // Close buttons and submit for Edit Document modal
        editDocumentCloseEls.forEach(el => el.addEventListener('click', () => closeModal(editDocumentModal)));
        if (editDocumentSubmit) {
            editDocumentSubmit.addEventListener('click', saveDocumentContent);
        }

        // Delete button in Edit Document modal (DB-first: delete Document record, then sync DB -> Rasa)
        const deleteDocBtn = $('#deleteDocBtn');
        if (deleteDocBtn) {
            deleteDocBtn.addEventListener('click', async () => {
                const filename = $('#edit_doc_filename').value;
                if (!filename) {
                    showToast('error', 'Filename is missing');
                    return;
                }

                const confirmResult = await Swal.fire({
                    title: 'Delete document?',
                    text: `Delete "${filename}" from the database and sync to Rasa?`,
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonText: 'Yes, delete',
                    cancelButtonText: 'Cancel'
                });
                if (!confirmResult.isConfirmed) return;

                try {
                    deleteDocBtn.disabled = true;
                    const res = await fetch(
                        '{{ route('staff.document_management.document.destroy') }}', {
                            method: 'DELETE',
                            headers: {
                                'X-CSRF-TOKEN': csrf,
                                'Content-Type': 'application/json',
                                'X-Requested-With': 'XMLHttpRequest'
                            },
                            body: JSON.stringify({
                                file_name: filename
                            })
                        });

                    const json = await (res.ok ? res.json() : {
                        success: false,
                        error: await res.text()
                    });

                    if (!res.ok || !json.success) {
                        console.error('[Delete] response status:', res.status, 'body:', json);
                        throw new Error(json.message || json.error || 'Failed to delete document');
                    }

                    showToast('success', `Document "${filename}" deleted and DB synced to Rasa`);
                    closeModal(editDocumentModal);
                    fetchDocs();

                } catch (err) {
                    console.error('Delete doc error:', err);
                    showToast('error', err.message || 'Failed to delete document');
                } finally {
                    deleteDocBtn.disabled = false;
                }
            });
        }

        const viewModal = $('#viewFaqModal');
        const viewCloseEls = $$('[data-close="view"]', viewModal || document);
        const viewForm = $('#viewFaqForm');
        const viewFaqId = $('#view_faq_id');
        const viewTopic = $('#view_intent');
        const viewResponse = $('#view_response');
        const viewTimestamps = $('#view_timestamps');
        const updateSubmit = $('#updateFaqSubmit');
        const deleteBtn = $('#deleteFaqBtn');

        // Delete handler for admin (delete from DB first, then sync Rasa storage with DB)
        if (deleteBtn) {
            deleteBtn.addEventListener('click', async () => {
                const id = viewFaqId.value;
                const confirmResult = await Swal.fire({
                    title: 'Delete Document?',
                    text: 'This will delete the document from the database',
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonText: 'Yes, delete',
                    cancelButtonText: 'Cancel'
                });
                if (!confirmResult.isConfirmed) return;

                const url = DESTROY_TEMPLATE.replace('__ID__', id);
                try {
                    deleteBtn.disabled = true;
                    const res = await fetch(url, {
                        method: 'DELETE',
                        headers: {
                            'X-CSRF-TOKEN': csrf,
                            'X-Requested-With': 'XMLHttpRequest'
                        }
                    });
                    const json = await res.json();
                    if (!res.ok) {
                        throw new Error(json.message || 'Failed to delete document');
                    }

                    // Record deletion in document changes log
                    await logDocumentChange(viewTopic.value || `faq:${id}`, 'deleted');

                    // Sync the entire FAQ set from DB to Rasa so local storage matches DB
                    try {
                        const faqsRes = await fetch('{{ route('admin.knowledgebase.all-json') }}', {
                            headers: {
                                'X-Requested-With': 'XMLHttpRequest'
                            }
                        });
                        if (!faqsRes.ok) throw new Error('Failed to fetch FAQs for sync');
                        const faqData = await faqsRes.json();
                        const faqs = faqData.faqs || [];

                        const rasaRes = await fetch('{{ config('services.faq_sync.url') }}', {
                            method: 'POST',
                            headers: {
                                'X-FAQ-UPDATER-TOKEN': '{{ config('services.faq_sync.secret') }}',
                                'Content-Type': 'application/json',
                            },
                            body: JSON.stringify({
                                faqs
                            })
                        });

                        const contentType = rasaRes.headers ? rasaRes.headers.get('content-type') :
                            null;
                        let result;
                        if (contentType && contentType.includes('application/json')) {
                            result = await rasaRes.json();
                        } else {
                            const errorText = await rasaRes.text();
                            throw new Error('Rasa sync failed: ' + errorText);
                        }

                        if (!result.ok) {
                            throw new Error(result.error || 'Rasa sync failed');
                        }

                    } catch (syncErr) {
                        console.error('Rasa sync failed after delete:', syncErr);
                        // Still proceed, but inform the admin
                        showToast('error', 'Deleted locally but failed to sync to Rasa');
                        closeModal(viewModal);
                        fetchList(currentPage);
                        return;
                    }

                    showToast('success', 'Document deleted and Rasa synced successfully');
                    closeModal(viewModal);
                    fetchList(currentPage);
                } catch (err) {
                    showToast('error', err.message || 'Error deleting document');
                    console.error(err);
                } finally {
                    deleteBtn.disabled = false;
                }
            });
        }

        // More actions elements (modal "..." menu)
        const moreBtn = $('#moreActionsBtn');
        const moreMenu = $('#moreActionsMenu');

        // More actions button toggle
        if (moreBtn) {
            moreBtn.addEventListener('click', (e) => {
                e.stopPropagation();
                moreMenu.classList.toggle('hidden');
            });
        }

        // Hide menu when clicking outside
        document.addEventListener('click', (e) => {
            if (moreMenu && !moreMenu.contains(e.target) && e.target !== moreBtn) {
                moreMenu.classList.add('hidden');
            }
        });

        // Previous revision elements (collapsible)
        const prevWrapper = $('#previousRevisionWrapper');
        const togglePrevBtn = $('#togglePrevRevisionBtn');
        const prevBlock = $('#prevRevisionBlock');
        const prevMeta = $('#prevRevisionMeta');
        const prevContent = $('#prevRevisionContent');
        const restorePrevBtn = $('#restorePrevBtn');

        // Toggle previous revision block
        if (togglePrevBtn) {
            togglePrevBtn.addEventListener('click', (e) => {
                e.preventDefault();
                if (!prevBlock) return;
                const isHidden = prevBlock.classList.toggle('hidden');
                togglePrevBtn.textContent = isHidden ? 'Show previous response' : 'Hide previous response';
            });
        }

        // Restore previous revision (uses undo endpoint provided by server)
        if (restorePrevBtn) {
            restorePrevBtn.addEventListener('click', async () => {
                const url = restorePrevBtn.dataset.url || '';
                if (!url) return;

                // Ask for confirmation before restoring
                const confirmResult = await Swal.fire({
                    title: 'Restore previous response?',
                    text: 'Do you want to restore this response?',
                    icon: 'question',
                    showCancelButton: true,
                    confirmButtonText: 'Yes, restore',
                    cancelButtonText: 'Cancel'
                });
                if (!confirmResult.isConfirmed) return;

                try {
                    restorePrevBtn.disabled = true;
                    const res = await fetch(url, {
                        method: 'POST',
                        headers: {
                            'X-CSRF-TOKEN': csrf,
                            'X-Requested-With': 'XMLHttpRequest',
                            'Content-Type': 'application/json'
                        }
                    });
                    const json = await res.json();
                    if (!res.ok) {
                        const err = json.message || 'Failed to restore previous response';
                        throw new Error(err);
                    }
                    // Use server-provided confirmation message when available
                    showToast('success', json.message || 'Previous response restored');
                    closeModal(viewModal);
                    try {
                        localStorage.setItem('ts_tickets_changed', String(Date.now()));
                    } catch (e) {}
                    fetchList(currentPage);
                } catch (err) {
                    showToast('error', err.message || 'Error');
                    console.error(err);
                } finally {
                    restorePrevBtn.disabled = false;
                }
            });
        }

        function openModal(modal) {
            if (modal) modal.classList.remove('hidden');
        }

        function closeModal(modal) {
            if (modal) modal.classList.add('hidden');
        }

        // Fetch docs list via AJAX
        async function fetchDocs() {
            console.log('[DEBUG] fetchDocs() function called');
            const docsListEl = $('#docsList');
            console.log('[DEBUG] docsListEl found:', docsListEl);
            try {
                docsListEl.innerHTML = '<div class="text-center text-sm text-gray-500">Loading docs...</div>';

                // Admin should list documents from the database first (authoritative source).
                // The controller returns { ok: true, files: [...] }.
                const res = await fetch(LIST_URL, {
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest'
                    }
                });

                if (!res.ok) {
                    const errorText = await res.text();
                    throw new Error(`Failed to load docs: ${res.status} - ${errorText}`);
                }

                const json = await res.json();
                if (!json.ok) throw new Error(json.error || 'Failed to load docs');

                renderDocsList(json.files || []);
            } catch (err) {
                docsListEl.innerHTML =
                    `<div class="text-center text-sm text-red-600">Error loading documents.</div>`;
            }
        }

        function truncate(str, n = 140) {
            if (!str) return '';
            return (str.length > n) ? (str.slice(0, n - 1) + '…') : str;
        }

        function formatFileSize(bytes) {
            if (!bytes || bytes === 0) return '0 B';
            const k = 1024;
            const sizes = ['B', 'KB', 'MB', 'GB'];
            const i = Math.floor(Math.log(bytes) / Math.log(k));
            return parseFloat((bytes / Math.pow(k, i)).toFixed(1)) + ' ' + sizes[i];
        }

        function formatDate(dateString) {
            if (!dateString) return '';
            try {
                const date = new Date(dateString);
                return date.toLocaleDateString();
            } catch (e) {
                return dateString;
            }
        }

        function onViewDocClick(e) {
            const filename = e.currentTarget.getAttribute('data-filename');
            if (!filename) return;

            const rasaBaseUrl = '{{ config('services.faq_list_docs.url') }}'.replace('/list-docs', '');
            const secret = '{{ config('services.faq_list_docs.secret') }}';
            const downloadUrl =
                `${rasaBaseUrl}/download/${encodeURIComponent(filename)}?token=${encodeURIComponent(secret)}`;
            window.open(downloadUrl, '_blank');
        }

        function onEditDocClick(e) {
            const filename = e.currentTarget.getAttribute('data-filename');
            if (!filename) return;

            $('#edit_doc_filename').value = filename;
            loadDocumentContent(filename);
        }

        async function loadDocumentContent(filename) {
            const contentTextarea = $('#edit_doc_content');
            const errorEl = $('#edit_doc_error');

            contentTextarea.value = '';
            errorEl.classList.add('hidden');
            errorEl.textContent = '';

            try {
                contentTextarea.value = 'Loading document content...';
                contentTextarea.disabled = true;

                const rasaBaseUrl = '{{ config('services.faq_list_docs.url') }}'.replace('/list-docs', '');
                const secret = '{{ config('services.faq_list_docs.secret') }}';

                const res = await fetch(
                    `${rasaBaseUrl}/download/${encodeURIComponent(filename)}?token=${encodeURIComponent(secret)}`, {
                        headers: {
                            'X-Requested-With': 'XMLHttpRequest'
                        }
                    });

                if (!res.ok) {
                    throw new Error(`Failed to load document: ${res.status}`);
                }

                const content = await res.text();

                contentTextarea.value = content;
                contentTextarea.disabled = false;

                openModal(editDocumentModal);

            } catch (err) {
                errorEl.textContent = `Error loading document: ${err.message}`;
                errorEl.classList.remove('hidden');
                contentTextarea.disabled = false;
                contentTextarea.value = '';
            }
        }

        async function logDocumentChange(filename, action) {
            try {
                const csrf = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
                const res = await fetch('{{ route('admin.document-changes.log') }}', {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': csrf,
                        'Content-Type': 'application/json',
                        'X-Requested-With': 'XMLHttpRequest'
                    },
                    body: JSON.stringify({
                        file_name: filename,
                        action: action
                    })
                });

                if (!res.ok) {
                    console.error('Failed to log document change:', await res.text());
                }
            } catch (err) {
                console.error('Error logging document change:', err);
            }
        }

        async function checkTrainingStatus() {
            try {
                const res = await fetch('{{ route('admin.document-changes.training-status') }}', {
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest'
                    }
                });

                if (res.ok) {
                    const data = await res.json();
                    const alertEl = $('#trainingAlert');

                    if (data.requires_training) {
                        alertEl.classList.remove('hidden');
                    } else {
                        alertEl.classList.add('hidden');
                    }
                }
            } catch (err) {
                console.error('Error checking training status:', err);
            }
        }

        async function saveDocumentContent() {
            const filename = $('#edit_doc_filename').value;
            const content = $('#edit_doc_content').value;
            const errorEl = $('#edit_doc_error');
            const submitBtn = $('#editDocumentSubmit');
            const spinner = $('#editDocSpinner');
            const btnText = $('#editDocBtnText');

            if (!filename || !content) {
                showToast('error', 'Filename and content are required');
                return;
            }

            errorEl.classList.add('hidden');
            errorEl.textContent = '';

            submitBtn.disabled = true;
            spinner.classList.remove('hidden');
            btnText.textContent = 'Saving...';

            try {
                const rasaBaseUrl = '{{ config('services.faq_list_docs.url') }}'.replace('/list-docs', '');
                const secret = '{{ config('services.faq_list_docs.secret') }}';

                const res = await fetch(`${rasaBaseUrl}/update-document`, {
                    method: 'POST',
                    headers: {
                        'X-FAQ-UPDATER-TOKEN': secret,
                        'Content-Type': 'application/json',
                        'X-Requested-With': 'XMLHttpRequest'
                    },
                    body: JSON.stringify({
                        file_name: filename,
                        file_content: content,
                        file_type: filename.toLowerCase().endsWith('.md') ? 'text/markdown' :
                            'text/plain'
                    })
                });

                const json = await res.json();

                if (!res.ok || !json.ok) {
                    throw new Error(json.error || 'Failed to save document');
                }

                await logDocumentChange(filename, 'updated');

                showToast('success', `Document "${filename}" updated successfully`);

                closeModal(editDocumentModal);

                fetchDocs();

                checkTrainingStatus();

            } catch (err) {
                errorEl.textContent = `Error saving document: ${err.message}`;
                errorEl.classList.remove('hidden');
            } finally {
                submitBtn.disabled = false;
                spinner.classList.add('hidden');
                btnText.textContent = 'Save Changes';
            }
        }

        function renderDocsList(files) {
            const docsListEl = $('#docsList');
            if (!files || files.length === 0) {
                docsListEl.innerHTML = '<div class="text-center text-sm text-gray-500">No docs files found.</div>';
                return;
            }

            const rasaBaseUrl = '{{ config('services.faq_list_docs.url') }}'.replace('/list-docs', '');
            const secret = '{{ config('services.faq_list_docs.secret') }}';
            docsListEl.innerHTML = files.map(file => `
                    <div class="flex items-center justify-between p-4 border border-gray-200 rounded-lg hover:bg-gray-50">
                        <div class="flex items-center gap-3">
                            <svg class="h-8 w-8 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                            </svg>
                            <div>
                                <div class="text-sm font-medium text-gray-900">${escapeHtml(file.name)}</div>
                                <div class="text-xs text-gray-500">${formatFileSize(file.size)} • Modified ${formatDate(file.modified)}</div>
                            </div>
                        </div>
                        <div class="flex items-center gap-2">
                            <button class="editDocBtn inline-flex items-center gap-1 rounded-md border border-amber-200 bg-amber-50 px-3 py-1.5 text-sm font-medium text-amber-700 hover:bg-amber-100"
                                    data-filename="${file.name}">
                                <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                                </svg>
                                <span class="hidden sm:inline">Edit</span>
                            </button>
                            <button class="viewDocBtn inline-flex items-center gap-1 rounded-md border border-blue-200 bg-blue-50 px-3 py-1.5 text-sm font-medium text-blue-700 hover:bg-blue-100"
                                    data-filename="${file.name}">
                                <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                                </svg>
                                <span class="hidden sm:inline">View</span>
                            </button>
                        </div>
                    </div>
                `).join('');

            $$('.viewDocBtn').forEach(btn => btn.addEventListener('click', onViewDocClick));
            $$('.editDocBtn').forEach(btn => btn.addEventListener('click', onEditDocClick));
        }

        function renderPagination(meta) {
            if (!meta || !meta.total) {
                paginationControls.innerHTML = '';
                return;
            }
            const total = meta.total || 0;
            const per = meta.per_page || currentPerPage;
            const current = meta.current_page || currentPage;
            const last = meta.last_page || 1;

            const pages = [];
            const delta = 2;
            const left = Math.max(1, current - delta);
            const right = Math.min(last, current + delta);
            for (let i = left; i <= right; i++) pages.push(i);

            const prevDisabled = current <= 1;
            const nextDisabled = current >= last;

            paginationControls.innerHTML = `
      <div class="flex items-center gap-3">
        <div class="text-sm text-slate-600">Showing ${per} per page — ${total} total</div>
      </div>
      <div class="flex items-center gap-2">
        <button ${prevDisabled ? 'disabled' : ''} data-page="${current-1}" class="pagerBtn rounded-md border border-gray-200 bg-white px-3 py-1 text-sm ${prevDisabled ? 'opacity-50' : 'hover:bg-gray-50'}">Prev</button>
        ${pages.map(p => `<button data-page="${p}" class="pagerBtn rounded-md ${p===current ? 'bg-blue-600 text-white' : 'border border-gray-200 bg-white text-sm hover:bg-gray-50'} px-3 py-1">${p}</button>`).join('')}
        <button ${nextDisabled ? 'disabled' : ''} data-page="${current+1}" class="pagerBtn rounded-md border border-gray-200 bg-white px-3 py-1 text-sm ${nextDisabled ? 'opacity-50' : 'hover:bg-gray-50'}">Next</button>
      </div>
    `;

            $$('.pagerBtn').forEach(b => b.addEventListener('click', (e) => {
                const p = parseInt(b.getAttribute('data-page') || '1', 10);
                if (!isNaN(p)) fetchList(p);
            }));
        }

        function escapeHtml(s) {
            if (s === null || s === undefined) return '';
            return String(s)
                .replaceAll('&', '&')
                .replaceAll('<', '<')
                .replaceAll('>', '>')
                .replaceAll('"', '"')
                .replaceAll("'", '&#039;');
        }

        function toggleClear(show) {
            if (clearSearchBtn) {
                clearSearchBtn.classList.toggle('hidden', !show);
            }
        }

        // View FAQ click handler
        async function onViewClick(e) {
            const id = e.currentTarget.getAttribute('data-id');
            if (!id) return;
            const url = SHOW_TEMPLATE.replace('__ID__', id);
            try {
                const res = await fetch(url, {
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest'
                    }
                });
                if (!res.ok) throw new Error('Failed to load FAQ');
                const faq = await res.json();
                populateViewModal(faq);
                openModal(viewModal);
            } catch (err) {
                showToast('error', 'Error loading FAQ');
                console.error(err);
            }
        }

        function populateViewModal(faq) {
            if (!viewModal) return;
            viewFaqId.value = faq.id;
            viewTopic.value = faq.intent || '';
            $('#view_description').value = faq.description || '';
            viewResponse.value = faq.response || '';
            viewTimestamps.textContent = `Created: ${faq.created_at || ''} | Updated: ${faq.updated_at || ''}`;

            if (moreBtn) moreBtn.classList.remove('hidden');

            if (moreRestoreBtn) {
                moreRestoreBtn.classList.add('hidden');
            }

            if (faq.latest_revision && prevWrapper) {
                prevWrapper.classList.remove('hidden');
                if (prevMeta) prevMeta.textContent =
                    `${faq.latest_revision.action || 'update'} at ${faq.latest_revision.created_at || ''}`;
                if (prevContent) prevContent.textContent = faq.latest_revision.response || '';
                if (restorePrevBtn && faq.undo_url) {
                    restorePrevBtn.dataset.url = faq.undo_url;
                }
                if (prevBlock) prevBlock.classList.add('hidden');
                if (togglePrevBtn) togglePrevBtn.textContent = 'Show previous response';
            } else if (prevWrapper) {
                prevWrapper.classList.add('hidden');
            }
        }

        // Search handlers
        if (searchBtn) {
            searchBtn.addEventListener('click', () => fetchList(1));
        }
        if (qInput) {
            qInput.addEventListener('keypress', (e) => {
                if (e.key === 'Enter') fetchList(1);
            });
        }
        if (clearSearchBtn) {
            clearSearchBtn.addEventListener('click', () => {
                qInput.value = '';
                fetchList(1);
            });
        }

        // Per page change
        if (perPageSelect) {
            perPageSelect.addEventListener('change', () => {
                currentPerPage = parseInt(perPageSelect.value || '25', 10);
                fetchList(1);
            });
        }

        // Mobile search
        const qMobile = $('#q_mobile');
        const mobileSearchBtn = $('#mobileSearchBtn');
        const perPageMobile = $('#per_page_mobile');

        if (mobileSearchBtn && qMobile) {
            mobileSearchBtn.addEventListener('click', () => {
                if (qInput) qInput.value = qMobile.value;
                fetchList(1);
            });
        }
        if (qMobile) {
            qMobile.addEventListener('keypress', (e) => {
                if (e.key === 'Enter') {
                    if (qInput) qInput.value = qMobile.value;
                    fetchList(1);
                }
            });
        }
        if (perPageMobile) {
            perPageMobile.addEventListener('change', () => {
                if (perPageSelect) perPageSelect.value = perPageMobile.value;
                currentPerPage = parseInt(perPageMobile.value || '25', 10);
                fetchList(1);
            });
        }

        // Mobile drawer handlers
        const mobileActionsToggle = $('#mobileActionsToggle');
        const mobileDrawer = $('#mobileDrawer');
        const mobileDrawerOverlay = $('#mobileDrawerOverlay');
        const mobileDrawerClose = $('#mobileDrawerClose');

        function openDrawer() {
            if (mobileDrawer) mobileDrawer.classList.remove('translate-y-full');
            if (mobileDrawerOverlay) mobileDrawerOverlay.classList.remove('hidden');
        }

        function closeDrawer() {
            if (mobileDrawer) mobileDrawer.classList.add('translate-y-full');
            if (mobileDrawerOverlay) mobileDrawerOverlay.classList.add('hidden');
        }

        if (mobileActionsToggle) {
            mobileActionsToggle.addEventListener('click', openDrawer);
        }
        if (mobileDrawerClose) {
            mobileDrawerClose.addEventListener('click', closeDrawer);
        }
        if (mobileDrawerOverlay) {
            mobileDrawerOverlay.addEventListener('click', closeDrawer);
        }

        // Mobile drawer action buttons
        const mobileRefreshDocsBtn = $('#mobileRefreshDocsBtn');
        const mobileUploadFileBtn = $('#mobileUploadFileBtn');
        const mobileHistoryBtn = $('#mobileHistoryBtn');

        if (mobileRefreshDocsBtn) {
            mobileRefreshDocsBtn.addEventListener('click', () => {
                closeDrawer();
                fetchDocs();
            });
        }

        if (mobileUploadFileBtn) {
            mobileUploadFileBtn.addEventListener('click', () => {
                closeDrawer();
                openModal(uploadModal);
            });
        }

        if (mobileHistoryBtn) {
            mobileHistoryBtn.addEventListener('click', () => {
                closeDrawer();
                openModal(historyModal);
                loadHistory();
            });
        }

        // History modal and button
        const openHistoryBtn = $('#openHistoryBtn');
        const historyModal = $('#historyModal');
        const historyLoading = $('#historyLoading');
        const historyError = $('#historyError');
        const historyNoRecords = $('#historyNoRecords');
        const historyTableBody = $('#historyTableBody');

        if (openHistoryBtn) {
            openHistoryBtn.addEventListener('click', () => {
                openModal(historyModal);
                loadHistory();
            });
        }

        // Close history modal buttons
        const historyCloseButtons = $$('[data-close="history"]', historyModal || document);
        historyCloseButtons.forEach(btn => btn.addEventListener('click', () => closeModal(historyModal)));

        async function loadHistory() {
            if (!historyModal) return;
            historyLoading.classList.remove('hidden');
            historyError.classList.add('hidden');
            historyNoRecords.classList.add('hidden');
            historyTableBody.innerHTML = '';

            if (!HISTORY_URL) {
                historyError.textContent = 'History URL is not configured.';
                historyError.classList.remove('hidden');
                historyLoading.classList.add('hidden');
                return;
            }

            try {
                const res = await fetch(HISTORY_URL, {
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest'
                    }
                });

                if (!res.ok) {
                    throw new Error(`Failed to load history: ${res.status}`);
                }

                const data = await res.json();

                if (!Array.isArray(data) || data.length === 0) {
                    historyNoRecords.classList.remove('hidden');
                    return;
                }

                data.forEach(record => {
                    const action = record.action || '';
                    const fileName = record.file_name || record.filename || '';
                    const user = record.user_name || record.user || record.performed_by || record
                        .uploaded_by || '';
                    const timestamp = record.timestamp || record.created_at || record.uploaded_at ||
                        record.updated_at || '';

                    const tr = document.createElement('tr');
                    tr.innerHTML = `
                            <td class="px-3 py-2">${escapeHtml(action)}</td>
                            <td class="px-3 py-2">${escapeHtml(fileName)}</td>
                            <td class="px-3 py-2">${escapeHtml(user)}</td>
                            <td class="px-3 py-2">${formatDate(timestamp)}</td>
                        `;
                    historyTableBody.appendChild(tr);
                });
            } catch (err) {
                historyError.textContent = err.message || 'Error loading history';
                historyError.classList.remove('hidden');
            } finally {
                historyLoading.classList.add('hidden');
            }
        }

        // Initialize: fetch docs on page load
        fetchDocs();
        checkTrainingStatus();
    })();
</script>
<script>
    (function() {
        const uploadLogsBtn = document.getElementById('uploadLogsBtn');
        const uploadLogsModal = document.getElementById('uploadLogsModal');
        const uploadLogsTableBody = document.getElementById('uploadLogsTableBody');
        const uploadLogsPagination = document.getElementById('uploadLogsPagination');
        const closeEls = document.querySelectorAll('[data-close="upload-logs"]');

        function openModal(el) {
            if (el) el.classList.remove('hidden');
        }

        function closeModal(el) {
            if (el) el.classList.add('hidden');
        }

        if (uploadLogsBtn) uploadLogsBtn.addEventListener('click', () => {
            fetchUploadLogs(1);
            openModal(uploadLogsModal);
        });
        closeEls.forEach(el => el.addEventListener('click', () => closeModal(uploadLogsModal)));

        async function fetchUploadLogs(page = 1) {
            try {
                const per_page = 10;
                const res = await fetch(
                    `{{ route('staff.upload-logs.index') }}?page=${page}&per_page=${per_page}`, {
                        headers: {
                            'X-Requested-With': 'XMLHttpRequest'
                        }
                    });
                if (!res.ok) throw new Error('Failed to fetch upload logs');
                const data = await res.json();

                renderUploadLogsTable(data.data || []);
                renderUploadLogsPagination(data);
            } catch (err) {
                console.error('Failed to fetch upload logs', err);
                if (uploadLogsTableBody) uploadLogsTableBody.innerHTML =
                    `<tr><td colspan="4" class="px-4 py-4 text-sm text-red-600">Failed to load upload logs</td></tr>`;
                if (uploadLogsPagination) uploadLogsPagination.innerHTML = '';
            }
        }

        function renderUploadLogsTable(rows) {
            if (!uploadLogsTableBody) return;
            if (!rows || rows.length === 0) {
                uploadLogsTableBody.innerHTML =
                    `<tr><td colspan="4" class="px-4 py-6 text-center text-sm text-gray-500">No logs found</td></tr>`;
                return;
            }

            uploadLogsTableBody.innerHTML = rows.map(r => {
                const size = r.file_size ? formatFileSize(r.file_size) : '-';
                const uploadDate = r.upload_date ? new Date(r.upload_date).toLocaleString() : '-';
                const serverDate = r.server_recieved_date ? new Date(r.server_recieved_date)
                    .toLocaleString() : '-';
                return `<tr><td class="px-4 py-3 text-sm text-gray-900">${escapeHtml(r.file_name)}</td><td class="px-4 py-3 text-sm text-gray-700">${size}</td><td class="px-4 py-3 text-sm text-gray-700">${uploadDate}</td><td class="px-4 py-3 text-sm text-gray-700">${serverDate}</td></tr>`;
            }).join('');
        }

        function renderUploadLogsPagination(meta) {
            if (!uploadLogsPagination) return;
            if (!meta || !meta.total) {
                uploadLogsPagination.innerHTML = '';
                return;
            }
            const current = meta.current_page || 1;
            const last = meta.last_page || 1;
            let html = '';
            if (current > 1) html +=
                `<button class="px-3 py-1 border rounded mr-2" data-page="${current-1}">Prev</button>`;
            html += `<span class="text-sm text-gray-700 mr-2">Page ${current} of ${last}</span>`;
            if (current < last) html +=
                `<button class="px-3 py-1 border rounded" data-page="${current+1}">Next</button>`;
            uploadLogsPagination.innerHTML = html;
            uploadLogsPagination.querySelectorAll('button').forEach(b => b.addEventListener('click', () =>
                fetchUploadLogs(parseInt(b.getAttribute('data-page')))));
        }

        function escapeHtml(s) {
            if (s == null) return '';
            return String(s).replaceAll('&', '&').replaceAll('<', '<').replaceAll('>', '>').replaceAll('"', '"')
                .replaceAll("'", '&#039;');
        }

        function formatFileSize(bytes) {
            if (!bytes) return '0 B';
            const k = 1024;
            const sizes = ['B', 'KB', 'MB', 'GB'];
            const i = Math.floor(Math.log(bytes) / Math.log(k));
            return (bytes / Math.pow(k, i)).toFixed(1) + ' ' + sizes[i];
        }
    })();
</script>
