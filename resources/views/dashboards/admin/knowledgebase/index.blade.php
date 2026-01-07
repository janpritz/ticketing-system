@extends('layouts.admin')

@section('title', 'Document Management')

@section('admin-content')
    <div class="sm:px-2">
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
            <div>
                <h1 class="text-xl sm:text-2xl font-semibold text-slate-900">Document Management</h1>
            </div>
            @if (!empty($isDeletedView))
                <div class="flex sm:hidden items-center gap-2">
                    <a href="{{ route('admin.knowledgebase.index') }}"
                        class="p-2 rounded-lg bg-white border border-gray-200 text-slate-700 hover:bg-gray-50"
                        aria-label="Back to Knowledgebase Management (mobile)">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-slate-700" viewBox="0 0 24 24" fill="currentColor">
                            <path d="M15 6l-6 6 6 6" />
                        </svg>
                    </a>
                </div>
            @endif

            <!-- Desktop actions -->
            @if (!empty($isDeletedView))
                <div class="hidden sm:flex items-center gap-2">
                    <a href="{{ route('admin.knowledgebase.index') }}"
                        class="inline-flex items-center gap-2 rounded-lg border border-gray-200 bg-white hover:bg-gray-50 text-sm font-medium px-3 py-2">
                        ← Back to Knowledgebase Management
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

                    <!-- History Button (global Document Management History modal) -->
                    <button id="uploadLogsBtn" type="button"
                        class="inline-flex items-center gap-2 rounded-lg bg-gray-600 hover:bg-gray-700 text-white text-sm font-medium px-3 py-2"
                        aria-label="History">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" width="16" height="16" fill="currentColor" viewBox="0 0 16 16">
                            <path d="M8.515 1.019A7 7 0 0 0 8 1V0a8 8 0 0 1 .589.022zm2.004.45a7 7 0 0 0-.985-.299l.219-.976q.576.129 1.126.342zm1.37.71a7 7 0 0 0-.439-.27l.493-.87a8 8 0 0 1 .979.654l-.615.789a7 7 0 0 0-.418-.302zm1.834 1.79a7 7 0 0 0-.653-.796l.724-.69q.406.429.747.91zm.744 1.352a7 7 0 0 0-.214-.468l.893-.45a8 8 0 0 1 .45 1.088l-.95.313a7 7 0 0 0-.179-.483m.53 2.507a7 7 0 0 0-.1-1.025l.985-.17q.1.58.116 1.17zm-.131 1.538q.05-.254.081-.51l.993.123a8 8 0 0 1-.23 1.155l-.964-.267q.069-.247.12-.501m-.952 2.379q.276-.436.486-.908l.914.405q-.24.54-.555 1.038zm-.964 1.205q.183-.183.35-.378l.758.653a8 8 0 0 1-.401.432z" />
                            <path d="M8 1a7 7 0 1 0 4.95 11.95l.707.707A8.001 8.001 0 1 1 8 0z" />
                            <path d="M7.5 3a.5.5 0 0 1 .5.5v5.21l3.248 1.856a.5.5 0 0 1-.496.868l-3.5-2A.5.5 0 0 1 7 9V3.5a.5.5 0 0 1 .5-.5" />
                        </svg>
                        <span class="hidden lg:inline">History</span>
                        <span class="lg:hidden">History</span>
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
            <p class="text-sm text-gray-600 mb-4">Documents stored in the Rasa server for Knowledgebase training.</p>
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
                    <button id="mobileHistoryBtn" type="button"
                            class="w-full inline-flex items-center justify-center gap-2 rounded-lg border border-gray-200 bg-white hover:bg-gray-50 text-sm font-medium px-4 py-2">
                        <svg class="h-4 w-4 text-slate-700" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h4l2-3 4 6 3-4 3 5" />
                        </svg>
                        History
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Mobile Drawer Overlay -->
    <div id="mobileDrawerOverlay" class="fixed inset-0 bg-black/50 z-30 hidden sm:hidden"></div>

    <!-- Create Knowledgebase Modal -->
    <div id="createFaqModal" class="fixed inset-0 z-50 hidden">
        <div class="absolute inset-0 bg-black/40" data-close="create"></div>
        <div class="absolute inset-0 flex items-center justify-center p-4">
            <div
                class="w-full max-w-full sm:max-w-2xl bg-white rounded-none sm:rounded-lg shadow border border-gray-200 overflow-auto max-h-[90vh]">
                <div class="h-12 flex items-center justify-between px-4 border-b">
                    <div class="text-sm font-semibold text-slate-800">Add Document</div>
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
                            class="rounded-md bg-blue-600 hover:bg-blue-700 text-white text-sm font-medium px-4 py-2">Submit</button>
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
                    <div class="text-sm font-semibold text-slate-800">Upload File</div>
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

    <!-- View/Edit Knowledgebase Modal -->
    <div id="viewFaqModal" class="fixed inset-0 z-50 hidden"
        data-update-template="{{ route('admin.knowledgebase.update', ['faq' => '__ID__']) }}"
        data-show-url-template="{{ route('admin.knowledgebase.show', ['faq' => '__ID__']) }}"
        data-destroy-template="{{ route('admin.knowledgebase.destroy', ['faq' => '__ID__']) }}">
        <div class="absolute inset-0 bg-black/40" data-close="view"></div>
        <div class="absolute inset-0 flex items-center justify-center p-4">
            <div
                class="relative w-full max-w-full sm:max-w-2xl bg-white rounded-none sm:rounded-lg shadow border border-gray-200 overflow-auto max-h-[90vh]">

                <!-- Header -->
                <div class="h-12 flex items-center px-4 border-b">
                    <div class="text-sm font-semibold text-slate-800">Document Details</div>
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
                        <!-- Removed per-document history button -->
                        <button id="more_restore_btn" type="button"
                            class="w-full text-left px-3 py-2 text-sm text-gray-700 hover:bg-gray-50 hidden">Restore
                            Document</button>
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

    <!-- Document Management History Modal -->
    <div id="uploadLogsModal" class="fixed inset-0 z-50 hidden overflow-y-auto">
        <div class="absolute inset-0 bg-black/60 backdrop-blur-md transition-opacity" data-modal-backdrop></div>
        <div class="relative mx-auto my-0 sm:my-8 w-full h-full sm:h-auto sm:w-[95%] max-w-2xl flex items-center">
            <button type="button" class="absolute top-3 right-3 text-slate-700 hover:text-slate-900 z-50" data-close="upload-logs" aria-label="Close">
                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-x" viewBox="0 0 16 16">
                    <path d="M4.646 4.646a.5.5 0 0 1 .708 0L8 7.293l2.646-2.647a.5.5 0 0 1 .708.708L8.707 8l2.647 2.646a.5.5 0 0 1-.708.708L8 8.707l-2.646 2.647a.5.5 0 0 1-.708-.708L7.293 8 4.646 5.354a.5.5 0 0 1 0-.708" />
                </svg>
            </button>
            <div class="bg-white shadow-2xl w-full h-full sm:h-auto sm:max-h-[95vh] sm:max-w-2xl overflow-hidden sm:rounded-2xl flex flex-col">
                <!-- Header -->
                <div class="px-4 sm:px-6 py-3 border-b flex items-center justify-between">
                    <div class="text-sm font-semibold text-slate-800">Document Upload History</div>
                    <div class="flex items-center gap-2">
                        <!-- header controls (kept for spacing) -->
                    </div>
                </div>
                <!-- Body -->
                <div class="p-4 overflow-auto flex-1">
                    <div id="uploadLogsTableWrapper" class="overflow-auto">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">File Name</th>
                                    <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Size</th>
                                    <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Upload Date</th>
                                    <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Server Received</th>
                                </tr>
                            </thead>
                            <tbody id="uploadLogsTableBody" class="bg-white divide-y divide-gray-200"></tbody>
                        </table>
                    </div>
                </div>
                <!-- Footer (pagination) -->
                <div class="px-4 py-3 border-t bg-gray-50 text-sm">
                    <div id="uploadLogsPagination" class="flex items-center justify-end"></div>
                </div>
            </div>
        </div>
    </div>

    <!-- Hidden state with URLs -->
    <div id="admin-faqs-state" class="hidden" data-list-url="{{ $listUrl ?? route('admin.knowledgebase.list') }}"
        data-store-url="{{ route('admin.knowledgebase.store') }}"
        data-show-url-template="{{ route('admin.knowledgebase.show', ['faq' => '__ID__']) }}"
        data-update-url-template="{{ route('admin.knowledgebase.update', ['faq' => '__ID__']) }}"
        data-destroy-url-template="{{ route('admin.knowledgebase.destroy', ['faq' => '__ID__']) }}"
        data-revisions-url-template="{{ route('admin.knowledgebase.revisions', ['faq' => '__ID__']) }}"
        data-restore-url-template="{{ route('admin.knowledgebase.restore', ['faq' => '__ID__']) }}"
        data-enable-url-template="{{ route('admin.knowledgebase.enable', ['faq' => '__ID__']) }}"
        data-disable-url-template="{{ route('admin.knowledgebase.disable', ['faq' => '__ID__']) }}"
        data-local-documents='@json($localDocuments ?? [])'
        data-history-url=""
        ></div>

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

                    const rasaUrl = '{{ config("services.faq_list_docs.url") }}';
                    const secret = '{{ config("services.faq_list_docs.secret") }}';

                    const res = await fetch(rasaUrl, {
                        headers: {
                            'X-FAQ-UPDATER-TOKEN': secret,
                            'X-Requested-With': 'XMLHttpRequest'
                        }
                    });

                    if (!res.ok) {
                        const errorText = await res.text();
                        throw new Error(`Failed to load docs: ${res.status} - ${errorText}`);
                    }

                    const json = await res.json();

                    if (!json.ok) {
                        throw new Error(json.error || 'Failed to load docs');
                    }

                    renderDocsList(json.files || []);
                } catch (err) {
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

                const rasaBaseUrl = '{{ config("services.faq_list_docs.url") }}'.replace('/list-docs', '');
                const secret = '{{ config("services.faq_list_docs.secret") }}';
                const downloadUrl = `${rasaBaseUrl}/download/${encodeURIComponent(filename)}?token=${encodeURIComponent(secret)}`;
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

                    const rasaBaseUrl = '{{ config("services.faq_list_docs.url") }}'.replace('/list-docs', '');
                    const secret = '{{ config("services.faq_list_docs.secret") }}';

                    const res = await fetch(`${rasaBaseUrl}/download/${encodeURIComponent(filename)}?token=${encodeURIComponent(secret)}`, {
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
                        console.error('Failed to log document change:', await res.text());
                    }
                } catch (err) {
                    console.error('Error logging document change:', err);
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
                    const rasaBaseUrl = '{{ config("services.faq_list_docs.url") }}'.replace('/list-docs', '');
                    const secret = '{{ config("services.faq_list_docs.secret") }}';

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
                        const user = record.user_name || record.user || record.performed_by || record.uploaded_by || '';
                        const timestamp = record.timestamp || record.created_at || record.uploaded_at || record.updated_at || '';

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
                    const res = await fetch(`{{ route('staff.upload-logs.index') }}?page=${page}&per_page=${per_page}`, {
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
                    if (uploadLogsTableBody) uploadLogsTableBody.innerHTML = `<tr><td colspan="4" class="px-4 py-4 text-sm text-red-600">Failed to load upload logs</td></tr>`;
                    if (uploadLogsPagination) uploadLogsPagination.innerHTML = '';
                }
            }

            function renderUploadLogsTable(rows) {
                if (!uploadLogsTableBody) return;
                if (!rows || rows.length === 0) {
                    uploadLogsTableBody.innerHTML = `<tr><td colspan="4" class="px-4 py-6 text-center text-sm text-gray-500">No logs found</td></tr>`;
                    return;
                }

                uploadLogsTableBody.innerHTML = rows.map(r => {
                    const size = r.file_size ? formatFileSize(r.file_size) : '-';
                    const uploadDate = r.upload_date ? new Date(r.upload_date).toLocaleString() : '-';
                    const serverDate = r.server_recieved_date ? new Date(r.server_recieved_date).toLocaleString() : '-';
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
                if (current > 1) html += `<button class="px-3 py-1 border rounded mr-2" data-page="${current-1}">Prev</button>`;
                html += `<span class="text-sm text-gray-700 mr-2">Page ${current} of ${last}</span>`;
                if (current < last) html += `<button class="px-3 py-1 border rounded" data-page="${current+1}">Next</button>`;
                uploadLogsPagination.innerHTML = html;
                uploadLogsPagination.querySelectorAll('button').forEach(b => b.addEventListener('click', () =>
                    fetchUploadLogs(parseInt(b.getAttribute('data-page')))));
            }

            function escapeHtml(s) {
                if (s == null) return '';
                return String(s).replaceAll('&', '&').replaceAll('<', '<').replaceAll('>', '>').replaceAll('"', '"').replaceAll("'", '&#039;');
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
@endsection
