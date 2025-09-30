@extends('layouts.admin')

@section('title', 'Pending FAQs')

@section('admin-content')
    <div class="sm:px-2">
        <div class="flex items-center justify-between gap-4">
            <div>
                <h1 class="text-2xl font-semibold text-slate-900">Pending FAQs</h1>
            </div>

            <!-- Desktop back button -->
            <div class="hidden sm:block">
                <a href="{{ route('admin.faqs.index') }}"
                    class="inline-flex items-center gap-2 rounded-lg border border-gray-200 bg-white hover:bg-gray-50 text-sm font-medium px-3 py-2">
                    ← Back to FAQ Management
                </a>
            </div>

            <!-- Mobile toolbar: search toggle + back -->
            <div class="flex sm:hidden items-center gap-2">
                <button id="mobileSearchToggle" type="button"
                    class="p-2 rounded-lg bg-gray-100 hover:bg-gray-200 text-slate-700" aria-label="Search">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 24 24" fill="currentColor">
                        <path
                            d="M15.5 14h-.79l-.28-.27a6.5 6.5 0 10-.71.71l.27.28v.79L20 21.5 21.5 20l-6-6zM10 15a5 5 0 110-10 5 5 0 010 10z" />
                    </svg>
                </button>

                <a href="{{ route('admin.faqs.index') }}"
                    class="p-2 rounded-lg bg-white border border-gray-200 text-slate-700 hover:bg-gray-50"
                    aria-label="Back to FAQ Management (mobile)">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-slate-700" viewBox="0 0 24 24"
                        fill="currentColor">
                        <path d="M15 6l-6 6 6 6" />
                    </svg>
                </a>
            </div>
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
                        <input id="q" type="text" name="q" placeholder="Search topic or response"
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
                </div>
            </div>

            <!-- Mobile search area (toggled) -->
            <div id="mobileSearchArea" class="sm:hidden mt-3 hidden">
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
                          <th class="px-3 py-3 text-left font-medium">Response</th>
                          <th class="px-3 py-3 text-left font-medium">Status</th>
                          <th class="py-3 pl-3 pr-5 text-left font-medium">Action</th>
                        </tr>
                    </thead>
                    <tbody id="faqsTbody" class="divide-y divide-gray-100">
                        <tr>
                            <td colspan="4" class="px-5 py-6 text-center text-sm text-gray-500">Loading...</td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <div id="faqsFooter" class="px-5 py-3 border-t border-gray-200">
                <div id="paginationControls" class="flex items-center justify-between"></div>
            </div>
        </div>
    </div>

    <!-- View FAQ Modal for Pending page -->
    <div id="viewPendingFaqModal" class="fixed inset-0 z-50 hidden">
        <div class="absolute inset-0 bg-black/40 pending-modal-close" aria-hidden="true"></div>
        <div class="absolute inset-0 flex items-center justify-center p-4">
            <div class="relative w-full max-w-2xl bg-white rounded-lg shadow border border-gray-200">
                <!-- Header -->
                <div class="h-12 flex items-center px-4 border-b">
                    <div class="text-sm font-semibold text-slate-800">FAQ Details</div>
                </div>

                <!-- Close button top-right (X icon only) -->
                <button type="button"
                    class="absolute top-3 right-3 text-slate-500 hover:text-slate-700 pending-modal-close"
                    aria-label="Close">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24"
                        stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>

                <!-- Body -->
                <form id="viewPendingFaqForm" class="p-4 space-y-4" onsubmit="return false;">
                    <input type="hidden" id="pending_view_faq_id" name="faq_id" value="">
                    <div>
                        <label class="block text-sm font-medium text-slate-700">Intent</label>
                        <input type="text" id="pending_view_intent" readonly
                               class="mt-1 w-full rounded-md border border-gray-300 px-3 py-2 text-sm bg-gray-50" />
                        <p id="pending_view_intent_error" class="mt-1 text-xs text-red-600 hidden"></p>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-slate-700">Response</label>
                        <textarea id="pending_view_response" rows="6" readonly
                            class="mt-1 w-full rounded-md border border-gray-300 px-3 py-2 text-sm bg-gray-50 whitespace-pre-line"></textarea>
                    </div>

                    <div class="pt-2 flex flex-col sm:flex-row items-stretch sm:items-center justify-between gap-3">
                        <div class="text-xs text-slate-500 mb-2 sm:mb-0" id="pending_view_timestamps"></div>
                        <div class="flex items-center gap-3 w-full sm:w-auto">
                            <button id="responseTrainedBtn" type="button"
                                class="w-full sm:w-auto rounded-md bg-emerald-600 hover:bg-emerald-700 text-white text-sm font-medium px-4 py-2">Response
                                Trained</button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
        <rasa-chatbot-widget error-message="Server is not running. Please come again in a few minutes."
            widget-title="Sangkay Chatbot" server-url="https://miniature-eureka-v6pxww557qqq36gg6-5005.app.github.dev/"
            bot-icon="{{ asset('logo-white.png') }}"
            initial-payload="As my sangkay, I would love to know your name. What is your name?" stream-messages="true">
            <style>
                :root {
                    --color-primary: #184c1c;
                }
            </style>
        </rasa-chatbot-widget>
    </div>

    <div id="pending-faqs-state" class="hidden" data-list-url="{{ route('admin.faqs.pending.list') }}"
        data-show-url-template="{{ route('admin.faqs.show', ['faq' => '__ID__']) }}"
        data-train-url-template="{{ route('admin.faqs.train', ['faq' => '__ID__']) }}"></div>

@endsection

@section('admin-scripts')
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script>
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
            const stateEl = document.getElementById('pending-faqs-state');
            const LIST_URL = stateEl.getAttribute('data-list-url');
            const TRAIN_TEMPLATE = stateEl.getAttribute('data-train-url-template');
            const csrf = document.querySelector('meta[name="csrf-token"]').getAttribute('content');

            const $ = (sel, root = document) => root.querySelector(sel);
            const $$ = (sel, root = document) => Array.from(root.querySelectorAll(sel));

            const qInput = $('#q');
            const perPageSelect = $('#per_page');
            const searchBtn = $('#searchBtn');
            const clearSearchBtn = $('#clearSearch');
            const faqsTbody = $('#faqsTbody');
            const paginationControls = $('#paginationControls');

            // Mobile search elements
            const mobileSearchToggle = $('#mobileSearchToggle');
            const mobileSearchArea = $('#mobileSearchArea');
            const qMobile = $('#q_mobile');
            const mobileSearchBtn = $('#mobileSearchBtn');
            const perPageMobile = $('#per_page_mobile');

            let currentPage = 1;

            function truncate(str, n = 180) {
                if (!str) return '';
                return (str.length > n) ? (str.slice(0, n - 1) + '…') : str;
            }

            function escapeHtml(s) {
                if (s === null || s === undefined) return '';
                return String(s)
                    .replaceAll('&', '&')
                    .replaceAll('<', '<')
                    .replaceAll('>', '>')
                    .replaceAll('"', '"')
                    .replaceAll("'", "&#039;");
            }

            async function fetchList(page = 1) {
                currentPage = page;
                const q = encodeURIComponent((qInput.value || '').trim());
                const per = perPageSelect.value || '25';
                const url = `${LIST_URL}?q=${q}&per_page=${per}&page=${page}`;
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
                        `<tr><td colspan="4" class="px-5 py-6 text-center text-sm text-red-600">Error loading FAQs</td></tr>`;
                    paginationControls.innerHTML = '';
                    console.error(err);
                }
            }

            function renderTable(items) {
                if (!items || items.length === 0) {
                    faqsTbody.innerHTML =
                        `<tr><td colspan="4" class="px-5 py-10 text-center text-sm text-gray-500">No pending FAQs.</td></tr>`;
                    return;
                }
                faqsTbody.innerHTML = items.map(f => `
      <tr class="hover:bg-gray-50">
        <td class="py-3 pl-5 pr-3 align-top">
          <div class="text-slate-900 font-medium">${escapeHtml(f.intent)}</div>
        </td>
        <td class="px-3 py-3 align-top">
          <div class="text-slate-700 whitespace-pre-line">${escapeHtml(truncate(f.response, 200))}</div>
        </td>
        <td class="px-3 py-3 align-top">
          <div class="text-slate-700">${escapeHtml(f.status || 'pending')}</div>
        </td>
        <td class="py-3 pl-3 pr-5 align-top">
          <div class="flex items-center gap-2">
            <button class="verifyFaqBtn inline-flex items-center gap-1 rounded-lg bg-orange-500 hover:bg-orange-600 text-white px-3 py-1.5 text-xs font-medium"
                    data-id="${f.id}">
              Verify Training
            </button>
          </div>
        </td>
      </tr>
    `).join('');
                $$('.verifyFaqBtn').forEach(b => b.addEventListener('click', onVerifyClick));
            }

            function renderPagination(meta) {
                if (!meta || !meta.total) {
                    paginationControls.innerHTML = '';
                    return;
                }
                const total = meta.total || 0;
                const per = meta.per_page || parseInt(perPageSelect.value || '25', 10);
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

            function toggleClear(show) {
                clearSearchBtn.classList.toggle('hidden', !show);
            }

            searchBtn.addEventListener('click', () => fetchList(1));
            perPageSelect.addEventListener('change', () => fetchList(1));
            clearSearchBtn.addEventListener('click', () => {
                qInput.value = '';
                toggleClear(false);
                fetchList(1);
            });
            qInput.addEventListener('keyup', (e) => {
                if (e.key === 'Enter') fetchList(1);
            });

            // Mobile search UI (toggles mobile search bar)
            if (mobileSearchToggle) {
                mobileSearchToggle.addEventListener('click', () => {
                    if (mobileSearchArea) mobileSearchArea.classList.toggle('hidden');
                    if (qMobile) qMobile.focus();
                });
            }

            if (mobileSearchBtn) {
                mobileSearchBtn.addEventListener('click', () => {
                    const v = qMobile ? qMobile.value.trim() : '';
                    qInput.value = v;
                    toggleClear(v !== '');
                    fetchList(1);
                });
            }

            if (perPageMobile) {
                perPageMobile.addEventListener('change', () => {
                    perPageSelect.value = perPageMobile.value;
                    fetchList(1);
                });
            }

            // Modal helpers for viewing and verifying training
            const viewModal = document.getElementById('viewPendingFaqModal');
            const viewCloseEls = $$('.pending-modal-close', viewModal || document);
            const pendingViewFaqId = document.getElementById('pending_view_faq_id');
            const pendingViewIntent = document.getElementById('pending_view_intent');
            const pendingViewResponse = document.getElementById('pending_view_response');
            const pendingViewTimestamps = document.getElementById('pending_view_timestamps');

            function openModal(modal) {
                if (modal) modal.classList.remove('hidden');
            }

            function closeModal(modal) {
                if (modal) modal.classList.add('hidden');
            }

            viewCloseEls.forEach(el => el.addEventListener('click', () => closeModal(viewModal)));

            async function onVerifyClick(e) {
                const id = e.currentTarget.getAttribute('data-id');
                if (!id) return;
                // fetch faq details and open modal
                const showUrl = stateEl.getAttribute('data-show-url-template').replace('__ID__', id);
                try {
                    const res = await fetch(showUrl, {
                        headers: {
                            'X-Requested-With': 'XMLHttpRequest'
                        }
                    });
                    if (!res.ok) throw new Error('Failed to load FAQ');
                    const f = await res.json();
                    pendingViewFaqId.value = f.id;
                    pendingViewTopic.value = f.topic || '';
                    pendingViewResponse.value = f.response || '';
                    pendingViewTimestamps.innerHTML =
                        `<div class="text-xs">Created: ${escapeHtml(f.created_at || '')} &nbsp; Updated: ${escapeHtml(f.updated_at || '')}</div>`;
                    openModal(viewModal);
                } catch (err) {
                    showToast('error', 'Failed to load FAQ');
                    console.error(err);
                }
            }

            const responseTrainedBtn = document.getElementById('responseTrainedBtn');

            // Shared trainer helper
            async function trainFaq(id, btn, confirmMessage) {
                if (!id) return;
                const result = await Swal.fire({
                    title: 'Confirm',
                    text: confirmMessage,
                    icon: 'question',
                    showCancelButton: true,
                    confirmButtonText: 'Yes',
                    cancelButtonText: 'Cancel'
                });
                if (!result.isConfirmed) return;
                const url = TRAIN_TEMPLATE.replace('__ID__', id);
                try {
                    if (btn) btn.disabled = true;
                    const res = await fetch(url, {
                        method: 'PUT',
                        headers: {
                            'X-CSRF-TOKEN': csrf,
                            'X-Requested-With': 'XMLHttpRequest'
                        }
                    });
                    const json = await res.json();
                    if (!res.ok) {
                        const msg = json.message || 'Failed to mark as trained';
                        throw new Error(msg);
                    }
                    showToast('success', 'Marked as trained');
                    // close modal and refresh list
                    closeModal(viewModal);
                    fetchList(currentPage);
                } catch (err) {
                    showToast('error', err.message || 'Error');
                    console.error(err);
                } finally {
                    if (btn) btn.disabled = false;
                }
            }


            // Response-trained action
            if (responseTrainedBtn) {
                responseTrainedBtn.addEventListener('click', async () => {
                    const id = pendingViewFaqId.value;
                    await trainFaq(id, responseTrainedBtn, 'Mark this FAQ as trained (Response)?');
                });
            }

            // Init
            fetchList(1);
        })();
    </script>
@endsection
