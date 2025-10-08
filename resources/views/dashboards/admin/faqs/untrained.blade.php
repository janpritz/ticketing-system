@extends('layouts.admin')

@section('title', 'Untrained FAQs')

@section('admin-content')
    <div class="sm:px-2">
        <div class="flex items-center justify-between gap-4">
            <div>
                <h1 class="text-2xl font-semibold text-slate-900">Untrain FAQs Page</h1>
            </div>

            <div class="hidden sm:block">
                <a href="{{ route('admin.faqs.index') }}"
                    class="inline-flex items-center gap-2 rounded-lg border border-gray-200 bg-white hover:bg-gray-50 text-sm font-medium px-3 py-2">
                    ← Back to FAQ Management
                </a>
            </div>

            <div class="flex sm:hidden items-center gap-2">
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
                </div>
            </div>

            <!-- Mobile search area -->
            <div id="mobileSearchArea" class="sm:hidden mt-3">
                <div class="flex items-center gap-2">
                    <input id="q_mobile" type="text" placeholder="Search intent or response"
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
                          <th class="px-3 py-3 text-left font-medium">Status</th>
                          <th class="py-3 pl-3 pr-5 text-left font-medium">Action</th>
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
                <div id="paginationControls" class="flex items-center justify-between"></div>
            </div>
        </div>
    </div>


    <div id="untrained-faqs-state" class="hidden"
         data-list-url="{{ route('admin.faqs.untrained.list') }}"
         data-default-status="untrained"
         data-show-url-template="{{ route('admin.faqs.show', ['faq' => '__ID__']) }}"
         data-train-url-template="{{ route('admin.faqs.train', ['faq' => '__ID__']) }}"
         data-untrain-url-template="{{ route('admin.faqs.untrain', ['faq' => '__ID__']) }}"></div>

@endsection

@section('admin-scripts')
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
(function() {
    const stateEl = document.getElementById('untrained-faqs-state');
    const LIST_URL = stateEl.getAttribute('data-list-url');
    const TRAIN_TEMPLATE = stateEl.getAttribute('data-train-url-template');
    const UNTRAIN_TEMPLATE = stateEl.getAttribute('data-untrain-url-template');
    const DEFAULT_STATUS = stateEl.getAttribute('data-default-status') || 'all';
    const csrf = document.querySelector('meta[name="csrf-token"]').getAttribute('content');

    const $ = (sel, root = document) => root.querySelector(sel);
    const $$ = (sel, root = document) => Array.from(root.querySelectorAll(sel));

    const qInput = $('#q');
    const perPageSelect = $('#per_page');
    const searchBtn = $('#searchBtn');
    const clearSearchBtn = $('#clearSearch');
    const faqsTbody = $('#faqsTbody');
    const paginationControls = $('#paginationControls');

    let currentPage = 1;

    function escapeHtml(s) {
        if (s === null || s === undefined) return '';
        return String(s)
            .replaceAll('&', '&')
            .replaceAll('<', '<')
            .replaceAll('>', '>')
            .replaceAll('"', '"')
            .replaceAll("'", "&#039;");
    }

    function truncate(str, n = 180) {
        if (!str) return '';
        return (str.length > n) ? (str.slice(0, n - 1) + '…') : str;
    }

    async function fetchList(page = 1) {
        currentPage = page;
        const q = encodeURIComponent((qInput ? qInput.value : '').trim());
        const per = (perPageSelect ? perPageSelect.value : '25');
        const sep = LIST_URL.includes('?') ? '&' : '?';
        const statusPart = (DEFAULT_STATUS && DEFAULT_STATUS !== 'all') ? `&status=${encodeURIComponent(DEFAULT_STATUS)}` : '';
        const url = `${LIST_URL}${sep}q=${q}&per_page=${per}&page=${page}${statusPart}`;
        try {
            const res = await fetch(url, { headers: { 'X-Requested-With': 'XMLHttpRequest' }});
            if (!res.ok) throw new Error('Failed to load FAQs');
            const json = await res.json();
            renderTable(json.items || []);
            renderPagination(json.meta || {});
        } catch (err) {
            faqsTbody.innerHTML =
                `<tr><td colspan="5" class="px-5 py-6 text-center text-sm text-red-600">Error loading FAQs</td></tr>`;
            paginationControls.innerHTML = '';
            console.error(err);
        }
    }

    function renderTable(items) {
        if (!items || items.length === 0) {
            faqsTbody.innerHTML =
                `<tr><td colspan="5" class="px-5 py-10 text-center text-sm text-gray-500">No FAQs found.</td></tr>`;
            return;
        }

        faqsTbody.innerHTML = items.map(f => {
            // Determine selected state classes
            const isTrained = (f.status === 'trained');
            const isUntrained = !isTrained;

            // Train pill classes
            const trainCls = isTrained
                ? 'inline-flex items-center gap-1 rounded-full px-3 py-1.5 text-sm font-medium bg-emerald-600 text-white'
                : 'inline-flex items-center gap-1 rounded-full px-3 py-1.5 text-sm font-medium border border-gray-200 bg-white text-slate-700';

            // Untrain pill classes
            const untrainCls = isUntrained
                ? 'inline-flex items-center gap-1 rounded-full px-3 py-1.5 text-sm font-medium bg-gray-300 text-slate-700'
                : 'inline-flex items-center gap-1 rounded-full px-3 py-1.5 text-sm font-medium border border-gray-200 bg-white text-slate-700';

            return `
  <tr class="hover:bg-gray-50">
    <td class="py-3 pl-5 pr-3 align-top">
      <div class="text-slate-900 font-medium">${escapeHtml(f.intent)}</div>
    </td>
    <td class="px-3 py-3 align-top">
      <div class="text-slate-700 whitespace-pre-line max-w-xl">${escapeHtml(truncate(f.description || '', 180))}</div>
    </td>
    <td class="px-3 py-3 align-top">
      <div class="text-slate-700 whitespace-pre-line">${escapeHtml(truncate(f.response || '', 200))}</div>
    </td>
    <td class="px-3 py-3 align-top">
      <div class="text-slate-700">${escapeHtml(f.status || 'untrained')}</div>
    </td>
    <td class="py-3 pl-3 pr-5 align-top">
      <div class="inline-flex items-center gap-2" data-faq-id="${f.id}">
        <button class="pillToggle train ${trainCls}" data-value="trained" data-id="${f.id}">Train</button>
        <button class="pillToggle untrain ${untrainCls}" data-value="untrained" data-id="${f.id}">Untrain</button>
      </div>
    </td>
  </tr>`;
        }).join('');

        function attachPillHandlers() {
            $$('.pillToggle').forEach(btn => btn.addEventListener('click', async (e) => {
                const id = btn.getAttribute('data-id');
                const value = btn.getAttribute('data-value'); // 'trained' or 'untrained'
                if (!id || !value) return;

                let url = '';
                let method = 'POST';
                if (value === 'trained') {
                    url = TRAIN_TEMPLATE ? TRAIN_TEMPLATE.replace('__ID__', id) : '';
                    method = 'PUT';
                } else {
                    url = UNTRAIN_TEMPLATE ? UNTRAIN_TEMPLATE.replace('__ID__', id) : '';
                    method = 'POST';
                }
                if (!url) return;

                // Use SweetAlert2 for confirmation
                const confirmOpts = {
                    title: value === 'trained' ? 'Mark this response as trained?' : 'Mark this response as not trained?',
                    icon: value === 'trained' ? 'question' : 'warning',
                    showCancelButton: true,
                    confirmButtonText: 'Yes',
                    cancelButtonText: 'Cancel'
                };
                const confirmed = await Swal.fire(confirmOpts);
                if (!confirmed.isConfirmed) return;

                try {
                    // disable all buttons for this faq while request is ongoing
                    const container = btn.closest('[data-faq-id]');
                    if (container) {
                        $$('.pillToggle', container).forEach(b => b.disabled = true);
                    } else {
                        btn.disabled = true;
                    }

                    const res = await fetch(url, {
                        method: method,
                        headers: {
                            'X-CSRF-TOKEN': csrf,
                            'X-Requested-With': 'XMLHttpRequest',
                            'Content-Type': 'application/json'
                        }
                    });

                    const json = await res.json().catch(() => null);
                    if (!res.ok) {
                        const err = json && json.message ? json.message : 'Failed to update status';
                        throw new Error(err);
                    }

                    // Show success toast and refresh the list to reflect new status (keeps UI consistent)
                    Swal.fire({
                        toast: true,
                        position: 'top-end',
                        icon: 'success',
                        title: json && json.message ? json.message : (value === 'trained' ? 'Marked as trained' : 'Marked as not trained'),
                        showConfirmButton: false,
                        timer: 2000,
                        timerProgressBar: true
                    });

                    fetchList(currentPage);
                } catch (err) {
                    console.error(err);
                    Swal.fire({
                        icon: 'error',
                        title: 'Error',
                        text: err.message || 'Error updating FAQ status'
                    });
                } finally {
                    const container = btn.closest('[data-faq-id]');
                    if (container) {
                        $$('.pillToggle', container).forEach(b => b.disabled = false);
                    } else {
                        btn.disabled = false;
                    }
                }
            }));
        }
        attachPillHandlers();
    }

    function renderPagination(meta) {
        if (!meta || !meta.total) {
            paginationControls.innerHTML = '';
            return;
        }
        const total = meta.total || 0;
        const per = meta.per_page || parseInt(perPageSelect ? perPageSelect.value : '25', 10);
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

    // search handlers
    if (searchBtn) searchBtn.addEventListener('click', () => fetchList(1));
    if (perPageSelect) perPageSelect.addEventListener('change', () => fetchList(1));
    if (clearSearchBtn) clearSearchBtn.addEventListener('click', () => { if (qInput) qInput.value = ''; fetchList(1); });
    if (qInput) qInput.addEventListener('keyup', (e) => { if (e.key === 'Enter') fetchList(1); });


    // init
    fetchList(1);
})();
</script>
@endsection