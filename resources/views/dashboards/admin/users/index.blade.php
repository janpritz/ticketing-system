@extends('layouts.admin')

@section('title', 'User Management')

@section('admin-content')
<div class="sm:px-2">
    <div class="flex items-center justify-between gap-4">
        <div>
            <h1 class="text-2xl font-semibold text-slate-900">User Management</h1>
            <p class="text-sm text-slate-500">Manage staff accounts (excluding Primary Administrator)</p>
        </div>

        <!-- Desktop actions -->
        <div class="hidden sm:flex items-center gap-2">
            <button id="openCreateModalBtn" type="button" class="inline-flex items-center gap-2 rounded-lg bg-blue-600 hover:bg-blue-700 text-white text-sm font-medium px-3 py-2" aria-label="Add Staff">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" viewBox="0 0 24 24" fill="currentColor"><path d="M11 11V5h2v6h6v2h-6v6h-2v-6H5v-2z"/></svg>
                <span class="hidden sm:inline">Add Staff</span>
            </button>
        </div>

        <!-- Mobile toolbar: icons only -->
        <div class="flex sm:hidden items-center gap-2">
            <button id="mobileSearchToggle" type="button" class="p-2 rounded-lg bg-gray-100 hover:bg-gray-200 text-slate-700" aria-label="Search">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 24 24" fill="currentColor"><path d="M15.5 14h-.79l-.28-.27a6.5 6.5 0 10-.71.71l.27.28v.79L20 21.5 21.5 20l-6-6zM10 15a5 5 0 110-10 5 5 0 010 10z" /></svg>
            </button>

            <button id="openCreateModalBtnMobile" type="button" class="p-2 rounded-lg bg-blue-600 hover:bg-blue-700 text-white" aria-label="Add Staff (mobile)">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 24 24" fill="currentColor"><path d="M11 11V5h2v6h6v2h-6v6h-2v-6H5v-2z"/></svg>
            </button>
        </div>
    </div>

    @if (session('status'))
        <div class="mt-4 rounded-md border border-emerald-200 bg-emerald-50 text-emerald-800 px-4 py-2 text-sm">
            {{ session('status') }}
        </div>
    @endif
    @if ($errors->any())
        <div class="mt-4 rounded-md border border-red-200 bg-red-50 text-red-800 px-4 py-2 text-sm">
            {{ $errors->first() }}
        </div>
    @endif

    <div class="mt-5">
        <!-- Desktop search -->
        <div class="hidden sm:flex items-center gap-3">
            <form method="GET" action="{{ route('admin.users.index') }}" class="flex items-center gap-2">
                <label class="relative block">
                    <span class="sr-only">Search</span>
                    <span class="absolute inset-y-0 left-0 flex items-center pl-3 text-gray-400">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" viewBox="0 0 24 24" fill="currentColor">
                            <path d="M15.5 14h-.79l-.28-.27a6.5 6.5 0 10-.71.71l.27.28v.79L20 21.5 21.5 20l-6-6zM10 15a5 5 0 110-10 5 5 0 010 10z" />
                        </svg>
                    </span>
                    <input type="text" name="q" value="{{ $q ?? '' }}" placeholder="Search name, email, role, category"
                           class="w-72 pl-9 pr-3 py-2 text-sm rounded-md border border-gray-200 focus:ring-2 focus:ring-blue-500 focus:border-blue-500" />
                </label>
                <button type="submit" class="rounded-md border border-gray-300 bg-white hover:bg-gray-50 text-sm px-3 py-2">Search</button>
                @if(($q ?? '') !== '')
                    <a href="{{ route('admin.users.index') }}" class="text-sm text-slate-600 hover:text-slate-800">Clear</a>
                @endif
            </form>
        </div>

        <!-- Mobile search area (toggled) -->
        <div id="mobileSearchArea" class="sm:hidden mt-3 hidden">
            <div class="flex items-center gap-2">
                <input id="q_mobile" type="text" placeholder="Search name, email, role, category" class="flex-1 pl-3 pr-3 py-2 rounded-md border border-gray-200 text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500" />
                <button id="mobileSearchBtn" type="button" class="rounded-md border border-gray-300 bg-white hover:bg-gray-50 text-sm px-3 py-2" aria-label="Search">Search</button>
            </div>
        </div>
    </div>

    <div class="mt-4 bg-white rounded-xl border border-gray-200 overflow-hidden">
        <div class="overflow-x-auto">
            <table class="min-w-full text-sm">
                <thead class="bg-gray-50 text-gray-600">
                    <tr>
                        <th class="py-3 pl-5 pr-3 text-left font-medium">Name</th>
                        <th class="px-3 py-3 text-left font-medium">Email</th>
                        <th class="px-3 py-3 text-left font-medium">Role</th>
                        <th class="px-3 py-3 text-left font-medium">Category</th>
                        <th class="py-3 pl-3 pr-5 text-left font-medium">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @forelse($users as $u)
                        <tr class="hover:bg-gray-50">
                            <td class="py-3 pl-5 pr-3 align-top">
                                <div class="text-slate-900 font-medium">{{ $u->name }}</div>
                            </td>
                            <td class="px-3 py-3 align-top">
                                <div class="text-slate-900">{{ $u->email }}</div>
                            </td>
                            <td class="px-3 py-3 align-top">
                                <span class="inline-flex items-center rounded-md px-2 py-0.5 text-xs font-medium ring-1 ring-inset {{ $u->role === 'Primary Administrator' ? 'text-purple-700 bg-purple-50 ring-purple-600/20' : 'text-slate-700 bg-slate-50 ring-slate-600/20' }}">
                                    {{ $u->role }}
                                </span>
                            </td>
                            <td class="px-3 py-3 align-top">
                                <div class="text-slate-900">{{ $u->category ?? '—' }}</div>
                            </td>
                            <td class="py-3 pl-3 pr-5 align-top">
                                <div class="flex items-center gap-2">
                                    <button type="button"
                                            class="openEditModalBtn inline-flex items-center justify-center rounded-md border border-gray-200 bg-white w-8 h-8 text-sm text-gray-700 hover:bg-gray-50"
                                            data-id="{{ $u->id }}"
                                            data-name="{{ $u->name }}"
                                            data-email="{{ $u->email }}"
                                            data-role="{{ $u->role }}"
                                            data-category="{{ $u->category }}"
                                            aria-label="Edit user">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" viewBox="0 0 24 24" fill="currentColor">
                                            <path d="M3 17.25V21h3.75L17.81 9.94l-3.75-3.75L3 17.25zM20.71 7.04a1.003 1.003 0 0 0 0-1.41l-2.34-2.34a1.003 1.003 0 0 0-1.41 0l-1.83 1.83 3.75 3.75 1.83-1.83z"/>
                                        </svg>
                                    </button>
                                    <form method="POST" action="{{ route('admin.users.destroy', $u) }}" onsubmit="return confirm('Delete this user? This action cannot be undone.');">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="inline-flex items-center justify-center rounded-md border border-red-200 bg-white w-8 h-8 text-sm text-red-700 hover:bg-red-50" aria-label="Delete user">
                                            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" viewBox="0 0 24 24" fill="currentColor">
                                                <path d="M3 6h18v2H3V6zm2 3h14l-1.1 12.2c-.08.9-.86 1.6-1.76 1.6H8.86c-.9 0-1.68-.7-1.76-1.6L6 9zm5 3v7h2v-7h-2zm4 0v7h2v-7h-2zM9 4V3h6v1h5v2H4V4h5z"/>
                                            </svg>
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="px-5 py-10 text-center text-sm text-gray-500">No staff users found.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="px-5 py-3 border-t border-gray-200">
            <div class="flex items-center justify-between">
                <div class="text-sm text-slate-600">
                    Showing {{ $users->perPage() }} per page — {{ $users->total() }} total
                    @if($users->total() > 0)
                        &nbsp;•&nbsp; displaying {{ $users->firstItem() }}–{{ $users->lastItem() }}
                    @endif
                </div>
                <div>
                    {{ $users->links() }}
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Create Staff Modal -->
<div id="createStaffModal" class="fixed inset-0 z-50 hidden">
  <div class="absolute inset-0 bg-black/40" data-close="create"></div>
  <div class="absolute inset-0 flex items-center justify-center p-4">
    <div class="w-full max-w-lg bg-white rounded-lg shadow border border-gray-200">
      <div class="h-12 flex items-center justify-between px-4 border-b">
        <div class="text-sm font-semibold text-slate-800">Add Staff</div>
        <button type="button" class="text-slate-500 hover:text-slate-700" data-close="create" aria-label="Close">
          <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 24 24" fill="currentColor"><path d="M6 18 18 6M6 6l12 12"/></svg>
        </button>
      </div>
      <form method="POST" action="{{ route('admin.users.store') }}" class="p-4 space-y-4">
        @csrf
        <input type="hidden" name="form_context" value="create">
        <div>
          <label class="block text-sm font-medium text-slate-700">Full Name</label>
          <input type="text" name="name" value="{{ old('form_context') === 'create' ? old('name') : '' }}" required
                 class="mt-1 w-full rounded-md border border-gray-300 px-3 py-2 text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500" />
          @if (old('form_context') === 'create') @error('name')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror @endif
        </div>
        <div>
          <label class="block text-sm font-medium text-slate-700">Email</label>
          <input type="email" name="email" value="{{ old('form_context') === 'create' ? old('email') : '' }}" required
                 class="mt-1 w-full rounded-md border border-gray-300 px-3 py-2 text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500" />
          @if (old('form_context') === 'create') @error('email')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror @endif
        </div>
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
          <div>
            <label class="block text-sm font-medium text-slate-700">Role</label>
            <input type="text" name="role" value="{{ old('form_context') === 'create' ? old('role') : '' }}" required placeholder="e.g. IT Support"
                   class="mt-1 w-full rounded-md border border-gray-300 px-3 py-2 text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500" />
            <p class="mt-1 text-[11px] text-slate-500">Note: "Primary Administrator" cannot be created here.</p>
            @if (old('form_context') === 'create') @error('role')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror @endif
          </div>
          <div>
            <label class="block text-sm font-medium text-slate-700">Category/Department (optional)</label>
            <input type="text" name="category" value="{{ old('form_context') === 'create' ? old('category') : '' }}" placeholder="e.g. Admissions"
                   class="mt-1 w-full rounded-md border border-gray-300 px-3 py-2 text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500" />
            @if (old('form_context') === 'create') @error('category')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror @endif
          </div>
        </div>
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
          <div>
            <label class="block text-sm font-medium text-slate-700">Password</label>
            <input type="password" name="password" required
                   class="mt-1 w-full rounded-md border border-gray-300 px-3 py-2 text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500" />
            @if (old('form_context') === 'create') @error('password')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror @endif
          </div>
          <div>
            <label class="block text-sm font-medium text-slate-700">Confirm Password</label>
            <input type="password" name="password_confirmation" required
                   class="mt-1 w-full rounded-md border border-gray-300 px-3 py-2 text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500" />
          </div>
        </div>
        <div class="pt-2 flex items-center justify-end gap-3">
          <button type="button" class="rounded-md border border-gray-300 bg-white hover:bg-gray-50 text-sm px-4 py-2" data-close="create">Cancel</button>
          <button type="submit" class="rounded-md bg-blue-600 hover:bg-blue-700 text-white text-sm font-medium px-4 py-2">Create Staff</button>
        </div>
      </form>
    </div>
  </div>
</div>

<!-- Edit Staff Modal -->
<div id="editStaffModal" class="fixed inset-0 z-50 hidden" data-update-template="{{ route('admin.users.update', ['user' => '__ID__']) }}">
  <div class="absolute inset-0 bg-black/40" data-close="edit"></div>
  <div class="absolute inset-0 flex items-center justify-center p-4">
    <div class="w-full max-w-lg bg-white rounded-lg shadow border border-gray-200">
      <div class="h-12 flex items-center justify-between px-4 border-b">
        <div class="text-sm font-semibold text-slate-800">Edit Staff</div>
        <button type="button" class="text-slate-500 hover:text-slate-700" data-close="edit" aria-label="Close">
          <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 24 24" fill="currentColor"><path d="M6 18 18 6M6 6l12 12"/></svg>
        </button>
      </div>
      <form id="editStaffForm" method="POST" action="#" class="p-4 space-y-4">
        @csrf
        @method('PUT')
        <input type="hidden" name="editing_user_id" id="edit_user_id" value="{{ old('editing_user_id') }}">
        <div>
          <label class="block text-sm font-medium text-slate-700">Full Name</label>
          <input type="text" name="name" id="edit_name" value="" required
                 class="mt-1 w-full rounded-md border border-gray-300 px-3 py-2 text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500" />
          @if (old('editing_user_id')) @error('name')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror @endif
        </div>
        <div>
          <label class="block text-sm font-medium text-slate-700">Email</label>
          <input type="email" name="email" id="edit_email" value="" required
                 class="mt-1 w-full rounded-md border border-gray-300 px-3 py-2 text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500" />
          @if (old('editing_user_id')) @error('email')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror @endif
        </div>
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
          <div>
            <label class="block text-sm font-medium text-slate-700">Role</label>
            <input type="text" name="role" id="edit_role" value="" required placeholder="e.g. IT Support"
                   class="mt-1 w-full rounded-md border border-gray-300 px-3 py-2 text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500" />
            <p class="mt-1 text-[11px] text-slate-500">Note: "Primary Administrator" cannot be set here.</p>
            @if (old('editing_user_id')) @error('role')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror @endif
          </div>
          <div>
            <label class="block text-sm font-medium text-slate-700">Category/Department (optional)</label>
            <input type="text" name="category" id="edit_category" value="" placeholder="e.g. Admissions"
                   class="mt-1 w-full rounded-md border border-gray-300 px-3 py-2 text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500" />
            @if (old('editing_user_id')) @error('category')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror @endif
          </div>
        </div>
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
          <div>
            <label class="block text-sm font-medium text-slate-700">New Password (optional)</label>
            <input type="password" name="password"
                   class="mt-1 w-full rounded-md border border-gray-300 px-3 py-2 text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500" />
            @if (old('editing_user_id')) @error('password')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror @endif
          </div>
          <div>
            <label class="block text-sm font-medium text-slate-700">Confirm New Password</label>
            <input type="password" name="password_confirmation"
                   class="mt-1 w-full rounded-md border border-gray-300 px-3 py-2 text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500" />
          </div>
        </div>
        <div class="pt-2 flex items-center justify-end gap-3">
          <button type="button" class="rounded-md border border-gray-300 bg-white hover:bg-gray-50 text-sm px-4 py-2" data-close="edit">Cancel</button>
          <button type="submit" class="rounded-md bg-blue-600 hover:bg-blue-700 text-white text-sm font-medium px-4 py-2">Save Changes</button>
        </div>
      </form>
    </div>
  </div>
</div>

@endsection

<!-- Hidden state for JS (prevents Blade in JS parsing issues) -->
<div id="admin-users-state" class="hidden"
     data-has-errors="{{ $errors->any() ? '1' : '0' }}"
     data-old-edit-id="{{ old('editing_user_id') }}"
     data-old-form-context="{{ old('form_context') }}"
     data-old-name="{{ old('name') }}"
     data-old-email="{{ old('email') }}"
     data-old-role="{{ old('role') }}"
     data-old-category="{{ old('category') }}"></div>
@section('admin-scripts')
<script>
(function () {
  const $ = (sel, root = document) => root.querySelector(sel);
  const $$ = (sel, root = document) => Array.from(root.querySelectorAll(sel));
  const body = document.body;

  // Create modal elements
  const createModal = $('#createStaffModal');
  const createOpenBtn = $('#openCreateModalBtn');
  const createCloseEls = $$('[data-close="create"]', createModal || body);

  // Edit modal elements
  const editModal = $('#editStaffModal');
  const editCloseEls = $$('[data-close="edit"]', editModal || body);
  const updateTemplate = editModal ? editModal.getAttribute('data-update-template') : null;
  const editForm = $('#editStaffForm');
  const editId = $('#edit_user_id');
  const editName = $('#edit_name');
  const editEmail = $('#edit_email');
  const editRole = $('#edit_role');
  const editCategory = $('#edit_category');

  function openModal(modal) {
    if (!modal) return;
    modal.classList.remove('hidden');
  }
  function closeModal(modal) {
    if (!modal) return;
    modal.classList.add('hidden');
  }

  // Open Create
  if (createOpenBtn) {
    createOpenBtn.addEventListener('click', () => {
      openModal(createModal);
    });
  }

  // Mobile create button - open the same modal
  const createOpenMobileBtn = $('#openCreateModalBtnMobile');
  if (createOpenMobileBtn) {
    createOpenMobileBtn.addEventListener('click', () => {
      openModal(createModal);
    });
  }

  // Close Create
  createCloseEls.forEach(el => el.addEventListener('click', () => closeModal(createModal)));

  // Open Edit with data
  $$('.openEditModalBtn').forEach(btn => {
    btn.addEventListener('click', () => {
      const id = btn.getAttribute('data-id');
      const name = btn.getAttribute('data-name') || '';
      const email = btn.getAttribute('data-email') || '';
      const role = btn.getAttribute('data-role') || '';
      const category = btn.getAttribute('data-category') || '';

      if (editId) editId.value = id || '';
      if (editName) editName.value = name;
      if (editEmail) editEmail.value = email;
      if (editRole) editRole.value = role;
      if (editCategory) editCategory.value = category;

      if (editForm && updateTemplate && id) {
        editForm.setAttribute('action', updateTemplate.replace('__ID__', id));
      }
      openModal(editModal);
    });
  });

  // Close Edit
  editCloseEls.forEach(el => el.addEventListener('click', () => closeModal(editModal)));

  // Close on Escape
  document.addEventListener('keydown', (e) => {
    if (e.key === 'Escape') {
      closeModal(createModal);
      closeModal(editModal);
    }
  });

  // Auto-open modal after validation error (values read from hidden state element)
  const stateEl = document.getElementById('admin-users-state');
  const HAS_ERRORS = stateEl ? stateEl.getAttribute('data-has-errors') === '1' : false;
  const OLD_EDIT_ID = stateEl ? stateEl.getAttribute('data-old-edit-id') : null;
  const OLD_FORM_CONTEXT = stateEl ? stateEl.getAttribute('data-old-form-context') : null;
  const OLD_NAME = stateEl ? stateEl.getAttribute('data-old-name') : '';
  const OLD_EMAIL = stateEl ? stateEl.getAttribute('data-old-email') : '';
  const OLD_ROLE = stateEl ? stateEl.getAttribute('data-old-role') : '';
  const OLD_CATEGORY = stateEl ? stateEl.getAttribute('data-old-category') : '';

  if (HAS_ERRORS) {
    if (OLD_EDIT_ID) {
      if (editId) editId.value = OLD_EDIT_ID || '';
      if (editName) editName.value = OLD_NAME || '';
      if (editEmail) editEmail.value = OLD_EMAIL || '';
      if (editRole) editRole.value = OLD_ROLE || '';
      if (editCategory) editCategory.value = OLD_CATEGORY || '';
      if (editForm && updateTemplate && OLD_EDIT_ID) {
        editForm.setAttribute('action', updateTemplate.replace('__ID__', OLD_EDIT_ID));
      }
      openModal(editModal);
    } else if (OLD_FORM_CONTEXT === 'create') {
      openModal(createModal);
    }
  }

  // Mobile search UI (toggles mobile search bar)
  const mobileSearchToggle = $('#mobileSearchToggle');
  const mobileSearchArea = $('#mobileSearchArea');
  const qMobile = $('#q_mobile');
  const mobileSearchBtn = $('#mobileSearchBtn');
  const mobileClearSearch = $('#mobileClearSearch');

  if (mobileSearchToggle) {
    mobileSearchToggle.addEventListener('click', () => {
      if (mobileSearchArea) mobileSearchArea.classList.toggle('hidden');
      if (qMobile) qMobile.focus();
    });
  }

  if (mobileSearchBtn) {
    mobileSearchBtn.addEventListener('click', () => {
      const form = document.createElement('form');
      form.method = 'GET';
      form.action = "{{ route('admin.users.index') }}";
      const input = document.createElement('input');
      input.type = 'hidden';
      input.name = 'q';
      input.value = qMobile ? qMobile.value.trim() : '';
      form.appendChild(input);
      document.body.appendChild(form);
      form.submit();
    });
  }

  if (mobileClearSearch) {
    mobileClearSearch.addEventListener('click', () => {
      if (qMobile) qMobile.value = '';
      // navigate to the index without query
      window.location.href = "{{ route('admin.users.index') }}";
    });
  }
})();
</script>
@endsection