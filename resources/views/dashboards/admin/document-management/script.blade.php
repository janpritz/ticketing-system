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
    (function() {
        // --- DOM Elements ---
        const documentsTbody = document.getElementById('documentsTbody');
        const documentsPagination = document.getElementById('documentsPagination');

        const viewDocumentModal = document.getElementById('viewDocumentModal');
        const editDocumentModal = document.getElementById('editDocumentModal');
        const uploadFileModal = document.getElementById('uploadFileModal');
        const viewDocFilename = document.getElementById('view_doc_filename');
        const viewDocContent = document.getElementById('view_doc_content');
        const editDocFilename = document.getElementById('edit_doc_filename');
        const editDocContent = document.getElementById('edit_doc_content');
        const editDocError = document.getElementById('edit_doc_error');
        const editDocumentSubmit = document.getElementById('editDocumentSubmit');
        const editDocSpinner = document.getElementById('editDocSpinner');
        const editDocBtnText = document.getElementById('editDocBtnText');
        const deleteDocBtn = document.getElementById('deleteDocBtn');
        const viewDocEditBtn = document.getElementById('viewDocEditBtn');

        const csrf = document.querySelector('meta[name="csrf-token"]').getAttribute('content');

        // --- Get Routes from hidden state ---
        const adminFaqsState = document.getElementById('admin-faqs-state');
        const LIST_URL = adminFaqsState ? adminFaqsState.dataset.listUrl : '{{ route('admin.knowledgebase.list') }}';
        const UPDATE_DOC_URL = '{{ route('admin.knowledgebase.update-document') }}';
        const DELETE_DOC_URL = '{{ route('admin.knowledgebase.delete-document') }}';
        const UPLOAD_URL = '{{ route('admin.knowledgebase.uploadDocument') }}';

        // --- Upload Elements ---
        const uploadOpenBtn = document.getElementById('uploadFileBtn');
        const uploadCloseEls = document.querySelectorAll('[data-close="upload"]');
        const uploadForm = document.getElementById('uploadFileForm');
        const uploadSubmit = document.getElementById('uploadFileSubmit');
        const faqFileInput = document.getElementById('faqFile');

        // Open/Close Upload Modal
        if (uploadOpenBtn) {
            uploadOpenBtn.addEventListener('click', () => openModal(uploadFileModal));
        }
        uploadCloseEls.forEach(el => el.addEventListener('click', () => closeModal(uploadFileModal)));

        // -------------------------
        // 🔄 AJAX REFRESH SERVICE
        // -------------------------
        // Hits Laravel backend, gets fresh files, and re-paints the table UI
        async function fetchAndReloadTable() {
            try {
                const res = await fetch(LIST_URL, {
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest'
                    }
                });

                if (!res.ok) throw new Error('Failed to fetch documents');

                const data = await res.json();
                //console.log(data);
                if (data.ok && Array.isArray(data.files)) {
                    renderDocumentsTable(data.files);
                }
            } catch (err) {
                console.error('Failed to reload table dynamically:', err);
                showToast('error', 'Failed to refresh table view.');
            }
        }

        // -------------------------
        // 📤 UPLOAD HANDLER
        // -------------------------
        if (uploadSubmit) {
            uploadSubmit.addEventListener('click', async () => {
                const file = faqFileInput.files[0];
                if (!file) {
                    document.getElementById('upload_file_error').textContent = 'Please select a file';
                    document.getElementById('upload_file_error').classList.remove('hidden');
                    return;
                }

                // Validate file type
                const allowedTypes = ['text/plain'];
                if (!allowedTypes.includes(file.type) && !file.name.match(/\.txt$/i)) {
                    document.getElementById('upload_file_error').textContent =
                        'Only .txt files are allowed';
                    document.getElementById('upload_file_error').classList.remove('hidden');
                    return;
                }

                document.getElementById('upload_file_error').classList.add('hidden');

                const originalHTML = uploadSubmit.innerHTML;
                uploadSubmit.disabled = true;
                uploadSubmit.innerHTML = `
                    <svg class="animate-spin h-4 w-4 mx-auto" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                    </svg>
                    <span class="ml-2">Uploading...</span>`;

                try {
                    const fileContent = await file.text();

                    const res = await fetch(UPLOAD_URL, {
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
                        throw new Error(result.error || 'File upload failed');
                    }

                    showToast('success', 'File saved successfully');

                    closeModal(uploadFileModal);
                    uploadForm.reset();

                    // 🚀 RELOAD TABLE DYNAMICALLY
                    await fetchAndReloadTable();

                } catch (err) {
                    console.error('[Admin Upload] Upload error:', err);
                    showToast('error', err.message || 'Failed to upload file');
                } finally {
                    uploadSubmit.innerHTML = originalHTML;
                    uploadSubmit.disabled = false;
                }
            });
        }

        // -------------------------
        // ✏️ EDIT HANDLER
        // -------------------------
        async function saveDocumentContent() {
            const filename = editDocFilename.value;
            const content = editDocContent.value;

            if (!filename || !content) {
                showToast('error', 'Filename and content are required');
                return;
            }

            editDocError.classList.add('hidden');
            editDocumentSubmit.disabled = true;
            editDocSpinner.classList.remove('hidden');
            editDocBtnText.textContent = 'Saving...';

            try {
                const res = await fetch(UPDATE_DOC_URL, {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': csrf,
                        'Content-Type': 'application/json',
                        'X-Requested-With': 'XMLHttpRequest'
                    },
                    body: JSON.stringify({
                        file_name: filename,
                        content: content
                    })
                });

                const result = await res.json();

                if (!res.ok || !result.ok) {
                    throw new Error(result.error || 'Failed to save document');
                }

                showToast('success', `Document "${filename}" saved successfully`);
                closeModal(editDocumentModal);

                // 🚀 RELOAD TABLE DYNAMICALLY
                await fetchAndReloadTable();

            } catch (err) {
                editDocError.textContent = err.message || 'Failed to save document';
                editDocError.classList.remove('hidden');
                showToast('error', err.message || 'Failed to save document');
            } finally {
                editDocumentSubmit.disabled = false;
                editDocSpinner.classList.add('hidden');
                editDocBtnText.textContent = 'Save Changes';
            }
        }

        // -------------------------
        // 🗑️ DELETE HANDLER
        // -------------------------
        async function onDeleteDocClick() {
            const filename = editDocFilename.value;
            if (!filename) {
                showToast('error', 'Filename is missing');
                return;
            }

            const confirmResult = await Swal.fire({
                title: 'Delete document?',
                text: `Delete "${filename}" from the database?`,
                icon: 'warning',
                showCancelButton: true,
                confirmButtonText: 'Yes, delete',
                cancelButtonText: 'Cancel'
            });

            if (!confirmResult.isConfirmed) return;

            try {
                deleteDocBtn.disabled = true;

                const res = await fetch(DELETE_DOC_URL, {
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

                const json = await res.json();

                if (!res.ok || !json.ok) {
                    throw new Error(json.error || 'Failed to delete document');
                }

                showToast('success', `Document "${filename}" deleted successfully`);
                closeModal(editDocumentModal);

                // 🚀 RELOAD TABLE DYNAMICALLY
                await fetchAndReloadTable();

            } catch (err) {
                showToast('error', err.message || 'Failed to delete document');
            } finally {
                deleteDocBtn.disabled = false;
            }
        }

        // -------------------------
        // 👀 VIEW HANDLER
        // -------------------------
        async function onViewDocClick(e) {
            const filename = e.currentTarget.getAttribute('data-filename');
            if (!filename) return;

            try {
                viewDocFilename.value = filename;
                viewDocContent.textContent = 'Loading...';
                openModal(viewDocumentModal);

                const doc = await loadDocumentContent(filename);

                if (doc && doc.content) {
                    viewDocContent.textContent = doc.content;
                } else {
                    viewDocContent.textContent = 'No content available';
                }
            } catch (err) {
                viewDocContent.textContent = 'Error loading document content';
                showToast('error', err.message || 'Failed to load document');
            }
        }

        async function onEditDocClick(e) {
            const filename = e.currentTarget.getAttribute('data-filename');
            if (!filename) return;

            try {
                editDocFilename.value = filename;
                editDocContent.value = 'Loading...';
                editDocError.classList.add('hidden');
                openModal(editDocumentModal);

                const doc = await loadDocumentContent(filename);

                if (doc && doc.content) {
                    editDocContent.value = doc.content;
                } else {
                    editDocContent.value = '';
                }
            } catch (err) {
                editDocContent.value = '';
                editDocError.textContent = err.message || 'Failed to load document';
                editDocError.classList.remove('hidden');
                showToast('error', err.message || 'Failed to load document');
            }
        }

        async function loadDocumentContent(filename) {
            const res = await fetch(LIST_URL, {
                headers: {
                    'X-Requested-With': 'XMLHttpRequest'
                }
            });
            if (!res.ok) throw new Error('Failed to load documents');

            const data = await res.json();
            if (!data.ok) throw new Error(data.error || 'Failed to load documents');

            return data.files.find(f => f.name === filename);
        }

        function onViewDocEditClick() {
            const filename = viewDocFilename.value;
            if (!filename) return;
            closeModal(viewDocumentModal);

            editDocFilename.value = filename;
            editDocContent.value = viewDocContent.textContent;
            openModal(editDocumentModal);
        }

        

        // --- Utilities ---
        function formatFileSize(bytes) {
            if (!bytes || bytes === 0) return '0 B';
            const k = 1024;
            const sizes = ['B', 'KB', 'MB', 'GB'];
            const i = Math.floor(Math.log(bytes) / Math.log(k));
            return parseFloat((bytes / Math.pow(k, i)).toFixed(1)) + ' ' + sizes[i];
        }

        function formatDate(dateString) {
            if (!dateString) return '-';
            try {
                const date = new Date(dateString);
                return date.toLocaleDateString() + ' ' + date.toLocaleTimeString();
            } catch (e) {
                return dateString;
            }
        }

        function escapeHtml(s) {
            if (!s) return '';
            return String(s).replaceAll('&', '&').replaceAll('<', '<').replaceAll('>', '>').replaceAll('"', '"')
                .replaceAll("'", '&#039;');
        }

        function openModal(modal) {
            if (modal) modal.classList.remove('hidden');
        }

        function closeModal(modal) {
            if (modal) modal.classList.add('hidden');
        }

        // --- Standard Static Events ---
        document.querySelectorAll('[data-close="view-doc"]').forEach(el => el.addEventListener('click', () =>
            closeModal(viewDocumentModal)));
        document.querySelectorAll('[data-close="edit-doc"]').forEach(el => el.addEventListener('click', () =>
            closeModal(editDocumentModal)));
        if (editDocumentSubmit) editDocumentSubmit.addEventListener('click', saveDocumentContent);
        if (deleteDocBtn) deleteDocBtn.addEventListener('click', onDeleteDocClick);
        if (viewDocEditBtn) viewDocEditBtn.addEventListener('click', onViewDocEditClick);

        // --- Restore Handler ---
        async function onRestoreDocClick(e) {
            const filename = e.currentTarget.getAttribute('data-filename');
            if (!filename) return;

            const confirmResult = await Swal.fire({
                title: 'Restore document?',
                text: `Restore "${filename}" from trash?`,
                icon: 'warning',
                showCancelButton: true,
                confirmButtonText: 'Yes, restore',
                cancelButtonText: 'Cancel'
            });

            if (!confirmResult.isConfirmed) return;

            try {
                const res = await fetch('{{ route('admin.knowledgebase.restore-document') }}', {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': csrf,
                        'Content-Type': 'application/json',
                        'X-Requested-With': 'XMLHttpRequest'
                    },
                    body: JSON.stringify({
                        file_name: filename
                    })
                });

                const json = await res.json();

                if (!res.ok || !json.ok) {
                    throw new Error(json.error || 'Failed to restore document');
                }

                showToast('success', `Document "${filename}" restored successfully`);
                await fetchAndReloadTable();

            } catch (err) {
                showToast('error', err.message || 'Failed to restore document');
            }
        }

        // --- Enhanced Table Renderer with Restore Logic ---
        function renderDocumentsTable(documents) {
            if (!documentsTbody) return;

            if (!documents || documents.length === 0) {
                documentsTbody.innerHTML = `
                    <tr>
                        <td colspan="5" class="px-5 py-6 text-center text-sm text-gray-500">
                            No documents found.
                        </td>
                    </tr>
                `;
                return;
            }

            documentsTbody.innerHTML = documents.map(doc => `
                <tr class="hover:bg-gray-50">
                    <td class="py-3 pl-5 pr-3">
                        <div class="flex items-center gap-3">
                            <svg class="h-5 w-5 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                            </svg>
                            <span class="text-sm font-medium text-gray-900">${escapeHtml(doc.name)}</span>
                        </div>
                    </td>
                    <td class="px-3 py-3 text-sm text-gray-700">${formatFileSize(doc.size)}</td>
                    <td class="px-3 py-3 text-sm text-gray-700">${escapeHtml(doc.created_by || '-')}</td>
                    <td class="px-3 py-3 text-sm text-gray-700">${formatDate(doc.modified)}</td>
                    <td class="px-3 py-3">
                        <div class="flex items-center gap-2">
                            <button class="viewDocBtn inline-flex items-center gap-1 rounded-md border border-blue-200 bg-blue-50 px-3 py-1.5 text-sm font-medium text-blue-700 hover:bg-blue-100"
                                    data-filename="${escapeHtml(doc.name)}">
                                <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                                </svg>
                                <span>View</span>
                            </button>
                            ${doc.deleted_at ? `
                            <button class="restoreDocBtn inline-flex items-center gap-1 rounded-md border border-green-200 bg-green-50 px-3 py-1.5 text-sm font-medium text-green-700 hover:bg-green-100"
                                    data-filename="${escapeHtml(doc.name)}">
                                <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
                                </svg>
                                <span>Restore</span>
                            </button>
                            ` : `
                            <button class="editDocBtn inline-flex items-center gap-1 rounded-md border border-amber-200 bg-amber-50 px-3 py-1.5 text-sm font-medium text-amber-700 hover:bg-amber-100"
                                    data-filename="${escapeHtml(doc.name)}">
                                <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                                </svg>
                                <span>Edit</span>
                            </button>
                            `}
                        </div>
                    </td>
                </tr>
            `).join('');

            // Re-bind actions to new rows
            document.querySelectorAll('.viewDocBtn').forEach(btn => btn.addEventListener('click', onViewDocClick));
            document.querySelectorAll('.editDocBtn').forEach(btn => btn.addEventListener('click', onEditDocClick));
            document.querySelectorAll('.restoreDocBtn').forEach(btn => btn.addEventListener('click', onRestoreDocClick));
        }

        // --- Boot Initial Render ---
        fetchAndReloadTable();
    })();
</script>
