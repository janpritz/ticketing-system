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
        const documentsTbody = document.getElementById('documentsTbody');
        const documentsPagination = document.getElementById('documentsPagination');
        
        // Get local documents from the page data
        const localDocuments = @json($localDocuments ?? []);
        
        // Modal elements
        const viewDocumentModal = document.getElementById('viewDocumentModal');
        const editDocumentModal = document.getElementById('editDocumentModal');
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
        
        // Routes
        const LIST_URL = '{{ route("admin.knowledgebase.list") }}';
        const UPDATE_DOC_URL = '{{ route("admin.knowledgebase.update-document") }}';
        const DELETE_DOC_URL = '{{ route("admin.knowledgebase.delete-document") }}';
        
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
            if (s === null || s === undefined) return '';
            return String(s)
                .replaceAll('&', '&')
                .replaceAll('<', '<')
                .replaceAll('>', '>')
                .replaceAll('"', '"')
                .replaceAll("'", '&#039;');
        }
        
        function openModal(modal) {
            if (modal) modal.classList.remove('hidden');
        }
        
        function closeModal(modal) {
            if (modal) modal.classList.add('hidden');
        }
        
        // Load document content from database
        async function loadDocumentContent(filename) {
            try {
                const res = await fetch(LIST_URL, {
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest'
                    }
                });
                
                if (!res.ok) {
                    throw new Error('Failed to load documents');
                }
                
                const data = await res.json();
                if (!data.ok) {
                    throw new Error(data.error || 'Failed to load documents');
                }
                
                const doc = data.files.find(f => f.name === filename);
                if (!doc) {
                    throw new Error('Document not found');
                }
                
                return doc;
            } catch (err) {
                console.error('Error loading document:', err);
                throw err;
            }
        }
        
        // View document handler
        async function onViewDocClick(e) {
            const filename = e.currentTarget.getAttribute('data-filename');
            if (!filename) return;
            
            try {
                viewDocFilename.value = filename;
                viewDocContent.textContent = 'Loading...';
                openModal(viewDocumentModal);
                
                const doc = await loadDocumentContent(filename);
                
                // Load actual content from the document
                const contentRes = await fetch(LIST_URL, {
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest'
                    }
                });
                
                const contentData = await contentRes.json();
                const docWithContent = contentData.files.find(f => f.name === filename);
                
                if (docWithContent && docWithContent.content) {
                    viewDocContent.textContent = docWithContent.content;
                } else {
                    viewDocContent.textContent = 'No content available';
                }
            } catch (err) {
                viewDocContent.textContent = 'Error loading document content';
                showToast('error', err.message || 'Failed to load document');
            }
        }
        
        // Edit document handler
        async function onEditDocClick(e) {
            const filename = e.currentTarget.getAttribute('data-filename');
            if (!filename) return;
            
            try {
                editDocFilename.value = filename;
                editDocContent.value = 'Loading...';
                editDocError.classList.add('hidden');
                openModal(editDocumentModal);
                
                const doc = await loadDocumentContent(filename);
                
                // Load actual content from the document
                const contentRes = await fetch(LIST_URL, {
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest'
                    }
                });
                
                const contentData = await contentRes.json();
                const docWithContent = contentData.files.find(f => f.name === filename);
                
                if (docWithContent && docWithContent.content) {
                    editDocContent.value = docWithContent.content;
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
        
        // Save document content
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
                // Update document in database
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
                
                // Refresh the document list
                renderDocumentsTable(localDocuments);
                
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
        
        // Delete document handler
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
                
                // Refresh the document list
                renderDocumentsTable(localDocuments);
                
            } catch (err) {
                showToast('error', err.message || 'Failed to delete document');
            } finally {
                deleteDocBtn.disabled = false;
            }
        }
        
        // View edit button handler
        function onViewDocEditClick() {
            const filename = viewDocFilename.value;
            if (!filename) return;
            
            closeModal(viewDocumentModal);
            
            // Trigger edit modal
            editDocFilename.value = filename;
            editDocContent.value = viewDocContent.textContent;
            openModal(editDocumentModal);
        }
        
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
                                <span class="hidden sm:inline">View</span>
                            </button>
                            <button class="editDocBtn inline-flex items-center gap-1 rounded-md border border-amber-200 bg-amber-50 px-3 py-1.5 text-sm font-medium text-amber-700 hover:bg-amber-100"
                                    data-filename="${escapeHtml(doc.name)}">
                                <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                                </svg>
                                <span class="hidden sm:inline">Edit</span>
                            </button>
                        </div>
                    </td>
                </tr>
            `).join('');
            
            // Add event listeners for view and edit buttons
            document.querySelectorAll('.viewDocBtn').forEach(btn => {
                btn.addEventListener('click', onViewDocClick);
            });
            
            document.querySelectorAll('.editDocBtn').forEach(btn => {
                btn.addEventListener('click', onEditDocClick);
            });
        }
        
        // Close modal handlers
        document.querySelectorAll('[data-close="view-doc"]').forEach(el => {
            el.addEventListener('click', () => closeModal(viewDocumentModal));
        });
        
        document.querySelectorAll('[data-close="edit-doc"]').forEach(el => {
            el.addEventListener('click', () => closeModal(editDocumentModal));
        });
        
        // Save document handler
        if (editDocumentSubmit) {
            editDocumentSubmit.addEventListener('click', saveDocumentContent);
        }
        
        // Delete document handler
        if (deleteDocBtn) {
            deleteDocBtn.addEventListener('click', onDeleteDocClick);
        }
        
        // View edit button handler
        if (viewDocEditBtn) {
            viewDocEditBtn.addEventListener('click', onViewDocEditClick);
        }
        
        // Initialize the table with local documents
        renderDocumentsTable(localDocuments);
    })();
</script>
