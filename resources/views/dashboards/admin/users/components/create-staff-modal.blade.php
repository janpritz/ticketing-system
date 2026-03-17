<div id="createStaffModal" class="fixed inset-0 z-50 hidden">
    <div class="absolute inset-0 bg-black/40" data-close="create"></div>
    <div class="absolute inset-0 flex items-center justify-center p-4">
        <div class="w-full max-w-lg max-h-[90vh] bg-white rounded-lg shadow border border-gray-200 overflow-y-auto">
            <div class="h-12 flex items-center justify-between px-4 border-b">
                <div class="text-sm font-semibold text-slate-800">Add Staff</div>
                <button type="button" class="text-slate-500 hover:text-slate-700" data-close="create" aria-label="Close">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 24 24" fill="currentColor">
                        <path d="M6 18 18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>
            <form method="POST" action="{{ route('admin.users.store') }}" class="p-4 space-y-4">
                @csrf
                <input type="hidden" name="form_context" value="create">
                <div>
                    <label class="block text-sm font-medium text-slate-700">Full Name</label>
                    <input type="text" name="name"
                        value="{{ old('form_context') === 'create' ? old('name') : '' }}" required
                        class="mt-1 w-full rounded-md border border-gray-300 px-3 py-2 text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500" />
                    @if (old('form_context') === 'create')
                        @error('name')
                            <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                        @enderror
                    @endif
                </div>
                <div>
                    <label class="block text-sm font-medium text-slate-700">Email</label>
                    <input type="email" name="email" id="create_email"
                        value="{{ old('form_context') === 'create' ? old('email') : '' }}" required
                        class="mt-1 w-full rounded-md border border-gray-300 px-3 py-2 text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500" />
                    <div id="create_email_feedback" class="mt-1 flex items-center gap-1 text-xs"></div>
                    @if (old('form_context') === 'create')
                        @error('email')
                            <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                        @enderror
                    @endif
                </div>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-slate-700">Department</label>
                        @php
                            $departments = \App\Models\Department::orderBy('name')->get();
                        @endphp
                        <select name="department_id" id="create_department"
                            class="mt-1 w-full rounded-md border border-gray-300 px-3 py-2 text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                            required>
                            <option value="" disabled selected>Select department</option>
                            @foreach ($departments as $dept)
                                <option value="{{ $dept->id }}"
                                    {{ old('form_context') === 'create' && old('department_id') == $dept->id ? 'selected' : '' }}>
                                    {{ $dept->name }}</option>
                            @endforeach
                        </select>
                        @if (old('form_context') === 'create')
                            @error('department_id')
                                <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                            @enderror
                        @endif
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-slate-700">Role</label>
                        <select name="role_id" id="create_role"
                            class="mt-1 w-full rounded-md border border-gray-300 px-3 py-2 text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                            required disabled>
                            <option value="" disabled selected>Select a department first</option>
                        </select>
                        @if (old('form_context') === 'create')
                            @error('role_id')
                                <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                            @enderror
                        @endif
                    </div>
                </div>
                <div>
                    <label class="block text-sm font-medium text-slate-700">Additional Roles (from any
                        department)</label>
                    <button type="button" id="toggle_additional_roles_btn"
                        class="mt-1 text-sm text-blue-600 hover:text-blue-800 flex items-center gap-1">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" viewBox="0 0 24 24" fill="currentColor">
                            <path d="M11 11V5h2v6h6v2h-6v6h-2v-6H5v-2z" />
                        </svg>
                        Add Additional Roles
                    </button>
                    <div id="create_additional_roles_container"
                        class="mt-2 hidden max-h-32 overflow-y-auto border border-gray-300 rounded-md p-2 space-y-1">
                        <p class="text-sm text-slate-500">Loading roles...</p>
                    </div>
                </div>
                <div class="pt-2 flex items-center justify-end gap-3">
                    <button type="button"
                        class="rounded-md border border-gray-300 bg-white hover:bg-gray-50 text-sm px-4 py-2"
                        data-close="create">Cancel</button>
                    <button type="submit"
                        class="rounded-md bg-blue-600 hover:bg-blue-700 text-white text-sm font-medium px-4 py-2">Save</button>
                </div>
            </form>
        </div>
    </div>
</div>
