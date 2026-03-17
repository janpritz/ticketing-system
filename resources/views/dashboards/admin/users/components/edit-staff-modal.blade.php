<div id="editStaffModal" class="fixed inset-0 z-50 hidden"
    data-update-template="{{ route('admin.users.update', ['user' => '__ID__']) }}">
    <div class="absolute inset-0 bg-black/40" data-close="edit"></div>
    <div class="absolute inset-0 flex items-center justify-center p-4">
        <div class="w-full max-w-lg max-h-[90vh] bg-white rounded-lg shadow border border-gray-200 overflow-y-auto">
            <div class="h-12 flex items-center justify-between px-4 border-b">
                <div class="text-sm font-semibold text-slate-800">Edit Staff</div>
                <button type="button" class="text-slate-500 hover:text-slate-700" data-close="edit" aria-label="Close">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 24 24" fill="currentColor">
                        <path d="M6 18 18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>
            <form id="editStaffForm" method="POST" action="#" class="p-4 space-y-4">
                @csrf
                @method('PUT')
                <input type="hidden" name="editing_user_id" id="edit_user_id" value="{{ old('editing_user_id') }}">
                <div class="flex justify-center mb-4">
                    <img id="edit_profile_photo" src="" alt="Profile Photo"
                        class="w-20 h-20 rounded-md object-cover border border-gray-200">
                </div>
                <div>
                    <label class="block text-sm font-medium text-slate-700">Full Name</label>
                    <input type="text" name="name" id="edit_name" value="" required
                        class="mt-1 w-full rounded-md border border-gray-300 px-3 py-2 text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500" />
                    @if (old('editing_user_id'))
                        @error('name')
                            <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                        @enderror
                    @endif
                </div>
                <div>
                    <label class="block text-sm font-medium text-slate-700">
                        Email
                        <div class="inline-flex items-center gap-2 ml-2" id="email_verification_status"></div>
                    </label>
                    <input type="email" name="email" id="edit_email" value="" required
                        class="mt-1 w-full rounded-md border border-gray-300 px-3 py-2 text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500" />
                    @if (old('editing_user_id'))
                        @error('email')
                            <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                        @enderror
                    @endif
                </div>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-slate-700">Department</label>
                        @php
                            $editDepartments = \App\Models\Department::orderBy('name')->get();
                        @endphp
                        <select name="department_id" id="edit_department" required
                            class="mt-1 w-full rounded-md border border-gray-300 px-3 py-2 text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                            <option value="" disabled>Select department</option>
                            @foreach ($editDepartments as $dept)
                                <option value="{{ $dept->id }}">{{ $dept->name }}</option>
                            @endforeach
                        </select>
                        @if (old('editing_user_id'))
                            @error('department_id')
                                <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                            @enderror
                        @endif
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-slate-700">Role</label>
                        <select name="role_id" id="edit_role"
                            class="mt-1 w-full rounded-md border border-gray-300 px-3 py-2 text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                            required disabled>
                            <option value="" disabled>Select a department first</option>
                        </select>
                        @if (old('editing_user_id'))
                            @error('role_id')
                                <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                            @enderror
                        @endif
                    </div>
                </div>
                <div>
                    <label class="block text-sm font-medium text-slate-700">Additional Roles (from any
                        department)</label>
                    <button type="button" id="toggle_edit_additional_roles_btn"
                        class="mt-1 text-sm text-blue-600 hover:text-blue-800 flex items-center gap-1">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" viewBox="0 0 24 24" fill="currentColor">
                            <path d="M11 11V5h2v6h6v2h-6v6h-2v-6H5v-2z" />
                        </svg>
                        Add Additional Roles
                    </button>
                    <div id="edit_additional_roles_container"
                        class="mt-2 hidden max-h-32 overflow-y-auto border border-gray-300 rounded-md p-2 space-y-1">
                        <p class="text-sm text-slate-500">Loading roles...</p>
                    </div>
                </div>
                <div class="pt-2 flex items-center justify-end gap-3">
                    <button type="button"
                        class="rounded-md border border-gray-300 bg-white hover:bg-gray-50 text-sm px-4 py-2"
                        data-close="edit">Cancel</button>
                    <button type="submit"
                        class="rounded-md bg-blue-600 hover:bg-blue-700 text-white text-sm font-medium px-4 py-2">Save
                        Changes</button>
                </div>
            </form>
        </div>
    </div>
</div>
