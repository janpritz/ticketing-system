@extends('layouts.admin')

@section('title', 'Ticket Management')

@section('admin-content')
<div class="sm:px-2">
  <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4">
    <div>
      <h1 class="text-2xl font-semibold text-slate-900">Ticket Management</h1>
      <p class="text-sm text-slate-500 mt-1">Manage all tickets: respond, reroute, edit, delete.</p>
    </div>

    <div class="flex items-center gap-3">
      <!-- Desktop search + per-page -->
      <div class="hidden md:flex items-center gap-2">
        <label class="relative block">
          <span class="sr-only">Search</span>
          <span class="absolute inset-y-0 left-0 flex items-center pl-3 text-gray-400">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" viewBox="0 0 24 24" fill="currentColor">
              <path d="M15.5 14h-.79l-.28-.27a6.5 6.5 0 10-.71.71l.27.28v.79L20 21.5 21.5 20l-6-6zM10 15a5 5 0 110-10 5 5 0 010 10z"/>
            </svg>
          </span>
          <input id="q" type="text" placeholder="Search tickets..." class="pl-9 pr-3 py-2 rounded-md border border-gray-200 text-sm w-72" />
        </label>
        <button id="searchBtn" class="rounded-md border border-gray-300 bg-white px-3 py-2 text-sm">Search</button>
      </div>

      <!-- Mobile search -->
      <div class="md:hidden flex items-center gap-2">
        <input id="q_mobile" type="text" placeholder="Search tickets..." class="pl-3 pr-3 py-2 rounded-md border border-gray-200 text-sm w-full" />
        <button id="searchBtnMobile" class="rounded-md border border-gray-300 bg-white px-3 py-2 text-sm">Search</button>
      </div>

      <button id="openFiltersBtn" class="ml-2 rounded-md border border-gray-200 bg-white px-3 py-2 text-sm">Filters</button>
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
            <th class="py-3 pl-3 pr-5 text-left font-medium">Actions</th>
          </tr>
        </thead>
        <tbody id="ticketsTbody" class="divide-y divide-gray-100">
          <tr>
            <td colspan="7" class="px-5 py-6 text-center text-sm text-gray-500">Loading...</td>
          </tr>
        </tbody>
      </table>
    </div>
    <div id="ticketsFooter" class="px-5 py-3 border-t border-gray-200">
      <div id="ticketsPagination" class="flex items-center justify-between"></div>
    </div>
  </div>
</div>

<div id="ticketsDrawerOverlay" class="hidden fixed inset-0 bg-black/30 z-40"></div>
<!-- Bottom drawer: Filters & Sort -->
<div id="ticketsBottomDrawer" class="fixed left-0 right-0 bottom-0 z-50 bg-white border-t border-gray-200 shadow-lg transform translate-y-full transition-transform duration-200">
  <div class="px-4 py-3 flex items-center justify-between border-b">
    <div class="text-sm font-semibold text-slate-800">Filters & Sort</div>
    <button id="closeFiltersBtn" type="button" class="p-2 rounded-md text-slate-600 hover:text-slate-800 hover:bg-gray-50" aria-label="Close filters">
      <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 24 24" fill="currentColor"><path d="M6 18 18 6M6 6l12 12"/></svg>
    </button>
  </div>
  <div class="px-4 py-3 grid grid-cols-1 sm:grid-cols-4 gap-3">
    <div>
      <label for="filterStatus" class="block text-xs text-slate-600 mb-1">Status</label>
      <select id="filterStatus" class="w-full rounded-md border border-gray-300 bg-white text-sm px-3 py-2">
        <option value="">All</option>
        <option value="Open">Open</option>
        <option value="Re-routed">Re-routed</option>
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
        @foreach($roles as $r)
          <option value="{{ $r }}">{{ $r }}</option>
        @endforeach
      </select>
    </div>
    <div>
      <label for="filterAssigneeId" class="block text-xs text-slate-600 mb-1">Assignee</label>
      <select id="filterAssigneeId" class="w-full rounded-md border border-gray-300 bg-white text-sm px-3 py-2">
        <option value="">All</option>
        @isset($users)
          @foreach($users as $u)
            <option value="{{ $u->id }}">{{ $u->name }}@if(!empty($u->role)) ({{ $u->role }})@endif</option>
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
    <button id="resetFiltersBtn" type="button" class="rounded-md border border-gray-300 bg-white px-3 py-2 text-sm">Reset</button>
    <button id="applyFiltersBtn" type="button" class="rounded-md bg-blue-600 text-white px-4 py-2 text-sm">Apply</button>
  </div>
</div>

<!-- View / Respond Modal -->
<div id="ticketModal" class="fixed inset-0 z-50 hidden overflow-auto">
  <div class="absolute inset-0 bg-black/40" data-modal-backdrop></div>
  <!-- Use adaptive container that allows the modal to scroll when taller than the viewport -->
  <div class="relative mx-auto my-6 w-[90%] max-w-3xl max-h-[90vh]">
    <div class="bg-white rounded-xl shadow-xl ring-1 ring-black/5 overflow-hidden max-h-[90vh] flex flex-col">
<!-- Bottom drawer: Filters & Sort (opens from bottom on mobile / small screens) -->
<!-- (moved earlier into header area to avoid being inside modal) -->
      <div class="flex items-center justify-between px-5 py-4 border-b">
        <div class="text-sm font-semibold text-slate-800">Ticket Details</div>
        <div>
          <button type="button" data-modal-close aria-label="Close ticket details" class="inline-flex items-center justify-center h-8 w-8 rounded-md text-slate-600 hover:text-slate-800 hover:bg-gray-50">
            <span class="sr-only">Close</span>
            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
              <line x1="18" y1="6" x2="6" y2="18"></line>
              <line x1="6" y1="6" x2="18" y2="18"></line>
            </svg>
          </button>
        </div>
      </div>
      <div class="px-5 py-4 space-y-4 overflow-auto flex-1">
        <div id="tmInfo" class="text-xs text-gray-500"></div>
        <div>
          <label class="block text-xs text-gray-500">Question</label>
          <div id="tmQuestion" class="text-sm text-gray-800 whitespace-pre-wrap"></div>
        </div>
        <div>
          <label class="block text-xs text-gray-500">Response (send email)</label>
          <textarea id="tmResponse" class="w-full rounded-md border-gray-300 px-3 py-2 text-sm" rows="4"></textarea>
        </div>
        <div>
          <label class="block text-xs text-gray-500">Reroute to</label>
          <div class="flex items-center gap-2">
            <select id="tmRerouteSelect" class="rounded-md border-gray-300 text-sm px-3 py-2">
              <option value="" selected disabled>Select role</option>
              <option>Primary Administrator</option>
              <option>Enrollment</option>
              <option>Finance and Payments</option>
              <option>Scholarships</option>
              <option>Academic Concerns</option>
              <option>Exams</option>
              <option>Student Services</option>
              <option>Library Services</option>
              <option>IT Support</option>
              <option>Graduation</option>
              <option>Athletics and Sports</option>
            </select>
            <!-- Inline reroute button shown only when a role is selected -->
            <button id="tmRerouteInlineBtn" type="button" class="hidden rounded-md bg-white border border-gray-200 px-3 py-1.5 text-sm">Reroute</button>
          </div>

          <!-- Collapsible reroute history (hidden when empty) -->
          <div id="tmRerouteHistoryContainer" class="mt-3 hidden">
            <button type="button" id="tmHistoryToggle" class="w-full text-left text-sm text-slate-600 px-2 py-1 rounded-md hover:bg-gray-50 flex items-center justify-between">
              <span>Reroute History</span>
              <svg id="tmHistoryIcon" class="h-4 w-4 text-slate-500 transition-transform" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                <path d="M6 9l6 6 6-6" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
              </svg>
            </button>
            <div id="tmHistoryPanel" class="mt-2 px-2 py-2 border rounded-md bg-gray-50 hidden max-h-64 overflow-auto"></div>
          </div>
        </div>
      </div>
      <div class="px-5 py-3 border-t flex items-center justify-between gap-3">
        <div></div>
        <div class="flex items-center gap-2">
          <!-- Send button is disabled by default and becomes active/blue when response has text -->
          <button id="tmSendResponse" type="button" disabled class="rounded-md bg-gray-300 text-white px-4 py-1.5 text-sm">Send</button>
        </div>
      </div>
    </div>
  </div>
</div>

@endsection
@section('admin-scripts')
<script>
(function(){
  const state = document.createElement('div');
  state.id = 'admin-tickets-state';
  state.className = 'hidden';
  // Prefix routes with /public for deployments that serve the app under the public folder (e.g. fritzcabalhin.com/public/...)
  state.setAttribute('data-list-url', "/public{{ route('admin.tickets.list') }}");
  // Use named routes to build templates — insert the placeholder string '__ID__' into the route.
  state.setAttribute('data-show-url-template', "/public{{ route('admin.tickets.show', ['ticket' => '__ID__']) }}");
  state.setAttribute('data-respond-url-template', "/public{{ route('admin.tickets.respond', ['ticket' => '__ID__']) }}");
  state.setAttribute('data-reroute-url-template', "/public{{ route('admin.tickets.reroute', ['ticket' => '__ID__']) }}");
  state.setAttribute('data-destroy-url-template', "/public{{ route('admin.tickets.destroy', ['ticket' => '__ID__']) }}");
  document.body.appendChild(state);

  const LIST_URL = state.getAttribute('data-list-url');
  const SHOW_TEMPLATE = state.getAttribute('data-show-url-template');
  const RESPOND_TEMPLATE = state.getAttribute('data-respond-url-template');
  const REROUTE_TEMPLATE = state.getAttribute('data-reroute-url-template');
  const DESTROY_TEMPLATE = state.getAttribute('data-destroy-url-template');
  const csrf = document.querySelector('meta[name="csrf-token"]').getAttribute('content');

  const ticketsTbody = document.getElementById('ticketsTbody');
  const ticketsPagination = document.getElementById('ticketsPagination');
  const ticketModal = document.getElementById('ticketModal');

  let currentPage = 1;
  let ticketsMap = new Map();

  function fmtDate(d){ try { const dt=new Date(d); return isNaN(dt)?'':dt.toLocaleString(); } catch(_) { return ''; } }

  async function fetchList(page = 1, minimal = false) {
    currentPage = page;
    try {
      // read UI filters
      const qEl = document.getElementById('q');
      const qMobileEl = document.getElementById('q_mobile');
      const qVal = (qEl && qEl.value.trim()) ? qEl.value.trim() : (qMobileEl && qMobileEl.value.trim() ? qMobileEl.value.trim() : '');
      const perEl = document.getElementById('filterPerPage') || document.getElementById('perPageSelect');
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
 
      const res = await fetch(url, { headers: { 'X-Requested-With': 'XMLHttpRequest' }, credentials: 'same-origin' });
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
        try { localStorage.setItem('ts_tickets_last_changed', String(json.last_changed)); } catch(e){}
      }
    } catch (err) {
      ticketsTbody.innerHTML = '<tr><td colspan="7" class="px-5 py-6 text-center text-sm text-red-600">Error loading tickets</td></tr>';
    }
  }

  function renderTable(items){
    ticketsMap = new Map(items.map(t => [String(t.id), t]));
    if (!items.length) {
      ticketsTbody.innerHTML = '<tr><td colspan="7" class="px-5 py-10 text-center text-sm text-gray-500">No tickets found.</td></tr>';
      return;
    }
    ticketsTbody.innerHTML = items.map(t => {
      const year = t.date_created ? new Date(t.date_created).getFullYear() : (new Date().getFullYear());
      const ticketNo = `T-${year}-${String(t.id).padStart(4,'0')}`;
      return `
        <tr class="hover:bg-gray-50">
          <td class="py-4 pl-5 pr-3">${ticketNo}</td>
          <td class="px-3 py-4">${escapeHtml(t.category||'')}</td>
          <td class="px-3 py-4">${escapeHtml((t.question||'').slice(0,80))}</td>
          <td class="px-3 py-4">${escapeHtml(t.status||'')}</td>
          <td class="px-3 py-4">${escapeHtml((t.staff && t.staff.name) || '-')}</td>
          <td class="px-3 py-4">${escapeHtml(fmtDate(t.date_created||t.created_at))}</td>
          <td class="py-4 pl-3 pr-5">
            <div class="flex items-center gap-2">
              <button class="btn-view inline-flex items-center gap-2 rounded-lg border border-gray-200 bg-white px-3 py-1.5 text-xs" data-id="${t.id}">View</button>
            </div>
          </td>
        </tr>
      `;
    }).join('');
    // Use event delegation for actions (more reliable with dynamic table updates)
    // Only the "View" action is rendered in the list now.
    ticketsTbody.addEventListener('click', (e) => {
      const btn = e.target.closest('.btn-view');
      if (!btn) return;
      const id = btn.getAttribute('data-id');
      if (!id) {
        console.error('Action button missing data-id');
        return;
      }
      openModalFor(id);
    });
  }

  function renderPagination(meta) {
    if (!meta || !meta.total) {
      ticketsPagination.innerHTML = '';
      return;
    }
    const total = meta.total || 0;
    const per = meta.per_page || (document.getElementById('filterPerPage') ? document.getElementById('filterPerPage').value : 25);
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
    qInput.addEventListener('keyup', (e) => { if (e.key === 'Enter') fetchList(1); });
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

  function escapeHtml(s){ if (s==null) return ''; return String(s).replace(/&/g,'&').replace(/</g,'<').replace(/>/g,'>').replace(/"/g,'"').replace(/'/g,"&#039;"); }

  async function openModalFor(id){
    const url = SHOW_TEMPLATE.replace('__ID__', id);
    try {
      // Helpful debug logs to diagnose why the ticket fetch might fail (status, content-type).
      console.debug('openModalFor: fetching', url);
      const res = await fetch(url, { headers: { 'X-Requested-With': 'XMLHttpRequest' }, credentials: 'same-origin' });
      console.debug('openModalFor: response status=', res.status, 'ok=', res.ok, 'headers=', Array.from(res.headers.entries()));
      const contentType = (res.headers.get('content-type') || '').toLowerCase();
      if (!res.ok) {
        const text = await res.text().catch(() => '[body unreadable]');
        console.error('openModalFor: non-ok response', res.status, text);
        alert('Failed to load ticket (status ' + res.status + '). See console for details.');
        return;
      }
      if (!contentType.includes('application/json')) {
        const text = await res.text().catch(() => '[body unreadable]');
        console.error('openModalFor: expected JSON but got', contentType, text);
        alert('Failed to load ticket: unexpected response from server. Check console for details.');
        return;
      }
      const t = await res.json();
      // populate modal
      document.getElementById('tmInfo').textContent = `#${t.id} • ${t.email || '-'} • ${t.category || ''}`;
      document.getElementById('tmQuestion').textContent = t.question || '';
      const respEl = document.getElementById('tmResponse');
      const sendBtn = document.getElementById('tmSendResponse');
      const rerouteSelect = document.getElementById('tmRerouteSelect');
      const rerouteInlineBtn = document.getElementById('tmRerouteInlineBtn');

      // reset response field and UI
      if (respEl) respEl.value = '';
      ticketModal.classList.remove('hidden');

      // Helper to toggle send button enabled state and style
      function toggleSendButton() {
        if (!sendBtn || !respEl) return;
        const hasText = respEl.value.trim().length > 0;
        sendBtn.disabled = !hasText;
        if (hasText) {
          sendBtn.className = 'rounded-md bg-indigo-600 text-white px-4 py-1.5 text-sm';
        } else {
          sendBtn.className = 'rounded-md bg-gray-300 text-white px-4 py-1.5 text-sm';
        }
      }

      // Hook response input for realtime enable/disable
      if (respEl) {
        respEl.removeEventListener('input', toggleSendButton);
        respEl.addEventListener('input', toggleSendButton);
      }
      // initial toggle
      toggleSendButton();

      // Show/hide inline reroute button when a role is selected
      if (rerouteSelect && rerouteInlineBtn) {
        // initialize visibility
        rerouteInlineBtn.classList.toggle('hidden', !rerouteSelect.value);
        rerouteSelect.removeEventListener('change', () => {});
        rerouteSelect.addEventListener('change', () => {
          rerouteInlineBtn.classList.toggle('hidden', !rerouteSelect.value);
        });
      }

      // Render reroute history if present. The server returns routing histories under either
      // `routingHistories` or `routing_histories` depending on how Eloquent serialized it.
      const historyContainer = document.getElementById('tmRerouteHistoryContainer');
      const historyPanel = document.getElementById('tmHistoryPanel');
      const historyToggle = document.getElementById('tmHistoryToggle');
      const historyIcon = document.getElementById('tmHistoryIcon');

      function renderRerouteHistory(histories) {
        if (!historyContainer || !historyPanel || !historyToggle) return;
        const list = histories || (t.routingHistories || t.routing_histories) || [];
        if (!Array.isArray(list) || list.length === 0) {
          historyContainer.classList.add('hidden');
          return;
        }

        // Build history items
        historyPanel.innerHTML = list.map(h => {
          const routedAt = h.routed_at || h.routedAt || h.created_at || '';
          const staffName = (h.staff && (h.staff.name || h.staff_name)) || h.staff_name || '-';
          const status = h.status || '';
          const notes = h.notes || '';
          return `<div class="border-b last:border-b-0 py-2 text-sm">
                    <div class="text-xs text-slate-500">${escapeHtml(routedAt)}</div>
                    <div class="text-sm text-slate-800 font-medium">${escapeHtml(staffName)} — ${escapeHtml(status)}</div>
                    <div class="text-sm text-slate-700">${escapeHtml(notes)}</div>
                  </div>`;
        }).join('');

        historyContainer.classList.remove('hidden');
        historyPanel.classList.add('hidden'); // collapsed by default
        if (historyIcon) historyIcon.classList.remove('rotate-180');

        // Toggle behavior
        historyToggle.onclick = () => {
          const isHidden = historyPanel.classList.contains('hidden');
          if (isHidden) {
            historyPanel.classList.remove('hidden');
            if (historyIcon) historyIcon.classList.add('rotate-180');
          } else {
            historyPanel.classList.add('hidden');
            if (historyIcon) historyIcon.classList.remove('rotate-180');
          }
        };
      }

      // Populate history based on returned ticket
      try {
        renderRerouteHistory(t.routingHistories || t.routing_histories || []);
      } catch (e) {
        console.warn('Failed to render reroute history', e);
        if (historyContainer) historyContainer.classList.add('hidden');
      }

      // Send handler (safe attach)
      if (sendBtn) {
        sendBtn.onclick = async () => {
          if (!respEl) return;
          const msg = respEl.value.trim();
          if (!msg) {
            if (window.Swal) Swal.fire({ position: 'top-end', icon: 'warning', toast: true, title: 'Enter a response', showConfirmButton: false, timer: 3000, timerProgressBar: true });
            return;
          }
          const rUrl = RESPOND_TEMPLATE.replace('__ID__', id);
          try {
            const resp = await fetch(rUrl, { method: 'POST', headers: { 'Content-Type':'application/json','X-CSRF-TOKEN':csrf }, body: JSON.stringify({ message: msg }), credentials: 'same-origin' });
            if (resp && resp.ok) {
              try { localStorage.setItem('ts_tickets_changed', String(Date.now())); } catch(e){}
              fetchList(currentPage);
              ticketModal.classList.add('hidden');
              if (window.Swal) Swal.fire({ position: 'top-end', icon: 'success', toast: true, title: 'Response sent', showConfirmButton: false, timer: 3000, timerProgressBar: true });
            } else {
              const txt = resp ? await resp.text().catch(()=> '') : '';
              console.error('Send response failed', txt);
              ticketModal.classList.add('hidden');
              if (window.Swal) Swal.fire({ position: 'top-end', icon: 'error', toast: true, title: 'Send failed', showConfirmButton: false, timer: 3000, timerProgressBar: true });
            }
          } catch(err){
            console.error('Send response error', err);
            ticketModal.classList.add('hidden');
            if (window.Swal) Swal.fire({ position: 'top-end', icon: 'error', toast: true, title: 'Send error', showConfirmButton: false, timer: 3000, timerProgressBar: true });
          }
        };
      }

      // Reroute handler (inline button) - safe attach
      if (rerouteInlineBtn) {
        rerouteInlineBtn.onclick = async () => {
          const role = rerouteSelect ? rerouteSelect.value : '';
          if (!role) {
            if (window.Swal) Swal.fire({ position: 'top-end', icon: 'warning', toast: true, title: 'Choose a role', showConfirmButton: false, timer: 3000, timerProgressBar: true });
            return;
          }
          const rUrl = REROUTE_TEMPLATE.replace('__ID__', id);
          try {
            const resp = await fetch(rUrl, { method: 'POST', headers: { 'Content-Type':'application/json','X-CSRF-TOKEN':csrf }, body: JSON.stringify({ role }), credentials: 'same-origin' });
            if (resp && resp.ok) {
              try { localStorage.setItem('ts_tickets_changed', String(Date.now())); } catch(e){}
              fetchList(currentPage);
              ticketModal.classList.add('hidden');
              if (window.Swal) Swal.fire({ position: 'top-end', icon: 'success', toast: true, title: 'Rerouted', showConfirmButton: false, timer: 3000, timerProgressBar: true });
            } else {
              const txt = resp ? await resp.text().catch(()=> '') : '';
              console.error('Reroute failed', txt);
              ticketModal.classList.add('hidden');
              if (window.Swal) Swal.fire({ position: 'top-end', icon: 'error', toast: true, title: 'Reroute failed', showConfirmButton: false, timer: 3000, timerProgressBar: true });
            }
          } catch(err){
            console.error('Error rerouting', err);
            ticketModal.classList.add('hidden');
            if (window.Swal) Swal.fire({ position: 'top-end', icon: 'error', toast: true, title: 'Reroute error', showConfirmButton: false, timer: 3000, timerProgressBar: true });
          }
        };
      }
    } catch(err){
      console.error('openModalFor: unexpected error', err);
      alert('Failed to load ticket (unexpected error). See console for details.');
    }
  }

  // inline save edit
  async function saveEdit(id, payload){
    try {
      const upUrl = "{{ url('/admin/tickets') }}/" + id;
      const res = await fetch(upUrl, { method: 'PUT', headers: { 'Content-Type':'application/json','X-CSRF-TOKEN':csrf }, body: JSON.stringify(payload), credentials: 'same-origin' });
      if (!res.ok) throw new Error('Failed to update');
      try { localStorage.setItem('ts_tickets_changed', String(Date.now())); } catch(e){}
      fetchList(currentPage);
    } catch(err){ console.error(err); alert('Update failed'); }
  }

  async function deleteTicket(id){
    try {
      const dUrl = DESTROY_TEMPLATE.replace('__ID__', id);
      const res = await fetch(dUrl, { method: 'DELETE', headers: { 'X-CSRF-TOKEN': csrf }, credentials: 'same-origin' });
      if (!res.ok) throw new Error('Failed to delete');
      try { localStorage.setItem('ts_tickets_changed', String(Date.now())); } catch(e){}
      fetchList(currentPage);
    } catch(err){ console.error(err); alert('Delete failed'); }
  }

  // Close modal handlers
  document.addEventListener('click', (e) => {
    if (e.target && e.target.closest('[data-modal-backdrop]')) {
      ticketModal.classList.add('hidden');
    }
    if (e.target && e.target.getAttribute && e.target.getAttribute('data-modal-close') != null) {
      ticketModal.classList.add('hidden');
    }
    if (e.target && e.target.closest && e.target.closest('[data-modal-close]')) {
      ticketModal.classList.add('hidden');
    }
  });

  // Cross-tab refresh listeners
  window.addEventListener('storage', (e)=> { if (e && e.key === 'ts_tickets_changed') fetchList(currentPage); });
  window.addEventListener('focus', ()=> { try { if (localStorage.getItem('ts_tickets_changed')) fetchList(currentPage); } catch(_){} });
  document.addEventListener('visibilitychange', ()=> { try { if (!document.hidden && localStorage.getItem('ts_tickets_changed')) fetchList(currentPage); } catch(_){} });

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