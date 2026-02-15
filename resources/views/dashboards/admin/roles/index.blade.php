@extends('layouts.admin')

@section('title', 'Role Management')

@section('admin-content')
<div class="sm:px-2">
  <div class="flex items-center justify-between gap-4">
    <div>
      <h1 class="text-2xl font-semibold text-slate-900">Role Management</h1>
      <p class="text-sm text-slate-500">Manage staff roles and their department assignments.</p>
    </div>

    <div class="flex items-center gap-2">
      <button id="openCreateRoleBtn" type="button" class="inline-flex items-center gap-2 rounded-lg bg-blue-600 hover:bg-blue-700 text-white text-sm font-medium px-3 py-2" aria-label="Add Role">
        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" viewBox="0 0 24 24" fill="currentColor"><path d="M11 11V5h2v6h6v2h-6v6h-2v-6H5v-2z"/></svg>
        <span class="hidden sm:inline">Add Role</span>
      </button>
    </div>
  </div>
  <div class="mt-5 bg-white rounded-xl border border-gray-200 overflow-hidden">
    <div class="overflow-x-auto">
      <table class="min-w-full text-sm">
        <thead class="bg-gray-50 text-gray-600">
          <tr>
            <th class="py-3 pl-5 pr-3 text-left font-medium">Name</th>
            <th class="px-3 py-3 text-left font-medium">Department</th>
            <th class="px-3 py-3 text-left font-medium">Description</th>
            <th class="py-3 pl-3 pr-5 text-left font-medium">Actions</th>
          </tr>
        </thead>
        <tbody class="divide-y divide-gray-100" id="rolesTbody">
          @forelse($roles as $role)
            <tr class="hover:bg-gray-50">
              <td class="py-3 pl-5 pr-3 align-top">
                <div class="text-slate-900 font-medium">{{ $role->name }}</div>
              </td>
              <td class="px-3 py-3 align-top">
                <div class="text-slate-900">{{ $role->department->name ?? '—' }}</div>
              </td>
              <td class="px-3 py-3 align-top">
                <div class="text-slate-900">{{ $role->description ?? '—' }}</div>
              </td>
              <td class="py-3 pl-3 pr-5 align-top">
                <div class="flex items-center gap-2">
                  <button type="button"
                          class="openEditRoleBtn inline-flex items-center justify-center rounded-md border border-gray-200 bg-white w-8 h-8 text-sm text-gray-700 hover:bg-gray-50"
                          data-id="{{ $role->id }}"
                          data-name="{{ $role->name }}"
                          data-description="{{ $role->description }}"
                          data-department-id="{{ $role->department_id }}"
                          aria-label="Edit role">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" viewBox="0 0 24 24" fill="currentColor">
                      <path d="M3 17.25V21h3.75L17.81 9.94l-3.75-3.75L3 17.25zM20.71 7.04a1.003 1.003 0 0 0 0-1.41l-2.34-2.34a1.003 1.003 0 0 0-1.41 0l-1.83 1.83 3.75 3.75 1.83-1.83z"/>
                    </svg>
                  </button>
                  <button type="button" class="deleteRoleBtn inline-flex items-center justify-center rounded-md border border-red-200 bg-white w-8 h-8 text-sm text-red-700 hover:bg-red-50" aria-label="Delete role" data-id="{{ $role->id }}" data-name="{{ $role->name }}">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" viewBox="0 0 24 24" fill="currentColor">
                      <path d="M3 6h18v2H3V6zm2 3h14l-1.1 12.2c-.08.9-.86 1.6-1.76 1.6H8.86c-.9 0-1.68-.7-1.76-1.6L6 9zm5 3v7h2v-7h-2zm4 0v7h2v-7h-2zM9 4V3h6v1h5v2H4V4h5z"/>
                    </svg>
                  </button>
                </div>
              </td>
            </tr>
          @empty
            <tr>
              <td colspan="4" class="px-5 py-10 text-center text-sm text-gray-500">No roles found.</td>
            </tr>
          @endforelse
        </tbody>
      </table>
    </div>

    <div class="px-5 py-3 border-t border-gray-200">
      <div class="flex items-center justify-between">
        <div class="text-sm text-slate-600">Showing {{ $roles->perPage() }} per page — {{ $roles->total() }} total</div>
        <div>
          {{ $roles->links() }}
        </div>
      </div>
    </div>
  </div>
</div>

<!-- Create Role Modal -->
<div id="createRoleModal" class="fixed inset-0 z-50 hidden" aria-hidden="true">
  <div class="absolute inset-0 bg-black/40" data-close="create-role"></div>
  <div class="absolute inset-0 flex items-center justify-center p-4">
    <div class="w-full max-w-lg bg-white rounded-lg shadow border border-gray-200">
      <div class="h-12 flex items-center justify-between px-4 border-b">
        <div class="text-sm font-semibold text-slate-800">Add Role</div>
        <button type="button" class="text-slate-500 hover:text-slate-700" data-close="create-role" aria-label="Close">
          <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 24 24" fill="currentColor"><path d="M6 18 18 6M6 6l12 12"/></svg>
        </button>
      </div>
      <form id="createRoleForm" method="POST" action="{{ route('admin.roles.store') }}" class="p-4 space-y-4">
        @csrf
        <div>
          <label class="block text-sm font-medium text-slate-700">Department</label>
          <select name="department_id" required class="mt-1 w-full rounded-md border border-gray-300 px-3 py-2 text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
            <option value="">Select Department</option>
            @foreach($departments as $department)
              <option value="{{ $department->id }}">{{ $department->name }}</option>
            @endforeach
          </select>
        </div>
        <div>
          <label class="block text-sm font-medium text-slate-700">Name</label>
          <input type="text" name="name" required class="mt-1 w-full rounded-md border border-gray-300 px-3 py-2 text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500" />
        </div>
        <div>
          <label class="block text-sm font-medium text-slate-700">Description (optional)</label>
          <textarea name="description" rows="3" class="mt-1 w-full rounded-md border border-gray-300 px-3 py-2 text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500"></textarea>
        </div>

        <div class="pt-2 flex items-center justify-end gap-3">
          <button type="button" class="rounded-md border border-gray-300 bg-white hover:bg-gray-50 text-sm px-4 py-2" data-close="create-role">Cancel</button>
          <button type="submit" id="createRoleSubmit" class="rounded-md bg-blue-600 hover:bg-blue-700 text-white text-sm font-medium px-4 py-2">Save</button>
        </div>
      </form>
    </div>
  </div>
</div>

<!-- Edit Role Modal -->
<div id="editRoleModal" class="fixed inset-0 z-50 hidden" aria-hidden="true">
  <div class="absolute inset-0 bg-black/40" data-close="edit-role"></div>
  <div class="absolute inset-0 flex items-center justify-center p-4">
    <div class="w-full max-w-lg bg-white rounded-lg shadow border border-gray-200">
      <div class="h-12 flex items-center justify-between px-4 border-b">
        <div class="text-sm font-semibold text-slate-800">Edit Role</div>
        <button type="button" class="text-slate-500 hover:text-slate-700" data-close="edit-role" aria-label="Close">
          <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 24 24" fill="currentColor"><path d="M6 18 18 6M6 6l12 12"/></svg>
        </button>
      </div>
      <form id="editRoleForm" method="POST" action="#" class="p-4 space-y-4">
        @csrf
        @method('PUT')
        <input type="hidden" id="edit_role_id" name="role_id" value="" />
        <div>
          <label class="block text-sm font-medium text-slate-700">Department</label>
          <select id="edit_role_department" name="department_id" required class="mt-1 w-full rounded-md border border-gray-300 px-3 py-2 text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
            <option value="">Select Department</option>
            @foreach($departments as $department)
              <option value="{{ $department->id }}">{{ $department->name }}</option>
            @endforeach
          </select>
        </div>
        <div>
          <label class="block text-sm font-medium text-slate-700">Name</label>
          <input type="text" id="edit_role_name" name="name" required class="mt-1 w-full rounded-md border border-gray-300 px-3 py-2 text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500" />
        </div>
        <div>
          <label class="block text-sm font-medium text-slate-700">Description (optional)</label>
          <textarea id="edit_role_description" name="description" rows="3" class="mt-1 w-full rounded-md border border-gray-300 px-3 py-2 text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500"></textarea>
        </div>
        <div class="pt-2 flex items-center justify-end gap-3">
          <button type="button" class="rounded-md border border-gray-300 bg-white hover:bg-gray-50 text-sm px-4 py-2" data-close="edit-role">Cancel</button>
          <button type="submit" class="rounded-md bg-blue-600 hover:bg-blue-700 text-white text-sm font-medium px-4 py-2">Save Changes</button>
        </div>
      </form>
    </div>
  </div>
</div>
@endsection

@section('admin-scripts')
<!-- SweetAlert2 -->
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<script>
(function(){
  const createModal = document.getElementById('createRoleModal');
  const editModal = document.getElementById('editRoleModal');
  const openCreateBtn = document.getElementById('openCreateRoleBtn');

  function openModal(modal){ if(modal) modal.classList.remove('hidden'); }
  function closeModal(modal){ if(modal) modal.classList.add('hidden'); }

  // Open create
  if (openCreateBtn) openCreateBtn.addEventListener('click', ()=> openModal(createModal));

  // Close handlers
  document.querySelectorAll('[data-close="create-role"]').forEach(el => el.addEventListener('click', ()=> closeModal(createModal)));
  document.querySelectorAll('[data-close="edit-role"]').forEach(el => el.addEventListener('click', ()=> closeModal(editModal)));

  // Create form submission with SweetAlert
  const createForm = document.getElementById('createRoleForm');
  if (createForm) {
    createForm.addEventListener('submit', async function(e) {
      e.preventDefault();
      
      const submitBtn = document.getElementById('createRoleSubmit');
      const originalText = submitBtn.textContent;
      submitBtn.textContent = 'Saving...';
      submitBtn.disabled = true;

      try {
        const formData = new FormData(this);
        const response = await fetch(this.action, {
          method: 'POST',
          body: formData,
          headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
            'X-Requested-With': 'XMLHttpRequest'
          }
        });

        const data = await response.json();

        if (response.ok && data.success) {
          closeModal(createModal);
          Swal.fire({
            toast: true,
            position: 'top-end',
            icon: 'success',
            title: data.message || 'Role created successfully',
            showConfirmButton: false,
            timer: 3000,
            timerProgressBar: true
          }).then(() => {
            window.location.reload();
          });
        } else {
          Swal.fire({
            toast: true,
            position: 'top-end',
            icon: 'error',
            title: data.message || data.errors?.name?.[0] || 'Failed to create role',
            showConfirmButton: false,
            timer: 3000,
            timerProgressBar: true
          });
        }
      } catch (error) {
        Swal.fire({
          toast: true,
          position: 'top-end',
          icon: 'error',
          title: 'An error occurred',
          showConfirmButton: false,
          timer: 3000,
          timerProgressBar: true
        });
      } finally {
        submitBtn.textContent = originalText;
        submitBtn.disabled = false;
      }
    });
  }

  // Edit form submission with SweetAlert
  const editForm = document.getElementById('editRoleForm');
  if (editForm) {
    editForm.addEventListener('submit', async function(e) {
      e.preventDefault();
      
      const submitBtn = this.querySelector('button[type="submit"]');
      const originalText = submitBtn.textContent;
      submitBtn.textContent = 'Saving...';
      submitBtn.disabled = true;

      try {
        const formData = new FormData(this);
        formData.append('_method', 'PUT');
        
        const response = await fetch(this.action, {
          method: 'POST',
          body: formData,
          headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
            'X-Requested-With': 'XMLHttpRequest'
          }
        });

        const data = await response.json();

        if (response.ok && data.success) {
          closeModal(editModal);
          Swal.fire({
            toast: true,
            position: 'top-end',
            icon: 'success',
            title: data.message || 'Role updated successfully',
            showConfirmButton: false,
            timer: 3000,
            timerProgressBar: true
          }).then(() => {
            window.location.reload();
          });
        } else {
          Swal.fire({
            toast: true,
            position: 'top-end',
            icon: 'error',
            title: data.message || data.errors?.name?.[0] || 'Failed to update role',
            showConfirmButton: false,
            timer: 3000,
            timerProgressBar: true
          });
        }
      } catch (error) {
        Swal.fire({
          toast: true,
          position: 'top-end',
          icon: 'error',
          title: 'An error occurred',
          showConfirmButton: false,
          timer: 3000,
          timerProgressBar: true
        });
      } finally {
        submitBtn.textContent = originalText;
        submitBtn.disabled = false;
      }
    });
  }

  // Edit buttons
  document.querySelectorAll('.openEditRoleBtn').forEach(btn => {
    btn.addEventListener('click', () => {
      const id = btn.getAttribute('data-id');
      const name = btn.getAttribute('data-name') || '';
      const description = btn.getAttribute('data-description') || '';
      const departmentId = btn.getAttribute('data-department-id') || '';
      
      // populate form
      document.getElementById('edit_role_id').value = id;
      document.getElementById('edit_role_name').value = name;
      document.getElementById('edit_role_description').value = description;
      document.getElementById('edit_role_department').value = departmentId;
      
      // set form action
      const form = document.getElementById('editRoleForm');
      form.action = "{{ url('/admin/roles') }}/" + id;
      openModal(editModal);
    });
  });

  // Intercept delete buttons and use SweetAlert confirmation
  document.querySelectorAll('.deleteRoleBtn').forEach(btn => {
    btn.addEventListener('click', async function (e) {
      const id = this.getAttribute('data-id');
      const name = this.getAttribute('data-name') || 'this role';
      const roleRow = this.closest('tr');

      const result = await Swal.fire({
        title: 'Delete role?',
        text: 'This will unassign the role from any users. This action cannot be undone.',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonText: 'Delete',
        cancelButtonText: 'Cancel',
        confirmButtonColor: '#d33'
      });

      if (!result.isConfirmed) return;

      try {
        const response = await fetch(`/admin/roles/${id}`, {
          method: 'DELETE',
          headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
            'X-Requested-With': 'XMLHttpRequest',
            'Content-Type': 'application/json'
          }
        });

        const data = await response.json();

        if (response.ok && data.success) {
          Swal.fire({
            toast: true,
            position: 'top-end',
            icon: 'success',
            title: data.message || 'Role deleted successfully',
            showConfirmButton: false,
            timer: 3000,
            timerProgressBar: true
          });
          // Remove the row
          if (roleRow) roleRow.remove();
        } else {
          Swal.fire({
            toast: true,
            position: 'top-end',
            icon: 'error',
            title: data.message || 'Failed to delete role',
            showConfirmButton: false,
            timer: 3000,
            timerProgressBar: true
          });
        }
      } catch (error) {
        Swal.fire({
          toast: true,
          position: 'top-end',
          icon: 'error',
          title: 'An error occurred',
          showConfirmButton: false,
          timer: 3000,
          timerProgressBar: true
        });
      }
    });
  });

  // Success toasts are now handled by AJAX responses

})();
</script>
@endsection
