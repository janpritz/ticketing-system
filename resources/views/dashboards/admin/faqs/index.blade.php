@extends('layouts.admin')

@section('title', 'FAQ Management')

@section('admin-content')
<div class="sm:px-2">
  <div class="flex items-center justify-between gap-4">
    <div>
      <h1 class="text-2xl font-semibold text-slate-900">FAQ Management</h1>
    </div>
    <div class="flex items-center gap-2">
      <a href="{{ route('admin.faqs.pending') }}" class="inline-flex items-center gap-2 rounded-lg bg-orange-500 hover:bg-orange-600 text-white text-sm font-medium px-3 py-2">
        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" viewBox="0 0 24 24" fill="currentColor"><path d="M12 2a10 10 0 100 20 10 10 0 000-20zM11 6h2v6h-2V6zm0 8h2v2h-2v-2z"/></svg>
        Update FAQ Status
      </a>
      <button id="openCreateModalBtn" type="button" class="inline-flex items-center gap-2 rounded-lg bg-blue-600 hover:bg-blue-700 text-white text-sm font-medium px-3 py-2">
        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" viewBox="0 0 24 24" fill="currentColor"><path d="M11 11V5h2v6h6v2h-6v6h-2v-6H5v-2z"/></svg>
        Add FAQ
      </button>
    </div>
  </div>

  <div class="mt-4 flex items-start justify-between">
    <div class="flex items-center gap-2">
      <label class="relative block">
        <span class="sr-only">Search</span>
        <span class="absolute inset-y-0 left-0 flex items-center pl-3 text-gray-400">
          <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" viewBox="0 0 24 24" fill="currentColor">
            <path d="M15.5 14h-.79l-.28-.27a6.5 6.5 0 10-.71.71l.27.28v.79L20 21.5 21.5 20l-6-6zM10 15a5 5 0 110-10 5 5 0 010 10z" />
          </svg>
        </span>
        <input id="q" type="text" name="q" placeholder="Search topic or response"
               class="w-80 pl-9 pr-3 py-2 text-sm rounded-md border border-gray-200 focus:ring-2 focus:ring-blue-500 focus:border-blue-500" />
      </label>

      <button id="searchBtn" type="button" class="rounded-md border border-gray-300 bg-white hover:bg-gray-50 text-sm px-3 py-2">Search</button>
      <button id="clearSearch" type="button" class="text-sm text-slate-600 hover:text-slate-800 hidden">Clear</button>
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

  <div class="mt-4 bg-white rounded-xl border border-gray-200 overflow-hidden">
    <div class="overflow-x-auto">
      <table id="faqsTable" class="min-w-full text-sm">
        <thead class="bg-gray-50 text-gray-600">
          <tr>
            <th class="py-3 pl-5 pr-3 text-left font-medium">Topic</th>
            <th class="px-3 py-3 text-left font-medium">Response</th>
            <th class="px-3 py-3 text-left font-medium">Created At</th>
            <th class="px-3 py-3 text-left font-medium">Updated At</th>
            <th class="px-3 py-3 text-left font-medium">Status</th>
            <th class="py-3 pl-3 pr-5 text-left font-medium">Actions</th>
          </tr>
        </thead>
        <tbody id="faqsTbody" class="divide-y divide-gray-100">
          <tr><td colspan="4" class="px-5 py-6 text-center text-sm text-gray-500">Loading...</td></tr>
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
    <div class="w-full max-w-2xl bg-white rounded-lg shadow border border-gray-200">
      <div class="h-12 flex items-center justify-between px-4 border-b">
        <div class="text-sm font-semibold text-slate-800">Add FAQ</div>
        <button type="button" class="text-slate-500 hover:text-slate-700" data-close="create" aria-label="Close">
          <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 24 24" fill="currentColor"><path d="M6 18 18 6M6 6l12 12"/></svg>
        </button>
      </div>
      <form id="createFaqForm" class="p-4 space-y-4">
        <div>
          <label class="block text-sm font-medium text-slate-700">Topic</label>
          <input type="text" name="topic" id="create_topic" required
                 class="mt-1 w-full rounded-md border border-gray-300 px-3 py-2 text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500" />
          <p id="create_topic_error" class="mt-1 text-xs text-red-600 hidden"></p>
        </div>
        <div>
          <label class="block text-sm font-medium text-slate-700">Response</label>
          <textarea name="response" id="create_response" rows="6" required
                    class="mt-1 w-full rounded-md border border-gray-300 px-3 py-2 text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500"></textarea>
          <p id="create_response_error" class="mt-1 text-xs text-red-600 hidden"></p>
        </div>
        <div class="pt-2 flex items-center justify-end gap-3">
          <button type="button" class="rounded-md border border-gray-300 bg-white hover:bg-gray-50 text-sm px-4 py-2" data-close="create">Cancel</button>
          <button id="createFaqSubmit" type="button" class="rounded-md bg-blue-600 hover:bg-blue-700 text-white text-sm font-medium px-4 py-2">Create FAQ</button>
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
    <div class="relative w-full max-w-2xl bg-white rounded-lg shadow border border-gray-200">
      
      <!-- Header -->
      <div class="h-12 flex items-center px-4 border-b">
        <div class="text-sm font-semibold text-slate-800">FAQ Details</div>
      </div>

      <!-- Close button top-right -->
      <button type="button" class="absolute top-3 right-3 text-slate-500 hover:text-slate-700"
              data-close="view" aria-label="Close">
        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24"
             stroke="currentColor" stroke-width="2">
          <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/>
        </svg>
      </button>

      <!-- Body -->
      <form id="viewFaqForm" class="p-4 space-y-4">
        @csrf
        @method('PUT')
        <input type="hidden" id="view_faq_id" name="faq_id" value="">

        <!-- Topic -->
        <div>
          <label class="block text-sm font-medium text-slate-700">Topic</label>
          <input type="text" name="topic" id="view_topic" required
                 class="mt-1 w-full rounded-md border border-gray-300 px-3 py-2 text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500" />
          <p id="view_topic_error" class="mt-1 text-xs text-red-600 hidden"></p>
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
                    class="rounded-md bg-blue-600 hover:bg-blue-700 text-white text-sm font-medium px-4 py-2">Save Changes</button>
          </div>
        </div>
      </form>
    </div>
  </div>
</div>


<!-- Hidden state with URLs -->
<div id="admin-faqs-state" class="hidden"
     data-list-url="{{ route('admin.faqs.list') }}"
     data-store-url="{{ route('admin.faqs.store') }}"
     data-show-url-template="{{ route('admin.faqs.show', ['faq' => '__ID__']) }}"
     data-update-url-template="{{ route('admin.faqs.update', ['faq' => '__ID__']) }}"
     data-destroy-url-template="{{ route('admin.faqs.destroy', ['faq' => '__ID__']) }}"></div>

@endsection

@section('admin-scripts')
<script>
(function () {
  const stateEl = document.getElementById('admin-faqs-state');
  const LIST_URL = stateEl.getAttribute('data-list-url');
  const STORE_URL = stateEl.getAttribute('data-store-url');
  const SHOW_TEMPLATE = stateEl.getAttribute('data-show-url-template');
  const UPDATE_TEMPLATE = stateEl.getAttribute('data-update-url-template');
  const DESTROY_TEMPLATE = stateEl.getAttribute('data-destroy-url-template');
  const csrf = document.querySelector('meta[name="csrf-token"]').getAttribute('content');

  const $ = (sel, root = document) => root.querySelector(sel);
  const $$ = (sel, root = document) => Array.from(root.querySelectorAll(sel));

  // Elements
  const qInput = $('#q');
  const perPageSelect = $('#per_page');
  const searchBtn = $('#searchBtn');
  const clearSearchBtn = $('#clearSearch');
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
  const viewTopic = $('#view_topic');
  const viewResponse = $('#view_response');
  const viewTimestamps = $('#view_timestamps');
  const updateSubmit = $('#updateFaqSubmit');
  const deleteBtn = $('#deleteFaqBtn');

  let currentPage = 1;
  let currentQuery = '';
  let currentPerPage = parseInt(perPageSelect.value || '25', 10);
  let autoRefreshInterval = null;

  function openModal(modal) { if (modal) modal.classList.remove('hidden'); }
  function closeModal(modal) { if (modal) modal.classList.add('hidden'); }

  // Fetch list via AJAX
  async function fetchList(page = 1) {
    currentPage = page;
    const q = encodeURIComponent((qInput.value || '').trim());
    const per = perPageSelect.value || '25';
    const url = `${LIST_URL}?q=${q}&per_page=${per}&page=${page}`;
    try {
      const res = await fetch(url, { headers: { 'X-Requested-With': 'XMLHttpRequest' } });
      if (!res.ok) throw new Error('Failed to load FAQs');
      const json = await res.json();
      renderTable(json.items || []);
      renderPagination(json.meta || {});
      toggleClear(qInput.value.trim() !== '');
    } catch (err) {
      faqsTbody.innerHTML = `<tr><td colspan="6" class="px-5 py-6 text-center text-sm text-red-600">Error loading FAQs</td></tr>`;
      paginationControls.innerHTML = '';
      console.error(err);
    }
  }

  function truncate(str, n=140) {
    if (!str) return '';
    return (str.length > n) ? (str.slice(0,n-1) + '…') : str;
  }

  function renderTable(items) {
    if (!items || items.length === 0) {
      faqsTbody.innerHTML = `<tr><td colspan="6" class="px-5 py-10 text-center text-sm text-gray-500">No FAQs found.</td></tr>`;
      return;
    }
    faqsTbody.innerHTML = items.map(f => `
      <tr class="hover:bg-gray-50">
        <td class="py-3 pl-5 pr-3 align-top">
          <div class="text-slate-900 font-medium">${escapeHtml(f.topic)}</div>
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
          <div class="text-slate-700">${escapeHtml(f.status || 'pending')}</div>
        </td>
        <td class="py-3 pl-3 pr-5 align-top">
          <div class="flex items-center gap-2">
            <button class="viewFaqBtn inline-flex items-center gap-1 rounded-lg border border-gray-200 bg-white px-3 py-1.5 text-xs font-medium text-gray-700 hover:bg-gray-50"
                    data-id="${f.id}">
              View
            </button>
          </div>
        </td>
      </tr>
    `).join('');
    // attach handlers
    $$('.viewFaqBtn').forEach(btn => btn.addEventListener('click', onViewClick));
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
      .replaceAll('&','&')
      .replaceAll('<','<')
      .replaceAll('>','>')
      .replaceAll('"','"')
      .replaceAll("'","&#039;");
  }

  function toggleClear(show) {
    clearSearchBtn.classList.toggle('hidden', !show);
  }

  // Search handlers
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

  // Create modal handlers
  if (createOpenBtn) createOpenBtn.addEventListener('click', () => {
    // reset fields
    createForm.reset();
    $('#create_topic_error').classList.add('hidden');
    $('#create_response_error').classList.add('hidden');
    openModal(createModal);
  });
  createCloseEls.forEach(el => el.addEventListener('click', () => closeModal(createModal)));

  createSubmit.addEventListener('click', async () => {
    // clear errors
    $('#create_topic_error').classList.add('hidden');
    $('#create_response_error').classList.add('hidden');

    const topic = $('#create_topic').value.trim();
    const response = $('#create_response').value.trim();
    if (!topic || !response) {
      if (!topic) { $('#create_topic_error').textContent = 'Topic is required'; $('#create_topic_error').classList.remove('hidden'); }
      if (!response) { $('#create_response_error').textContent = 'Response is required'; $('#create_response_error').classList.remove('hidden'); }
      return;
    }

    try {
      createSubmit.disabled = true;
      const res = await fetch(STORE_URL, {
        method: 'POST',
        headers: {
          'Content-Type': 'application/json',
          'X-CSRF-TOKEN': csrf,
          'X-Requested-With': 'XMLHttpRequest'
        },
        body: JSON.stringify({ topic, response })
      });
      const json = await res.json();
      if (!res.ok) {
        const err = (json.errors && Object.values(json.errors).flat().join(' ')) || json.message || 'Failed to create FAQ';
        throw new Error(err);
      }
      showToast('success', 'FAQ created');
      closeModal(createModal);
      fetchList(1);
    } catch (err) {
      showToast('error', err.message || 'Error');
      console.error(err);
    } finally {
      createSubmit.disabled = false;
    }
  });

  // View modal handlers
  viewCloseEls.forEach(el => el.addEventListener('click', () => closeModal(viewModal)));
  let activeFaqId = null;

  async function onViewClick(e) {
    const id = e.currentTarget.getAttribute('data-id');
    if (!id) return;
    activeFaqId = id;
    // fetch details
    const showUrl = SHOW_TEMPLATE.replace('__ID__', id);
    try {
      const res = await fetch(showUrl, { headers: { 'X-Requested-With': 'XMLHttpRequest' } });
      if (!res.ok) throw new Error('Failed to load FAQ');
      const f = await res.json();
      // populate
      viewFaqId.value = f.id;
      viewTopic.value = f.topic || '';
      viewResponse.value = f.response || '';
      viewTimestamps.innerHTML = `<div class="text-xs">Created: ${escapeHtml(f.created_at || '')} &nbsp; Updated: ${escapeHtml(f.updated_at || '')}</div>`;
      // set update form action (not required for fetch but kept for semantics)
      viewForm.setAttribute('action', UPDATE_TEMPLATE.replace('__ID__', id));
      openModal(viewModal);
    } catch (err) {
      showToast('error', 'Failed to load FAQ');
      console.error(err);
    }
  }

  updateSubmit.addEventListener('click', async () => {
    const id = viewFaqId.value;
    if (!id) return;
    const url = UPDATE_TEMPLATE.replace('__ID__', id);
    const payload = {
      topic: viewTopic.value.trim(),
      response: viewResponse.value.trim()
    };
    // basic validation
    let hasErr = false;
    $('#view_topic_error').classList.add('hidden');
    $('#view_response_error').classList.add('hidden');
    if (!payload.topic) { $('#view_topic_error').textContent = 'Topic required'; $('#view_topic_error').classList.remove('hidden'); hasErr = true; }
    if (!payload.response) { $('#view_response_error').textContent = 'Response required'; $('#view_response_error').classList.remove('hidden'); hasErr = true; }
    if (hasErr) return;

    try {
      updateSubmit.disabled = true;
      const res = await fetch(url, {
        method: 'PUT',
        headers: {
          'Content-Type': 'application/json',
          'X-CSRF-TOKEN': csrf,
          'X-Requested-With': 'XMLHttpRequest'
        },
        body: JSON.stringify(payload)
      });
      const json = await res.json();
      if (!res.ok) {
        const err = (json.errors && Object.values(json.errors).flat().join(' ')) || json.message || 'Failed to update';
        throw new Error(err);
      }
      showToast('success', 'FAQ updated');
      closeModal(viewModal);
      fetchList(currentPage);
    } catch (err) {
      showToast('error', err.message || 'Error');
      console.error(err);
    } finally {
      updateSubmit.disabled = false;
    }
  });

  deleteBtn.addEventListener('click', async () => {
    const id = viewFaqId.value;
    if (!id) return;
    if (!confirm('Delete this FAQ? This action cannot be undone.')) return;
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
        const err = json.message || 'Failed to delete';
        throw new Error(err);
      }
      showToast('success', 'FAQ deleted');
      closeModal(viewModal);
      // refresh list (stay on same page if possible)
      fetchList(currentPage);
    } catch (err) {
      showToast('error', err.message || 'Error');
      console.error(err);
    } finally {
      deleteBtn.disabled = false;
    }
  });

  // Auto-refresh every 20s
  function startAutoRefresh() {
    if (autoRefreshInterval) clearInterval(autoRefreshInterval);
    autoRefreshInterval = setInterval(() => fetchList(currentPage), 20000);
  }

  // Initialize
  fetchList(1);
  startAutoRefresh();

  // Close modals on Escape
  document.addEventListener('keydown', (e) => {
    if (e.key === 'Escape') {
      closeModal(createModal);
      closeModal(viewModal);
    }
  });
})();
</script>
@endsection