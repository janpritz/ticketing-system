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
                  <button type="button" class="editCategoryBtn inline-flex items-center justify-center rounded-md border border-gray-200 bg-white w-8 h-8 text-sm text-gray-700 hover:bg-gray-50" aria-label="Edit category" data-id="{{ $category->id }}" data-name="{{ $category->name }}" data-description="{{ $category->description }}" data-role-id="{{ $category->role_id }}">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" viewBox="0 0 24 24" fill="currentColor"><path d="M3 17.25V21h3.75L17.81 9.94l-3.75-3.75L3 17.25zM20.71 7.04a1.003 1.003 0 0 0 0-1.41l-2.34-2.34a1.003 1.003 0 0 0-1.41 0l-1.83 1.83 3.75 3.75 1.83-1.83z"/></svg>
                  </button>

                  <button type="button" class="deleteCategoryBtn inline-flex items-center justify-center rounded-md border border-red-200 bg-white w-8 h-8 text-sm text-red-700 hover:bg-red-50" aria-label="Delete category" data-id="{{ $category->id }}" data-name="{{ $category->name }}">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" viewBox="0 0 24 24" fill="currentColor"><path d="M3 6h18v2H3V6zm2 3h14l-1.1 12.2c-.08.9-.86 1.6-1.76 1.6H8.86c-.9 0-1.68-.7-1.76-1.6L6 9zm5 3v7h2v-7h-2zm4 0v7h2v-7h-2zM9 4V3h6v1h5v2H4V4h5z"/></svg>
                  </button>
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