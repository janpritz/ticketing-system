@extends('layouts.admin')

@section('title', 'Ticket Management')

@section('admin-content')
<div class="sm:px-2">
  <div class="flex items-center justify-between gap-4">
    <div>
      <h1 class="text-2xl font-semibold text-slate-900">Ticket Management</h1>
      <p class="text-sm text-slate-500 mt-1">Manage all tickets: respond, reroute, edit, delete.</p>
    </div>
    <div>
      <a href="{{ route('admin.dashboard') }}" class="inline-flex items-center gap-2 rounded-lg border border-gray-200 bg-white hover:bg-gray-50 text-sm font-medium px-3 py-2">
        ← Back to Dashboard
      </a>
    </div>
  </div>

  <div class="mt-4 bg-white rounded-xl border border-gray-200 overflow-hidden">
    <div class="overflow-x-auto">
      <table id="ticketsTable" class="min-w-full text-sm">
        <thead class="bg-gray-50 text-gray-600">
          <tr>
            <th class="py-3 pl-5 pr-3 text-left font-medium">Ticket</th>
            <th class="px-3 py-3 text-left font-medium">Subject</th>
            <th class="px-3 py-3 text-left font-medium">Category</th>
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

<!-- View / Respond Modal -->
<div id="ticketModal" class="fixed inset-0 z-50 hidden">
  <div class="absolute inset-0 bg-black/40" data-modal-backdrop></div>
  <div class="relative mx-auto my-10 w-[90%] max-w-3xl">
    <div class="bg-white rounded-xl shadow-xl ring-1 ring-black/5">
      <div class="flex items-center justify-between px-5 py-4 border-b">
        <div class="text-sm font-semibold text-slate-800">Ticket</div>
        <button type="button" class="text-gray-500 hover:text-gray-700" data-modal-close>
          <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 24 24" fill="currentColor">
            <path d="M6 18L18 6M6 6l12 12" />
          </svg>
        </button>
      </div>
      <div class="px-5 py-4 space-y-4">
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
        </div>
      </div>
      <div class="px-5 py-3 border-t flex items-center justify-between gap-3">
        <div class="flex items-center gap-2">
          <button id="tmDeleteBtn" type="button" class="rounded-md border border-red-200 bg-white text-red-700 px-3 py-1.5 text-sm">Delete</button>
          <button id="tmEditBtn" type="button" class="rounded-md border border-gray-200 bg-white px-3 py-1.5 text-sm">Edit</button>
        </div>
        <div class="flex items-center gap-2">
          <button id="tmRerouteBtn" type="button" class="rounded-md bg-white border border-gray-200 px-3 py-1.5 text-sm">Reroute</button>
          <button id="tmSendResponse" type="button" class="rounded-md bg-indigo-600 text-white px-4 py-1.5 text-sm">Send</button>
        </div>
      </div>
    </div>
  </div>
</div>

@section('admin-scripts')
<script>
(function(){
  const state = document.createElement('div');
  state.id = 'admin-tickets-state';
  state.className = 'hidden';
  state.setAttribute('data-list-url', "{{ route('admin.tickets.list') }}");
  state.setAttribute('data-show-url-template', "{{ route('admin.tickets.show', ['ticket' => '__ID__']) }}");
  state.setAttribute('data-respond-url-template', "{{ url('/admin/tickets') }}/__ID__/respond");
  state.setAttribute('data-reroute-url-template', "{{ url('/admin/tickets') }}/__ID__/reroute");
  state.setAttribute('data-destroy-url-template', "{{ url('/admin/tickets') }}/__ID__");
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

  async function fetchList(page=1){
    currentPage = page;
    try {
      const res = await fetch(LIST_URL + '?page=' + page, { headers: { 'X-Requested-With': 'XMLHttpRequest' }});
      if (!res.ok) throw new Error('Failed to load tickets');
      const json = await res.json();
      renderTable(json.items || []);
      renderPagination(json.meta || {});
    } catch(err) {
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
          <td class="px-3 py-4">${escapeHtml((t.question||'').slice(0,80))}</td>
          <td class="px-3 py-4">${escapeHtml(t.category||'')}</td>
          <td class="px-3 py-4">${escapeHtml(t.status||'')}</td>
          <td class="px-3 py-4">${escapeHtml((t.staff && t.staff.name) || '-')}</td>
          <td class="px-3 py-4">${escapeHtml(fmtDate(t.date_created||t.created_at))}</td>
          <td class="py-4 pl-3 pr-5">
            <div class="flex items-center gap-2">
              <button class="btn-view inline-flex items-center gap-2 rounded-lg border border-gray-200 bg-white px-3 py-1.5 text-xs" data-id="${t.id}">View</button>
              <button class="btn-edit inline-flex items-center gap-2 rounded-lg border border-gray-200 bg-white px-3 py-1.5 text-xs" data-id="${t.id}">Edit</button>
              <button class="btn-delete inline-flex items-center gap-2 rounded-lg border border-red-200 bg-white px-3 py-1.5 text-xs text-red-700" data-id="${t.id}">Delete</button>
            </div>
          </td>
        </tr>
      `;
    }).join('');
    // attach view handlers
    document.querySelectorAll('.btn-view').forEach(b => b.addEventListener('click', async (e) => {
      const id = b.getAttribute('data-id');
      openModalFor(id);
    }));
    document.querySelectorAll('.btn-edit').forEach(b => b.addEventListener('click', (e)=> {
      const id = b.getAttribute('data-id');
      const t = ticketsMap.get(String(id));
      if (!t) return;
      // simple inline edit: prompt for question
      const q = prompt('Edit question', t.question||'');
      if (q === null) return;
      saveEdit(id, { question: q });
    }));
    document.querySelectorAll('.btn-delete').forEach(b => b.addEventListener('click', (e)=> {
      const id = b.getAttribute('data-id');
      if (!confirm('Delete ticket?')) return;
      deleteTicket(id);
    }));
  }

  function renderPagination(meta){
    ticketsPagination.innerHTML = '';
    const total = meta.total || 0;
    const current = meta.current_page || 1;
    const last = meta.last_page || 1;
    ticketsPagination.innerHTML = `<div class="text-sm text-slate-600">Page ${current} of ${last} • ${total} total</div>
      <div class="flex items-center gap-2">
        <button ${current<=1?'disabled':''} data-page="${current-1}" class="pagerBtn rounded-md border border-gray-200 bg-white px-3 py-1 text-sm">Prev</button>
        <button ${current>=last?'disabled':''} data-page="${current+1}" class="pagerBtn rounded-md border border-gray-200 bg-white px-3 py-1 text-sm">Next</button>
      </div>`;
    ticketsPagination.querySelectorAll('.pagerBtn').forEach(b => b.addEventListener('click', ()=> {
      const p = Number(b.getAttribute('data-page')||1);
      if (!isNaN(p)) fetchList(p);
    }));
  }

  function escapeHtml(s){ if (s==null) return ''; return String(s).replace(/&/g,'&').replace(/</g,'<').replace(/>/g,'>').replace(/"/g,'"').replace(/'/g,"&#039;"); }

  async function openModalFor(id){
    const url = SHOW_TEMPLATE.replace('__ID__', id);
    try {
      const res = await fetch(url, { headers: { 'X-Requested-With': 'XMLHttpRequest' }});
      if (!res.ok) throw new Error('Failed to load ticket');
      const t = await res.json();
      // populate modal
      document.getElementById('tmInfo').textContent = `#${t.id} • ${t.email || '-'} • ${t.category || ''}`;
      document.getElementById('tmQuestion').textContent = t.question || '';
      document.getElementById('tmResponse').value = '';
      ticketModal.classList.remove('hidden');
      // actions
      document.getElementById('tmSendResponse').onclick = async () => {
        const msg = document.getElementById('tmResponse').value.trim();
        if (!msg) return alert('Enter a response');
        const rUrl = RESPOND_TEMPLATE.replace('__ID__', id);
        try {
          const resp = await fetch(rUrl, { method: 'POST', headers: { 'Content-Type':'application/json','X-CSRF-TOKEN':csrf }, body: JSON.stringify({ message: msg })});
          if (!resp.ok) throw new Error('Failed to send response');
          try { localStorage.setItem('ts_tickets_changed', String(Date.now())); } catch(e){}
          fetchList(currentPage);
          ticketModal.classList.add('hidden');
        } catch(err){ console.error(err); alert('Error sending response'); }
      };
      document.getElementById('tmRerouteBtn').onclick = async () => {
        const role = document.getElementById('tmRerouteSelect').value;
        if (!role) return alert('Choose a role');
        const rUrl = REROUTE_TEMPLATE.replace('__ID__', id);
        try {
          const resp = await fetch(rUrl, { method: 'POST', headers: { 'Content-Type':'application/json','X-CSRF-TOKEN':csrf }, body: JSON.stringify({ role })});
          if (!resp.ok) throw new Error('Failed to reroute');
          try { localStorage.setItem('ts_tickets_changed', String(Date.now())); } catch(e){}
          fetchList(currentPage);
          ticketModal.classList.add('hidden');
        } catch(err){ console.error(err); alert('Error rerouting'); }
      };
      document.getElementById('tmDeleteBtn').onclick = async () => {
        if (!confirm('Delete ticket?')) return;
        try {
          const dUrl = DESTROY_TEMPLATE.replace('__ID__', id);
          const res = await fetch(dUrl, { method: 'DELETE', headers: { 'X-CSRF-TOKEN': csrf }});
          if (!res.ok) throw new Error('Failed to delete');
          try { localStorage.setItem('ts_tickets_changed', String(Date.now())); } catch(e){}
          fetchList(currentPage);
          ticketModal.classList.add('hidden');
        } catch(err){ console.error(err); alert('Delete failed'); }
      };
    } catch(err){ console.error(err); alert('Failed to load ticket'); }
  }

  // inline save edit
  async function saveEdit(id, payload){
    try {
      const upUrl = "{{ url('/admin/tickets') }}/" + id;
      const res = await fetch(upUrl, { method: 'PUT', headers: { 'Content-Type':'application/json','X-CSRF-TOKEN':csrf }, body: JSON.stringify(payload)});
      if (!res.ok) throw new Error('Failed to update');
      try { localStorage.setItem('ts_tickets_changed', String(Date.now())); } catch(e){}
      fetchList(currentPage);
    } catch(err){ console.error(err); alert('Update failed'); }
  }

  async function deleteTicket(id){
    try {
      const dUrl = DESTROY_TEMPLATE.replace('__ID__', id);
      const res = await fetch(dUrl, { method: 'DELETE', headers: { 'X-CSRF-TOKEN': csrf }});
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

  // initial load
  fetchList(1);
})();
</script>
@endsection