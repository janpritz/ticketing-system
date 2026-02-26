<!-- FAQ Management -->
@extends('layouts.admin')

@section('title', 'FAQ Management')

@section('admin-content')
    <div class="sm:px-2">
        <!-- Header -->
        <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4">
            <div>
                <h1 class="text-2xl font-semibold text-slate-900">FAQ Management</h1>
                <p class="text-sm text-slate-500 mt-1">Review and approve FAQs from tickets</p>
            </div>
            <div class="flex items-center gap-3">
                <!-- Desktop filters (hidden on mobile) -->
                <div class="hidden md:flex items-center gap-2">
                    <label class="relative block">
                        <span class="sr-only">Search</span>
                        <span class="absolute inset-y-0 left-0 flex items-center pl-3 text-gray-400">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" viewBox="0 0 24 24" fill="currentColor">
                                <path
                                    d="M15.5 14h-.79l-.28-.27a6.5 6.5 0 10-.71.71l.27.28v.79L20 21.5 21.5 20l-6-6zM10 15a5 5 0 110-10 5 5 0 010 10z" />
                            </svg>
                        </span>
                        <input id="q" type="text" placeholder="Search FAQs..."
                            class="pl-9 pr-3 py-2 rounded-md border border-gray-200 text-sm w-72" />
                    </label>
                    <button id="searchBtn"
                        class="rounded-md border border-gray-300 bg-white px-3 py-2 text-sm">Search</button>
                </div>

                <!-- Mobile search -->
                <div class="md:hidden flex items-center gap-2 w-full">
                    <input id="q_mobile" type="text" placeholder="Search FAQs..."
                        class="pl-3 pr-3 py-2 rounded-md border border-gray-200 text-sm w-full" />
                    <button id="searchBtnMobile"
                        class="rounded-md border border-gray-300 bg-white px-3 py-2 text-sm">Search</button>
                </div>

                <!-- Analyze Tickets Button -->
                <button onclick="openAnalyzeModal()"
                    class="ml-2 rounded-md border border-gray-200 bg-white px-3 py-2 text-sm inline-flex items-center gap-2">
                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor"
                        class="bi bi-bar-chart-steps" viewBox="0 0 16 16">
                        <path
                            d="M.5 0a.5.5 0 0 1 .5.5v15a.5.5 0 0 1-1 0V.5A.5.5 0 0 1 .5 0M2 1.5a.5.5 0 0 1 .5-.5h4a.5.5 0 0 1 .5.5v1a.5.5 0 0 1-.5.5h-4a.5.5 0 0 1-.5-.5zm2 4a.5.5 0 0 1 .5-.5h7a.5.5 0 0 1 .5.5v1a.5.5 0 0 1-.5.5h-7a.5.5 0 0 1-.5-.5zm2 4a.5.5 0 0 1 .5-.5h6a.5.5 0 0 1 .5.5v1a.5.5 0 0 1-.5.5h-6a.5.5 0 0 1-.5-.5zm2 4a.5.5 0 0 1 .5-.5h7a.5.5 0 0 1 .5.5v1a.5.5 0 0 1-.5.5h-7a.5.5 0 0 1-.5-.5z" />
                    </svg>
                    <span class="hidden md:inline">Analyze Tickets</span>
                </button>

                <!-- Filters Button (Mobile) -->
                <button id="openFiltersBtn"
                    class="ml-2 rounded-md border border-gray-200 bg-white px-3 py-2 text-sm">Filters</button>
            </div>
        </div>

        <!-- Table Container with Horizontal Scroll -->
        <div class="mt-4 bg-white rounded-xl border border-gray-200 overflow-hidden">
            <div class="overflow-x-auto">
                <table id="faqsTable" class="min-w-full text-sm">

                    <thead class="bg-gray-50 text-gray-600">
                        <tr>
                            <th class="py-3 pl-5 pr-3 text-left font-medium">Topic</th>
                            <th class="px-3 py-3 text-left font-medium">Question</th>
                            <th class="px-3 py-3 text-left font-medium">Answer</th>
                            <th class="px-3 py-3 text-left font-medium">Status</th>
                            <th class="px-3 py-3 text-left font-medium">Actions</th>
                        </tr>
                    </thead>
                    <tbody id="faqsTbody" class="divide-y divide-gray-100">
                        <tr>
                            <td colspan="5" class="px-5 py-6 text-center text-sm text-gray-500">Loading...</td>
                        </tr>
                    </tbody>
                </table>
            </div>
            <div id="faqsFooter" class="px-5 py-3 border-t border-gray-200">
                <div id="faqsPagination" class="flex items-center justify-between"></div>
            </div>
        </div>
    </div>

    <!-- Drawer Overlay -->
    <div id="faqsDrawerOverlay" class="hidden fixed inset-0 bg-black/30 z-40"></div>

    <!-- Bottom Drawer: Filters -->
    <div id="faqsBottomDrawer"
        class="fixed inset-x-0 bottom-0 z-50 bg-white border-t border-gray-200 shadow-lg transform translate-y-full transition-transform duration-200">
        <div class="px-4 py-3 flex items-center justify-between border-b">
            <div class="text-sm font-semibold text-slate-800">Filters</div>
            <button id="closeFiltersBtn" type="button"
                class="p-2 rounded-md text-slate-600 hover:text-slate-800 hover:bg-gray-50" aria-label="Close filters">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 24 24" fill="currentColor">
                    <path d="M6 18 18 6M6 6l12 12" />
                </svg>
            </button>
        </div>
        <div class="px-4 py-3 grid grid-cols-1 sm:grid-cols-3 gap-3">
            <div>
                <label for="filterStatus" class="block text-xs text-slate-600 mb-1">Status</label>
                <select id="filterStatus" class="w-full rounded-md border border-gray-300 bg-white text-sm px-3 py-2">
                    <option value="pending" selected>Pending</option>
                    <option value="approved">Approved</option>
                    <option value="rejected">Rejected</option>
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

    <!-- Success Notification -->
    <div id="success-notification"
        class="fixed top-4 right-4 bg-green-500 text-white px-4 py-2 rounded-lg shadow-lg z-[9999] hidden">
        FAQ approved successfully!
    </div>

    <!-- Error Notification -->
    <div id="error-notification"
        class="fixed top-4 right-4 bg-red-500 text-white px-4 py-2 rounded-lg shadow-lg z-[9999] hidden">
        Failed to approve FAQ. Please try again.
    </div>

    <!-- Analyze Tickets Modal - Modern Design -->
    <div id="analyze-modal" class="fixed inset-0 z-50 hidden overflow-y-auto">
        <div class="absolute inset-0 bg-black/60 backdrop-blur-md transition-opacity" data-modal-backdrop></div>
        <!-- Centered panel with modern minimal design -->
        <div class="relative mx-auto my-0 sm:my-8 w-full h-full sm:h-auto sm:w-[95%] max-w-2xl flex items-center">
            <div
                class="bg-white shadow-2xl w-full h-full sm:h-auto sm:max-h-[95vh] sm:max-w-2xl overflow-hidden sm:rounded-2xl flex flex-col">

                <!-- Header - Minimal & Clean -->
                <div class="px-4 sm:px-6 py-4 border-b border-gray-100 flex items-center justify-between shrink-0">
                    <div class="flex-1 min-w-0">
                        <h3 class="text-lg font-semibold text-gray-900">Analyze Tickets for FAQ Generation</h3>
                        <p class="text-sm text-gray-500 mt-1">Process closed tickets using OpenAI to generate FAQ clusters
                        </p>
                    </div>
                    <button type="button"
                        class="text-gray-400 hover:text-gray-600 transition-colors p-1 rounded-lg hover:bg-gray-100"
                        aria-label="Close" onclick="closeAnalyzeModal()">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24"
                            stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </button>
                </div>

                <!-- Content - Scrollable -->
                <div class="flex-1 overflow-y-auto px-6 py-5 space-y-5">

                    <!-- Unprocessed Tickets Info -->
                    <div class="bg-blue-50 rounded-xl p-4 border border-blue-100">
                        <div class="flex items-center gap-2 mb-2">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 text-blue-600" fill="none"
                                viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                            <span class="text-xs font-medium text-blue-700 uppercase tracking-wide">Unprocessed
                                Tickets</span>
                        </div>
                        <div class="text-2xl font-bold text-blue-900" id="unprocessed-count">0</div>
                        <p class="text-blue-700 text-xs mt-2">
                            These closed tickets will be analyzed by OpenAI to generate FAQ clusters.
                        </p>
                    </div>

                    <!-- Progress Section (Hidden by default) -->
                    <div id="progress-section" class="hidden space-y-3">
                        <div class="w-full bg-gray-200 rounded-full h-2">
                            <div id="progress-bar" class="bg-blue-600 h-2 rounded-full transition-all duration-300"
                                style="width: 0%"></div>
                        </div>
                        <p id="progress-text" class="text-sm text-gray-600">Initializing analysis...</p>
                    </div>

                    <!-- Results Section (Hidden by default) -->
                    <div id="results-section" class="hidden space-y-3">
                        <div class="bg-emerald-50 rounded-xl p-4 border border-emerald-200">
                            <div class="flex items-center gap-2 mb-3">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-emerald-600" fill="none"
                                    viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                                </svg>
                                <span class="text-sm font-semibold text-emerald-700">Analysis Completed!</span>
                            </div>
                            <ul class="text-emerald-700 text-sm space-y-2">
                                <li class="flex justify-between">
                                    <span>Tickets Processed:</span>
                                    <span class="font-semibold" id="tickets-processed">0</span>
                                </li>
                                <li class="flex justify-between">
                                    <span>FAQs Generated:</span>
                                    <span class="font-semibold" id="faqs-generated">0</span>
                                </li>
                            </ul>
                        </div>
                    </div>
                </div>

                <!-- Footer - Actions -->
                <div class="px-6 py-4 border-t border-gray-100 bg-gray-50 shrink-0">
                    <div class="flex flex-col sm:flex-row items-stretch sm:items-center justify-between gap-3">
                        <div></div>
                        <div class="flex items-center gap-2">
                            <button type="button"
                                class="flex-1 sm:flex-none inline-flex items-center justify-center rounded-lg bg-white px-4 py-2 text-sm font-medium text-gray-700 ring-1 ring-gray-300 hover:bg-gray-50 transition-colors"
                                onclick="closeAnalyzeModal()">Cancel</button>
                            <button type="button"
                                class="flex-1 sm:flex-none inline-flex items-center justify-center gap-2 rounded-lg bg-green-600 px-5 py-2 text-sm font-medium text-white hover:bg-green-700 transition-colors shadow-sm disabled:opacity-50 disabled:cursor-not-allowed"
                                id="analyze-btn" onclick="startAnalysis()">
                                <!-- icon shown when idle -->
                                <svg id="analyzeIcon" xmlns="http://www.w3.org/2000/svg" class="h-4 w-4"
                                    viewBox="0 0 24 24" fill="currentColor">
                                    <path
                                        d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm0 18c-4.41 0-8-3.59-8-8s3.59-8 8-8 8 3.59 8 8-3.59 8-8 8zm3.5-9c.83 0 1.5-.67 1.5-1.5S16.33 8 15.5 8 14 8.67 14 9.5s.67 1.5 1.5 1.5zm-7 0c.83 0 1.5-.67 1.5-1.5S9.33 8 8.5 8 7 8.67 7 9.5 7.67 11 8.5 11zm3.5 6.5c2.33 0 4.31-1.46 5.11-3.5H6.89c.8 2.04 2.78 3.5 5.11 3.5z" />
                                </svg>
                                <!-- spinner shown while requesting -->
                                <svg id="analyzeSpinner" xmlns="http://www.w3.org/2000/svg"
                                    class="h-4 w-4 animate-spin hidden" viewBox="0 0 24 24" fill="none"
                                    stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M12 2v4M12 18v4M4.93 4.93l2.83 2.83M16.24 16.24l2.83 2.83M2 12h4M18 12h4M4.93 19.07l2.83-2.83M16.24 7.76l2.83-2.83" />
                                </svg>
                                <span id="analyzeText">Start Analysis</span>
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('admin-scripts')
    @parent
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script>
        (function() {
            const state = document.createElement('div');
            state.id = 'admin-faqs-state';
            state.className = 'hidden';
            state.setAttribute('data-list-url', "{{ route('admin.faqs.list') ?? route('admin.faqs.index') }}");
            state.setAttribute('data-update-status-url', "{{ route('admin.faqs.update-status') }}");
            document.body.appendChild(state);

            const LIST_URL = state.getAttribute('data-list-url');
            const UPDATE_STATUS_URL = state.getAttribute('data-update-status-url');
            const csrf = document.querySelector('meta[name="csrf-token"]').getAttribute('content');

            const faqsTbody = document.getElementById('faqsTbody');
            const faqsPagination = document.getElementById('faqsPagination');

            let currentPage = 1;
            let faqsMap = new Map();

            function escapeHtml(s) {
                if (s == null) return '';
                return String(s).replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;').replace(/"/g,
                    '&quot;').replace(/'/g, "&#039;");
            }

            function getStatusClass(status) {
                const classes = {
                    'approved': 'text-green-700 bg-green-50 ring-green-600/20',
                    'pending': 'text-yellow-700 bg-yellow-50 ring-yellow-600/20',
                    'rejected': 'text-red-700 bg-red-50 ring-red-600/20'
                };
                return classes[status] || 'text-slate-700 bg-slate-50 ring-slate-600/20';
            }

            async function fetchList(page = 1) {
                currentPage = page;
                try {
                    const qEl = document.getElementById('q');
                    const qMobileEl = document.getElementById('q_mobile');
                    const qVal = (qEl && qEl.value.trim()) ? qEl.value.trim() : (qMobileEl && qMobileEl.value
                    .trim() ? qMobileEl.value.trim() : '');

                    const statusEl = document.getElementById('filterStatus');
                    const perEl = document.getElementById('filterPerPage');

                    const statusVal = statusEl ? statusEl.value : 'pending';
                    const per = perEl ? perEl.value : '25';

                    const sep = LIST_URL.includes('?') ? '&' : '?';
                    let url =
                        `${LIST_URL}${sep}page=${page}&per_page=${encodeURIComponent(per)}&status=${encodeURIComponent(statusVal)}`;

                    if (qVal) url += '&search=' + encodeURIComponent(qVal);

                    const res = await fetch(url, {
                        headers: {
                            'X-Requested-With': 'XMLHttpRequest'
                        }
                    });
                    if (!res.ok) throw new Error('Failed to load FAQs');
                    const json = await res.json();
                    renderTable(json.items || []);
                    renderPagination(json.meta || {});
                } catch (err) {
                    console.error('Error loading FAQs:', err);
                    faqsTbody.innerHTML =
                        '<tr><td colspan="5" class="px-5 py-6 text-center text-sm text-red-600">Error loading FAQs</td></tr>';
                }
            }

            function renderTable(items) {
                faqsMap = new Map(items.map(f => [String(f.id), f]));
                if (!items.length) {
                    faqsTbody.innerHTML =
                        '<tr><td colspan="5" class="px-5 py-10 text-center text-sm text-gray-500">No FAQs found.</td></tr>';
                    return;
                }
                faqsTbody.innerHTML = items.map(f => {
                    const actionButtons = getActionButtons(f);
                    return `
                <tr class="hover:bg-gray-50">
                    <td class="py-4 pl-5 pr-3">${escapeHtml((f.general_topic || '').slice(0, 50))}</td>
                    <td class="px-3 py-4">${escapeHtml((f.suggested_q || '').slice(0, 80))}</td>
                    <td class="px-3 py-4">${escapeHtml((f.suggested_a || '').slice(0, 100))}</td>
                    <td class="px-3 py-4"><span class="inline-flex items-center gap-1.5 rounded-full px-3 py-1 text-xs font-medium ring-1 ${getStatusClass(f.status)}">${escapeHtml(f.status || '')}</span></td>
                    <td class="px-3 py-4 space-x-2">${actionButtons}</td>
                </tr>
            `;
                }).join('');
            }

            function getActionButtons(faq) {
                if (faq.status === 'pending') {
                    return `
                <div class="flex gap-2">
                    <button onclick="updateFAQStatus(${faq.id}, 'approved')" class="px-3 py-1 bg-green-600 text-white text-xs rounded hover:bg-green-700">Approve</button>
                    <button onclick="updateFAQStatus(${faq.id}, 'rejected')" class="px-3 py-1 bg-red-600 text-white text-xs rounded hover:bg-red-700">Reject</button>
                </div>
            `;
                } else if (faq.status === 'approved') {
                    return `<button onclick="updateFAQStatus(${faq.id}, 'rejected')" class="px-3 py-1 bg-red-600 text-white text-xs rounded hover:bg-red-700">Reject</button>`;
                } else if (faq.status === 'rejected') {
                    return `<button onclick="updateFAQStatus(${faq.id}, 'approved')" class="px-3 py-1 bg-green-600 text-white text-xs rounded hover:bg-green-700">Approve</button>`;
                }
                return '';
            }

            function renderPagination(meta) {
                if (!meta || !meta.total) {
                    faqsPagination.innerHTML = '';
                    return;
                }
                const total = meta.total || 0;
                const per = meta.per_page || 25;
                const current = meta.current_page || 1;
                const last = meta.last_page || 1;

                const delta = 2;
                const left = Math.max(1, current - delta);
                const right = Math.min(last, current + delta);
                const pages = [];
                for (let i = left; i <= right; i++) pages.push(i);

                const prevDisabled = current <= 1;
                const nextDisabled = current >= last;

                faqsPagination.innerHTML = `
            <div class="flex items-center gap-3">
                <div class="text-sm text-slate-600">Showing ${per} per page — ${total} total</div>
            </div>
            <div class="flex items-center gap-2">
                <button ${prevDisabled ? 'disabled' : ''} data-page="${current-1}" class="pagerBtn rounded-md border border-gray-200 bg-white px-3 py-1 text-sm ${prevDisabled ? 'opacity-50' : 'hover:bg-gray-50'}">Prev</button>
                ${pages.map(p => `<button data-page="${p}" class="pagerBtn rounded-md ${p===current ? 'bg-blue-600 text-white' : 'border border-gray-200 bg-white text-sm hover:bg-gray-50'} px-3 py-1">${p}</button>`).join('')}
                <button ${nextDisabled ? 'disabled' : ''} data-page="${current+1}" class="pagerBtn rounded-md border border-gray-200 bg-white px-3 py-1 text-sm ${nextDisabled ? 'opacity-50' : 'hover:bg-gray-50'}">Next</button>
            </div>
        `;

                faqsPagination.querySelectorAll('.pagerBtn').forEach(b => b.addEventListener('click', (e) => {
                    const p = parseInt(b.getAttribute('data-page') || '1', 10);
                    if (!isNaN(p)) fetchList(p);
                }));
            }

            // Search handlers
            const searchBtn = document.getElementById('searchBtn');
            const searchBtnMobile = document.getElementById('searchBtnMobile');
            const qInput = document.getElementById('q');
            const qMobileInput = document.getElementById('q_mobile');

            if (searchBtn) searchBtn.addEventListener('click', () => fetchList(1));
            if (qInput) qInput.addEventListener('keyup', (e) => {
                if (e.key === 'Enter') fetchList(1);
            });
            if (searchBtnMobile) searchBtnMobile.addEventListener('click', () => {
                if (qMobileInput && qInput) qInput.value = qMobileInput.value;
                fetchList(1);
            });

            // Filter drawer handlers
            const openFiltersBtn = document.getElementById('openFiltersBtn');
            const closeFiltersBtn = document.getElementById('closeFiltersBtn');
            const applyFiltersBtn = document.getElementById('applyFiltersBtn');
            const resetFiltersBtn = document.getElementById('resetFiltersBtn');
            const drawer = document.getElementById('faqsBottomDrawer');
            const overlay = document.getElementById('faqsDrawerOverlay');

            if (openFiltersBtn) {
                openFiltersBtn.addEventListener('click', (e) => {
                    e.stopPropagation();
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

            if (closeFiltersBtn) {
                closeFiltersBtn.addEventListener('click', () => {
                    drawer.classList.add('translate-y-full');
                    overlay.classList.add('hidden');
                });
            }

            if (applyFiltersBtn) {
                applyFiltersBtn.addEventListener('click', () => {
                    drawer.classList.add('translate-y-full');
                    overlay.classList.add('hidden');
                    fetchList(1);
                });
            }

            if (resetFiltersBtn) {
                resetFiltersBtn.addEventListener('click', () => {
                    const statusEl = document.getElementById('filterStatus');
                    const perEl = document.getElementById('filterPerPage');
                    if (statusEl) statusEl.value = 'pending';
                    if (perEl) perEl.value = '25';
                    if (qInput) qInput.value = '';
                    if (qMobileInput) qMobileInput.value = '';
                    fetchList(1);
                });
            }

            if (overlay) {
                overlay.addEventListener('click', () => {
                    drawer.classList.add('translate-y-full');
                    overlay.classList.add('hidden');
                });
            }

            document.addEventListener('keydown', (e) => {
                if (e.key === 'Escape' && !drawer.classList.contains('translate-y-full')) {
                    drawer.classList.add('translate-y-full');
                    overlay.classList.add('hidden');
                }
            });

            // Global update function
            window.updateFAQStatus = function(faqId, status) {
                const statusText = status.charAt(0).toUpperCase() + status.slice(1);
                Swal.fire({
                    title: 'Confirm Action',
                    text: `Are you sure you want to ${status} this FAQ?`,
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: status === 'approved' ? '#10b981' : '#ef4444',
                    cancelButtonColor: '#6b7280',
                    confirmButtonText: statusText,
                    cancelButtonText: 'Cancel'
                }).then((result) => {
                    if (result.isConfirmed) {
                        fetch(UPDATE_STATUS_URL, {
                                method: 'POST',
                                headers: {
                                    'Content-Type': 'application/json',
                                    'X-CSRF-TOKEN': csrf
                                },
                                body: JSON.stringify({
                                    id: faqId,
                                    status: status
                                })
                            })
                            .then(response => response.json())
                            .then(data => {
                                if (data.success) {
                                    Swal.fire({
                                        title: 'Success!',
                                        text: `FAQ ${status} successfully!`,
                                        icon: 'success',
                                        confirmButtonColor: '#3b82f6'
                                    }).then(() => {
                                        fetchList(currentPage);
                                    });
                                } else {
                                    Swal.fire({
                                        title: 'Error!',
                                        text: data.message || 'Failed to update FAQ.',
                                        icon: 'error',
                                        confirmButtonColor: '#ef4444'
                                    });
                                }
                            })
                            .catch(error => {
                                Swal.fire({
                                    title: 'Error!',
                                    text: 'Error: ' + error.message,
                                    icon: 'error',
                                    confirmButtonColor: '#ef4444'
                                });
                            });
                    }
                });
            };

            // Modal functions
            window.openAnalyzeModal = function() {
                document.getElementById('analyze-modal').classList.remove('hidden');
                document.body.classList.add('overflow-hidden');

                // Reset modal state
                document.getElementById('progress-section').classList.add('hidden');
                document.getElementById('results-section').classList.add('hidden');
                document.getElementById('analyzeIcon').classList.remove('hidden');
                document.getElementById('analyzeSpinner').classList.add('hidden');
                document.getElementById('analyzeText').textContent = 'Start Analysis';

                // Check if there are unprocessed tickets
                const unprocessedCount = {{ $unprocessedTickets ?? 0 }};
                const analyzeBtn = document.getElementById('analyze-btn');
                if (unprocessedCount === 0) {
                    analyzeBtn.disabled = true;
                    document.getElementById('analyzeText').textContent = 'No Tickets to Analyze';
                } else {
                    analyzeBtn.disabled = false;
                }
            };

            window.closeAnalyzeModal = function() {
                document.getElementById('analyze-modal').classList.add('hidden');
                document.body.classList.remove('overflow-hidden');
            };

            window.startAnalysis = function() {
                const btn = document.getElementById('analyze-btn');
                const icon = document.getElementById('analyzeIcon');
                const spinner = document.getElementById('analyzeSpinner');
                const text = document.getElementById('analyzeText');

                btn.disabled = true;
                icon.classList.add('hidden');
                spinner.classList.remove('hidden');
                text.textContent = 'Analyzing...';

                document.getElementById('progress-section').classList.remove('hidden');
                document.getElementById('results-section').classList.add('hidden');

                // Call the backend API to process analysis
                fetch('{{ route('admin.faqs.process-analysis') }}', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content || ''
                        },
                        body: JSON.stringify({})
                    })
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            window.completeAnalysis(data);
                        } else {
                            window.showError(data.message || 'Analysis failed');
                            btn.disabled = false;
                            spinner.classList.add('hidden');
                            icon.classList.remove('hidden');
                            text.textContent = 'Start Analysis';
                            document.getElementById('progress-section').classList.add('hidden');
                        }
                    })
                    .catch(error => {
                        window.showError('Error: ' + error.message);
                        btn.disabled = false;
                        spinner.classList.add('hidden');
                        icon.classList.remove('hidden');
                        text.textContent = 'Start Analysis';
                        document.getElementById('progress-section').classList.add('hidden');
                    });

                // Simulate progress updates
                let progress = 0;
                const interval = setInterval(() => {
                    progress += Math.random() * 25;
                    if (progress > 90) progress = 90;

                    document.getElementById('progress-bar').style.width = progress + '%';
                    document.getElementById('progress-text').textContent = 'Processing tickets... ' + Math
                        .round(progress) + '%';

                    if (progress >= 90) {
                        clearInterval(interval);
                    }
                }, 800);
            };

            window.completeAnalysis = function(data) {
                document.getElementById('progress-bar').style.width = '100%';
                document.getElementById('progress-text').textContent = 'Analysis complete!';

                setTimeout(() => {
                    document.getElementById('progress-section').classList.add('hidden');
                    document.getElementById('results-section').classList.remove('hidden');

                    document.getElementById('tickets-processed').textContent = data.tickets_processed || 0;
                    document.getElementById('faqs-generated').textContent = data.faqs_generated || 0;

                    window.showSuccess('Analysis completed successfully!');

                    // Reload page after 3 seconds to show new FAQs
                    setTimeout(() => {
                        location.reload();
                    }, 3000);
                }, 1000);
            };

            window.showSuccess = function(message) {
                const notification = document.getElementById('success-notification');
                notification.textContent = message;
                notification.classList.remove('hidden');
                setTimeout(() => {
                    notification.classList.add('hidden');
                }, 4000);
            };

            window.showError = function(message) {
                const notification = document.getElementById('error-notification');
                notification.textContent = message;
                notification.classList.remove('hidden');
                setTimeout(() => {
                    notification.classList.add('hidden');
                }, 4000);
            };

            // Initialize
            document.addEventListener('DOMContentLoaded', function() {
                const unprocessedCount = {{ $unprocessedTickets ?? 0 }};
                document.getElementById('unprocessed-count').textContent = unprocessedCount;

                const analyzeBtn = document.getElementById('analyze-btn');
                if (unprocessedCount === 0) {
                    analyzeBtn.disabled = true;
                }

                fetchList(1);
            });
        })();
    </script>
@endsection
