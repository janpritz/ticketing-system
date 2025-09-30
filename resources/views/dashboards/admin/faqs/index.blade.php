@extends('layouts.admin')

@section('title', $isDeletedView ? 'Deleted FAQs' : 'FAQ Management')

@section('admin-content')
<div class="sm:px-2">
  <div class="flex items-center justify-between gap-4">
    <div>
      <h1 class="text-2xl font-semibold text-slate-900">{{ !empty($isDeletedView) ? 'Deleted FAQs' : 'FAQ Management' }}</h1>
    </div>

    <!-- Desktop actions -->
    @if(!empty($isDeletedView))
      <div class="hidden sm:flex items-center gap-2">
        <a href="{{ route('admin.faqs.index') }}" class="inline-flex items-center gap-2 rounded-lg border border-gray-200 bg-white hover:bg-gray-50 text-sm font-medium px-3 py-2">
          ← Back to FAQ Management
        </a>
      </div>
    @else
      <div class="hidden sm:flex items-center gap-2">
        <a href="{{ route('admin.faqs.pending') }}" class="inline-flex items-center gap-2 rounded-lg bg-orange-500 hover:bg-orange-600 text-white text-sm font-medium px-3 py-2" aria-label="Update FAQ Status">
          <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" viewBox="0 0 24 24" fill="currentColor"><path d="M12 2a10 10 0 100 20 10 10 0 000-20zM11 6h2v6h-2V6zm0 8h2v2h-2v-2z"/></svg>
          <span class="hidden sm:inline">Update FAQ Status</span>
        </a>
        <button id="openCreateModalBtn" type="button" class="inline-flex items-center gap-2 rounded-lg bg-blue-600 hover:bg-blue-700 text-white text-sm font-medium px-3 py-2" aria-label="Add FAQ">
          <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" viewBox="0 0 24 24" fill="currentColor"><path d="M11 11V5h2v6h6v2h-6v6h-2v-6H5v-2z"/></svg>
          <span class="hidden sm:inline">Add FAQ</span>
        </button>
      </div>
    @endif

    <!-- Mobile toolbar: icons only -->
    <div class="flex sm:hidden items-center gap-2">
      <button id="mobileSearchToggle" type="button" class="p-2 rounded-lg bg-gray-100 hover:bg-gray-200 text-slate-700" aria-label="Search">
        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 24 24" fill="currentColor"><path d="M15.5 14h-.79l-.28-.27a6.5 6.5 0 10-.71.71l.27.28v.79L20 21.5 21.5 20l-6-6zM10 15a5 5 0 110-10 5 5 0 010 10z" /></svg>
      </button>
      @if(!empty($isDeletedView))
        <a href="{{ route('admin.faqs.index') }}" class="p-2 rounded-lg bg-white border border-gray-200 text-slate-700 hover:bg-gray-50" aria-label="Back to FAQ Management (mobile)">
          <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-slate-700" viewBox="0 0 24 24" fill="currentColor"><path d="M15 6l-6 6 6 6"/></svg>
        </a>
      @else
        <a href="{{ route('admin.faqs.pending') }}" class="p-2 rounded-lg bg-orange-500 hover:bg-orange-600 text-white" aria-label="Update FAQ Status (mobile)">
          <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 24 24" fill="currentColor"><path d="M12 2a10 10 0 100 20 10 10 0 000-20zM11 6h2v6h-2V6zm0 8h2v2h-2v-2z"/></svg>
        </a>
  
        <a href="{{ route('admin.faqs.deleted') }}" class="p-2 rounded-lg bg-white border border-gray-200 text-slate-700 hover:bg-gray-50" aria-label="Trash (mobile)">
          <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-slate-700" viewBox="0 0 24 24" fill="currentColor"><path d="M3 6h18v2H3V6zm2 3h14l-1.1 12.2c-.08.9-.86 1.6-1.76 1.6H8.86c-.9 0-1.68-.7-1.76-1.6L6 9zM9 4V3h6v1h5v2H4V4h5z"/></svg>
        </a>
  
        <button id="openCreateModalBtnMobile" type="button" class="p-2 rounded-lg bg-blue-600 hover:bg-blue-700 text-white" aria-label="Add FAQ (mobile)">
          <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 24 24" fill="currentColor"><path d="M11 11V5h2v6h6v2h-6v6h-2v-6H5v-2z"/></svg>
        </button>
      @endif
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
              <path d="M15.5 14h-.79l-.28-.27a6.5 6.5 0 10-.71.71l.27.28v.79L20 21.5 21.5 20l-6-6zM10 15a5 5 0 110-10 5 5 0 010 10z" />
            </svg>
          </span>
          <input id="q" type="text" name="q" placeholder="Search intent or response"
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

        <!-- Trash / Back button -->
        @if(!empty($isDeletedView))
        @else
          <a href="{{ route('admin.faqs.deleted') }}" class="inline-flex items-center gap-2 rounded-lg border border-gray-200 bg-white hover:bg-gray-50 text-sm px-3 py-2 ml-3">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 text-slate-700" viewBox="0 0 24 24" fill="currentColor">
              <path d="M3 6h18v2H3V6zm2 3h14l-1.1 12.2c-.08.9-.86 1.6-1.76 1.6H8.86c-.9 0-1.68-.7-1.76-1.6L6 9zM9 4V3h6v1h5v2H4V4h5z"/>
            </svg>
            <span class="hidden sm:inline">Trash</span>
          </a>
        @endif
      </div>
    </div>

    <!-- Mobile search area (toggled) -->
    <div id="mobileSearchArea" class="sm:hidden mt-3 hidden">
      <div class="flex items-center gap-2">
        <input id="q_mobile" type="text" placeholder="Search topic or response" class="flex-1 pl-3 pr-3 py-2 rounded-md border border-gray-200 text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500" />
        <button id="mobileSearchBtn" type="button" class="rounded-md border border-gray-300 bg-white hover:bg-gray-50 text-sm px-3 py-2" aria-label="Search">Search</button>
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
    <div class="w-full max-w-full sm:max-w-2xl bg-white rounded-none sm:rounded-lg shadow border border-gray-200 overflow-auto max-h-[90vh]">
      <div class="h-12 flex items-center justify-between px-4 border-b">
        <div class="text-sm font-semibold text-slate-800">Add FAQ</div>
        <button type="button" class="text-slate-500 hover:text-slate-700" data-close="create" aria-label="Close">
          <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 24 24" fill="currentColor"><path d="M6 18 18 6M6 6l12 12"/></svg>
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
    <div class="relative w-full max-w-full sm:max-w-2xl bg-white rounded-none sm:rounded-lg shadow border border-gray-200 overflow-auto max-h-[90vh]">
      
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

      <!-- More actions (shows '...' when restore/revisions available) -->
      <button id="moreActionsBtn" type="button" class="absolute top-3 right-12 text-slate-500 hover:text-slate-700 hidden" aria-label="More actions">
        <span class="text-xl font-bold">⋯</span>
      </button>

      <!-- More actions menu (hidden by default) -->
      <div id="moreActionsMenu" class="absolute top-10 right-3 hidden bg-white border border-gray-200 rounded shadow-md z-50 w-44">
        <div class="py-1">
          <button id="more_revisions_btn" type="button" class="w-full text-left px-3 py-2 text-sm text-gray-700 hover:bg-gray-50 hidden">View Revisions</button>
          <button id="more_restore_btn" type="button" class="w-full text-left px-3 py-2 text-sm text-gray-700 hover:bg-gray-50 hidden">Restore FAQ</button>
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

        <!-- Previous revision collapsible (populated dynamically) -->
        <div id="previousRevisionWrapper" class="mt-3 hidden">
          <button type="button" id="togglePrevRevisionBtn" class="text-sm text-blue-600 hover:underline">Show previous response</button>
          <div id="prevRevisionBlock" class="mt-2 hidden bg-gray-50 border border-gray-200 rounded p-3 text-sm whitespace-pre-line max-h-64 overflow-auto sm:max-h-[50vh]">
            <div id="prevRevisionMeta" class="text-xs text-slate-500 mb-2"></div>
            <div id="prevRevisionContent" class="text-slate-800"></div>
            <div class="mt-3 flex justify-end">
              <button id="restorePrevBtn" type="button" class="rounded-md bg-yellow-500 hover:bg-yellow-600 text-white text-sm px-3 py-1">Restore Previous</button>
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
                    class="rounded-md bg-blue-600 hover:bg-blue-700 text-white text-sm font-medium px-4 py-2">Save Changes</button>
          </div>
        </div>
      </form>
    </div>
  </div>
</div>



<!-- Hidden state with URLs -->
<div id="admin-faqs-state" class="hidden"
     data-list-url="{{ $listUrl ?? route('admin.faqs.list') }}"
     data-store-url="{{ route('admin.faqs.store') }}"
     data-show-url-template="{{ route('admin.faqs.show', ['faq' => '__ID__']) }}"
     data-update-url-template="{{ route('admin.faqs.update', ['faq' => '__ID__']) }}"
     data-destroy-url-template="{{ route('admin.faqs.destroy', ['faq' => '__ID__']) }}"
     data-revisions-url-template="{{ route('admin.faqs.revisions', ['faq' => '__ID__']) }}"
     data-restore-url-template="{{ route('admin.faqs.restore', ['faq' => '__ID__']) }}"></div>

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
(function () {
  const stateEl = document.getElementById('admin-faqs-state');
  const LIST_URL = stateEl.getAttribute('data-list-url');
  const STORE_URL = stateEl.getAttribute('data-store-url');
  const SHOW_TEMPLATE = stateEl.getAttribute('data-show-url-template');
  const UPDATE_TEMPLATE = stateEl.getAttribute('data-update-url-template');
  const DESTROY_TEMPLATE = stateEl.getAttribute('data-destroy-url-template');
  const RESTORE_TEMPLATE = stateEl.getAttribute('data-restore-url-template');
  const csrf = document.querySelector('meta[name="csrf-token"]').getAttribute('content');

  const $ = (sel, root = document) => root.querySelector(sel);
  const $$ = (sel, root = document) => Array.from(root.querySelectorAll(sel));
 
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

  function openModal(modal) { if (modal) modal.classList.remove('hidden'); }
  function closeModal(modal) { if (modal) modal.classList.add('hidden'); }

  // Fetch list via AJAX
  async function fetchList(page = 1) {
    currentPage = page;
    const q = encodeURIComponent((qInput.value || '').trim());
    const per = perPageSelect.value || '25';
    const url = `${LIST_URL}?q=${q}&per_page=${per}&page=${page}&include_deleted=${showDeleted ? '1' : '0'}`;
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
      <tr class="hover:bg-gray-50 ${f.deleted_at ? 'opacity-70' : ''}">
        <td class="py-3 pl-5 pr-3 align-top">
          <div class="text-slate-900 font-medium">${escapeHtml(f.intent)}</div>
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
            ${f.deleted_at ? (
              `<button class="restoreDeletedBtn inline-flex items-center gap-1 rounded-lg border border-gray-200 bg-white px-3 py-1.5 text-xs font-medium text-gray-700 hover:bg-gray-50" data-id="${f.id}">Restore</button>`
            ) : (
              `<button class="viewFaqBtn inline-flex items-center gap-1 rounded-lg border border-gray-200 bg-white px-3 py-1.5 text-xs font-medium text-gray-700 hover:bg-gray-50" data-id="${f.id}">View</button>`
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
          fetchList(currentPage);
        } catch (err) {
          showToast('error', err.message || 'Error');
          console.error(err);
        } finally {
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

  // Show deleted toggle handler
  if (showDeletedCheckbox) {
    showDeletedCheckbox.addEventListener('change', () => {
      showDeleted = !!showDeletedCheckbox.checked;
      fetchList(1);
    });
  }

  // Mobile search UI (toggles mobile search bar)
  const mobileSearchToggle = $('#mobileSearchToggle');
  const mobileSearchArea = $('#mobileSearchArea');
  const qMobile = $('#q_mobile');
  const mobileSearchBtn = $('#mobileSearchBtn');
  const mobileClearSearch = $('#mobileClearSearch');
  const perPageMobile = $('#per_page_mobile');

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

  if (mobileClearSearch) {
    mobileClearSearch.addEventListener('click', () => {
      if (qMobile) qMobile.value = '';
      qInput.value = '';
      toggleClear(false);
      fetchList(1);
    });
  }

  if (perPageMobile) {
    perPageMobile.addEventListener('change', () => {
      perPageSelect.value = perPageMobile.value;
      fetchList(1);
    });
  }

  // Create modal handlers
  if (createOpenBtn) createOpenBtn.addEventListener('click', () => {
    // reset fields
    createForm.reset();
    $('#create_topic_error').classList.add('hidden');
    $('#create_response_error').classList.add('hidden');
    openModal(createModal);
  });

  // Mobile create button - open the same modal
  const createOpenMobileBtn = $('#openCreateModalBtnMobile');
  if (createOpenMobileBtn) {
    createOpenMobileBtn.addEventListener('click', () => {
      // reset fields same as desktop open
      createForm.reset();
      $('#create_intent_error').classList.add('hidden');
      $('#create_response_error').classList.add('hidden');
      openModal(createModal);
    });
  }

  createCloseEls.forEach(el => el.addEventListener('click', () => closeModal(createModal)));

  createSubmit.addEventListener('click', async () => {
    // clear errors
    $('#create_topic_error').classList.add('hidden');
    $('#create_response_error').classList.add('hidden');

    const intent = $('#create_intent').value.trim();
    const response = $('#create_response').value.trim();
    if (!intent || !response) {
      if (!intent) { $('#create_intent_error').textContent = 'Intent is required'; $('#create_intent_error').classList.remove('hidden'); }
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
        body: JSON.stringify({ intent, response })
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
      viewTopic.value = f.intent || '';
      viewResponse.value = f.response || '';
      viewTimestamps.innerHTML = `
        <div class="tiny-text">Created: ${escapeHtml(f.created_at || '')}</div>
        <div class="text-super-small">Updated: ${escapeHtml(f.updated_at || '')}</div>
      `;
      // set update form action (not required for fetch but kept for semantics)
      viewForm.setAttribute('action', UPDATE_TEMPLATE.replace('__ID__', id));

      // Configure the "more actions" button/menu based on server flags
      if (moreBtn) {
        if (f.can_restore || f.can_revert || f.can_undo) {
          moreBtn.classList.remove('hidden');
          // Show/hide individual menu items
          if (moreRestoreBtn) {
            if (f.can_restore) {
              moreRestoreBtn.classList.remove('hidden');
              moreRestoreBtn.dataset.url = f.restore_url || '';
            } else {
              moreRestoreBtn.classList.add('hidden');
            }
          }
          if (moreRevisionsBtn) {
            if (f.can_revert) {
              moreRevisionsBtn.classList.remove('hidden');
              moreRevisionsBtn.dataset.url = f.revisions_url || '';
            } else {
              moreRevisionsBtn.classList.add('hidden');
            }
          }
        } else {
          moreBtn.classList.add('hidden');
          if (moreMenu) moreMenu.classList.add('hidden');
        }
      }

      // Populate previous revision collapsible
      if (f.latest_revision && prevWrapper) {
        prevWrapper.classList.remove('hidden');
        if (prevMeta) prevMeta.textContent = `${f.latest_revision.action || 'previous'} • ${f.latest_revision.created_at || ''}`;
        if (prevContent) prevContent.textContent = f.latest_revision.response || '';
        if (restorePrevBtn) restorePrevBtn.dataset.url = f.undo_url || '';
        // collapse by default
        if (prevBlock) prevBlock.classList.add('hidden');
        if (togglePrevBtn) togglePrevBtn.textContent = 'Show previous response';
      } else {
        if (prevWrapper) prevWrapper.classList.add('hidden');
      }

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
      intent: viewTopic.value.trim(),
      response: viewResponse.value.trim()
    };
    // basic validation
    let hasErr = false;
    $('#view_intent_error').classList.add('hidden');
    $('#view_response_error').classList.add('hidden');
    if (!payload.intent) { $('#view_intent_error').textContent = 'Intent required'; $('#view_intent_error').classList.remove('hidden'); hasErr = true; }
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
    const result = await Swal.fire({
      title: 'Delete FAQ?',
      text: 'This action cannot be undone.',
      icon: 'warning',
      showCancelButton: true,
      confirmButtonText: 'Yes, delete it',
      cancelButtonText: 'Cancel',
    });
    if (!result.isConfirmed) return;
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

  // More actions menu toggling & handlers
  if (moreBtn) {
    moreBtn.addEventListener('click', (e) => {
      e.stopPropagation();
      if (moreMenu) moreMenu.classList.toggle('hidden');
    });

    // Close menu when clicking outside
    document.addEventListener('click', (ev) => {
      if (!moreMenu) return;
      const target = ev.target;
      if (moreMenu.classList.contains('hidden')) return;
      if (!moreMenu.contains(target) && target !== moreBtn && !moreBtn.contains(target)) {
        moreMenu.classList.add('hidden');
      }
    });

    // View revisions - navigate to revisions page (full page)
    if (moreRevisionsBtn) {
      moreRevisionsBtn.addEventListener('click', () => {
        const url = moreRevisionsBtn.dataset.url || '';
        if (!url) return;
        // navigate to the revisions page in the same tab
        window.location.href = url;
      });
    }

    // Restore action - POST to restore endpoint
    if (moreRestoreBtn) {
      moreRestoreBtn.addEventListener('click', async () => {
        const url = moreRestoreBtn.dataset.url || '';
        if (!url) return;

        // Ask for confirmation before restoring
        const confirmResult = await Swal.fire({
          title: 'Restore FAQ?',
          text: 'Do you want to restore this response?',
          icon: 'question',
          showCancelButton: true,
          confirmButtonText: 'Yes, restore',
          cancelButtonText: 'Cancel'
        });
        if (!confirmResult.isConfirmed) return;

        try {
          moreRestoreBtn.disabled = true;
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
          // Use server-provided confirmation message when available
          showToast('success', json.message || 'FAQ restored');
          closeModal(viewModal);
          fetchList(currentPage);
        } catch (err) {
          showToast('error', err.message || 'Error');
          console.error(err);
        } finally {
          moreRestoreBtn.disabled = false;
        }
      });
    }
  }

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