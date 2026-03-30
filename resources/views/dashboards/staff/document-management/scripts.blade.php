<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
    function showToast(type,message) {
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
        const csrfMeta = document.querySelector('meta[name="csrf-token"]');
        const csrf = csrfMeta ? csrfMeta.getAttribute('content') : '';
        const documentsTbody = document.getElementById('documentsTbody');
        const documentsPagination = document.getElementById('documentsPagination');
        const viewDocumentModal = document.getElementById('viewDocumentModal');
        const viewDocFilename = document.getElementById('view_doc_filename');
        const viewDocContent = document.getElementById('view_doc_content');
        const editDocFilename = document.getElementById('edit_doc_filename');
        const editDocContent = document.getElementById('edit_doc_content');
        const editDocError = document.getElementById('edit_doc_error');
        const editDocSpinner = document.getElementById('editDocSpinner');
        const editDocBtnText = document.getElementById('editDocBtnText');
        const viewDocEditBtn = document.getElementById('viewDocEditBtn');
        const LIST_URL = @json($listUrl ?? route('staff.document_management.files'));

        const $ = (sel, root = document) => root.querySelector(sel);
        const $$ = (sel, root = document) => Array.from(root.querySelectorAll(sel));

        const uploadModal = $('#uploadFileModal');
        const uploadOpenBtn = $('#uploadFileBtn');
        const uploadCloseEls = $$('[data-close="upload"]', uploadModal || document);
        const uploadForm = $('#uploadFileForm');
        const uploadSubmit = $('#uploadFileSubmit');
        const faqFileInput = $('#faqFile');

        const editDocumentModal = $('#editDocumentModal');
        const editDocumentCloseEls = $$('[data-close="edit-doc"]', editDocumentModal || document);
        const editDocumentSubmit = $('#editDocumentSubmit');

        const mobileActionsToggle = $('#mobileActionsToggle');
        const mobileDrawer = $('#mobileDrawer');
        const mobileDrawerOverlay = $('#mobileDrawerOverlay');
        const mobileDrawerClose = $('#mobileDrawerClose');
        const mobileUploadFileBtn = $('#mobileUploadFileBtn');

        function openModal(modal) {
            if (modal) modal.classList.remove('hidden');
        }

        function closeModal(modal) {
            if (modal) modal.classList.add('hidden');
        }

        function openDrawer() {
            if (mobileDrawer) mobileDrawer.classList.remove('translate-y-full');
            if (mobileDrawerOverlay) mobileDrawerOverlay.classList.remove('hidden');
        }

        function closeDrawer() {
            if (mobileDrawer) mobileDrawer.classList.add('translate-y-full');
            if (mobileDrawerOverlay) mobileDrawerOverlay.classList.add('hidden');
        }

        function escapeHtml(value) {
            if (value == null) return '';
            const div = document.createElement('div');
            div.textContent = String(value);
            return div.innerHTML;
        }

        function formatFileSize(bytes) {
            if (!bytes) return '0 B';
            const units = ['B', 'KB', 'MB', 'GB'];
            const index = Math.min(Math.floor(Math.log(bytes) / Math.log(1024)), units.length - 1);
            return `${(bytes / Math.pow(1024, index)).toFixed(1)} ${units[index]}`;
        }

        function formatDate(dateString) {
            if (!dateString) return '-';
            try {
                return new Date(dateString).toLocaleString();
            } catch (error) {
                return dateString;
            }
        }

        async function fetchAndReloadTable() {
            try {
                const res = await fetch(LIST_URL, {
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest'
                    }
                });

                if (!res.ok) {
                    const errorData = await res.json().catch(() => ({}));
                    throw new Error(errorData.error || `Error ${res.status}`);
                }

                const json = await res.json();
                if (!json.ok) {
                    throw new Error(json.error || 'Failed to load documents');
                }

                renderDocumentsTable(json.files || []);
            } catch (error) {
                const message = error.message || 'Error loading documents.';

                if (documentsTbody) {
                    documentsTbody.innerHTML = `
                            <tr>
                                <td colspan="5" class="px-5 py-6 text-center text-sm text-red-600">${escapeHtml(message)}</td>
                            </tr>`;
                }

                if (documentsPagination) {
                    documentsPagination.innerHTML = '';
                }
            }
        }

        function renderDocumentsTable(documents) {
            if (!documentsTbody) return;

            if (!Array.isArray(documents) || documents.length === 0) {
                documentsTbody.innerHTML = `
                        <tr>
                            <td colspan="5" class="px-5 py-6 text-center text-sm text-gray-500">
                                No documents found.
                            </td>
                        </tr>
                    `;
                return;
            }

            documentsTbody.innerHTML = documents.map((doc) => `
                    <tr class="hover:bg-gray-50">
                        <td class="py-3 pl-5 pr-3">
                            <div class="flex items-center gap-3">
                                <svg class="h-5 w-5 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                                </svg>
                                <span class="text-sm font-medium text-gray-900">${escapeHtml(doc.name || doc.file_name)}</span>
                            </div>
                        </td>
                        <td class="px-3 py-3 text-sm text-gray-700">${formatFileSize(doc.size)}</td>
                        <td class="px-3 py-3 text-sm text-gray-700">${escapeHtml(doc.created_by_name || doc.created_by || 'You')}</td>
                        <td class="px-3 py-3 text-sm text-gray-700">${formatDate(doc.modified)}</td>
                        <td class="py-3 pl-3 pr-5">
                            <div class="flex items-center justify-end gap-2">
                                <button class="viewDocBtn inline-flex items-center gap-1 rounded-md border border-blue-200 bg-blue-50 px-3 py-1.5 text-sm font-medium text-blue-700 hover:bg-blue-100" data-id="${escapeHtml(doc.id)}" data-filename="${escapeHtml(doc.name || doc.file_name)}">
                                    <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                                    </svg>
                                    <span>View</span>
                                </button>
                                ${doc.deleted_at ? `
                                    <button class="restoreDocBtn inline-flex items-center gap-1 rounded-md border border-green-200 bg-green-50 px-3 py-1.5 text-sm font-medium text-green-700 hover:bg-green-100 disabled:cursor-not-allowed disabled:opacity-70" data-id="${escapeHtml(doc.id)}" data-filename="${escapeHtml(doc.name || doc.file_name)}">
                                        <svg class="h-4 w-4 restoreDocIcon" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
                                        </svg>
                                        <svg class="h-4 w-4 animate-spin hidden restoreDocSpinner" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                        </svg>
                                        <span class="restoreDocText">Restore</span>
                                    </button>
                                ` : `
                                    <button class="editDocBtn inline-flex items-center gap-1 rounded-md border border-amber-200 bg-amber-50 px-3 py-1.5 text-sm font-medium text-amber-700 hover:bg-amber-100" data-id="${escapeHtml(doc.id)}" data-filename="${escapeHtml(doc.name || doc.file_name)}">
                                        <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                                        </svg>
                                        <span>Edit</span>
                                    </button>
                                    <button class="deleteDocBtn inline-flex items-center gap-1 rounded-md border border-red-200 bg-red-50 px-3 py-1.5 text-sm font-medium text-red-700 hover:bg-red-100 disabled:cursor-not-allowed disabled:opacity-70" data-id="${escapeHtml(doc.id)}" data-filename="${escapeHtml(doc.name || doc.file_name)}">
                                        <svg class="h-4 w-4 deleteDocIcon" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6M9 7V4a1 1 0 011-1h4a1 1 0 011 1v3m-7 0h8" />
                                        </svg>
                                        <svg class="h-4 w-4 animate-spin hidden deleteDocSpinner" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                        </svg>
                                        <span class="deleteDocText">Delete</span>
                                    </button>
                                `}
                            </div>
                        </td>
                    </tr>
                `).join('');

            document.querySelectorAll('.viewDocBtn').forEach((btn) => btn.addEventListener('click',
                onViewDocClick));
            document.querySelectorAll('.editDocBtn').forEach((btn) => btn.addEventListener('click',
                onEditDocClick));
            document.querySelectorAll('.deleteDocBtn').forEach((btn) => btn.addEventListener('click',
                onDeleteDocClick));
            document.querySelectorAll('.restoreDocBtn').forEach((btn) => btn.addEventListener('click',
                onRestoreDocClick));
        }

        async function loadDocumentContent(filename) {
            const res = await fetch(LIST_URL, {
                headers: {
                    'X-Requested-With': 'XMLHttpRequest'
                }
            });

            if (!res.ok) throw new Error('Failed to load documents');

            const json = await res.json();
            if (!json.ok) throw new Error(json.error || 'Failed to load documents');

            return (json.files || []).find((file) => (file.name || file.file_name) === filename);
        }

        async function onViewDocClick(event) {
            const filename = event.currentTarget.getAttribute('data-filename');
            if (!filename) return;

            try {
                if (viewDocFilename) viewDocFilename.value = filename;
                if (viewDocContent) viewDocContent.textContent = 'Loading...';
                openModal(viewDocumentModal);

                const doc = await loadDocumentContent(filename);
                if (viewDocContent) {
                    viewDocContent.textContent = doc && doc.content ? doc.content : 'No content available';
                }
            } catch (error) {
                if (viewDocContent) viewDocContent.textContent = 'Error loading document content';
                showToast('error', error.message || 'Failed to load document');
            }
        }

        async function onEditDocClick(event) {
            const documentId = event.currentTarget.getAttribute('data-id');
            const filename = event.currentTarget.getAttribute('data-filename');
            if (!documentId || !filename) return;

            try {
                if (editDocFilename) editDocFilename.value = filename;
                if (editDocFilename) editDocFilename.dataset.documentId = documentId;
                if (editDocContent) editDocContent.value = 'Loading...';
                if (editDocError) {
                    editDocError.classList.add('hidden');
                    editDocError.textContent = '';
                }
                openModal(editDocumentModal);

                const doc = await loadDocumentContent(filename);
                if (editDocContent) {
                    editDocContent.value = doc && doc.content ? doc.content : '';
                }
            } catch (error) {
                if (editDocContent) editDocContent.value = '';
                if (editDocError) {
                    editDocError.textContent = error.message || 'Failed to load document';
                    editDocError.classList.remove('hidden');
                }
                showToast('error', error.message || 'Failed to load document');
            }
        }

        async function onRestoreDocClick(event) {
            const button = event.currentTarget;
            const documentId = event.currentTarget.getAttribute('data-id');
            const filename = event.currentTarget.getAttribute('data-filename');
            if (!documentId || !filename) return;

            const icon = button.querySelector('.restoreDocIcon');
            const spinner = button.querySelector('.restoreDocSpinner');
            const text = button.querySelector('.restoreDocText');

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
                button.disabled = true;
                if (icon) icon.classList.add('hidden');
                if (spinner) spinner.classList.remove('hidden');
                if (text) text.textContent = 'Restoring...';

                const res = await fetch('{{ route('staff.document_management.restore-document') }}', {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': csrf,
                        'Content-Type': 'application/json',
                        'X-Requested-With': 'XMLHttpRequest'
                    },
                    body: JSON.stringify({
                        id: documentId
                    })
                });

                const json = await res.json();
                if (!res.ok || !json.ok) {
                    throw new Error(json.error || 'Failed to restore document');
                }

                showToast('success', `Document "${filename}" restored successfully`);
                await fetchAndReloadTable();
            } catch (error) {
                showToast('error', error.message || 'Failed to restore document');
            } finally {
                button.disabled = false;
                if (icon) icon.classList.remove('hidden');
                if (spinner) spinner.classList.add('hidden');
                if (text) text.textContent = 'Restore';
            }
        }

        function onViewDocEditClick() {
            const filename = viewDocFilename ? viewDocFilename.value : '';
            if (!filename) return;

            closeModal(viewDocumentModal);
            if (editDocFilename) editDocFilename.value = filename;
            const matchingDocument = document.querySelector(`.editDocBtn[data-filename="${CSS.escape(filename)}"]`);
            if (editDocFilename && matchingDocument) {
                editDocFilename.dataset.documentId = matchingDocument.getAttribute('data-id') || '';
            }
            if (editDocContent && viewDocContent) editDocContent.value = viewDocContent.textContent;
            openModal(editDocumentModal);
        }

        async function saveDocumentContent() {
            const documentId = editDocFilename?.dataset.documentId;
            const filename = editDocFilename?.value;
            const content = editDocContent?.value;

            if (!documentId || !filename || !content) {
                showToast('error', 'Document reference and content are required');
                return;
            }

            if (editDocumentSubmit) editDocumentSubmit.disabled = true;
            if (editDocSpinner) editDocSpinner.classList.remove('hidden');
            if (editDocBtnText) editDocBtnText.textContent = 'Saving...';
            if (editDocError) {
                editDocError.classList.add('hidden');
                editDocError.textContent = '';
            }

            try {
                const res = await fetch('{{ route('staff.document_management.update-document') }}', {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': csrf,
                        'Content-Type': 'application/json',
                        'X-Requested-With': 'XMLHttpRequest'
                    },
                    body: JSON.stringify({
                        id: documentId,
                        content: content
                    })
                });

                const json = await res.json();
                if (!res.ok || !json.ok) {
                    throw new Error(json.error || 'Failed to save document');
                }

                showToast('success', `Document "${filename}" updated successfully`);
                closeModal(editDocumentModal);
                await fetchAndReloadTable();
            } catch (error) {
                if (editDocError) {
                    editDocError.textContent = `Error saving document: ${error.message}`;
                    editDocError.classList.remove('hidden');
                }
            } finally {
                if (editDocumentSubmit) editDocumentSubmit.disabled = false;
                if (editDocSpinner) editDocSpinner.classList.add('hidden');
                if (editDocBtnText) editDocBtnText.textContent = 'Save Changes';
            }
        }

        async function deleteDocument(documentId, filename, button = null) {
            if (!documentId || !filename) {
                showToast('error', 'Document reference is missing');
                return;
            }

            const icon = button?.querySelector('.deleteDocIcon');
            const spinner = button?.querySelector('.deleteDocSpinner');
            const text = button?.querySelector('.deleteDocText');

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
                if (button) {
                    button.disabled = true;
                    if (icon) icon.classList.add('hidden');
                    if (spinner) spinner.classList.remove('hidden');
                    if (text) text.textContent = 'Deleting...';
                }

                const res = await fetch(`{{ url('staff/document-management/document') }}/${encodeURIComponent(documentId)}`, {
                    method: 'DELETE',
                    headers: {
                        'X-CSRF-TOKEN': csrf,
                        'Content-Type': 'application/json',
                        'X-Requested-With': 'XMLHttpRequest'
                    },
                    body: JSON.stringify({
                        id: documentId
                    })
                });

                const json = await (res.ok ? res.json() : {
                    success: false,
                    error: await res.text()
                });
                if (!res.ok || !json.success) {
                    throw new Error(json.message || json.error || 'Failed to delete document');
                }

                showToast('success', `Document "${filename}" deleted successfully`);
                closeModal(editDocumentModal);
                await fetchAndReloadTable();
            } catch (error) {
                showToast('error', error.message || 'Failed to delete document');
            } finally {
                if (button) {
                    button.disabled = false;
                    if (icon) icon.classList.remove('hidden');
                    if (spinner) spinner.classList.add('hidden');
                    if (text) text.textContent = 'Delete';
                }
            }
        }

        async function onDeleteDocClick(event) {
            const button = event.currentTarget;
            const documentId = event.currentTarget.getAttribute('data-id');
            const filename = event.currentTarget.getAttribute('data-filename');

            await deleteDocument(documentId, filename, button);
        }

        if (uploadOpenBtn) uploadOpenBtn.addEventListener('click', () => openModal(uploadModal));
        uploadCloseEls.forEach((element) => element.addEventListener('click', () => closeModal(uploadModal)));

        if (editDocumentSubmit) editDocumentSubmit.addEventListener('click', saveDocumentContent);
        editDocumentCloseEls.forEach((element) => element.addEventListener('click', () => closeModal(
            editDocumentModal)));

        if (mobileActionsToggle) mobileActionsToggle.addEventListener('click', openDrawer);
        if (mobileDrawerClose) mobileDrawerClose.addEventListener('click', closeDrawer);
        if (mobileDrawerOverlay) mobileDrawerOverlay.addEventListener('click', closeDrawer);
        if (mobileUploadFileBtn) mobileUploadFileBtn.addEventListener('click', () => {
            closeDrawer();
            openModal(uploadModal);
        });

        document.querySelectorAll('[data-close="view-doc"]').forEach((element) => {
            element.addEventListener('click', () => closeModal(viewDocumentModal));
        });
        if (viewDocEditBtn) viewDocEditBtn.addEventListener('click', onViewDocEditClick);

        if (uploadSubmit) {
            uploadSubmit.addEventListener('click', async () => {
                const file = faqFileInput?.files?.[0];
                const errorEl = $('#upload_file_error');

                if (!file) {
                    if (errorEl) {
                        errorEl.textContent = 'Please select a file';
                        errorEl.classList.remove('hidden');
                    }
                    return;
                }

                if ((!['text/plain'].includes(file.type)) && !file.name.match(/\.txt$/i)) {
                    if (errorEl) {
                        errorEl.textContent = 'Only .txt files are allowed';
                        errorEl.classList.remove('hidden');
                    }
                    return;
                }

                if (errorEl) errorEl.classList.add('hidden');

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
                    const res = await fetch('{{ route('staff.document_management.upload') }}', {
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
                        throw new Error(result.error || result.message || 'File upload failed');
                    }

                    showToast('success', 'File saved successfully');
                    closeModal(uploadModal);
                    if (uploadForm) uploadForm.reset();
                    await fetchAndReloadTable();
                } catch (error) {
                    showToast('error', error.message || 'Failed to upload file');
                } finally {
                    uploadSubmit.innerHTML = originalHTML;
                    uploadSubmit.disabled = false;
                }
            });
        }

        fetchAndReloadTable();
    })();
</script>
