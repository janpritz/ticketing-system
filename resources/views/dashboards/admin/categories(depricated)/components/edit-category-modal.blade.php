<div id="editCategoryModal" class="fixed inset-0 z-50 hidden">
  <div class="absolute inset-0 bg-black/40" id="editCategoryModalBackdrop"></div>
  <div class="relative max-w-xl mx-auto mt-20 bg-white rounded-lg shadow-lg overflow-hidden">
    <div class="p-4 border-b">
      <div class="flex items-center justify-between">
        <div>
          <h2 class="text-lg font-semibold text-slate-900">Edit Category</h2>
          <p class="text-sm text-slate-500">Modify a category assigned to a role.</p>
        </div>
        <div>
          <button type="button" id="closeEditCategoryModal" class="text-gray-500 hover:text-gray-700" aria-label="Close modal">&times;</button>
        </div>
      </div>
    </div>

    <form id="editCategoryForm" method="POST" class="p-6 space-y-4">
      @csrf
      @method('PUT')

      <input type="hidden" id="editCategoryId" name="category_id" value="">

      <div>
        <label class="block text-sm font-medium text-slate-700">Role</label>
        <div class="mt-1">
          <select id="editRoleId" name="role_id" required class="py-2 px-3 block w-full border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm">
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
          <input type="text" id="editName" name="name" required value="" class="py-2 px-3 block w-full border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm" />
          @error('name') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
        </div>
      </div>

      <div>
        <label class="block text-sm font-medium text-slate-700">Description (optional)</label>
        <div class="mt-1">
          <textarea id="editDescription" name="description" rows="3" class="py-2 px-3 block w-full border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm"></textarea>
          @error('description') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
        </div>
      </div>

      <div class="pt-2 flex items-center justify-end gap-3">
        <button type="button" id="cancelEditCategory" class="rounded-md border border-gray-300 bg-white hover:bg-gray-50 text-sm px-4 py-2">Cancel</button>
        <button type="submit" class="rounded-md bg-blue-600 hover:bg-blue-700 text-white text-sm font-medium px-4 py-2">Save Changes</button>
      </div>
    </form>
  </div>
</div>