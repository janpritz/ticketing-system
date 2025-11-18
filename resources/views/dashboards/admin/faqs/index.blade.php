@extends('layouts.admin')

@section('title', $isDeletedView ? 'Deleted FAQs' : 'FAQ Management')

@section('admin-content')
    <div class="sm:px-2">
        <div class="flex items-center justify-between gap-4">
            <div>
                <h1 class="text-2xl font-semibold text-slate-900">
                    {{ !empty($isDeletedView) ? 'Deleted FAQs' : 'FAQ Management' }}</h1>
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
            @else
                <div class="sm:hidden">
                    <button id="mobileActionsToggle" type="button"
                        class="p-2 rounded-lg bg-gray-100 hover:bg-gray-200 text-slate-700 ml-4" aria-label="Open actions drawer">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 24 24" fill="currentColor">
                            <path d="M3 6h18v2H3V6zm0 5h18v2H3v-2zm0 5h18v2H3v-2z" />
                        </svg>
                    </button>
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
                    <!-- Interactive Pending / Trained toggle (yellow-themed) -->
                    <div id="faqsStatusToggleGroup" class="inline-flex items-center rounded-full bg-yellow-400 p-1">
                        <button id="faqsStatusAllBtn" type="button"
                            class="px-4 py-1.5 rounded-full bg-white text-yellow-700 font-medium text-sm">All</button>
                        <button id="faqsStatusTrainedBtn" type="button"
                            class="ml-1 px-4 py-1.5 rounded-full bg-yellow-400 text-white font-medium text-sm">Trained</button>
                        <button id="faqsStatusUntrainedBtn" type="button"
                            class="ml-1 px-4 py-1.5 rounded-full bg-yellow-400 text-white font-medium text-sm">Untrained</button>
                    </div>

                    <button id="openCreateModalBtn" type="button"
                        class="inline-flex items-center gap-2 rounded-lg bg-blue-600 hover:bg-blue-700 text-white text-sm font-medium px-3 py-2"
                        aria-label="Add FAQ">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" viewBox="0 0 24 24" fill="currentColor">
                            <path d="M11 11V5h2v6h6v2h-6v6h-2v-6H5v-2z" />
                        </svg>
                        <span class="hidden sm:inline">Add FAQ</span>
                    </button>
                </div>
            @endif

        </div>

        <div class="mt-4">
            <!-- Desktop search / filters -->
            <div class="hidden sm:flex items-start justify-between">
                <div class="flex items-center gap-2">
                    <label class="relative block">
                        <span class="sr-only">Search</span>
                        <span class="absolute inset-y-0 left-0 flex items-center pl-3 text-gray-400">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" viewBox="0 0 24 24" fill="currentColor">
                                <path
                                    d="M15.5 14h-.79l-.28-.27a6.5 6.5 0 10-.71.71l.27.28v.79L20 21.5 21.5 20l-6-6zM10 15a5 5 0 110-10 5 5 0 010 10z" />
                            </svg>
                        </span>
                        <input id="q" type="text" name="q" placeholder="Search intent or response"
                            class="w-80 pl-9 pr-3 py-2 text-sm rounded-md border border-gray-200 focus:ring-2 focus:ring-blue-500 focus:border-blue-500" />
                    </label>

                    <button id="searchBtn" type="button"
                        class="rounded-md border border-gray-300 bg-white hover:bg-gray-50 text-sm px-3 py-2">Search</button>
                    <button id="clearSearch" type="button"
                        class="text-sm text-slate-600 hover:text-slate-800 hidden">Clear</button>
                </div>

                <div class="flex items-center gap-3">
                    <label class="text-sm text-slate-600">Per page</label>
                    <select id="per_page" class="rounded-md border border-gray-200 bg-white text-sm px-3 py-2">
                        <option value="25" selected>25</option>
                        <option value="50">50</option>
                        <option value="100">100</option>
                    </select>

                    <!-- Trash / Back button -->
                    @if (!empty($isDeletedView))
                    @else
                        <a href="{{ route('admin.faqs.deleted') }}"
                            class="inline-flex items-center gap-2 rounded-lg border border-gray-200 bg-white hover:bg-gray-50 text-sm px-3 py-2 ml-3">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 text-slate-700" viewBox="0 0 24 24"
                                fill="currentColor">
                                <path
                                    d="M3 6h18v2H3V6zm2 3h14l-1.1 12.2c-.08.9-.86 1.6-1.76 1.6H8.86c-.9 0-1.68-.7-1.76-1.6L6 9zM9 4V3h6v1h5v2H4V4h5z" />
                            </svg>
                            <span class="hidden sm:inline">Trash</span>
                        </a>
                    @endif
                </div>
            </div>


            <!-- Mobile actions drawer (bottom sheet) -->
            <div id="mobileDrawerOverlay" class="hidden sm:hidden fixed inset-0 bg-black/30 z-40"></div>
            <div id="mobileDrawer"
                class="sm:hidden fixed left-0 right-0 bottom-0 transform translate-y-full transition-transform duration-200 bg-white border-t border-gray-200 z-50">
                <div class="px-4 py-3 flex items-center justify-between">
                    <div class="flex items-center gap-3">
                        <button id="mobileDrawerClose" type="button" class="p-2 rounded-md text-slate-700 hover:bg-gray-50"
                            aria-label="Close drawer">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24"
                                stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                            </svg>
                        </button>
                        <div class="text-sm font-medium">Actions</div>
                    </div>
                </div>

                <div class="px-4 pb-4 space-y-2">
                    <div class="flex items-center gap-2 w-full">
                        <!-- Mobile: full-width interactive Pending / Trained buttons (match desktop behavior) -->
                        <div class="flex w-full rounded bg-yellow-400 p-1">
                            <button id="mobileAllToggle" type="button"
                                class="flex-1 px-4 py-3 rounded bg-white text-yellow-700 font-medium text-sm text-center"
                                aria-label="All (mobile)">All</button>
                            <button id="mobileTrainedToggle" type="button"
                                class="ml-1 flex-1 px-4 py-3 rounded bg-yellow-400 text-white font-medium text-sm text-center"
                                aria-label="Trained (mobile)">Trained</button>
                            <button id="mobileUntrainedToggle" type="button"
                                class="ml-1 flex-1 px-4 py-3 rounded bg-yellow-400 text-white font-medium text-sm text-center"
                                aria-label="Untrained (mobile)">Untrained</button>
                        </div>
                    </div>
                    {{-- <button id="mobileActionUpdateStatus" type="button"
                        class="w-full flex items-center gap-3 px-3 py-2 rounded-md bg-amber-600 text-white hover:opacity-90"
                        data-pending-url="{{ route('admin.faqs.index', ['status' => 'untrained']) }}" aria-label="Untrain FAQs (mobile)">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 flex-shrink-0" viewBox="0 0 24 24"
                            fill="currentColor">
                            <path d="M12 2a10 10 0 100 20 10 10 0 000-20zM11 6h2v6h-2V6zm0 8h2v2h-2v-2z" />
                        </svg>
                        <span class="font-medium">Untrain FAQs</span>
                    </button> --}}

                    <button id="mobileActionTrash" type="button"
                        class="w-full text-left px-3 py-2 rounded-md bg-white hover:bg-gray-50 text-slate-700 flex items-center gap-3"
                        data-deleted-url="{{ route('admin.faqs.deleted') }}" aria-label="Trash (mobile)">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 24 24" fill="currentColor">
                            <path
                                d="M3 6h18v2H3V6zm2 3h14l-1.1 12.2c-.08.9-.86 1.6-1.76 1.6H8.86c-.9 0-1.68-.7-1.76-1.6L6 9zM9 4V3h6v1h5v2H4V4h5z" />
                        </svg>
                        <span>Trash</span>
                    </button>

                    <button id="mobileActionAdd" type="button"
                        class="w-full text-left px-3 py-2 rounded-md bg-blue-600 hover:bg-blue-700 text-white flex items-center gap-3"
                        aria-label="Add FAQ (mobile)">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 24 24" fill="currentColor">
                            <path d="M11 11V5h2v6h6v2h-6v6h-2v-6H5v-2z" />
                        </svg>
                        <span>Add FAQ</span>
                    </button>
                </div>
            </div>

            <!-- Mobile search area (always visible on mobile) -->
            <div id="mobileSearchArea" class="sm:hidden mt-3">
                <div class="flex items-center gap-2">
                    <input id="q_mobile" type="text" placeholder="Search topic or response"
                        class="flex-1 pl-3 pr-3 py-2 rounded-md border border-gray-200 text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500" />
                    <button id="mobileSearchBtn" type="button"
                        class="rounded-md border border-gray-300 bg-white hover:bg-gray-50 text-sm px-3 py-2"
                        aria-label="Search">Search</button>
                </div>

                <div class="mt-2 flex items-center justify-between">
                    <div class="flex items-center gap-2">
                        <label class="text-sm text-slate-600">Per page</label>
                        <select id="per_page_mobile" class="rounded-md border border-gray-200 bg-white text-sm px-3 py-2">
                            <option value="25" selected>25</option>
                            <option value="50">50</option>
                            <option value="100">100</option>
                        </select>
                    </div>
                </div>
            </div>
        </div>

        <div class="mt-4 bg-white rounded-xl border border-gray-200 overflow-hidden">
            <div class="overflow-x-auto">
                <table id="faqsTable" class="min-w-full text-sm">
                    <thead class="bg-gray-50 text-gray-600">
                        <tr>
                            <th class="py-3 pl-5 pr-3 text-left font-medium">Intent</th>
                            <th class="px-3 py-3 text-left font-medium">Description</th>
                            <th class="px-3 py-3 text-left font-medium">Response</th>
                            <th class="px-3 py-3 text-left font-medium">Created At</th>
                            <th class="px-3 py-3 text-left font-medium">Updated At</th>
                            <th class="px-3 py-3 text-left font-medium">Status</th>
                            <th class="py-3 pl-3 pr-5 text-left font-medium">Actions</th>
                        </tr>
                    </thead>
                    <tbody id="faqsTbody" class="divide-y divide-gray-100">
                        <tr>
                            <td colspan="7" class="px-5 py-6 text-center text-sm text-gray-500">Loading...</td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <div id="faqsFooter" class="px-5 py-3 border-t border-gray-200">
                <div id="paginationControls" class="flex items-center justify-between"></div>
            </div>
        </div>
    </div>

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

                <!-- Action pills (Train / Untrain) shown to the left of the "more actions" menu -->
                <div id="actionPills" class="absolute top-3 right-28 hidden space-x-2">
                    <button id="trainPillBtn" type="button"
                        class="rounded-full px-3 py-1.5 bg-emerald-600 text-white text-sm font-medium hidden">Train</button>
                    <button id="untrainPillBtn" type="button"
                        class="rounded-full px-3 py-1.5 bg-yellow-400 text-sm font-medium text-slate-900 hidden">Untrain</button>
                </div>
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
                        <button id="more_train_btn" type="button"
                            class="w-full text-left px-3 py-2 text-sm text-gray-700 hover:bg-gray-50 hidden">Train</button>
                        <button id="more_untrain_btn" type="button"
                            class="w-full text-left px-3 py-2 text-sm text-gray-700 hover:bg-gray-50 hidden">Untrain</button>
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



    <!-- Hidden state with URLs -->
    <div id="admin-faqs-state" class="hidden" data-list-url="{{ $listUrl ?? route('admin.faqs.list') }}"
        data-store-url="{{ route('admin.faqs.store') }}"
        data-show-url-template="{{ route('admin.faqs.show', ['faq' => '__ID__']) }}"
        data-update-url-template="{{ route('admin.faqs.update', ['faq' => '__ID__']) }}"
        data-destroy-url-template="{{ route('admin.faqs.destroy', ['faq' => '__ID__']) }}"
        data-revisions-url-template="{{ route('admin.faqs.revisions', ['faq' => '__ID__']) }}"
        data-restore-url-template="{{ route('admin.faqs.restore', ['faq' => '__ID__']) }}"
        data-train-url-template="{{ route('admin.faqs.train', ['faq' => '__ID__']) }}"
        data-untrain-url-template="{{ route('admin.faqs.untrain', ['faq' => '__ID__']) }}"
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
        (function() {
            const stateEl = document.getElementById('admin-faqs-state');
            const LIST_URL = stateEl.getAttribute('data-list-url');
            const STORE_URL = stateEl.getAttribute('data-store-url');
            const SHOW_TEMPLATE = stateEl.getAttribute('data-show-url-template');
            const UPDATE_TEMPLATE = stateEl.getAttribute('data-update-url-template');
            const DESTROY_TEMPLATE = stateEl.getAttribute('data-destroy-url-template');
            const RESTORE_TEMPLATE = stateEl.getAttribute('data-restore-url-template');
            const TRAIN_TEMPLATE = stateEl.getAttribute('data-train-url-template');
            const UNTRAIN_TEMPLATE = stateEl.getAttribute('data-untrain-url-template');
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
            const moreTrainBtn = $('#more_train_btn');
            const moreUntrainBtn = $('#more_untrain_btn');

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


            let currentPage = 1;
            let currentQuery = '';
            let currentPerPage = parseInt(perPageSelect.value || '25', 10);
            let autoRefreshInterval = null;
            let showDeleted = false;
            // status filter: 'all' (default) shows all statuses; 'trained' shows only trained FAQs
            let currentStatus = 'all';

            function openModal(modal) {
                if (modal) modal.classList.remove('hidden');
            }

            function closeModal(modal) {
                if (modal) modal.classList.add('hidden');
            }

            // Fetch list via AJAX
            async function fetchList(page = 1) {
                currentPage = page;
                const q = encodeURIComponent((qInput.value || '').trim());
                const per = perPageSelect.value || '25';
                const url =
                    `${LIST_URL}?q=${q}&per_page=${per}&page=${page}&include_deleted=${showDeleted ? '1' : '0'}&status=${encodeURIComponent(currentStatus)}`;
                try {
                    const res = await fetch(url, {
                        headers: {
                            'X-Requested-With': 'XMLHttpRequest'
                        }
                    });
                    if (!res.ok) throw new Error('Failed to load FAQs');
                    const json = await res.json();
                    renderTable(json.items || []);
                    renderPagination(json.meta || {});
                    toggleClear(qInput.value.trim() !== '');
                } catch (err) {
                    faqsTbody.innerHTML =
                        `<tr><td colspan="6" class="px-5 py-6 text-center text-sm text-red-600">Error loading FAQs</td></tr>`;
                    paginationControls.innerHTML = '';
                    console.error(err);
                }
            }

            function truncate(str, n = 140) {
                if (!str) return '';
                return (str.length > n) ? (str.slice(0, n - 1) + '…') : str;
            }

            function renderTable(items) {
                // If the status filter is 'all', ensure trained FAQs are listed first
                if (currentStatus === 'all' && Array.isArray(items) && items.length > 0) {
                    items = items.slice().sort((a, b) => {
                        if (a.status === b.status) return 0;
                        if (a.status === 'trained') return -1;
                        if (b.status === 'trained') return 1;
                        // Keep original order for other statuses
                        return 0;
                    });
                }

                if (!items || items.length === 0) {
                    faqsTbody.innerHTML =
                        `<tr><td colspan="7" class="px-5 py-10 text-center text-sm text-gray-500">No FAQs found.</td></tr>`;
                    return;
                }
                faqsTbody.innerHTML = items.map(f => `
      <tr class="${f.status === 'trained' ? 'bg-emerald-50' : 'hover:bg-gray-50'} ${f.deleted_at ? 'opacity-70' : ''}">
        <td class="py-3 pl-5 pr-3 align-top">
          <div class="text-slate-900 font-medium">${escapeHtml(f.intent)}</div>
        </td>
        <td class="px-3 py-3 align-top">
          <div class="text-slate-700 whitespace-pre-line max-w-xl">${escapeHtml(truncate(f.description || '', 140))}</div>
        </td>
        <td class="px-3 py-3 align-top">
          <div class="text-slate-700 whitespace-pre-line">${escapeHtml(truncate(f.response, 180))}</div>
        </td>
        <td class="px-3 py-3 align-top">
          <div class="text-slate-500 text-xs">${escapeHtml(f.created_at || '')}</div>
        </td>
        <td class="px-3 py-3 align-top">
          <div class="text-slate-500 text-xs">${escapeHtml(f.updated_at || '')}</div>
        </td>
        <td class="px-3 py-3 align-top">
          <div class="text-slate-700">${escapeHtml(f.status || 'untrained')}</div>
        </td>
        <td class="py-3 pl-3 pr-5 align-top">
          <div class="flex items-center gap-2">
            ${f.deleted_at ? (
              `<div class="flex items-center gap-2">
                <button class="restoreDeletedBtn inline-flex items-center gap-1 rounded-md border border-gray-200 bg-white px-3 py-1.5 text-sm font-medium text-gray-700 hover:bg-gray-50" data-id="${f.id}">Restore</button>
                <button class="deletePermanentBtn inline-flex items-center gap-1 rounded-md border border-red-200 bg-white text-red-700 px-3 py-1.5 text-sm font-medium hover:bg-red-50" data-id="${f.id}">Delete</button>
              </div>`
            ) : (
              `<div class="flex items-center gap-2">
                <button class="viewFaqBtn inline-flex items-center gap-1 rounded-lg border border-gray-200 bg-white px-3 py-1.5 text-xs font-medium text-gray-700 hover:bg-gray-50" data-id="${f.id}">View</button>
                <div class="hidden sm:flex items-center gap-2">
                  ${f.response_disabled ? (
                    `<button class="enableFaqBtn inline-flex items-center gap-1 rounded-lg border border-green-200 bg-white px-2 py-1.5 text-xs font-medium text-green-700 hover:bg-green-50" data-id="${f.id}" title="Enable FAQ">
                      <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" viewBox="0 0 24 24" fill="currentColor">
                        <path d="M12 4.5C7 4.5 2.73 7.61 1 12c1.73 4.39 6 7.5 11 7.5s9.27-3.11 11-7.5c-1.73-4.39-6-7.5-11-7.5zM12 17c-2.76 0-5-2.24-5-5s2.24-5 5-5 5 2.24 5 5-2.24 5-5 5zm0-8c-1.66 0-3 1.34-3 3s1.34 3 3 3 3-1.34 3-3-1.34-3-3-3z"/>
                      </svg>
                      <span class="hidden sm:inline">Enable</span>
                    </button>`
                  ) : (
                    `<button class="disableFaqBtn inline-flex items-center gap-1 rounded-lg border border-gray-200 bg-white px-2 py-1.5 text-xs font-medium text-gray-700 hover:bg-gray-50" data-id="${f.id}" title="Disable FAQ">
                      <span class="hidden sm:inline">Disable</span>
                    </button>`
                  )}
                </div>
                <button class="mobileToggleBtn sm:hidden rounded-full px-3 py-1 text-xs font-medium ${f.response_disabled ? 'bg-gray-400 text-white' : 'bg-green-400 text-white'}" data-id="${f.id}" data-disabled="${f.response_disabled ? '1' : '0'}">${f.response_disabled ? 'Disabled' : 'Enabled'}</button>
              </div>`
            )}
          </div>
        </td>
      </tr>
    `).join('');
                // attach handlers
                $$('.viewFaqBtn').forEach(btn => btn.addEventListener('click', onViewClick));
                // attach restore handlers for deleted rows
                $$('.restoreDeletedBtn').forEach(btn => {
                    btn.addEventListener('click', async (e) => {
                        const id = btn.getAttribute('data-id');
                        if (!id) return;
                        const url = RESTORE_TEMPLATE.replace('__ID__', id);
                        const confirmResult = await Swal.fire({
                            title: 'Restore FAQ?',
                            text: 'Do you want to restore this FAQ?',
                            icon: 'question',
                            showCancelButton: true,
                            confirmButtonText: 'Yes, restore',
                            cancelButtonText: 'Cancel'
                        });
                        if (!confirmResult.isConfirmed) return;
                        try {
                            btn.disabled = true;
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
                                const err = json.message || 'Failed to restore';
                                throw new Error(err);
                            }
                            showToast('success', json.message || 'FAQ restored');
                            try { localStorage.setItem('ts_tickets_changed', String(Date.now())); } catch (e) {}
                            fetchList(currentPage);
                        } catch (err) {
                            showToast('error', err.message || 'Error');
                            console.error(err);
                        } finally {
                            btn.disabled = false;
                        }
                    });
                });
                // attach permanent delete handlers for deleted rows
                $$('.deletePermanentBtn').forEach(btn => {
                    btn.addEventListener('click', async (e) => {
                        const id = btn.getAttribute('data-id');
                        if (!id) return;
                        const confirmResult = await Swal.fire({
                            title: 'Delete permanently?',
                            text: 'This will permanently delete the FAQ and cannot be undone.',
                            icon: 'warning',
                            showCancelButton: true,
                            confirmButtonText: 'Yes, delete permanently',
                            cancelButtonText: 'Cancel'
                        });
                        if (!confirmResult.isConfirmed) return;
                        const url = DESTROY_TEMPLATE.replace('__ID__', id);
                        try {
                            btn.disabled = true;
                            const res = await fetch(url, {
                                method: 'DELETE',
                                headers: {
                                    'X-CSRF-TOKEN': csrf,
                                    'X-Requested-With': 'XMLHttpRequest'
                                }
                            });
                            const json = await res.json();
                            if (!res.ok) {
                                const err = json.message || 'Failed to delete permanently';
                                throw new Error(err);
                            }
                            showToast('success', json.message || 'FAQ permanently deleted');
                            try { localStorage.setItem('ts_tickets_changed', String(Date.now())); } catch (e) {}
                            fetchList(currentPage);
                        } catch (err) {
                            showToast('error', err.message || 'Error');
                            console.error(err);
                        } finally {
                            btn.disabled = false;
                        }
                    });
                });

                // Attach enable/disable handlers for desktop buttons
                $$('.enableFaqBtn').forEach(btn => {
                    btn.addEventListener('click', async (e) => {
                        e.stopPropagation();
                        const id = btn.getAttribute('data-id');
                        if (!id) return;
                        const url = ENABLE_TEMPLATE.replace('__ID__', id);
                        
                        // Store original content
                        const originalHTML = btn.innerHTML;
                        
                        try {
                            btn.disabled = true;
                            // Show loading spinner
                            btn.innerHTML = `
                                <svg class="animate-spin h-4 w-4 mx-auto" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                </svg>
                            `;
                            
                            const res = await fetch(url, {
                                method: 'POST',
                                headers: {
                                    'X-CSRF-TOKEN': csrf,
                                    'X-Requested-With': 'XMLHttpRequest'
                                }
                            });
                            const json = await res.json();
                            if (!res.ok) {
                                throw new Error(json.message || 'Failed to enable FAQ');
                            }
                            showToast('success', 'FAQ enabled successfully');
                            fetchList(currentPage);
                        } catch (err) {
                            showToast('error', err.message || 'Error');
                            console.error(err);
                            // Restore original content on error
                            btn.innerHTML = originalHTML;
                            btn.disabled = false;
                        }
                    });
                });

                $$('.disableFaqBtn').forEach(btn => {
                    btn.addEventListener('click', async (e) => {
                        e.stopPropagation();
                        const id = btn.getAttribute('data-id');
                        if (!id) return;
                        const url = DISABLE_TEMPLATE.replace('__ID__', id);
                        
                        // Store original content
                        const originalHTML = btn.innerHTML;
                        
                        try {
                            btn.disabled = true;
                            // Show loading spinner
                            btn.innerHTML = `
                                <svg class="animate-spin h-4 w-4 mx-auto" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                </svg>
                            `;
                            
                            const res = await fetch(url, {
                                method: 'POST',
                                headers: {
                                    'X-CSRF-TOKEN': csrf,
                                    'X-Requested-With': 'XMLHttpRequest'
                                }
                            });
                            const json = await res.json();
                            if (!res.ok) {
                                throw new Error(json.message || 'Failed to disable FAQ');
                            }
                            showToast('success', 'FAQ disabled successfully');
                            fetchList(currentPage);
                        } catch (err) {
                            showToast('error', err.message || 'Error');
                            console.error(err);
                            // Restore original content on error
                            btn.innerHTML = originalHTML;
                            btn.disabled = false;
                        }
                    });
                });

                // Attach mobile toggle handlers
                $$('.mobileToggleBtn').forEach(btn => {
                    btn.addEventListener('click', async (e) => {
                        e.stopPropagation();
                        const id = btn.getAttribute('data-id');
                        const isDisabled = btn.getAttribute('data-disabled') === '1';
                        if (!id) return;
                        
                        const url = isDisabled
                            ? ENABLE_TEMPLATE.replace('__ID__', id)
                            : DISABLE_TEMPLATE.replace('__ID__', id);
                        
                        // Store original content
                        const originalHTML = btn.innerHTML;
                        
                        try {
                            btn.disabled = true;
                            // Show loading spinner
                            btn.innerHTML = `
                                <svg class="animate-spin h-4 w-4 mx-auto" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                </svg>
                            `;
                            
                            const res = await fetch(url, {
                                method: 'POST',
                                headers: {
                                    'X-CSRF-TOKEN': csrf,
                                    'X-Requested-With': 'XMLHttpRequest'
                                }
                            });
                            const json = await res.json();
                            if (!res.ok) {
                                throw new Error(json.message || `Failed to ${isDisabled ? 'enable' : 'disable'} FAQ`);
                            }
                            showToast('success', `FAQ ${isDisabled ? 'enabled' : 'disabled'} successfully`);
                            fetchList(currentPage);
                        } catch (err) {
                            showToast('error', err.message || 'Error');
                            console.error(err);
                            // Restore original content on error
                            btn.innerHTML = originalHTML;
                            btn.disabled = false;
                        }
                    });
                });
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

                // Show/hide action buttons based on FAQ state
                const actionPills = $('#actionPills');
                const trainPill = $('#trainPillBtn');
                const untrainPill = $('#untrainPillBtn');
                
                if (faq.status === 'trained') {
                    if (trainPill) trainPill.classList.add('hidden');
                    if (untrainPill) untrainPill.classList.remove('hidden');
                } else {
                    if (trainPill) trainPill.classList.remove('hidden');
                    if (untrainPill) untrainPill.classList.add('hidden');
                }
                if (actionPills) actionPills.classList.remove('hidden');

                // Show more actions button
                if (moreBtn) moreBtn.classList.remove('hidden');

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

            // Status toggle handlers (Desktop)
            const allBtn = $('#faqsStatusAllBtn');
            const trainedBtn = $('#faqsStatusTrainedBtn');
            const untrainedBtn = $('#faqsStatusUntrainedBtn');

            function updateStatusButtons(status) {
                [allBtn, trainedBtn, untrainedBtn].forEach(btn => {
                    if (!btn) return;
                    btn.classList.remove('bg-white', 'text-yellow-700');
                    btn.classList.add('bg-yellow-400', 'text-white');
                });
                
                const activeBtn = status === 'all' ? allBtn : (status === 'trained' ? trainedBtn : untrainedBtn);
                if (activeBtn) {
                    activeBtn.classList.remove('bg-yellow-400', 'text-white');
                    activeBtn.classList.add('bg-white', 'text-yellow-700');
                }
            }

            if (allBtn) {
                allBtn.addEventListener('click', () => {
                    currentStatus = 'all';
                    updateStatusButtons('all');
                    fetchList(1);
                });
            }
            if (trainedBtn) {
                trainedBtn.addEventListener('click', () => {
                    currentStatus = 'trained';
                    updateStatusButtons('trained');
                    fetchList(1);
                });
            }
            if (untrainedBtn) {
                untrainedBtn.addEventListener('click', () => {
                    currentStatus = 'untrained';
                    updateStatusButtons('untrained');
                    fetchList(1);
                });
            }

            // Mobile status toggles
            const mobileAll = $('#mobileAllToggle');
            const mobileTrained = $('#mobileTrainedToggle');
            const mobileUntrained = $('#mobileUntrainedToggle');

            function updateMobileStatusButtons(status) {
                [mobileAll, mobileTrained, mobileUntrained].forEach(btn => {
                    if (!btn) return;
                    btn.classList.remove('bg-white', 'text-yellow-700');
                    btn.classList.add('bg-yellow-400', 'text-white');
                });
                
                const activeBtn = status === 'all' ? mobileAll : (status === 'trained' ? mobileTrained : mobileUntrained);
                if (activeBtn) {
                    activeBtn.classList.remove('bg-yellow-400', 'text-white');
                    activeBtn.classList.add('bg-white', 'text-yellow-700');
                }
            }

            if (mobileAll) {
                mobileAll.addEventListener('click', () => {
                    currentStatus = 'all';
                    updateMobileStatusButtons('all');
                    updateStatusButtons('all');
                    fetchList(1);
                });
            }
            if (mobileTrained) {
                mobileTrained.addEventListener('click', () => {
                    currentStatus = 'trained';
                    updateMobileStatusButtons('trained');
                    updateStatusButtons('trained');
                    fetchList(1);
                });
            }
            if (mobileUntrained) {
                mobileUntrained.addEventListener('click', () => {
                    currentStatus = 'untrained';
                    updateMobileStatusButtons('untrained');
                    updateStatusButtons('untrained');
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

            // Template button handlers
            const createTemplateBtn = $('#createTemplateBtn');
            const createDescription = $('#create_description');
            if (createTemplateBtn && createDescription) {
                // Hide button if description is not empty
                const toggleCreateTemplateBtn = () => {
                    const isEmpty = createDescription.value.trim() === '';
                    createTemplateBtn.classList.toggle('hidden', !isEmpty);
                };
                createDescription.addEventListener('input', toggleCreateTemplateBtn);
                // Initial check
                toggleCreateTemplateBtn();

                createTemplateBtn.addEventListener('click', () => {
                    const intent = $('#create_intent').value.trim();
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
            if (viewTemplateBtn && viewDescription) {
                // Hide button if description is not empty
                const toggleViewTemplateBtn = () => {
                    const isEmpty = viewDescription.value.trim() === '';
                    viewTemplateBtn.classList.toggle('hidden', !isEmpty);
                };
                viewDescription.addEventListener('input', toggleViewTemplateBtn);
                // Initial check
                toggleViewTemplateBtn();

                viewTemplateBtn.addEventListener('click', () => {
                    const intent = $('#view_intent').value.trim();
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
                    const intent = $('#create_intent').value.trim();
                    const description = $('#create_description').value.trim();
                    const response = $('#create_response').value.trim();

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

            // Train/Untrain pill buttons
            const trainPill = $('#trainPillBtn');
            const untrainPill = $('#untrainPillBtn');

            if (trainPill) {
                trainPill.addEventListener('click', async () => {
                    const id = viewFaqId.value;
                    const url = TRAIN_TEMPLATE.replace('__ID__', id);
                    try {
                        trainPill.disabled = true;
                        const res = await fetch(url, {
                            method: 'PUT',
                            headers: {
                                'X-CSRF-TOKEN': csrf,
                                'X-Requested-With': 'XMLHttpRequest'
                            }
                        });
                        const json = await res.json();
                        if (!res.ok) {
                            throw new Error(json.message || 'Failed to train FAQ');
                        }
                        showToast('success', 'FAQ marked as trained');
                        closeModal(viewModal);
                        fetchList(currentPage);
                    } catch (err) {
                        showToast('error', err.message || 'Error');
                        console.error(err);
                    } finally {
                        trainPill.disabled = false;
                    }
                });
            }

            if (untrainPill) {
                untrainPill.addEventListener('click', async () => {
                    const id = viewFaqId.value;
                    const url = UNTRAIN_TEMPLATE.replace('__ID__', id);
                    try {
                        untrainPill.disabled = true;
                        const res = await fetch(url, {
                            method: 'POST',
                            headers: {
                                'X-CSRF-TOKEN': csrf,
                                'X-Requested-With': 'XMLHttpRequest'
                            }
                        });
                        const json = await res.json();
                        if (!res.ok) {
                            throw new Error(json.message || 'Failed to untrain FAQ');
                        }
                        showToast('success', 'FAQ marked as untrained');
                        closeModal(viewModal);
                        fetchList(currentPage);
                    } catch (err) {
                        showToast('error', err.message || 'Error');
                        console.error(err);
                    } finally {
                        untrainPill.disabled = false;
                    }
                });
            }

            // Initialize: fetch list on page load
            fetchList(1);

        })();

    </script>

@endsection
