@extends('layouts.admin')

@section('title', 'Ticket Management')

@section('admin-content')
    <div class="sm:px-2">
        <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4">
            <div>
                <h1 class="text-2xl font-semibold text-slate-900">Ticket Management</h1>
                <p class="text-sm text-slate-500 mt-1">Manage all tickets: respond, forward, edit, delete.</p>
            </div>
            <div class="flex items-center gap-3">
                <!-- Desktop search + per-page -->
                <div class="hidden md:flex items-center gap-2">
                    <label class="relative block">
                        <span class="sr-only">Search</span>
                        <span class="absolute inset-y-0 left-0 flex items-center pl-3 text-gray-400">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" viewBox="0 0 24 24" fill="currentColor">
                                <path
                                    d="M15.5 14h-.79l-.28-.27a6.5 6.5 0 10-.71.71l.27.28v.79L20 21.5 21.5 20l-6-6zM10 15a5 5 0 110-10 5 5 0 010 10z" />
                            </svg>
                        </span>
                        <input id="q" type="text" placeholder="Search tickets..."
                            class="pl-9 pr-3 py-2 rounded-md border border-gray-200 text-sm w-72" />
                    </label>
                    <button id="searchBtn"
                        class="rounded-md border border-gray-300 bg-white px-3 py-2 text-sm">Search</button>
                </div>

                <!-- Mobile search -->
                <div class="md:hidden flex items-center gap-2">
                    <input id="q_mobile" type="text" placeholder="Search tickets..."
                        class="pl-3 pr-3 py-2 rounded-md border border-gray-200 text-sm w-full" />
                    <button id="searchBtnMobile"
                        class="rounded-md border border-gray-300 bg-white px-3 py-2 text-sm">Search</button>
                </div>

                <button id="openFiltersBtn"
                    class="ml-2 rounded-md border border-gray-200 bg-white px-3 py-2 text-sm">Filters</button>
            </div>
        </div>

        <div class="mt-4 bg-white rounded-xl border border-gray-200 overflow-hidden">
            <div class="overflow-x-auto">
                <table id="ticketsTable" class="min-w-full text-sm">
                    <thead class="bg-gray-50 text-gray-600">
                        <tr>
                            <th class="py-3 pl-5 pr-3 text-left font-medium">Ticket</th>
                            <th class="px-3 py-3 text-left font-medium">Category</th>
                            <th class="px-3 py-3 text-left font-medium">Message</th>
                            <th class="px-3 py-3 text-left font-medium">Status</th>
                            <th class="px-3 py-3 text-left font-medium">Assignee</th>
                            <th class="px-3 py-3 text-left font-medium">Created</th>
                        </tr>
                    </thead>
                    <tbody id="ticketsTbody" class="divide-y divide-gray-100">
                        <tr>
                            <td colspan="6" class="px-5 py-6 text-center text-sm text-gray-500">Loading...</td>
                        </tr>
                    </tbody>
                </table>
            </div>
            <div id="ticketsFooter" class="px-5 py-3 border-t border-gray-200">
                <div id="ticketsPagination" class="flex items-center justify-between"></div>
            </div>
        </div>
    </div>

    <!-- Image Lightbox -->
    <div id="imageLightbox" class="fixed inset-0 z-60 hidden bg-black bg-opacity-75 flex items-center justify-center">
        <div class="relative w-full h-full">
            <img id="lightboxImage" src="" alt="" class="w-full h-full object-contain">
            <button id="lightboxPrev"
                class="absolute left-4 top-1/2 transform -translate-y-1/2 text-white text-4xl hover:text-gray-300 bg-black bg-opacity-50 rounded-full w-12 h-12 flex items-center justify-center">&larr;</button>
            <button id="lightboxNext"
                class="absolute right-4 top-1/2 transform -translate-y-1/2 text-white text-4xl hover:text-gray-300 bg-black bg-opacity-50 rounded-full w-12 h-12 flex items-center justify-center">&rarr;</button>
            <button id="lightboxClose"
                class="absolute top-4 right-4 text-white text-4xl hover:text-gray-300 bg-black bg-opacity-50 rounded-full w-12 h-12 flex items-center justify-center">&times;</button>
        </div>
    </div>

    <div id="ticketsDrawerOverlay" class="hidden fixed inset-0 bg-black/30 z-40"></div>
    <!-- Bottom drawer: Filters & Sort -->
    <div id="ticketsBottomDrawer"
        class="fixed left-0 right-0 bottom-0 z-50 bg-white border-t border-gray-200 shadow-lg transform translate-y-full transition-transform duration-200">
        <div class="px-4 py-3 flex items-center justify-between border-b">
            <div class="text-sm font-semibold text-slate-800">Filters & Sort</div>
            <button id="closeFiltersBtn" type="button"
                class="p-2 rounded-md text-slate-600 hover:text-slate-800 hover:bg-gray-50" aria-label="Close filters">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 24 24" fill="currentColor">
                    <path d="M6 18 18 6M6 6l12 12" />
                </svg>
            </button>
        </div>
        <div class="px-4 py-3 grid grid-cols-1 sm:grid-cols-4 gap-3">
            <div>
                <label for="filterStatus" class="block text-xs text-slate-600 mb-1">Status</label>
                <select id="filterStatus" class="w-full rounded-md border border-gray-300 bg-white text-sm px-3 py-2">
                    <option value="">All</option>
                    <option value="Open">Open</option>
                    <option value="Forwarded">Forwarded</option>
                    <option value="Closed">Closed</option>
                </select>
            </div>
            <div>
                <label for="filterSort" class="block text-xs text-slate-600 mb-1">Sort by</label>
                <select id="filterSort" class="w-full rounded-md border border-gray-300 bg-white text-sm px-3 py-2">
                    <option value="created_desc" selected>Created (newest)</option>
                    <option value="created_asc">Created (oldest)</option>
                    <option value="status_asc">Status (A-Z)</option>
                    <option value="status_desc">Status (Z-A)</option>
                    <option value="assignee_asc">Assignee (A-Z)</option>
                    <option value="assignee_desc">Assignee (Z-A)</option>
                </select>
            </div>
            <div>
                <label for="filterRole" class="block text-xs text-slate-600 mb-1">Role</label>
                <select id="filterRole" class="w-full rounded-md border border-gray-300 bg-white text-sm px-3 py-2">
                    <option value="">All</option>
                    @php
                        $roles = \App\Models\Role::orderBy('name')->pluck('name')->toArray();
                    @endphp
                    @foreach ($roles as $r)
                        <option value="{{ $r }}">{{ $r }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label for="filterAssigneeId" class="block text-xs text-slate-600 mb-1">Assignee</label>
                <select id="filterAssigneeId" class="w-full rounded-md border border-gray-300 bg-white text-sm px-3 py-2">
                    <option value="">All</option>
                    @isset($users)
                        @foreach ($users as $u)
                            <option value="{{ $u->id }}">{{ $u->name }}@if (!empty($u->role))
                                    ({{ $u->role }})
                                @endif
                            </option>
                        @endforeach
                    @endisset
                </select>
            </div>
            <div>
                <label for="filterPerPage" class="block text-xs text-slate-600 mb-1">Per page</label>
                <select id="filterPerPage" class="w-full rounded-md border border-gray-300 bg-white text-sm px-3 py-2">
                    <option value="10">10</option>
                    <option value="25" selected>25</option>
                    <option value="50">50</option>
                </select>
            </div>
        </div>
        <div class="px-4 py-3 border-t flex items-center justify-end gap-2">
            <button id="resetFiltersBtn" type="button"
                class="rounded-md border border-gray-300 bg-white px-3 py-2 text-sm">Reset</button>
            <button id="applyFiltersBtn" type="button"
                class="rounded-md bg-blue-600 text-white px-4 py-2 text-sm">Apply</button>
        </div>
    </div>

    <!-- Ticket Details Modal - Modern Design from Admin Dashboard -->
    <div id="ticketModal" class="fixed inset-0 z-50 hidden overflow-y-auto">
        <div class="absolute inset-0 bg-black/60 backdrop-blur-md transition-opacity" data-modal-backdrop></div>
        <!-- Centered panel with modern minimal design -->
        <div class="relative mx-auto my-0 sm:my-8 w-full h-full sm:h-auto sm:w-[95%] max-w-2xl flex items-center">
            <div
                class="bg-white shadow-2xl w-full h-full sm:h-auto sm:max-h-[95vh] sm:max-w-2xl overflow-hidden sm:rounded-2xl flex flex-col">

                <!-- Header - Minimal & Clean -->
                <div class="px-4 sm:px-6 py-4 border-b border-gray-100 flex items-center justify-between shrink-0">
                    <div class="flex-1 min-w-0">
                        <div class="flex items-center gap-3 mb-2">
                            <h3 id="tmTicketNo" class="text-lg font-semibold text-gray-900">Ticket #</h3>
                            <span id="tmStatus"
                                class="inline-flex items-center gap-1.5 rounded-full px-3 py-1 text-xs font-medium ring-1"></span>
                        </div>
                    </div>
                    <div class="flex items-center gap-2 ml-4">
                        <!-- Options Menu in Header -->
                        <div class="relative">
                            <button type="button" id="tmOptionsBtn"
                                class="inline-flex items-center gap-2 rounded-lg px-2 py-2 text-sm font-medium text-gray-700 hover:bg-gray-100 transition-colors"
                                aria-haspopup="true" aria-expanded="false">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" viewBox="0 0 24 24"
                                    fill="currentColor">
                                    <path
                                        d="M5 12a2 2 0 114 0 2 2 0 01-4 0zm5 0a2 2 0 114 0 2 2 0 01-4 0zm5 0a2 2 0 114 0 2 2 0 01-4 0z" />
                                </svg>
                                <span class="hidden sm:inline">Options</span>
                            </button>
                            <div id="tmOptionsMenu"
                                class="absolute right-0 top-full mt-2 w-48 bg-white rounded-lg shadow-lg ring-1 ring-black/5 hidden z-10 overflow-hidden">
                                <button type="button"
                                    class="w-full text-left px-4 py-2.5 text-sm text-gray-700 hover:bg-gray-50 transition-colors"
                                    data-option="toggle-history">Show History</button>
                                <button type="button" id="tmOptionForward"
                                    class="w-full text-left px-4 py-2.5 text-sm text-gray-700 hover:bg-gray-50 transition-colors border-t border-gray-100"
                                    data-option="show-forward">Forward Ticket</button>
                            </div>
                        </div>
                        <button type="button"
                            class="text-gray-400 hover:text-gray-600 transition-colors p-1 rounded-lg hover:bg-gray-100"
                            aria-label="Close" data-modal-close>
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24"
                                stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                            </svg>
                        </button>
                    </div>
                </div>

                <!-- Content - Scrollable -->
                <div class="flex-1 overflow-y-auto px-6 py-5 space-y-5">

                    <!-- Question/Issue - Primary Focus -->
                    <div class="bg-gray-50 rounded-xl p-4 border border-gray-100">
                        <div class="flex items-center gap-2 mb-2">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 text-gray-500" fill="none"
                                viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M8.228 9c.549-1.165 2.03-2 3.772-2 2.21 0 4 1.343 4 3 0 1.4-1.278 2.575-3.006 2.907-.542.104-.994.54-.994 1.093m0 3h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                            <span class="text-xs font-medium text-gray-500 uppercase tracking-wide">Issue</span>
                        </div>
                        <div id="tmQuestion" class="text-sm text-gray-900 whitespace-pre-wrap leading-relaxed"></div>
                    </div>

                    <!-- Attachments - Visible when present -->
                    <div id="tmAttachmentsBlock" class="hidden">
                        <div class="flex items-center gap-2 mb-3">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 text-gray-500" fill="none"
                                viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M15.172 7l-6.586 6.586a2 2 0 102.828 2.828l6.414-6.586a4 4 0 00-5.656-5.656l-6.415 6.585a6 6 0 108.486 8.486L20.5 13" />
                            </svg>
                            <span class="text-xs font-medium text-gray-700">Attachments</span>
                        </div>
                        <div id="tmAttachmentsList" class="flex flex-wrap gap-2"></div>
                    </div>

                    <!-- Sent Response - For closed tickets -->
                    <div id="tmStoredResponseBlock" class="hidden bg-emerald-50 rounded-xl p-4 border border-emerald-200">
                        <div class="flex items-center gap-2 mb-2">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 text-emerald-600" fill="none"
                                viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                            <span class="text-xs font-semibold text-emerald-700 uppercase tracking-wide">Sent
                                Response</span>
                        </div>
                        <div id="tmStoredResponse" class="text-sm text-gray-800 whitespace-pre-wrap leading-relaxed">
                        </div>
                    </div>

                    <!-- Collapsible Details Section -->
                    <div id="tmDetailsSection" class="border-t border-gray-100 pt-4">
                        <button type="button" id="tmToggleDetails"
                            class="flex items-center justify-between w-full text-left group">
                            <span class="text-sm font-medium text-gray-700 group-hover:text-gray-900">Show Details</span>
                            <svg id="tmDetailsChevron" xmlns="http://www.w3.org/2000/svg"
                                class="h-4 w-4 text-gray-500 transition-transform" fill="none" viewBox="0 0 24 24"
                                stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M19 9l-7 7-7-7" />
                            </svg>
                        </button>

                        <div id="tmDetailsContent" class="hidden mt-4 space-y-4 pb-2">
                            <!-- Category & Dates -->
                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                                <div>
                                    <label
                                        class="text-xs font-medium text-gray-500 uppercase tracking-wide">Category</label>
                                    <div id="tmCategory"
                                        class="mt-1 inline-flex items-center rounded-full bg-gray-100 px-2.5 py-1 text-xs font-medium text-gray-700">
                                    </div>
                                </div>
                                <div>
                                    <label
                                        class="text-xs font-medium text-gray-500 uppercase tracking-wide">Timeline</label>
                                    <div id="tmDates" class="mt-1 text-xs text-gray-700"></div>
                                </div>
                            </div>

                            <!-- Contact Info -->
                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                                <div>
                                    <label class="text-xs font-medium text-gray-500 uppercase tracking-wide">Email</label>
                                    <div id="tmEmail" class="mt-1 text-sm text-gray-800"></div>
                                </div>
                                <div>
                                    <label class="text-xs font-medium text-gray-500 uppercase tracking-wide">Recipient
                                        ID</label>
                                    <div id="tmRecepient" class="mt-1 text-sm text-gray-800"></div>
                                </div>
                            </div>

                            <!-- Routing History -->
                            <div id="tmHistorySection" class="hidden">
                                <label class="text-xs font-medium text-gray-500 uppercase tracking-wide">Routing
                                    History</label>
                                <ul id="tmHistoryList" class="mt-2 space-y-2"></ul>
                            </div>
                        </div>
                    </div>

                    <!-- Response Input -->
                    <div class="bg-gradient-to-br from-indigo-50 to-blue-50 rounded-xl p-4 border border-indigo-100">
                        <label for="tmResponse" class="flex items-center gap-2 text-sm font-medium text-indigo-900 mb-2">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24"
                                stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" />
                            </svg>
                            Your Response
                        </label>
                        <textarea id="tmResponse"
                            class="w-full rounded-lg border-indigo-200 text-sm focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 bg-white shadow-sm resize-none"
                            rows="4" placeholder="Type your response message here..."></textarea>
                    </div>

                    <!-- Forward Controls -->
                    <div id="tmForwardControls" class="hidden">
                        <div class="flex flex-col sm:flex-row sm:items-center gap-2">
                            <label for="tmForwardSelect" class="text-xs font-medium text-gray-700">Forward to:</label>
                            <select id="tmForwardSelect"
                                class="flex-1 sm:flex-none rounded-lg border-gray-300 text-sm focus:ring-2 focus:ring-indigo-500">
                                <option value="" selected disabled>Select user</option>
                            </select>
                            <button type="button" id="tmForwardApply"
                                class="inline-flex items-center justify-center rounded-lg bg-amber-600 px-4 py-2 text-sm font-medium text-white hover:bg-amber-700 transition-colors">
                                Forward Ticket
                            </button>
                        </div>
                    </div>
                </div>

                <!-- Footer - Actions -->
                <div class="px-6 py-4 border-t border-gray-100 bg-gray-50 shrink-0">
                    <!-- Main Actions -->
                    <div class="flex flex-col sm:flex-row items-stretch sm:items-center justify-between gap-3">
                        <div class="flex items-center gap-2">

                        </div>
                        <div class="flex items-center gap-2">
                            <button type="button"
                                class="flex-1 sm:flex-none inline-flex items-center justify-center rounded-lg bg-white px-4 py-2 text-sm font-medium text-gray-700 ring-1 ring-gray-300 hover:bg-gray-50 transition-colors"
                                data-modal-close>Cancel</button>
                            <button type="button" title="Send response" aria-label="Send response"
                                class="flex-1 sm:flex-none inline-flex items-center justify-center gap-2 rounded-lg bg-indigo-600 px-5 py-2 text-sm font-medium text-white hover:bg-indigo-700 transition-colors shadow-sm"
                                id="tmSendResponse">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" viewBox="0 0 24 24"
                                    fill="currentColor">
                                    <path d="M3 12l18-9-9 18-2-7-7-2z" />
                                </svg>
                                Send Response
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

@endsection
@section('admin-scripts')
    <script>
        (function() {
            const state = document.createElement('div');
            state.id = 'admin-tickets-state';
            state.className = 'hidden';
            state.setAttribute('data-list-url', "{{ route('admin.tickets.list') }}");
            // Use a raw URL template here (avoid route() encoding the placeholder)
            state.setAttribute('data-show-url-template', "{{ url('/admin/tickets') }}/__ID__");
            state.setAttribute('data-respond-url-template', "{{ url('/admin/tickets') }}/__ID__/respond");
            state.setAttribute('data-forward-url-template', "{{ url('/admin/tickets') }}/__ID__/forward");
            state.setAttribute('data-destroy-url-template', "{{ url('/admin/tickets') }}/__ID__");
            document.body.appendChild(state);

            const LIST_URL = state.getAttribute('data-list-url');
            const SHOW_TEMPLATE = state.getAttribute('data-show-url-template');
            const RESPOND_TEMPLATE = state.getAttribute('data-respond-url-template');
            const FORWARD_TEMPLATE = state.getAttribute('data-forward-url-template');
            const DESTROY_TEMPLATE = state.getAttribute('data-destroy-url-template');
            const csrf = document.querySelector('meta[name="csrf-token"]').getAttribute('content');

            const ticketsTbody = document.getElementById('ticketsTbody');
            const ticketsPagination = document.getElementById('ticketsPagination');
            const ticketModal = document.getElementById('ticketModal');

            let currentPage = 1;
            let ticketsMap = new Map();

            function fmtDate(d) {
                try {
                    const dt = new Date(d);
                    return isNaN(dt) ? '' : dt.toLocaleString();
                } catch (_) {
                    return '';
                }
            }

            async function fetchList(page = 1, minimal = false) {
                currentPage = page;
                try {
                    // read UI filters
                    const qEl = document.getElementById('q');
                    const qMobileEl = document.getElementById('q_mobile');
                    const qVal = (qEl && qEl.value.trim()) ? qEl.value.trim() : (qMobileEl && qMobileEl.value
                    .trim() ? qMobileEl.value.trim() : '');
                    const perEl = document.getElementById('filterPerPage') || document.getElementById(
                        'perPageSelect');
                    let per = perEl ? perEl.value : '25';

                    const statusEl = document.getElementById('filterStatus');
                    const sortEl = document.getElementById('filterSort');
                    const roleEl = document.getElementById('filterRole');
                    const assigneeIdEl = document.getElementById('filterAssigneeId');
                    const assigneeEl = document.getElementById('filterAssignee'); // fallback (text input)

                    const statusVal = statusEl ? statusEl.value : '';
                    const sortVal = sortEl ? sortEl.value : '';
                    const roleVal = roleEl ? roleEl.value : '';
                    const assigneeIdVal = assigneeIdEl ? assigneeIdEl.value : '';
                    const assigneeVal = assigneeEl ? assigneeEl.value.trim() : '';

                    // When in minimal mode (used by the poller) request just 1 item to keep payload small
                    if (minimal) per = '1';

                    const sep = LIST_URL.includes('?') ? '&' : '?';
                    let url = `${LIST_URL}${sep}page=${page}&per_page=${encodeURIComponent(per)}`;

                    // Only send full filters when not doing a minimal poll
                    if (!minimal) {
                        if (qVal) url += '&q=' + encodeURIComponent(qVal);
                        if (statusVal) url += '&status=' + encodeURIComponent(statusVal);
                        if (sortVal) url += '&sort=' + encodeURIComponent(sortVal);

                        // Role param only from dropdown (pills removed)
                        if (roleVal) {
                            url += '&role=' + encodeURIComponent(roleVal);
                        }

                        if (assigneeIdVal) url += '&assignee_id=' + encodeURIComponent(assigneeIdVal);
                        else if (assigneeVal) url += '&assignee=' + encodeURIComponent(assigneeVal);
                    }

                    const res = await fetch(url, {
                        headers: {
                            'X-Requested-With': 'XMLHttpRequest'
                        }
                    });
                    if (!res.ok) throw new Error('Failed to load tickets');
                    const json = await res.json();

                    // Poll-only mode: check last_changed and update main list only when it differs
                    if (minimal) {
                        const serverLast = json.last_changed || null;
                        const localLast = Number(localStorage.getItem('ts_tickets_last_changed') || 0);
                        if (serverLast && serverLast !== localLast) {
                            // record server's last_changed so other tabs or subsequent polls are in sync
                            localStorage.setItem('ts_tickets_last_changed', String(serverLast));
                            // refresh the currently visible page to show new data
                            fetchList(currentPage);
                        }
                        return;
                    }

                    renderTable(json.items || []);
                    renderPagination(json.meta || {});
                    // store last_changed in localStorage to allow efficient cross-tab / poll comparisons
                    if (json.last_changed) {
                        try {
                            localStorage.setItem('ts_tickets_last_changed', String(json.last_changed));
                        } catch (e) {}
                    }
                } catch (err) {
                    ticketsTbody.innerHTML =
                        '<tr><td colspan="6" class="px-5 py-6 text-center text-sm text-red-600">Error loading tickets</td></tr>';
                }
            }

            function renderTable(items) {
                ticketsMap = new Map(items.map(t => [String(t.id), t]));
                if (!items.length) {
                    ticketsTbody.innerHTML =
                        '<tr><td colspan="6" class="px-5 py-10 text-center text-sm text-gray-500">No tickets found.</td></tr>';
                    return;
                }
                ticketsTbody.innerHTML = items.map(t => {
                    const ticketNo = String(t.id);
                    return `
        <tr class="hover:bg-gray-50 cursor-pointer" data-id="${t.id}">
          <td class="py-4 pl-5 pr-3">${ticketNo}</td>
          <td class="px-3 py-4">${escapeHtml(t.category||'')}</td>
          <td class="px-3 py-4">${escapeHtml((t.question||'').slice(0,80))}</td>
          <td class="px-3 py-4">${escapeHtml(t.status||'')}</td>
          <td class="px-3 py-4">${escapeHtml((t.staff && t.staff.name) || '-')}</td>
          <td class="px-3 py-4">${escapeHtml(fmtDate(t.date_created||t.created_at))}</td>
        </tr>
      `;
                }).join('');
                // Use event delegation for row clicks (more reliable with dynamic table updates)
                ticketsTbody.addEventListener('click', (e) => {
                    const tr = e.target.closest('tr');
                    if (!tr) return;
                    const id = tr.getAttribute('data-id');
                    if (!id) return;
                    openModalFor(id);
                });
            }

            function renderPagination(meta) {
                if (!meta || !meta.total) {
                    ticketsPagination.innerHTML = '';
                    return;
                }
                const total = meta.total || 0;
                const per = meta.per_page || (document.getElementById('filterPerPage') ? document.getElementById(
                    'filterPerPage').value : 25);
                const current = meta.current_page || 1;
                const last = meta.last_page || 1;

                // windowed pages
                const delta = 2;
                const left = Math.max(1, current - delta);
                const right = Math.min(last, current + delta);
                const pages = [];
                for (let i = left; i <= right; i++) pages.push(i);

                const prevDisabled = current <= 1;
                const nextDisabled = current >= last;

                ticketsPagination.innerHTML = `
      <div class="flex items-center gap-3">
        <div class="text-sm text-slate-600">Showing ${per} per page — ${total} total</div>
      </div>
      <div class="flex items-center gap-2">
        <button ${prevDisabled ? 'disabled' : ''} data-page="${current-1}" class="pagerBtn rounded-md border border-gray-200 bg-white px-3 py-1 text-sm ${prevDisabled ? 'opacity-50' : 'hover:bg-gray-50'}">Prev</button>
        ${pages.map(p => `<button data-page="${p}" class="pagerBtn rounded-md ${p===current ? 'bg-blue-600 text-white' : 'border border-gray-200 bg-white text-sm hover:bg-gray-50'} px-3 py-1">${p}</button>`).join('')}
        <button ${nextDisabled ? 'disabled' : ''} data-page="${current+1}" class="pagerBtn rounded-md border border-gray-200 bg-white px-3 py-1 text-sm ${nextDisabled ? 'opacity-50' : 'hover:bg-gray-50'}">Next</button>
      </div>
    `;

                ticketsPagination.querySelectorAll('.pagerBtn').forEach(b => b.addEventListener('click', (e) => {
                    const p = parseInt(b.getAttribute('data-page') || '1', 10);
                    if (!isNaN(p)) fetchList(p);
                }));
            }

            // Hook search and per-page controls
            const searchBtn = document.getElementById('searchBtn');
            const searchBtnMobile = document.getElementById('searchBtnMobile');
            const qInput = document.getElementById('q');
            const qMobileInput = document.getElementById('q_mobile');
            const perPageSelect = document.getElementById('filterPerPage');

            if (searchBtn) {
                searchBtn.addEventListener('click', () => fetchList(1));
            }
            if (qInput) {
                qInput.addEventListener('keyup', (e) => {
                    if (e.key === 'Enter') fetchList(1);
                });
            }
            if (searchBtnMobile) {
                searchBtnMobile.addEventListener('click', () => {
                    // copy mobile query to desktop input so UI stays consistent
                    if (qMobileInput && qInput) qInput.value = qMobileInput.value;
                    fetchList(1);
                });
            }
            if (perPageSelect) {
                perPageSelect.addEventListener('change', () => fetchList(1));
            }

            // Role filter dropdown change handler
            const roleSelect = document.getElementById('filterRole');
            if (roleSelect) {
                roleSelect.addEventListener('change', () => {
                    fetchList(1);
                });
            }

            // Filters & drawer controls (apply / reset / close)
            const applyFiltersBtn = document.getElementById('applyFiltersBtn');
            const resetFiltersBtn = document.getElementById('resetFiltersBtn');
            const closeFiltersBtn = document.getElementById('closeFiltersBtn');
            const openFiltersBtn = document.getElementById('openFiltersBtn');

            if (applyFiltersBtn) {
                applyFiltersBtn.addEventListener('click', () => {
                    const drawer = document.getElementById('ticketsBottomDrawer');
                    const overlay = document.getElementById('ticketsDrawerOverlay');
                    if (drawer) {
                        drawer.classList.add('translate-y-full');
                    }
                    if (overlay) {
                        overlay.classList.add('hidden');
                    }
                    fetchList(1);
                });
            }

            if (resetFiltersBtn) {
                resetFiltersBtn.addEventListener('click', () => {
                    const statusEl = document.getElementById('filterStatus');
                    const sortEl = document.getElementById('filterSort');
                    const roleEl = document.getElementById('filterRole');
                    const assigneeIdEl = document.getElementById('filterAssigneeId');
                    const assigneeEl = document.getElementById('filterAssignee');
                    if (statusEl) statusEl.value = '';
                    if (sortEl) sortEl.value = 'created_desc';
                    if (roleEl) roleEl.value = '';
                    if (assigneeIdEl) assigneeIdEl.value = '';
                    if (assigneeEl) assigneeEl.value = '';
                    // also clear search inputs
                    if (qInput) qInput.value = '';
                    if (qMobileInput) qMobileInput.value = '';
                    fetchList(1);
                });
            }

            if (closeFiltersBtn) {
                closeFiltersBtn.addEventListener('click', () => {
                    const drawer = document.getElementById('ticketsBottomDrawer');
                    const overlay = document.getElementById('ticketsDrawerOverlay');
                    if (drawer) {
                        drawer.classList.add('translate-y-full');
                    }
                    if (overlay) {
                        overlay.classList.add('hidden');
                    }
                });
            }

            // Overlay click closes the drawer (FAQ-style)
            const ticketsDrawerOverlayEl = document.getElementById('ticketsDrawerOverlay');
            if (ticketsDrawerOverlayEl) {
                ticketsDrawerOverlayEl.addEventListener('click', () => {
                    const drawer = document.getElementById('ticketsBottomDrawer');
                    if (drawer) drawer.classList.add('translate-y-full');
                    ticketsDrawerOverlayEl.classList.add('hidden');
                });
            }

            // Close drawer on Escape
            document.addEventListener('keydown', (e) => {
                if (e.key === 'Escape') {
                    const drawer = document.getElementById('ticketsBottomDrawer');
                    const overlay = document.getElementById('ticketsDrawerOverlay');
                    if (drawer && !drawer.classList.contains('translate-y-full')) {
                        drawer.classList.add('translate-y-full');
                        if (overlay) overlay.classList.add('hidden');
                    }
                }
            });

            if (openFiltersBtn) {
                openFiltersBtn.addEventListener('click', (e) => {
                    e.stopPropagation();
                    const drawer = document.getElementById('ticketsBottomDrawer');
                    const overlay = document.getElementById('ticketsDrawerOverlay');
                    if (!drawer || !overlay) return;
                    const isOpen = !drawer.classList.contains('translate-y-full');
                    if (isOpen) {
                        drawer.classList.add('translate-y-full');
                        overlay.classList.add('hidden');
                    } else {
                        drawer.classList.remove('translate-y-full');
                        overlay.classList.remove('hidden');
                    }
                });
            }

            function escapeHtml(s) {
                if (s == null) return '';
                return String(s).replace(/&/g, '&').replace(/</g, '<').replace(/>/g, '>').replace(/"/g, '"').replace(
                    /'/g, "&#039;");
            }

            // Modern modal functions from admin dashboard
            let currentTicketId = null;

            const statusStyles = {
                'Open': 'text-blue-700 bg-blue-50 ring-blue-600/20',
                'Forwarded': 'text-amber-700 bg-amber-50 ring-amber-600/20',
                'Closed': 'text-emerald-700 bg-emerald-50 ring-emerald-600/20',
            };

            function statusClassFor(s) {
                return statusStyles[s] || 'text-slate-700 bg-slate-50 ring-slate-600/20';
            }

            function fmtDate(d) {
                try {
                    const dt = new Date(d);
                    if (isNaN(dt.getTime())) return '';
                    return dt.toLocaleString();
                } catch (_) {
                    return '';
                }
            }

            function escapeHtml(s) {
                if (s == null) return '';
                return String(s).replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;').replace(/"/g,
                    '&quot;').replace(/'/g, "&#039;");
            }

            function ensureHistorySection() {
                let section = document.getElementById('tmHistorySection');
                const list = document.getElementById('tmHistoryList');
                return {
                    section,
                    list
                };
            }

            function renderHistory(histArr) {
                const {
                    section,
                    list
                } = ensureHistorySection();
                if (!section || !list) return;
                if (!Array.isArray(histArr) || histArr.length === 0) {
                    list.innerHTML = '<li class="text-xs text-gray-500">No routing history.</li>';
                    return;
                }
                const items = histArr.map(h => {
                    const when = fmtDate(h.routed_at || h.created_at);
                    const who = (h.staff && h.staff.name) ? h.staff.name : '-';
                    const status = h.status || '';
                    const notes = h.notes || '';
                    return `
        <li class="text-xs text-gray-700">
          <div class="flex items-start justify-between">
            <div>
              <span class="font-medium">${status}</span>
              <span class="text-gray-500"> • ${who}</span>
            </div>
            <div class="text-gray-500">${when}</div>
          </div>
          ${notes ? `<div class="text-gray-600 mt-0.5">${notes}</div>` : ''}
        </li>
      `;
                });
                list.innerHTML = items.join('');
            }

            async function openModalFor(id) {
                currentTicketId = id;
                const url = SHOW_TEMPLATE.replace('__ID__', id);
                try {
                    const res = await fetch(url, {
                        headers: {
                            'X-Requested-With': 'XMLHttpRequest'
                        },
                        credentials: 'same-origin'
                    });
                    if (!res.ok) {
                        console.error('Dashboard: failed to load ticket', res.status);
                        return;
                    }
                    const t = await res.json();
                    if (!t) return;

                    const tmTicketNo = document.getElementById('tmTicketNo');
                    const tmStatus = document.getElementById('tmStatus');
                    const tmQuestion = document.getElementById('tmQuestion');
                    const tmCategory = document.getElementById('tmCategory');
                    const tmDates = document.getElementById('tmDates');
                    const tmEmail = document.getElementById('tmEmail');
                    const tmRecepient = document.getElementById('tmRecepient');
                    const tmResponse = document.getElementById('tmResponse');

                    const ticketNo = String(t.id);
                    const createdAt = fmtDate(t.date_created || t.created_at);
                    const updatedAt = fmtDate(t.updated_at);
                    const category = t.category ?? '';
                    const question = t.question ?? '';
                    const email = t.email ?? '';
                    const recepient = t.recepient_id ?? '';

                    // Fill fields
                    if (tmTicketNo) tmTicketNo.textContent = 'Ticket #' + ticketNo;
                    if (tmDates) tmDates.textContent = createdAt ?
                        `Created ${createdAt}${updatedAt ? ' • Updated ' + updatedAt : ''}` : '';
                    if (tmStatus) {
                        tmStatus.className =
                            'inline-flex items-center gap-1.5 rounded-full px-3 py-1 text-xs font-medium ring-1 ' +
                            statusClassFor(t.status);
                        tmStatus.innerHTML =
                            `<svg xmlns="http://www.w3.org/2000/svg" class="h-3.5 w-3.5" viewBox="0 0 24 24" fill="currentColor"><circle cx="12" cy="12" r="5"></circle></svg> ${t.status ?? ''}`;
                    }
                    if (tmCategory) tmCategory.textContent = category;
                    if (tmQuestion) tmQuestion.textContent = question;
                    if (tmEmail) tmEmail.textContent = email;
                    if (tmRecepient) tmRecepient.textContent = recepient;
                    if (tmResponse) tmResponse.value = '';

                    // Reset details section to collapsed state
                    const detailsContent = document.getElementById('tmDetailsContent');
                    const detailsChevron = document.getElementById('tmDetailsChevron');
                    const toggleDetailsBtn = document.getElementById('tmToggleDetails');
                    if (detailsContent) detailsContent.classList.add('hidden');
                    if (detailsChevron) detailsChevron.style.transform = 'rotate(0deg)';
                    if (toggleDetailsBtn) toggleDetailsBtn.querySelector('span').textContent = 'Show Details';

                    // Handle attachments
                    const attachmentsBlock = document.getElementById('tmAttachmentsBlock');
                    const attachmentsList = document.getElementById('tmAttachmentsList');
                    if (attachmentsBlock && attachmentsList) {
                        attachmentsList.innerHTML = '';
                        if (t.attachments) {
                            let attachments = [];
                            try {
                                attachments = JSON.parse(t.attachments);
                            } catch (e) {
                                attachments = [];
                            }
                            if (attachments.length > 0) {
                                attachments.forEach((path, index) => {
                                    const img = document.createElement('img');
                                    img.src = '/storage/' + path;
                                    img.alt = 'Attachment ' + (index + 1);
                                    img.className =
                                        'max-w-16 max-h-16 object-cover rounded cursor-pointer border border-gray-300 hover:border-indigo-400';
                                    img.onclick = () => openLightbox(attachments, index);
                                    attachmentsList.appendChild(img);
                                });
                                attachmentsBlock.classList.remove('hidden');
                            } else {
                                attachmentsBlock.classList.add('hidden');
                            }
                        } else {
                            attachmentsBlock.classList.add('hidden');
                        }
                    }

                    // Hide forward controls initially
                    const tmForwardControls = document.getElementById('tmForwardControls');
                    if (tmForwardControls) tmForwardControls.classList.add('hidden');

                    // Prepare and render history; keep hidden by default until toggled in Options
                    const hsObj = ensureHistorySection();
                    if (hsObj.section) hsObj.section.classList.add('hidden');
                    const histories = t.routing_histories || t.routingHistories || [];
                    renderHistory(Array.isArray(histories) ? histories : []);

                    // Toggle forward option and response display based on status
                    const isClosed = (t.status === 'Closed');
                    const tmOptionForward = document.getElementById('tmOptionForward');
                    const tmOptionForwardFooter = document.getElementById('tmOptionForwardFooter');
                    const tmStoredResponseBlock = document.getElementById('tmStoredResponseBlock');
                    const tmStoredResponse = document.getElementById('tmStoredResponse');
                    const tmSendResponse = document.getElementById('tmSendResponse');

                    if (tmOptionForward) tmOptionForward.classList.toggle('hidden', isClosed);
                    if (tmOptionForwardFooter) tmOptionForwardFooter.classList.toggle('hidden', isClosed);
                    if (tmForwardControls) tmForwardControls.classList.add('hidden');
                    if (tmStoredResponseBlock) {
                        if (isClosed) {
                            tmStoredResponseBlock.classList.remove('hidden');
                            if (tmStoredResponse) tmStoredResponse.textContent = t.response ? String(t.response) :
                                'No response on record.';
                        } else {
                            tmStoredResponseBlock.classList.add('hidden');
                            if (tmStoredResponse) tmStoredResponse.textContent = '';
                        }
                    }
                    if (tmResponse) {
                        tmResponse.disabled = isClosed;
                        tmResponse.placeholder = isClosed ? 'Ticket is closed. Response cannot be edited.' :
                            'Type your response message here...';
                    }
                    if (tmSendResponse) {
                        tmSendResponse.disabled = isClosed;
                        tmSendResponse.classList.toggle('opacity-50', isClosed);
                        tmSendResponse.classList.toggle('pointer-events-none', isClosed);
                    }

                    // Populate forward select with users
                    const tmForwardSelect = document.getElementById('tmForwardSelect');
                    if (tmForwardSelect && t.users) {
                        tmForwardSelect.innerHTML = '<option value="" selected disabled>Select user</option>';
                        t.users.forEach(user => {
                            const option = document.createElement('option');
                            option.value = user.id;
                            option.textContent = user.name;
                            tmForwardSelect.appendChild(option);
                        });
                    }

                    if (ticketModal) {
                        ticketModal.classList.remove('hidden');
                        document.body.classList.add('overflow-hidden');
                    }
                } catch (err) {
                    console.error('Dashboard: error loading ticket', err);
                }
            }

            function closeModal() {
                if (!ticketModal) return;
                ticketModal.classList.add('hidden');
                document.body.classList.remove('overflow-hidden');
                currentTicketId = null;
            }

            // Lightbox functions
            let currentLightboxImages = [];
            let currentLightboxIndex = 0;

            function openLightbox(images, index) {
                currentLightboxImages = images;
                currentLightboxIndex = index;
                const lightbox = document.getElementById('imageLightbox');
                const img = document.getElementById('lightboxImage');
                if (lightbox && img) {
                    img.src = '/storage/' + images[index];
                    lightbox.classList.remove('hidden');
                    document.body.classList.add('overflow-hidden');
                    updateLightboxButtons();
                }
            }

            function closeLightbox() {
                const lightbox = document.getElementById('imageLightbox');
                if (lightbox) {
                    lightbox.classList.add('hidden');
                    document.body.classList.remove('overflow-hidden');
                }
            }

            function updateLightboxButtons() {
                const prevBtn = document.getElementById('lightboxPrev');
                const nextBtn = document.getElementById('lightboxNext');
                if (prevBtn) prevBtn.style.display = currentLightboxIndex > 0 ? 'flex' : 'none';
                if (nextBtn) nextBtn.style.display = currentLightboxIndex < currentLightboxImages.length - 1 ? 'flex' :
                    'none';
            }

            function prevImage() {
                if (currentLightboxIndex > 0) {
                    currentLightboxIndex--;
                    const img = document.getElementById('lightboxImage');
                    if (img) img.src = '/storage/' + currentLightboxImages[currentLightboxIndex];
                    updateLightboxButtons();
                }
            }

            function nextImage() {
                if (currentLightboxIndex < currentLightboxImages.length - 1) {
                    currentLightboxIndex++;
                    const img = document.getElementById('lightboxImage');
                    if (img) img.src = '/storage/' + currentLightboxImages[currentLightboxIndex];
                    updateLightboxButtons();
                }
            }

            // inline save edit
            async function saveEdit(id, payload) {
                try {
                    const upUrl = "{{ url('/admin/tickets') }}/" + id;
                    const res = await fetch(upUrl, {
                        method: 'PUT',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': csrf
                        },
                        body: JSON.stringify(payload)
                    });
                    if (!res.ok) throw new Error('Failed to update');
                    try {
                        localStorage.setItem('ts_tickets_changed', String(Date.now()));
                    } catch (e) {}
                    fetchList(currentPage);
                } catch (err) {
                    console.error(err);
                    alert('Update failed');
                }
            }

            async function deleteTicket(id) {
                try {
                    const dUrl = DESTROY_TEMPLATE.replace('__ID__', id);
                    const res = await fetch(dUrl, {
                        method: 'DELETE',
                        headers: {
                            'X-CSRF-TOKEN': csrf
                        }
                    });
                    if (!res.ok) throw new Error('Failed to delete');
                    try {
                        localStorage.setItem('ts_tickets_changed', String(Date.now()));
                    } catch (e) {}
                    fetchList(currentPage);
                } catch (err) {
                    console.error(err);
                    alert('Delete failed');
                }
            }

            // Modern Modal Event Handlers from Admin Dashboard
            // Toggle Details Section
            const tmToggleDetails = document.getElementById('tmToggleDetails');
            const tmDetailsContent = document.getElementById('tmDetailsContent');
            const tmDetailsChevron = document.getElementById('tmDetailsChevron');

            if (tmToggleDetails && tmDetailsContent && tmDetailsChevron) {
                tmToggleDetails.addEventListener('click', () => {
                    const isHidden = tmDetailsContent.classList.contains('hidden');
                    tmDetailsContent.classList.toggle('hidden');
                    tmDetailsChevron.style.transform = isHidden ? 'rotate(180deg)' : 'rotate(0deg)';
                    tmToggleDetails.querySelector('span').textContent = isHidden ? 'Hide Details' :
                        'Show Details';
                });
            }

            // Options dropdown handlers
            function setupOptionsMenu(optionsBtnId, optionsMenuId) {
                const tmOptionsBtn = document.getElementById(optionsBtnId);
                const tmOptionsMenu = document.getElementById(optionsMenuId);

                if (!tmOptionsBtn || !tmOptionsMenu) return false;

                tmOptionsBtn.addEventListener('click', (e) => {
                    e.stopPropagation();
                    const isOpen = !tmOptionsMenu.classList.contains('hidden');
                    tmOptionsMenu.classList.toggle('hidden', isOpen);
                    tmOptionsBtn.setAttribute('aria-expanded', String(!isOpen));
                });

                document.addEventListener('click', (e) => {
                    if (!tmOptionsMenu.contains(e.target) && !tmOptionsBtn.contains(e.target)) {
                        tmOptionsMenu.classList.add('hidden');
                        tmOptionsBtn.setAttribute('aria-expanded', 'false');
                    }
                });

                tmOptionsMenu.addEventListener('click', (e) => {
                    const btn = e.target.closest('[data-option]');
                    if (!btn) return;
                    const action = btn.getAttribute('data-option');

                    // Hide menu after action
                    tmOptionsMenu.classList.add('hidden');
                    tmOptionsBtn.setAttribute('aria-expanded', 'false');

                    if (action === 'toggle-history') {
                        const hs = ensureHistorySection().section;
                        if (hs) {
                            const willShow = hs.classList.contains('hidden');
                            hs.classList.toggle('hidden');
                            btn.textContent = willShow ? 'Hide History' : 'Show History';
                        }
                    } else if (action === 'show-forward') {
                        const tmForwardControls = document.getElementById('tmForwardControls');
                        if (tmForwardControls) tmForwardControls.classList.remove('hidden');
                    }
                });

                return true;
            }

            // Setup both options menus
            setupOptionsMenu('tmOptionsBtn', 'tmOptionsMenu');
            setupOptionsMenu('tmOptionsBtnFooter', 'tmOptionsMenuFooter');

            // Forward via select + apply with SweetAlert
            const tmForwardApply = document.getElementById('tmForwardApply');
            const tmForwardSelect = document.getElementById('tmForwardSelect');

            if (tmForwardApply && tmForwardSelect) {
                tmForwardApply.addEventListener('click', async () => {
                    if (!currentTicketId) return;
                    if (!tmForwardSelect.value) {
                        if (window.Swal) {
                            Swal.fire({
                                icon: 'warning',
                                title: 'Selection Required',
                                text: 'Please choose a user to forward to.',
                                confirmButtonText: 'OK'
                            });
                        } else {
                            alert('Please choose a user to forward to.');
                        }
                        return;
                    }
                    const userId = tmForwardSelect.value;
                    const fUrl = FORWARD_TEMPLATE.replace('__ID__', currentTicketId);
                    try {
                        const res = await fetch(fUrl, {
                            method: 'POST',
                            headers: {
                                'Accept': 'application/json',
                                'Content-Type': 'application/json',
                                'X-CSRF-TOKEN': csrf
                            },
                            credentials: 'same-origin',
                            body: JSON.stringify({
                                user_id: userId
                            })
                        });
                        console.log('Forward request sent to:', fUrl);
                        console.log('Response status:', res.status, res.statusText);
                        if (res.ok) {
                            const data = await res.json();
                            console.log('Forward successful:', data);
                            if (window.Swal) {
                                Swal.fire({
                                    icon: 'success',
                                    title: 'Ticket Forwarded',
                                    text: 'Ticket has been forwarded successfully!',
                                    timer: 3000,
                                    timerProgressBar: true,
                                    showConfirmButton: false,
                                    position: 'top-end',
                                    toast: true
                                });
                            } else {
                                alert('Ticket forwarded successfully.');
                            }
                            closeModal();
                            try {
                                localStorage.setItem('ts_tickets_changed', String(Date.now()));
                            } catch (e) {}
                            fetchList(currentPage);
                        } else {
                            const errorText = await res.text();
                            console.error('Forward failed', res.status, errorText);
                            if (window.Swal) {
                                Swal.fire({
                                    icon: 'error',
                                    title: 'Forward Failed',
                                    text: 'Failed to forward ticket. Please try again. Error: ' +
                                        res.status + ' ' + res.statusText,
                                    confirmButtonText: 'OK'
                                });
                            } else {
                                alert('Forward failed. Please try again. Error: ' + res.status + ' ' + res
                                    .statusText);
                            }
                        }
                    } catch (err) {
                        console.error('Forward error', err);
                        if (window.Swal) {
                            Swal.fire({
                                icon: 'error',
                                title: 'Network Error',
                                text: 'Network error during forward.',
                                confirmButtonText: 'OK'
                            });
                        } else {
                            alert('Network error during forward.');
                        }
                    }
                });
            }

            // Send response with SweetAlert
            const tmSendResponse = document.getElementById('tmSendResponse');
            const tmResponse = document.getElementById('tmResponse');

            if (tmSendResponse && tmResponse) {
                tmSendResponse.addEventListener('click', async () => {
                    const msg = tmResponse.value.trim();
                    if (!msg) {
                        if (window.Swal) {
                            Swal.fire({
                                icon: 'warning',
                                title: 'Message Required',
                                text: 'Please enter a response message.',
                                confirmButtonText: 'OK'
                            });
                        } else {
                            alert('Please enter a response message.');
                        }
                        return;
                    }
                    if (!currentTicketId) {
                        if (window.Swal) {
                            Swal.fire({
                                icon: 'error',
                                title: 'No Ticket Selected',
                                text: 'No ticket selected.',
                                confirmButtonText: 'OK'
                            });
                        } else {
                            alert('No ticket selected.');
                        }
                        return;
                    }
                    try {
                        tmSendResponse.disabled = true;
                        tmSendResponse.classList.add('opacity-50', 'pointer-events-none');
                        const rUrl = RESPOND_TEMPLATE.replace('__ID__', currentTicketId);
                        const res = await fetch(rUrl, {
                            method: 'POST',
                            headers: {
                                'Accept': 'application/json',
                                'Content-Type': 'application/json',
                                'X-CSRF-TOKEN': csrf
                            },
                            credentials: 'same-origin',
                            body: JSON.stringify({
                                message: msg
                            })
                        });
                        if (res.ok) {
                            if (window.Swal) {
                                Swal.fire({
                                    icon: 'success',
                                    title: 'Response Sent',
                                    text: 'Response email sent successfully!',
                                    timer: 3000,
                                    timerProgressBar: true,
                                    showConfirmButton: false,
                                    position: 'top-end',
                                    toast: true
                                });
                            } else {
                                alert('Response email sent successfully.');
                            }
                            tmResponse.value = '';
                            closeModal();
                            try {
                                localStorage.setItem('ts_tickets_changed', String(Date.now()));
                            } catch (e) {}
                            fetchList(currentPage);
                        } else {
                            const txt = await res.text();
                            console.error('Send response failed', txt);
                            if (window.Swal) {
                                Swal.fire({
                                    icon: 'error',
                                    title: 'Failed to Send Response',
                                    text: 'Failed to send response. Please check mail configuration.',
                                    confirmButtonText: 'OK'
                                });
                            } else {
                                alert('Failed to send response. Please check mail configuration.');
                            }
                        }
                    } catch (err) {
                        console.error('Send response error', err);
                        if (window.Swal) {
                            Swal.fire({
                                icon: 'error',
                                title: 'Network Error',
                                text: 'Network error while sending response.',
                                confirmButtonText: 'OK'
                            });
                        } else {
                            alert('Network error while sending response.');
                        }
                    } finally {
                        tmSendResponse.disabled = false;
                        tmSendResponse.classList.remove('opacity-50', 'pointer-events-none');
                    }
                });
            }

            // Close modal handlers
            document.addEventListener('click', (e) => {
                if (e.target && e.target.closest('[data-modal-backdrop]')) {
                    closeModal();
                }
                if (e.target && e.target.getAttribute && e.target.getAttribute('data-modal-close') != null) {
                    closeModal();
                }
                if (e.target && e.target.closest && e.target.closest('[data-modal-close]')) {
                    closeModal();
                }
            });

            // Escape key to close modal
            document.addEventListener('keydown', (e) => {
                if (e.key === 'Escape' && ticketModal && !ticketModal.classList.contains('hidden')) {
                    closeModal();
                }
            });

            // Lightbox event listeners
            const lightboxCloseBtn = document.getElementById('lightboxClose');
            const lightboxPrevBtn = document.getElementById('lightboxPrev');
            const lightboxNextBtn = document.getElementById('lightboxNext');
            const lightboxEl = document.getElementById('imageLightbox');

            if (lightboxCloseBtn) lightboxCloseBtn.addEventListener('click', closeLightbox);
            if (lightboxPrevBtn) lightboxPrevBtn.addEventListener('click', prevImage);
            if (lightboxNextBtn) lightboxNextBtn.addEventListener('click', nextImage);

            // Close lightbox on background click
            if (lightboxEl) {
                lightboxEl.addEventListener('click', (e) => {
                    if (e.target === lightboxEl) closeLightbox();
                });
            }

            // Keyboard navigation for lightbox
            document.addEventListener('keydown', (e) => {
                if (lightboxEl && !lightboxEl.classList.contains('hidden')) {
                    if (e.key === 'Escape') closeLightbox();
                    else if (e.key === 'ArrowLeft') prevImage();
                    else if (e.key === 'ArrowRight') nextImage();
                }
            });

            // Cross-tab refresh listeners
            window.addEventListener('storage', (e) => {
                if (e && e.key === 'ts_tickets_changed') fetchList(currentPage);
            });
            window.addEventListener('focus', () => {
                try {
                    if (localStorage.getItem('ts_tickets_changed')) fetchList(currentPage);
                } catch (_) {}
            });
            document.addEventListener('visibilitychange', () => {
                try {
                    if (!document.hidden && localStorage.getItem('ts_tickets_changed')) fetchList(currentPage);
                } catch (_) {}
            });

            // Lightweight auto-reload poller using minimal payload (no backend DB pooling/optimization changes)
            let ticketsPollTimer = null;

            function startTicketsPoller() {
                if (ticketsPollTimer) clearInterval(ticketsPollTimer);
                ticketsPollTimer = setInterval(() => {
                    // minimal=true: server should return last_changed; only refresh when it differs
                    fetchList(currentPage, true);
                }, 15000); // 15s cadence
            }

            function stopTicketsPoller() {
                if (ticketsPollTimer) {
                    clearInterval(ticketsPollTimer);
                    ticketsPollTimer = null;
                }
            }

            // Pause/resume polling with page lifecycle
            document.addEventListener('visibilitychange', () => {
                if (document.hidden) stopTicketsPoller();
                else startTicketsPoller();
            });
            window.addEventListener('focus', startTicketsPoller);
            window.addEventListener('blur', stopTicketsPoller);

            // initial load
            fetchList(1);
            // start background poller
            startTicketsPoller();
        })();
    </script>
@endsection
