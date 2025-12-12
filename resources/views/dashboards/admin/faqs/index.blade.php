@extends('layouts.admin')

@section('title', 'Document Management')

@section('admin-content')
    <div class="sm:px-2">
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
            <div>
                <h1 class="text-xl sm:text-2xl font-semibold text-slate-900">FAQ Management</h1>
            </div>
            @if (!empty($isDeletedView))
                <div class="flex sm:hidden items-center gap-2">
                    <a href="{{ route('admin.faqs.index') }}"
                        class="p-2 rounded-lg bg-white border border-gray-200 text-slate-700 hover:bg-gray-50"
                        aria-label="Back to FAQ Management (mobile)">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-slate-700" viewBox="0 0 24 24" fill="currentColor">
                            <path d="M15 6l-6 6 6 6" />
                        </svg>
                    </a>
                </div>
            @endif

            <!-- Desktop actions -->
            @if (!empty($isDeletedView))
                <div class="hidden sm:flex items-center gap-2">
                    <a href="{{ route('admin.faqs.index') }}"
                        class="inline-flex items-center gap-2 rounded-lg border border-gray-200 bg-white hover:bg-gray-50 text-sm font-medium px-3 py-2">
                        ← Back to FAQ Management
                    </a>
                </div>
            @else
                <div class="hidden sm:flex items-center gap-2">
                    <!-- Upload File Button -->
                    <button id="uploadFileBtn" type="button"
                        class="inline-flex items-center gap-2 rounded-lg bg-purple-600 hover:bg-purple-700 text-white text-sm font-medium px-3 py-2"
                        aria-label="Upload File">
                        <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12" />
                        </svg>
                        <span class="hidden lg:inline">Upload Files</span>
                        <span class="lg:hidden">Upload</span>
                    </button>
                    <!-- Refresh Documents Button -->
                    <button id="refreshDocsBtn" type="button"
                        class="inline-flex items-center gap-2 rounded-lg bg-blue-600 hover:bg-blue-700 text-white text-sm font-medium px-3 py-2"
                        aria-label="Refresh Documents">
                        <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
                        </svg>
                        <span>Refresh</span>
                    </button>
                </div>
            @endif

        </div>

        <div class="mt-4 relative">
            <p class="text-sm text-gray-600 mb-4">Documents stored in the Rasa server for FAQ training.</p>
            <!-- Mobile hamburger menu aligned with text -->
            <div class="sm:hidden absolute top-0 right-0">
                <button id="mobileActionsToggle" type="button"
                        class="p-2 rounded-lg bg-gray-100 hover:bg-gray-200 text-slate-700 ml-4" aria-label="Open actions drawer">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 24 24" fill="currentColor">
                        <path d="M3 6h18v2H3V6zm0 5h18v2H3v-2zm0 5h18v2H3v-2z" />
                    </svg>
                </button>
            </div>
        </div>

        <!-- Training Required Alert -->
        <div id="trainingAlert" class="hidden bg-orange-50 border-l-4 border-orange-400 p-4 mb-4">
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

        <div class="mt-4 bg-white rounded-xl border border-gray-200 overflow-hidden">
            <div class="p-6">
                <div id="docsList" class="space-y-4">
                    <div class="text-center text-sm text-gray-500">Loading docs...</div>
                </div>
            </div>
        </div>
    </div>

    <!-- Mobile Bottom Drawer -->
    <div id="mobileDrawer" class="fixed inset-x-0 bottom-0 z-40 transform translate-y-full transition-transform duration-300 ease-in-out sm:hidden">
        <div class="bg-white border-t border-gray-200 shadow-lg">
            <div class="p-4">
                <div class="flex items-center justify-between mb-3">
                    <h3 class="text-sm font-medium text-gray-900">Actions</h3>
                    <button id="mobileDrawerClose" type="button" class="text-gray-400 hover:text-gray-600">
                        <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </button>
                </div>
                <div class="space-y-3">
                    <button id="mobileRefreshDocsBtn" type="button"
                            class="w-full inline-flex items-center justify-center gap-2 rounded-lg bg-blue-600 hover:bg-blue-700 text-white text-sm font-medium px-4 py-2">
                        <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
                        </svg>
                        Refresh Documents
                    </button>
                    <button id="mobileUploadFileBtn" type="button"
                            class="w-full inline-flex items-center justify-center gap-2 rounded-lg bg-purple-600 hover:bg-purple-700 text-white text-sm font-medium px-4 py-2">
                        <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12" />
                        </svg>
                        Upload Files
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Mobile Drawer Overlay -->
    <div id="mobileDrawerOverlay" class="fixed inset-0 bg-black/50 z-30 hidden sm:hidden"></div>

    <!-- Create FAQ Modal -->
    <div id="createFaqModal" class="fixed inset-0 z-50 hidden">
        <div class="absolute inset-0 bg-black/40" data-close="create"></div>
        <div class="absolute inset-0 flex items-center justify-center p-4">
            <div
                class="w-full max-w-full sm:max-w-2xl bg-white rounded-none sm:rounded-lg shadow border border-gray-200 overflow-auto max-h-[90vh]">
                <div class="h-12 flex items-center justify-between px-4 border-b">
                    <div class="text-sm font-semibold text-slate-800">Add FAQ</div>
                    <button type="button" class="text-slate-500 hover:text-slate-700" data-close="create"
                        aria-label="Close">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 24 24" fill="currentColor">
                            <path d="M6 18 18 6M6 6l12 12" />
                        </svg>
                    </button>
                </div>
                <form id="createFaqForm" class="p-4 space-y-4">
                    <div>
                        <label class="block text-sm font-medium text-slate-700">Intent</label>
                        <input type="text" name="intent" id="create_intent" required
                            class="mt-1 w-full rounded-md border border-gray-300 px-3 py-2 text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500" />
                        <p id="create_intent_error" class="mt-1 text-xs text-red-600 hidden"></p>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-slate-700">Description</label>
                        <div class="flex flex-col sm:flex-row gap-2">
                            <textarea name="description" id="create_description" rows="3" required
                                class="mt-1 w-full rounded-md border border-gray-300 px-3 py-2 text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500"></textarea>
                            <button id="createTemplateBtn" type="button"
                                class="mt-2 sm:mt-1 w-full sm:w-auto rounded-md border border-gray-300 bg-gray-50 hover:bg-gray-100 text-sm px-3 py-2 text-slate-700 sm:self-start">
                                Use template
                            </button>
                        </div>
                        <p id="create_description_error" class="mt-1 text-xs text-red-600 hidden"></p>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-slate-700">Response</label>
                        <textarea name="response" id="create_response" rows="6" required
                            class="mt-1 w-full rounded-md border border-gray-300 px-3 py-2 text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500"></textarea>
                        <p id="create_response_error" class="mt-1 text-xs text-red-600 hidden"></p>
                    </div>
                    <div class="pt-2 flex items-center justify-end gap-3">
                        <button type="button"
                            class="rounded-md border border-gray-300 bg-white hover:bg-gray-50 text-sm px-4 py-2"
                            data-close="create">Cancel</button>
                        <button id="createFaqSubmit" type="button"
                            class="rounded-md bg-blue-600 hover:bg-blue-700 text-white text-sm font-medium px-4 py-2">Create
                            FAQ</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Upload File Modal -->
    <div id="uploadFileModal" class="fixed inset-0 z-50 hidden">
        <div class="absolute inset-0 bg-black/40" data-close="upload"></div>
        <div class="absolute inset-0 flex items-center justify-center p-4">
            <div
                class="w-full max-w-full sm:max-w-lg bg-white rounded-none sm:rounded-lg shadow border border-gray-200 overflow-auto max-h-[90vh]">
                <div class="h-12 flex items-center justify-between px-4 border-b">
                    <div class="text-sm font-semibold text-slate-800">Upload FAQ File</div>
                    <button type="button" class="text-slate-500 hover:text-slate-700" data-close="upload"
                        aria-label="Close">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 24 24" fill="currentColor">
                            <path d="M6 18 18 6M6 6l12 12" />
                        </svg>
                    </button>
                </div>
                <form id="uploadFileForm" class="p-4 space-y-4">
                    <div>
                        <label class="block text-sm font-medium text-slate-700">Select Text File</label>
                        <input type="file" id="faqFile" accept=".txt" required
                            class="mt-1 w-full rounded-md border border-gray-300 px-3 py-2 text-sm focus:ring-2 focus:ring-purple-500 focus:border-purple-500" />
                        <p class="mt-1 text-xs text-slate-500">Only .txt files are allowed</p>
                        <p id="upload_file_error" class="mt-1 text-xs text-red-600 hidden"></p>
                    </div>
                    <div class="pt-2 flex items-center justify-end gap-3">
                        <button type="button"
                            class="rounded-md border border-gray-300 bg-white hover:bg-gray-50 text-sm px-4 py-2"
                            data-close="upload">Cancel</button>
                        <button id="uploadFileSubmit" type="button"
                            class="rounded-md bg-purple-600 hover:bg-purple-700 text-white text-sm font-medium px-4 py-2">Upload & Sync</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- View/Edit FAQ Modal -->
    <div id="viewFaqModal" class="fixed inset-0 z-50 hidden"
        data-update-template="{{ route('admin.faqs.update', ['faq' => '__ID__']) }}"
        data-show-url-template="{{ route('admin.faqs.show', ['faq' => '__ID__']) }}"
        data-destroy-template="{{ route('admin.faqs.destroy', ['faq' => '__ID__']) }}">
        <div class="absolute inset-0 bg-black/40" data-close="view"></div>
        <div class="absolute inset-0 flex items-center justify-center p-4">
            <div
                class="relative w-full max-w-full sm:max-w-2xl bg-white rounded-none sm:rounded-lg shadow border border-gray-200 overflow-auto max-h-[90vh]">

                <!-- Header -->
                <div class="h-12 flex items-center px-4 border-b">
                    <div class="text-sm font-semibold text-slate-800">FAQ Details</div>
                </div>

                <!-- Close button top-right -->
                <button type="button" class="absolute top-3 right-3 text-slate-500 hover:text-slate-700"
                    data-close="view" aria-label="Close">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24"
                        stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>

                <button id="moreActionsBtn" type="button"
                    class="absolute top-3 right-12 text-slate-500 hover:text-slate-700 hidden" aria-label="More actions">
                    <span class="text-xl font-bold">⋯</span>
                </button>

                <!-- More actions menu (hidden by default) -->
                <div id="moreActionsMenu"
                    class="absolute top-10 right-3 hidden bg-white border border-gray-200 rounded shadow-md z-50 w-44">
                    <div class="py-1">
                        <button id="more_revisions_btn" type="button"
                            class="w-full text-left px-3 py-2 text-sm text-gray-700 hover:bg-gray-50 hidden">View
                            Revisions</button>
                        <button id="more_restore_btn" type="button"
                            class="w-full text-left px-3 py-2 text-sm text-gray-700 hover:bg-gray-50 hidden">Restore
                            FAQ</button>
                    </div>
                </div>

                <!-- Body -->
                <form id="viewFaqForm" class="p-4 space-y-4">
                    @csrf
                    @method('PUT')
                    <input type="hidden" id="view_faq_id" name="faq_id" value="">

                    <!-- Topic -->
                    <div>
                        <label class="block text-sm font-medium text-slate-700">Intent</label>
                        <input type="text" name="intent" id="view_intent" required
                            class="mt-1 w-full rounded-md border border-gray-300 px-3 py-2 text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500" />
                        <p id="view_intent_error" class="mt-1 text-xs text-red-600 hidden"></p>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-slate-700">Description</label>
                        <div class="flex flex-col sm:flex-row gap-2">
                            <textarea name="description" id="view_description" rows="3" required
                                class="mt-1 w-full rounded-md border border-gray-300 px-3 py-2 text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500"></textarea>
                            <button id="viewTemplateBtn" type="button"
                                class="hidden mt-2 sm:mt-1 w-full sm:w-auto rounded-md border border-gray-300 bg-gray-50 hover:bg-gray-100 text-sm px-3 py-2 text-slate-700 sm:self-start">
                                Use template
                            </button>
                        </div>
                        <p id="view_description_error" class="mt-1 text-xs text-red-600 hidden"></p>
                    </div>
    
                    <!-- Previous revision collapsible (populated dynamically) -->
                    <div id="previousRevisionWrapper" class="mt-3 hidden">
                        <button type="button" id="togglePrevRevisionBtn"
                            class="text-sm text-blue-600 hover:underline">Show previous response</button>
                        <div id="prevRevisionBlock"
                            class="mt-2 hidden bg-gray-50 border border-gray-200 rounded p-3 text-sm whitespace-pre-line max-h-64 overflow-auto sm:max-h-[50vh]">
                            <div id="prevRevisionMeta" class="text-xs text-slate-500 mb-2"></div>
                            <div id="prevRevisionContent" class="text-slate-800"></div>
                            <div class="mt-3 flex justify-end">
                                <button id="restorePrevBtn" type="button"
                                    class="rounded-md bg-yellow-500 hover:bg-yellow-600 text-white text-sm px-3 py-1">Restore
                                    Previous</button>
                            </div>
                        </div>
                    </div>
    
                    <!-- Response -->
                    <div>
                        <label class="block text-sm font-medium text-slate-700">Response</label>
                        <textarea name="response" id="view_response" rows="6" required
                            class="mt-1 w-full rounded-md border border-gray-300 px-3 py-2 text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500"></textarea>
                        <p id="view_response_error" class="mt-1 text-xs text-red-600 hidden"></p>
                    </div>

                    <!-- Footer -->
                    <div class="pt-2 flex items-center justify-between">
                        <div class="text-xs text-slate-500" id="view_timestamps"></div>
                        <div class="flex items-center gap-3">
                            <button id="deleteFaqBtn" type="button"
                                class="rounded-md border border-red-200 bg-white text-sm px-3 py-2 text-red-700 hover:bg-red-50">Delete</button>
                            <button id="updateFaqSubmit" type="button"
                                class="rounded-md bg-blue-600 hover:bg-blue-700 text-white text-sm font-medium px-4 py-2">Save
                                Changes</button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Edit Document Modal -->
    <div id="editDocumentModal" class="fixed inset-0 z-50 hidden">
        <div class="absolute inset-0 bg-black/40" data-close="edit-doc"></div>
        <div class="absolute inset-0 flex items-center justify-center p-4">
            <div class="w-full max-w-full sm:max-w-4xl bg-white rounded-none sm:rounded-lg shadow border border-gray-200 overflow-auto max-h-[90vh]">
                <div class="h-12 flex items-center justify-between px-4 border-b">
                    <div class="text-sm font-semibold text-slate-800">Edit Document</div>
                    <button type="button" class="text-slate-500 hover:text-slate-700" data-close="edit-doc"
                            aria-label="Close">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 24 24" fill="currentColor">
                            <path d="M6 18 18 6M6 6l12 12" />
                        </svg>
                    </button>
                </div>
                <form id="editDocumentForm" class="p-4 space-y-4">
                    <div>
                        <label class="block text-sm font-medium text-slate-700">File Name</label>
                        <input type="text" id="edit_doc_filename" readonly
                               class="mt-1 w-full rounded-md border border-gray-300 px-3 py-2 text-sm bg-gray-50 text-gray-600" />
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-slate-700">Content</label>
                        <textarea id="edit_doc_content" rows="20" required
                                  class="mt-1 w-full rounded-md border border-gray-300 px-3 py-2 text-sm font-mono focus:ring-2 focus:ring-amber-500 focus:border-amber-500"
                                  placeholder="Enter document content here..."></textarea>
                        <p class="mt-1 text-xs text-slate-500">Supports plain text and markdown formatting</p>
                        <p id="edit_doc_error" class="mt-1 text-xs text-red-600 hidden"></p>
                    </div>
                    <div class="pt-2 flex items-center justify-end gap-3">
                        <button type="button"
                                class="rounded-md border border-gray-300 bg-white hover:bg-gray-50 text-sm px-4 py-2"
                                data-close="edit-doc">Cancel</button>
                        <button id="editDocumentSubmit" type="button"
                                class="rounded-md bg-amber-600 hover:bg-amber-700 text-white text-sm font-medium px-4 py-2">
                            <svg class="animate-spin h-4 w-4 mr-2 hidden" id="editDocSpinner" fill="none" viewBox="0 0 24 24">
                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                            </svg>
                            <span id="editDocBtnText">Save Changes</span>
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Hidden state with URLs -->
    <div id="admin-faqs-state" class="hidden" data-list-url="{{ $listUrl ?? route('admin.faqs.list') }}"
        data-store-url="{{ route('admin.faqs.store') }}"
        data-show-url-template="{{ route('admin.faqs.show', ['faq' => '__ID__']) }}"
        data-update-url-template="{{ route('admin.faqs.update', ['faq' => '__ID__']) }}"
        data-destroy-url-template="{{ route('admin.faqs.destroy', ['faq' => '__ID__']) }}"
        data-revisions-url-template="{{ route('admin.faqs.revisions', ['faq' => '__ID__']) }}"
        data-restore-url-template="{{ route('admin.faqs.restore', ['faq' => '__ID__']) }}"
        data-enable-url-template="{{ route('admin.faqs.enable', ['faq' => '__ID__']) }}"
        data-disable-url-template="{{ route('admin.faqs.disable', ['faq' => '__ID__']) }}"></div>

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

            const editDocumentModal = $('#editDocumentModal');
            const editDocumentCloseEls = $$('[data-close="edit-doc"]', editDocumentModal || document);
            const editDocumentForm = $('#editDocumentForm');
            const editDocumentSubmit = $('#editDocumentSubmit');

            const viewModal = $('#viewFaqModal');
            const viewCloseEls = $$('[data-close="view"]', viewModal || document);
            const viewForm = $('#viewFaqForm');
            const viewFaqId = $('#view_faq_id');
            const viewTopic = $('#view_intent');
            const viewResponse = $('#view_response');
            const viewTimestamps = $('#view_timestamps');
            const updateSubmit = $('#updateFaqSubmit');
            const deleteBtn = $('#deleteFaqBtn');

            // More actions elements (modal "..." menu)
            const moreBtn = $('#moreActionsBtn');
            const moreMenu = $('#moreActionsMenu');
            const moreRestoreBtn = $('#more_restore_btn');
            const moreRevisionsBtn = $('#more_revisions_btn');

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

            // More revisions button handler
            if (moreRevisionsBtn) {
                moreRevisionsBtn.addEventListener('click', () => {
                    const id = viewFaqId.value;
                    const url = stateEl.getAttribute('data-revisions-url-template').replace('__ID__', id);
                    window.location.href = url;
                });
            }

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
                        try { localStorage.setItem('ts_tickets_changed', String(Date.now())); } catch (e) {}
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
                    console.log('[DEBUG] Set loading message');

                    // Debug: Log the URL and configuration being used
                    const rasaUrl = '{{ config("services.faq_list_docs.url") }}';
                    const secret = '{{ config("services.faq_list_docs.secret") }}';
                    console.log('[DEBUG] fetchDocs - URL:', rasaUrl);
                    console.log('[DEBUG] fetchDocs - Secret length:', secret.length);
                    console.log('[DEBUG] fetchDocs - Secret (first 5 chars):', secret.substring(0, 5) + '...');

                    const res = await fetch(rasaUrl, {
                        headers: {
                            'X-FAQ-UPDATER-TOKEN': secret,
                            'X-Requested-With': 'XMLHttpRequest'
                        }
                    });

                    console.log('[DEBUG] fetchDocs - Response status:', res.status);
                    console.log('[DEBUG] fetchDocs - Response headers:', [...res.headers.entries()]);

                    if (!res.ok) {
                        const errorText = await res.text();
                        console.error('[DEBUG] fetchDocs - Error response:', errorText);
                        throw new Error(`Failed to load docs: ${res.status} - ${errorText}`);
                    }

                    const json = await res.json();
                    console.log('[DEBUG] fetchDocs - Response JSON:', json);

                    if (!json.ok) {
                        console.error('[DEBUG] fetchDocs - API error:', json.error);
                        throw new Error(json.error || 'Failed to load docs');
                    }

                    renderDocsList(json.files || []);
                } catch (err) {
                    console.error('[DEBUG] fetchDocs - Exception:', err);
                    docsListEl.innerHTML = `<div class="text-center text-sm text-red-600">Error loading FAQs Documents: Rasa server is offline.</div>`;
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

                // Open download link in new tab with authentication token
                const rasaBaseUrl = '{{ config("services.faq_list_docs.url") }}'.replace('/list-docs', '');
                const secret = '{{ config("services.faq_list_docs.secret") }}';
                const downloadUrl = `${rasaBaseUrl}/download/${encodeURIComponent(filename)}?token=${encodeURIComponent(secret)}`;
                window.open(downloadUrl, '_blank');
            }

            function onEditDocClick(e) {
                const filename = e.currentTarget.getAttribute('data-filename');
                if (!filename) return;

                console.log('[DEBUG] onEditDocClick called for:', filename);

                // Set filename in modal
                $('#edit_doc_filename').value = filename;

                // Load document content
                loadDocumentContent(filename);
            }

            async function loadDocumentContent(filename) {
                console.log('[DEBUG] loadDocumentContent called for:', filename);

                const contentTextarea = $('#edit_doc_content');
                const errorEl = $('#edit_doc_error');

                // Clear previous content and errors
                contentTextarea.value = '';
                errorEl.classList.add('hidden');
                errorEl.textContent = '';

                try {
                    // Show loading in textarea
                    contentTextarea.value = 'Loading document content...';
                    contentTextarea.disabled = true;

                    // Fetch document content
                    const rasaBaseUrl = '{{ config("services.faq_list_docs.url") }}'.replace('/list-docs', '');
                    const secret = '{{ config("services.faq_list_docs.secret") }}';

                    console.log('[DEBUG] loadDocumentContent - URL:', `${rasaBaseUrl}/download/${encodeURIComponent(filename)}?token=${encodeURIComponent(secret)}`);

                    const res = await fetch(`${rasaBaseUrl}/download/${encodeURIComponent(filename)}?token=${encodeURIComponent(secret)}`, {
                        headers: {
                            'X-Requested-With': 'XMLHttpRequest'
                        }
                    });

                    if (!res.ok) {
                        throw new Error(`Failed to load document: ${res.status}`);
                    }

                    const content = await res.text();
                    console.log('[DEBUG] Document content loaded, length:', content.length);

                    // Populate textarea
                    contentTextarea.value = content;
                    contentTextarea.disabled = false;

                    // Open modal
                    openModal(editDocumentModal);

                } catch (err) {
                    console.error('[DEBUG] Error loading document content:', err);
                    errorEl.textContent = `Error loading document: ${err.message}`;
                    errorEl.classList.remove('hidden');
                    contentTextarea.disabled = false;
                    contentTextarea.value = '';
                }
            }

            async function logDocumentChange(filename, action) {
                try {
                    const csrf = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
                    const res = await fetch('{{ route("admin.document-changes.log") }}', {
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
                        console.error('[DEBUG] Failed to log document change:', await res.text());
                    } else {
                        console.log('[DEBUG] Document change logged successfully');
                    }
                } catch (err) {
                    console.error('[DEBUG] Error logging document change:', err);
                }
            }

            async function checkTrainingStatus() {
                try {
                    const res = await fetch('{{ route("admin.document-changes.training-status") }}', {
                        headers: {
                            'X-Requested-With': 'XMLHttpRequest'
                        }
                    });

                    if (res.ok) {
                        const data = await res.json();
                        const alertEl = $('#trainingAlert');

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

                // Clear previous errors
                errorEl.classList.add('hidden');
                errorEl.textContent = '';

                // Show loading state
                submitBtn.disabled = true;
                spinner.classList.remove('hidden');
                btnText.textContent = 'Saving...';

                try {
                    const rasaBaseUrl = '{{ config("services.faq_list_docs.url") }}'.replace('/list-docs', '');
                    const secret = '{{ config("services.faq_list_docs.secret") }}';

                    console.log('[DEBUG] saveDocumentContent - URL:', `${rasaBaseUrl}/update-document`);
                    console.log('[DEBUG] saveDocumentContent - Secret length:', secret.length);

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
                            file_type: filename.toLowerCase().endsWith('.md') ? 'text/markdown' : 'text/plain'
                        })
                    });

                    const json = await res.json();

                    if (!res.ok || !json.ok) {
                        throw new Error(json.error || 'Failed to save document');
                    }

                    console.log('[DEBUG] Document saved successfully:', json);

                    // Log the document change
                    await logDocumentChange(filename, 'updated');

                    // Show success message
                    showToast('success', `Document "${filename}" updated successfully`);

                    // Close modal
                    closeModal(editDocumentModal);

                    // Refresh document list to show updated timestamp
                    fetchDocs();

                    // Check if training alert should be shown
                    checkTrainingStatus();

                } catch (err) {
                    console.error('[DEBUG] Error saving document:', err);
                    errorEl.textContent = `Error saving document: ${err.message}`;
                    errorEl.classList.remove('hidden');
                } finally {
                    // Reset loading state
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

                const rasaBaseUrl = '{{ config("services.faq_list_docs.url") }}'.replace('/list-docs', '');
                const secret = '{{ config("services.faq_list_docs.secret") }}';
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

                // Attach view and edit handlers
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

                // simple pagination: prev, pages window, next
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
                    .replaceAll('&', '&amp;')
                    .replaceAll('<', '&lt;')
                    .replaceAll('>', '&gt;')
                    .replaceAll('"', '&quot;')
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

                // Show more actions button
                if (moreBtn) moreBtn.classList.remove('hidden');

                // Show/hide more actions menu items
                if (moreRevisionsBtn) {
                    if (faq.latest_revision) {
                        moreRevisionsBtn.classList.remove('hidden');
                    } else {
                        moreRevisionsBtn.classList.add('hidden');
                    }
                }
                if (moreRestoreBtn) {
                    // Hide restore button for active FAQs (only show in deleted view if needed)
                    moreRestoreBtn.classList.add('hidden');
                }

                // Handle previous revision display
                if (faq.latest_revision && prevWrapper) {
                    prevWrapper.classList.remove('hidden');
                    if (prevMeta) prevMeta.textContent = `${faq.latest_revision.action || 'update'} at ${faq.latest_revision.created_at || ''}`;
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

            // Mobile sync cache button
            const mobileSyncBtn = $('#mobileSyncFaqCacheBtn');
            const mobileSyncIcon = $('#mobileSyncIcon');
            const mobileSyncText = $('#mobileSyncText');

            if (mobileSyncBtn) {
                mobileSyncBtn.addEventListener('click', async () => {
                    if (mobileSyncBtn.disabled) return;

                    closeDrawer(); // Close the drawer first

                    // Show loading state
                    mobileSyncBtn.disabled = true;
                    mobileSyncIcon.classList.add('animate-spin');
                    mobileSyncText.textContent = 'Syncing...';

                    try {
                        // First, fetch all FAQs from the new all-json endpoint
                        const faqRes = await fetch('{{ route("admin.faqs.all-json") }}', {
                            headers: {
                                'X-Requested-With': 'XMLHttpRequest'
                            }
                        });

                        if (!faqRes.ok) {
                            throw new Error('Failed to fetch FAQs from server');
                        }

                        const faqData = await faqRes.json();
                        const faqs = faqData.faqs || [];

                        console.log('[FAQ Sync] Fetched FAQs from server:', faqs.length);

                        let result;

                        // Handle empty FAQ table - sync with empty FAQ array to clear Rasa FAQ file
                        if (faqs.length === 0) {
                            console.log('[FAQ Sync] No FAQs found - syncing with empty array to clear Rasa FAQ file');
                        }

                        // Send all FAQs (or empty array) to Rasa sync endpoint
                        const rasaRes = await fetch('{{ config("services.faq_sync.url") }}', {
                            method: 'POST',
                            headers: {
                                'X-FAQ-UPDATER-TOKEN': '{{ config("services.faq_sync.secret") }}',
                                'Content-Type': 'application/json',
                            },
                            body: JSON.stringify({
                                faqs: faqs
                            })
                        });

                        // Check if response is JSON before parsing
                        const contentType = rasaRes.headers ? rasaRes.headers.get('content-type') : null;
                        if (contentType && contentType.includes('application/json')) {
                            result = await rasaRes.json();
                        } else {
                            // Handle HTML error response
                            const errorText = await rasaRes.text();
                            console.error('[FAQ Sync] Sync failed - received HTML instead of JSON:', errorText);
                            throw new Error('Rasa server returned an error instead of JSON response');
                        }

                        if (faqs.length === 0) {
                            console.log('[FAQ Sync] Successfully cleared FAQ file on Rasa server');
                        }

                        console.log('[FAQ Sync] Rasa response:', result);

                        // Check if the operation was successful
                        if (!result.ok) {
                            console.error('[FAQ Sync] Sync failed:', result.error || 'Unknown error');
                            throw new Error(result.error || 'Rasa sync failed');
                        }

                        console.log('[FAQ Sync] Success! Synced FAQs:', result.summary || result);

                        // Clear pending changes on successful sync
                        setPendingChanges(false);
                        // Set synced pending training flag
                        try {
                            localStorage.setItem('faq_synced_pending_training', 'true');
                        } catch (e) {
                            // localStorage not available
                        }
                        
                        // Show appropriate success message
                        if (faqs.length === 0) {
                            showToast('success', 'FAQ cache cleared successfully - no FAQs to sync');
                        } else {
                            showToast('success', `FAQ cache synced successfully (${result.summary?.successful || result.count || faqs.length} FAQs)`);
                        }

                    } catch (err) {
                        console.error('Sync error:', err);
                        showToast('error', err.message || 'Failed to sync FAQ cache');
                    } finally {
                        // Reset button state
                        mobileSyncIcon.classList.remove('animate-spin');
                        mobileSyncText.textContent = 'Sync to Rasa';
                        updateSyncButtonState();
                    }
                });
            }

            // Mobile upload file button (legacy - can be removed if not used elsewhere)
            const mobileUploadBtn = $('#mobileUploadFileBtn');

            if (mobileUploadBtn) {
                mobileUploadBtn.addEventListener('click', () => {
                    closeDrawer(); // Close the drawer first
                    openModal(uploadModal);
                });
            }

            // Mobile action buttons
            const mobileActionAdd = $('#mobileActionAdd');
            const mobileActionTrash = $('#mobileActionTrash');

            if (mobileActionAdd) {
                mobileActionAdd.addEventListener('click', () => {
                    closeDrawer();
                    openModal(createModal);
                });
            }
            if (mobileActionTrash) {
                mobileActionTrash.addEventListener('click', () => {
                    const url = mobileActionTrash.dataset.deletedUrl;
                    if (url) window.location.href = url;
                });
            }

            // Create modal handlers
            if (createOpenBtn) {
                createOpenBtn.addEventListener('click', () => openModal(createModal));
            }
            createCloseEls.forEach(el => el.addEventListener('click', () => closeModal(createModal)));

            // Upload modal handlers
            if (uploadOpenBtn) {
                uploadOpenBtn.addEventListener('click', () => openModal(uploadModal));
            }
            uploadCloseEls.forEach(el => el.addEventListener('click', () => closeModal(uploadModal)));

            // Edit document modal handlers
            editDocumentCloseEls.forEach(el => el.addEventListener('click', () => closeModal(editDocumentModal)));
            if (editDocumentSubmit) {
                editDocumentSubmit.addEventListener('click', saveDocumentContent);
            }

            // Template button handlers
            const createTemplateBtn = $('#createTemplateBtn');
            const createDescription = $('#create_description');
            const createIntent = $('#create_intent');
            if (createTemplateBtn && createDescription && createIntent) {
                // Hide button if description is not empty
                const toggleCreateTemplateBtn = () => {
                    const isEmpty = createDescription.value.trim() === '';
                    createTemplateBtn.classList.toggle('hidden', !isEmpty);
                };
                createDescription.addEventListener('input', toggleCreateTemplateBtn);
                // Initial check
                toggleCreateTemplateBtn();

                createTemplateBtn.addEventListener('click', () => {
                    const intent = createIntent.value.trim();
                    if (intent) {
                        createDescription.value = `This handles question about ${intent}.`;
                    } else {
                        createDescription.value = 'This handles question about ';
                    }
                    toggleCreateTemplateBtn();
                });
            }

            const viewTemplateBtn = $('#viewTemplateBtn');
            const viewDescription = $('#view_description');
            const viewIntent = $('#view_intent');
            if (viewTemplateBtn && viewDescription && viewIntent) {
                // Hide button if description is not empty
                const toggleViewTemplateBtn = () => {
                    const isEmpty = viewDescription.value.trim() === '';
                    viewTemplateBtn.classList.toggle('hidden', !isEmpty);
                };
                viewDescription.addEventListener('input', toggleViewTemplateBtn);
                // Initial check
                toggleViewTemplateBtn();

                viewTemplateBtn.addEventListener('click', () => {
                    const intent = viewIntent.value.trim();
                    if (intent) {
                        viewDescription.value = `This FAQ provides information about ${intent}.`;
                    } else {
                        viewDescription.value = 'This FAQ provides information about [topic].';
                    }
                    toggleViewTemplateBtn();
                });
            }

            if (createSubmit) {
                createSubmit.addEventListener('click', async () => {
                    const intent = createIntent.value.trim();
                    const description = createDescription.value.trim();
                    const responseEl = $('#create_response');
                    const response = responseEl ? responseEl.value.trim() : '';

                    if (!intent || !description || !response) {
                        showToast('error', 'All fields are required');
                        return;
                    }

                    // Store original content and show loading spinner
                    const originalHTML = createSubmit.innerHTML;
                    createSubmit.disabled = true;
                    createSubmit.innerHTML = `
                        <svg class="animate-spin h-4 w-4 mx-auto" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                        </svg>
                        <span class="ml-2">Creating...</span>
                    `;

                    try {
                        const res = await fetch(STORE_URL, {
                            method: 'POST',
                            headers: {
                                'X-CSRF-TOKEN': csrf,
                                'Content-Type': 'application/json',
                                'X-Requested-With': 'XMLHttpRequest'
                            },
                            body: JSON.stringify({ intent, description, response })
                        });
                        const json = await res.json();
                        if (!res.ok) {
                            throw new Error(json.message || 'Failed to create FAQ');
                        }
                        setPendingChanges(true);
                        showToast('success', 'FAQ created successfully');
                        closeModal(createModal);
                        createForm.reset();
                        fetchList(currentPage);
                    } catch (err) {
                        showToast('error', err.message || 'Error creating FAQ');
                        console.error(err);
                    } finally {
                        // Restore original content
                        createSubmit.innerHTML = originalHTML;
                        createSubmit.disabled = false;
                    }
                });
            }

            // Upload file submit handler
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

                    // Check for duplicate filename
                    const existingFiles = Array.from(document.querySelectorAll('.editDocBtn')).map(btn => btn.getAttribute('data-filename'));
                    if (existingFiles.includes(file.name)) {
                        $('#upload_file_error').textContent = `A file named "${file.name}" already exists. Please choose a different name or edit the existing file.`;
                        $('#upload_file_error').classList.remove('hidden');
                        return;
                    }

                    $('#upload_file_error').classList.add('hidden');

                    // Store original content and show loading spinner
                    const originalHTML = uploadSubmit.innerHTML;
                    uploadSubmit.disabled = true;
                    uploadSubmit.innerHTML = `
                        <svg class="animate-spin h-4 w-4 mx-auto" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                        </svg>
                        <span class="ml-2">Uploading...</span>
                    `;

                    try {
                        const fileContent = await file.text();
                        console.log('[File Upload] File content length:', fileContent.length);

                        // Send file content to Rasa upload endpoint
                        const baseUrl = '{{ config("services.faq_sync.url") }}'.replace('/sync-faqs', '');
                        const rasaRes = await fetch(`${baseUrl}/upload-file`, {
                            method: 'POST',
                            headers: {
                                'X-FAQ-UPDATER-TOKEN': '{{ config("services.faq_sync.secret") }}',
                                'Content-Type': 'application/json',
                            },
                            body: JSON.stringify({
                                file_content: fileContent,
                                file_name: file.name,
                                file_type: file.type
                            })
                        });

                        const result = await rasaRes.json();

                        console.log('[File Upload] Rasa response:', result);

                        if (!rasaRes.ok || !result.ok) {
                            console.error('[File Upload] Upload failed:', result.error || 'Unknown error');
                            throw new Error(result.error || 'File upload failed');
                        }

                        console.log('[File Upload] Success! File uploaded:', result);

                        showToast('success', `File uploaded and synced successfully`);

                        closeModal(uploadModal);
                        uploadForm.reset();

                    } catch (err) {
                        console.error('Upload error:', err);
                        showToast('error', err.message || 'Failed to upload file');
                    } finally {
                        // Restore original content
                        uploadSubmit.innerHTML = originalHTML;
                        uploadSubmit.disabled = false;
                    }
                });
            }

            // View modal handlers
            viewCloseEls.forEach(el => el.addEventListener('click', () => closeModal(viewModal)));

            if (updateSubmit) {
                updateSubmit.addEventListener('click', async () => {
                    const id = viewFaqId.value;
                    const intent = viewTopic.value.trim();
                    const description = $('#view_description').value.trim();
                    const response = viewResponse.value.trim();

                    if (!intent || !description || !response) {
                        showToast('error', 'All fields are required');
                        return;
                    }

                    const url = UPDATE_TEMPLATE.replace('__ID__', id);
                    try {
                        updateSubmit.disabled = true;
                        const res = await fetch(url, {
                            method: 'PUT',
                            headers: {
                                'X-CSRF-TOKEN': csrf,
                                'Content-Type': 'application/json',
                                'X-Requested-With': 'XMLHttpRequest'
                            },
                            body: JSON.stringify({ intent, description, response })
                        });
                        const json = await res.json();
                        if (!res.ok) {
                            throw new Error(json.message || 'Failed to update FAQ');
                        }
                        setPendingChanges(true);
                        showToast('success', 'FAQ updated successfully');
                        closeModal(viewModal);
                        fetchList(currentPage);
                    } catch (err) {
                        showToast('error', err.message || 'Error updating FAQ');
                        console.error(err);
                    } finally {
                        updateSubmit.disabled = false;
                    }
                });
            }

            if (deleteBtn) {
                deleteBtn.addEventListener('click', async () => {
                    const id = viewFaqId.value;
                    const confirmResult = await Swal.fire({
                        title: 'Delete FAQ?',
                        text: 'This will move the FAQ to trash',
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
                            throw new Error(json.message || 'Failed to delete FAQ');
                        }
                        setPendingChanges(true);
                        showToast('success', 'FAQ deleted successfully');
                        closeModal(viewModal);
                        fetchList(currentPage);
                    } catch (err) {
                        showToast('error', err.message || 'Error deleting FAQ');
                        console.error(err);
                    } finally {
                        deleteBtn.disabled = false;
                    }
                });
            }


            // FAQ Change Tracking
            const FAQ_CHANGES_KEY = 'faq_changes_pending';

            function hasPendingChanges() {
                try {
                    return localStorage.getItem(FAQ_CHANGES_KEY) === 'true';
                } catch (e) {
                    return false;
                }
            }

            function setPendingChanges(hasChanges) {
                try {
                    localStorage.setItem(FAQ_CHANGES_KEY, hasChanges ? 'true' : 'false');
                    updateSyncButtonState();
                } catch (e) {
                    // localStorage not available
                }
            }

            function updateSyncButtonState() {
                const hasChanges = hasPendingChanges();
                if (syncBtn) {
                    syncBtn.disabled = !hasChanges;
                    syncBtn.classList.toggle('opacity-50', !hasChanges);
                }
                if (mobileSyncBtn) {
                    mobileSyncBtn.disabled = !hasChanges;
                    mobileSyncBtn.classList.toggle('opacity-50', !hasChanges);
                }
            }

            // Sync FAQ Cache Button
            const syncBtn = $('#syncFaqCacheBtn');
            const syncIcon = $('#syncIcon');
            const syncText = $('#syncText');

            if (syncBtn) {
                syncBtn.addEventListener('click', async () => {
                    if (syncBtn.disabled) return;

                    // Show loading state
                    syncBtn.disabled = true;
                    syncIcon.classList.add('animate-spin');
                    syncText.textContent = 'Syncing...';

                    try {
                        // First, fetch all FAQs from the new all-json endpoint
                        const faqRes = await fetch('{{ route("admin.faqs.all-json") }}', {
                            headers: {
                                'X-Requested-With': 'XMLHttpRequest'
                            }
                        });

                        if (!faqRes.ok) {
                            throw new Error('Failed to fetch FAQs from server');
                        }

                        const faqData = await faqRes.json();
                        const faqs = faqData.faqs || [];

                        console.log('[FAQ Sync] Fetched FAQs from server:', faqs.length);

                        let result;

                        // Handle empty FAQ table - sync with empty FAQ array to clear Rasa FAQ file
                        if (faqs.length === 0) {
                            console.log('[FAQ Sync] No FAQs found - syncing with empty array to clear Rasa FAQ file');
                        }

                        // Send all FAQs (or empty array) to Rasa sync endpoint
                        const rasaRes = await fetch('{{ config("services.faq_sync.url") }}', {
                            method: 'POST',
                            headers: {
                                'X-FAQ-UPDATER-TOKEN': '{{ config("services.faq_sync.secret") }}',
                                'Content-Type': 'application/json',
                            },
                            body: JSON.stringify({
                                faqs: faqs
                            })
                        });

                        // Check if response is JSON before parsing
                        const contentType = rasaRes.headers ? rasaRes.headers.get('content-type') : null;
                        if (contentType && contentType.includes('application/json')) {
                            result = await rasaRes.json();
                        } else {
                            // Handle HTML error response
                            const errorText = await rasaRes.text();
                            console.error('[FAQ Sync] Sync failed - received HTML instead of JSON:', errorText);
                            throw new Error('Rasa server returned an error instead of JSON response');
                        }

                        if (faqs.length === 0) {
                            console.log('[FAQ Sync] Successfully cleared FAQ file on Rasa server');
                        }

                        console.log('[FAQ Sync] Rasa response:', result);

                        // Check if the operation was successful
                        if (!result.ok) {
                            console.error('[FAQ Sync] Sync failed:', result.error || 'Unknown error');
                            throw new Error(result.error || 'Rasa sync failed');
                        }

                        console.log('[FAQ Sync] Success! Synced FAQs:', result.summary || result);

                        // Clear pending changes on successful sync
                        setPendingChanges(false);
                        // Set synced pending training flag
                        try {
                            localStorage.setItem('faq_synced_pending_training', 'true');
                        } catch (e) {
                            // localStorage not available
                        }
                        
                        // Show appropriate success message
                        if (faqs.length === 0) {
                            showToast('success', 'FAQ cache cleared successfully - no FAQs to sync');
                        } else {
                            showToast('success', `FAQ cache synced successfully (${result.summary?.successful || result.count || faqs.length} FAQs)`);
                        }

                    } catch (err) {
                        console.error('Sync error:', err);
                        showToast('error', err.message || 'Failed to sync FAQ cache');
                    } finally {
                        // Reset button state
                        syncIcon.classList.remove('animate-spin');
                        syncText.textContent = 'Sync to Rasa';
                        updateSyncButtonState();
                    }
                });
            }

            // Function to check if sync is needed (optional - call after CRUD operations)
            function checkSyncNeeded() {
                // You could implement logic here to check if there are pending changes
                // For now, always enable the button
                if (syncBtn) {
                    syncBtn.disabled = false;
                }
            }

            // Initialize sync button state
            updateSyncButtonState();

            // Refresh button handler
            const refreshDocsBtn = $('#refreshDocsBtn');
            console.log('[DEBUG] Refresh button element:', refreshDocsBtn);
            if (refreshDocsBtn) {
                console.log('[DEBUG] Attaching event listener to refresh button');
                refreshDocsBtn.addEventListener('click', () => {
                    console.log('[DEBUG] Refresh button clicked - calling fetchDocs()');
                    fetchDocs();
                });
            } else {
                console.log('[DEBUG] Refresh button not found - might be on mobile view or deleted FAQs view');
            }

            async function trainRasa() {
                const btn = $('#trainRasaBtn');
                const spinner = $('#trainSpinner');
                const btnText = $('#trainBtnText');

                // Show loading state
                btn.disabled = true;
                spinner.classList.remove('hidden');
                btnText.textContent = 'Training...';

                try {
                    const csrf = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
                    const res = await fetch('{{ route("admin.document-changes.train-rasa") }}', {
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
                    showToast('success', 'Rasa training completed successfully!');

                    // Hide the training alert
                    $('#trainingAlert').classList.add('hidden');

                    // Refresh document list
                    fetchDocs();

                    // Check if we should show the API server alert
                    checkApiServerAlert();

                } catch (err) {
                    console.error('[DEBUG] Training error:', err);
                    showToast('error', `Training failed: ${err.message}`);
                } finally {
                    // Reset button state
                    btn.disabled = false;
                    spinner.classList.add('hidden');
                    btnText.textContent = 'Train Rasa';
                }
            }

            async function checkApiServerAlert() {
                try {
                    // Check if there's been a recent training (within last 60 minutes)
                    const res = await fetch('{{ route("admin.document-changes.check-recent-training") }}', {
                        headers: {
                            'X-Requested-With': 'XMLHttpRequest'
                        }
                    });

                    if (res.ok) {
                        const data = await res.json();
                        if (data.has_recent_training) {
                            // Show second alert for starting Rasa API server
                            setTimeout(() => {
                                Swal.fire({
                                    title: 'Start Rasa API Server',
                                    text: 'Training completed! Now start the Rasa API server to enable chatbot functionality?',
                                    icon: 'question',
                                    showCancelButton: true,
                                    confirmButtonText: 'Yes, start API server',
                                    cancelButtonText: 'Not now',
                                    confirmButtonColor: '#10B981'
                                }).then((result) => {
                                    if (result.isConfirmed) {
                                        startRasaApiServer();
                                    }
                                });
                            }, 1000); // Small delay to let the first toast disappear
                        }
                    }
                } catch (err) {
                    console.error('[DEBUG] Error checking recent training:', err);
                }
            }

            async function startRasaApiServer() {
                // Show loading state
                Swal.fire({
                    title: 'Starting Rasa API Server',
                    text: 'Please wait while the Rasa API server starts...',
                    allowOutsideClick: false,
                    allowEscapeKey: false,
                    showConfirmButton: false,
                    didOpen: () => {
                        Swal.showLoading();
                    }
                });

                try {
                    const csrf = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
                    const res = await fetch('{{ route("admin.document-changes.start-rasa-api") }}', {
                        method: 'POST',
                        headers: {
                            'X-CSRF-TOKEN': csrf,
                            'X-Requested-With': 'XMLHttpRequest'
                        }
                    });

                    const data = await res.json();

                    if (!res.ok || !data.success) {
                        throw new Error(data.message || 'Failed to start Rasa API server');
                    }

                    // Show success message
                    Swal.fire({
                        title: 'Success!',
                        text: 'Rasa API server started successfully on port 5005',
                        icon: 'success',
                        confirmButtonText: 'OK',
                        confirmButtonColor: '#10B981'
                    });

                } catch (err) {
                    console.error('[DEBUG] API server start error:', err);
                    Swal.fire({
                        title: 'Error',
                        text: `Failed to start Rasa API server: ${err.message}`,
                        icon: 'error',
                        confirmButtonText: 'OK'
                    });
                }
            }

            // Add event listener for train button
            const trainBtn = $('#trainRasaBtn');
            if (trainBtn) {
                trainBtn.addEventListener('click', trainRasa);
            }

            // Initialize: fetch docs on page load
            fetchDocs();
            checkTrainingStatus();

        })();

    </script>

@endsection
