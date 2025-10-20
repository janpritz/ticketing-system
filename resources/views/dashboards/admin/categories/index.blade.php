@extends('layouts.admin')

@section('title', 'Category Management')

@section('admin-content')
<div class="sm:px-2">
  <div class="flex items-center justify-between gap-4">
    <div>
      <h1 class="text-2xl font-semibold text-slate-900">Category Management</h1>
      <p class="text-sm text-slate-500">Manage categories assigned to roles.</p>
    </div>

    <div class="flex items-center gap-2">
      <button type="button" id="openAddCategoryModal" class="inline-flex items-center gap-2 rounded-lg bg-blue-600 hover:bg-blue-700 text-white text-sm font-medium px-3 py-2" aria-label="Add Category">
        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" viewBox="0 0 24 24" fill="currentColor"><path d="M11 11V5h2v6h6v2h-6v6h-2v-6H5v-2z"/></svg>
        <span class="hidden sm:inline">Add Category</span>
      </button>
    </div>
  </div>

  <div class="mt-5 bg-white rounded-xl border border-gray-200 overflow-hidden">
    <div class="overflow-x-auto">
      <table class="min-w-full text-sm">
        <thead class="bg-gray-50 text-gray-600">
          <tr>
            <th class="py-3 pl-5 pr-3 text-left font-medium">Name</th>
            <th class="px-3 py-3 text-left font-medium">Role</th>
            <th class="px-3 py-3 text-left font-medium">Description</th>
            <th class="py-3 pl-3 pr-5 text-left font-medium">Actions</th>
          </tr>
        </thead>
        <tbody class="divide-y divide-gray-100">
          @forelse($categories as $category)
            <tr class="hover:bg-gray-50">
              <td class="py-3 pl-5 pr-3 align-top">
                <div class="text-slate-900 font-medium">{{ $category->name }}</div>
              </td>
              <td class="px-3 py-3 align-top">
                <div class="text-slate-900">{{ $category->role->name ?? '—' }}</div>
              </td>
              <td class="px-3 py-3 align-top">
                <div class="text-slate-900">{{ $category->description ?? '—' }}</div>
              </td>
              <td class="py-3 pl-3 pr-5 align-top">
                <div class="flex items-center gap-2">
                  <button type="button"
                          data-id="{{ $category->id }}"
                          data-name="{{ $category->name }}"
                          data-role-id="{{ $category->role_id }}"
                          data-description="{{ $category->description }}"
                          class="openEditCategoryModal inline-flex items-center justify-center rounded-md border border-gray-200 bg-white w-8 h-8 text-sm text-gray-700 hover:bg-gray-50"
                          aria-label="Edit category">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" viewBox="0 0 24 24" fill="currentColor"><path d="M3 17.25V21h3.75L17.81 9.94l-3.75-3.75L3 17.25zM20.71 7.04a1.003 1.003 0 0 0 0-1.41l-2.34-2.34a1.003 1.003 0 0 0-1.41 0l-1.83 1.83 3.75 3.75 1.83-1.83z"/></svg>
                  </button>

                  <form method="POST" action="{{ route('admin.categories.destroy', $category) }}">
                    @csrf
                    @method('DELETE')
                    <button type="button" class="swalDeleteCategoryBtn inline-flex items-center justify-center rounded-md border border-red-200 bg-white w-8 h-8 text-sm text-red-700 hover:bg-red-50" aria-label="Delete category">
                      <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" viewBox="0 0 24 24" fill="currentColor"><path d="M3 6h18v2H3V6zm2 3h14l-1.1 12.2c-.08.9-.86 1.6-1.76 1.6H8.86c-.9 0-1.68-.7-1.76-1.6L6 9zm5 3v7h2v-7h-2zm4 0v7h2v-7h-2zM9 4V3h6v1h5v2H4V4h5z"/></svg>
                    </button>
                  </form>
                </div>
              </td>
            </tr>
          @empty
            <tr>
              <td colspan="4" class="px-5 py-10 text-center text-sm text-gray-500">No categories found.</td>
            </tr>
          @endforelse
        </tbody>
      </table>
    </div>

    <div class="px-5 py-3 border-t border-gray-200">
      <div class="flex items-center justify-between">
        <div class="text-sm text-slate-600">Showing {{ $categories->perPage() }} per page — {{ $categories->total() }} total</div>
        <div>
          {{ $categories->links() }}
        </div>
      </div>
    </div>
  </div>
</div>

<!-- Add Category Modal -->
<div id="addCategoryModal" class="fixed inset-0 z-50 hidden">
  <div class="absolute inset-0 bg-black/40" id="addCategoryModalBackdrop"></div>
  <div class="relative max-w-xl mx-auto mt-20 bg-white rounded-lg shadow-lg overflow-hidden">
    <div class="p-4 border-b">
      <div class="flex items-center justify-between">
        <div>
          <h2 class="text-lg font-semibold text-slate-900">Create Category</h2>
          <p class="text-sm text-slate-500">Assign a category to an existing role.</p>
        </div>
        <div>
          <button type="button" id="closeAddCategoryModal" class="text-gray-500 hover:text-gray-700" aria-label="Close modal">&times;</button>
        </div>
      </div>
    </div>

    <form method="POST" action="{{ route('admin.categories.store') }}" class="p-6 space-y-4">
      @csrf

      <div>
        <label class="block text-sm font-medium text-slate-700">Role</label>
        <div class="mt-1">
          <select name="role_id" required class="py-2 px-3 block w-full border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm">
            <option value="">Select role</option>
            @foreach($roles as $role)
              <option value="{{ $role->id }}" {{ old('role_id') == $role->id ? 'selected' : '' }}>{{ $role->name }}</option>
            @endforeach
          </select>
          @error('role_id') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
        </div>
      </div>

      <div>
        <label class="block text-sm font-medium text-slate-700">Category</label>
        <div class="mt-1">
          <input type="text" name="name" required value="{{ old('name') }}" class="py-2 px-3 block w-full border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm" />
          @error('name') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
        </div>
      </div>

      <div>
        <label class="block text-sm font-medium text-slate-700">Description (optional)</label>
        <div class="mt-1">
          <textarea name="description" rows="3" class="py-2 px-3 block w-full border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm">{{ old('description') }}</textarea>
          @error('description') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
        </div>
      </div>

      <div class="pt-2 flex items-center justify-end gap-3">
        <button type="button" id="cancelAddCategory" class="rounded-md border border-gray-300 bg-white hover:bg-gray-50 text-sm px-4 py-2">Cancel</button>
        <button type="submit" class="rounded-md bg-blue-600 hover:bg-blue-700 text-white text-sm font-medium px-4 py-2">Create Category</button>
      </div>
    </form>
  </div>
</div>

<!-- Edit Category Modal -->
<div id="editCategoryModal" class="fixed inset-0 z-50 hidden">
  <div class="absolute inset-0 bg-black/40" id="editCategoryModalBackdrop"></div>
  <div class="relative max-w-xl mx-auto mt-20 bg-white rounded-lg shadow-lg overflow-hidden">
    <div class="p-4 border-b">
      <div class="flex items-center justify-between">
        <div>
          <h2 class="text-lg font-semibold text-slate-900">Edit Category</h2>
          <p class="text-sm text-slate-500">Modify the category assigned to a role.</p>
        </div>
        <div>
          <button type="button" id="closeEditCategoryModal" class="text-gray-500 hover:text-gray-700" aria-label="Close modal">&times;</button>
        </div>
      </div>
    </div>

    <form id="editCategoryForm" method="POST" action="#" class="p-6 space-y-4">
      @csrf
      @method('PUT')
      <input type="hidden" name="edit_id" id="edit_id" value="{{ old('edit_id') ?? '' }}" />

      <div>
        <label class="block text-sm font-medium text-slate-700">Role</label>
        <div class="mt-1">
          <select name="role_id" id="edit_role_id" required class="py-2 px-3 block w-full border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm">
            <option value="">Select role</option>
            @foreach($roles as $role)
              <option value="{{ $role->id }}">{{ $role->name }}</option>
            @endforeach
          </select>
          @error('role_id') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
        </div>
      </div>

      <div>
        <label class="block text-sm font-medium text-slate-700">Category</label>
        <div class="mt-1">
          <input type="text" name="name" id="edit_name" required value="{{ old('name') }}" class="py-2 px-3 block w-full border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm" />
          @error('name') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
        </div>
      </div>

      <div>
        <label class="block text-sm font-medium text-slate-700">Description (optional)</label>
        <div class="mt-1">
          <textarea name="description" id="edit_description" rows="3" class="py-2 px-3 block w-full border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm">{{ old('description') }}</textarea>
          @error('description') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
        </div>
      </div>

      <div class="pt-2 flex items-center justify-end gap-3">
        <button type="button" id="cancelEditCategory" class="rounded-md border border-gray-300 bg-white hover:bg-gray-50 text-sm px-4 py-2">Cancel</button>
        <button type="submit" class="rounded-md bg-blue-600 hover:bg-blue-700 text-white text-sm font-medium px-4 py-2">Save changes</button>
      </div>
    </form>
  </div>
</div>

<script>
(function(){
  // Edit modal logic
  const openEditButtons = document.querySelectorAll('.openEditCategoryModal');
  const editModal = document.getElementById('editCategoryModal');
  const editBackdrop = document.getElementById('editCategoryModalBackdrop');
  const closeEditBtn = document.getElementById('closeEditCategoryModal');
  const cancelEditBtn = document.getElementById('cancelEditCategory');
  const editForm = document.getElementById('editCategoryForm');
  const editIdInput = document.getElementById('edit_id');
  const editNameInput = document.getElementById('edit_name');
  const editRoleSelect = document.getElementById('edit_role_id');
  const editDesc = document.getElementById('edit_description');

  function showEditModal() {
    if (!editModal) return;
    editModal.classList.remove('hidden');
    editModal.classList.add('flex', 'items-start');
    document.body.classList.add('overflow-hidden');
  }
  function hideEditModal() {
    if (!editModal) return;
    editModal.classList.add('hidden');
    editModal.classList.remove('flex', 'items-start');
    document.body.classList.remove('overflow-hidden');
  }

  openEditButtons.forEach(btn => {
    btn.addEventListener('click', function (e) {
      e.preventDefault();
      const id = this.getAttribute('data-id');
      const name = this.getAttribute('data-name') || '';
      const roleId = this.getAttribute('data-role-id') || '';
      const description = this.getAttribute('data-description') || '';

      // Populate form
      if (editIdInput) editIdInput.value = id;
      if (editNameInput) editNameInput.value = name;
      if (editRoleSelect) editRoleSelect.value = roleId;
      if (editDesc) editDesc.value = description;

      // Set form action to update endpoint
      if (editForm) editForm.action = '/admin/categories/' + encodeURIComponent(id);

      showEditModal();
    });
  });

  if (closeEditBtn) closeEditBtn.addEventListener('click', hideEditModal);
  if (cancelEditBtn) cancelEditBtn.addEventListener('click', hideEditModal);
  if (editBackdrop) editBackdrop.addEventListener('click', hideEditModal);
  document.addEventListener('keydown', function (e) {
    if (e.key === 'Escape') hideEditModal();
  });

  // If validation errors occurred for edit, open modal and populate with old input
  @if (old('edit_id'))
    document.addEventListener('DOMContentLoaded', function () {
      const id = {!! json_encode(old('edit_id')) !!};
      if (editForm) editForm.action = '/admin/categories/' + encodeURIComponent(id);
      if (editIdInput) editIdInput.value = id;
      @if(old('name'))
        if (editNameInput) editNameInput.value = {!! json_encode(old('name')) !!};
      @endif
      @if(old('role_id'))
        if (editRoleSelect) editRoleSelect.value = {!! json_encode(old('role_id')) !!};
      @endif
      @if(old('description'))
        if (editDesc) editDesc.value = {!! json_encode(old('description')) !!};
      @endif
      showEditModal();
    });
  @endif

})();
(function(){
  const openBtn = document.getElementById('openAddCategoryModal');
  const modal = document.getElementById('addCategoryModal');
  const closeBtn = document.getElementById('closeAddCategoryModal');
  const cancelBtn = document.getElementById('cancelAddCategory');
  const backdrop = document.getElementById('addCategoryModalBackdrop');

  function showModal() {
    if (!modal) return;
    modal.classList.remove('hidden');
    modal.classList.add('flex');
    modal.classList.add('items-start');
    document.body.classList.add('overflow-hidden');
  }
  function hideModal() {
    if (!modal) return;
    modal.classList.add('hidden');
    modal.classList.remove('flex');
    document.body.classList.remove('overflow-hidden');
  }

  if (openBtn) openBtn.addEventListener('click', function () { showModal(); });

  if (closeBtn) closeBtn.addEventListener('click', function () { hideModal(); });
  if (cancelBtn) cancelBtn.addEventListener('click', function () { hideModal(); });
  if (backdrop) backdrop.addEventListener('click', function () { hideModal(); });

  document.addEventListener('keydown', function (e) {
    if (e.key === 'Escape') hideModal();
  });

  // If there were validation errors or old input from a form submit, open the modal so user sees errors
  @if ($errors->any() || old('name') || old('role_id') || old('description'))
    document.addEventListener('DOMContentLoaded', function () { showModal(); });
  @endif

  // Show toast on successful create (existing behavior is in admin-scripts section)
})();
</script>

@endsection

@section('admin-scripts')
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
(function(){
  document.querySelectorAll('button[aria-label="Delete category"]').forEach(btn => {
    btn.addEventListener('click', function (e) {
      e.preventDefault();
      const form = this.closest('form');
      const row = this.closest('tr');
      const nameEl = row ? row.querySelector('div.font-medium') : null;
      const name = nameEl ? nameEl.textContent.trim() : 'this category';
      Swal.fire({
        title: 'Delete category?',
        text: 'This will remove the category. This action cannot be undone.',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonText: 'Delete',
        cancelButtonText: 'Cancel',
        confirmButtonColor: '#d33'
      }).then((result) => {
        if (result.isConfirmed) {
          if (form) form.submit();
        }
      });
    });
  });

  @if(session('status'))
    Swal.fire({
      toast: true,
      position: 'top-end',
      icon: 'success',
      title: {!! json_encode(session('status')) !!},
      showConfirmButton: false,
      timer: 3000,
      timerProgressBar: true
    });
  @endif

})();
</script>
@endsection