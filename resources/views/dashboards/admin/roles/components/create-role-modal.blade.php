<div id="createRoleModal" class="fixed inset-0 z-50 hidden" aria-hidden="true">
    <div class="absolute inset-0 bg-black/40" data-close="create-role"></div>
    <div class="absolute inset-0 flex items-center justify-center p-4">
        <div class="w-full max-w-lg bg-white rounded-lg shadow border border-gray-200">
            <div class="h-12 flex items-center justify-between px-4 border-b">
                <div class="text-sm font-semibold text-slate-800">Add Role</div>
                <button type="button" class="text-slate-500 hover:text-slate-700" data-close="create-role"
                    aria-label="Close">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 24 24" fill="currentColor">
                        <path d="M6 18 18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>
            <form id="createRoleForm" method="POST" action="{{ route('admin.roles.store') }}" class="p-4 space-y-4">
                @csrf
                <div>
                    <label class="block text-sm font-medium text-slate-700">Department</label>
                    <select name="department_id" required
                        class="mt-1 w-full rounded-md border border-gray-300 px-3 py-2 text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                        <option value="">Select Department</option>
                        @foreach ($departments as $department)
                            <option value="{{ $department->id }}">{{ $department->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-medium text-slate-700">Name</label>
                    <input type="text" name="name" required
                        class="mt-1 w-full rounded-md border border-gray-300 px-3 py-2 text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500" />
                </div>
                <div>
                    <label class="block text-sm font-medium text-slate-700">Description (optional)</label>
                    <textarea name="description" rows="3"
                        class="mt-1 w-full rounded-md border border-gray-300 px-3 py-2 text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500"></textarea>
                </div>

                <div class="pt-2 flex items-center justify-end gap-3">
                    <button type="button"
                        class="rounded-md border border-gray-300 bg-white hover:bg-gray-50 text-sm px-4 py-2"
                        data-close="create-role">Cancel</button>
                    <button type="submit" id="createRoleSubmit"
                        class="rounded-md bg-blue-600 hover:bg-blue-700 text-white text-sm font-medium px-4 py-2">Save</button>
                </div>
            </form>
        </div>
    </div>
</div>
