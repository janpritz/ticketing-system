<div id="createDeptModal" class="fixed inset-0 z-50 hidden" aria-hidden="true">
  <div class="absolute inset-0 bg-black/40" data-close="create-dept"></div>
  <div class="absolute inset-0 flex items-center justify-center p-4">
    <div class="w-full max-w-lg max-h-[90vh] bg-white rounded-lg shadow border border-gray-200 flex flex-col">
      <div class="h-12 flex items-center justify-between px-4 border-b shrink-0">
        <div class="text-sm font-semibold text-slate-800">Add Department</div>
        <button type="button" class="text-slate-500 hover:text-slate-700" data-close="create-dept" aria-label="Close">
          <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 24 24" fill="currentColor"><path d="M6 18 18 6M6 6l12 12"/></svg>
        </button>
      </div>
      <form id="createDeptForm" method="POST" action="{{ route('admin.departments.store') }}" class="p-4 space-y-4 overflow-y-auto">
        @csrf
        <div>
          <label class="block text-sm font-medium text-slate-700">Name</label>
          <input type="text" name="name" required class="mt-1 w-full rounded-md border border-gray-300 px-3 py-2 text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500" />
        </div>
        <div>
          <label class="block text-sm font-medium text-slate-700">Description (optional)</label>
          <textarea name="description" rows="2" class="mt-1 w-full rounded-md border border-gray-300 px-3 py-2 text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500"></textarea>
        </div>
        
        <div>
          <label class="block text-sm font-medium text-slate-700">Roles</label>
          <p class="text-xs text-slate-500 mt-1 mb-2">Add roles for this department. You can add more roles later.</p>
          <div id="rolesContainer" class="space-y-2 max-h-48 overflow-y-auto pr-2">
            <div class="flex items-center gap-2 role-row">
              <input type="text" name="roles[]" placeholder="Role name" class="flex-1 rounded-md border border-gray-300 px-3 py-2 text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500" />
              <button type="button" class="remove-role-btn hidden text-red-500 hover:text-red-700 p-1" aria-label="Remove role">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" viewBox="0 0 24 24" fill="currentColor"><path d="M6 18 18 6M6 6l12 12"/></svg>
              </button>
            </div>
            <div class="flex items-center gap-2 role-row">
              <input type="text" name="roles[]" placeholder="Role name" class="flex-1 rounded-md border border-gray-300 px-3 py-2 text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500" />
              <button type="button" class="remove-role-btn hidden text-red-500 hover:text-red-700 p-1" aria-label="Remove role">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" viewBox="0 0 24 24" fill="currentColor"><path d="M6 18 18 6M6 6l12 12"/></svg>
              </button>
            </div>
          </div>
          <button type="button" id="addRoleBtn" class="mt-2 text-sm text-blue-600 hover:text-blue-700 flex items-center gap-1">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" viewBox="0 0 24 24" fill="currentColor"><path d="M11 11V5h2v6h6v2h-6v6h-2v-6H5v-2z"/></svg>
            Add another role
          </button>
        </div>

        <div class="pt-2 flex items-center justify-end gap-3">
          <button type="button" class="rounded-md border border-gray-300 bg-white hover:bg-gray-50 text-sm px-4 py-2" data-close="create-dept">Cancel</button>
          <button type="submit" id="createDeptSubmit" class="rounded-md bg-blue-600 hover:bg-blue-700 text-white text-sm font-medium px-4 py-2">Save</button>
        </div>
      </form>
    </div>
  </div>
</div>