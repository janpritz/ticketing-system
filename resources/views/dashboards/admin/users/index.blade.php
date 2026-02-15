@extends('layouts.admin')

@section('title', 'Staff Management')

@section('admin-content')
    <div class="sm:px-2">
        <div class="flex items-center justify-between gap-4">
            <div>
                <h1 class="text-2xl font-semibold text-slate-900">{{ $isDeletedView ? 'Deleted Users' : 'Staff Management' }}</h1>
                <p class="text-sm text-slate-500">{{ $isDeletedView ? 'View and restore deleted staff accounts' : 'Manage staff accounts (excluding Primary Administrator)' }}</p>
            </div>

            <!-- Desktop actions -->
            <div class="hidden sm:flex items-center gap-2">
                @if ($isDeletedView)
                    <a href="{{ route('admin.users.index') }}"
                        class="inline-flex items-center gap-2 rounded-lg border border-gray-200 bg-white hover:bg-gray-50 text-sm font-medium px-3 py-2">
                        ← Back to User Management
                    </a>
                @else
                    <a href="{{ route('admin.roles.index') }}"
                        class="inline-flex items-center gap-2 rounded-lg border border-gray-300 bg-white hover:bg-gray-50 text-sm font-medium px-3 py-2 text-slate-700"
                        aria-label="Manage Roles">
                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor"
                            class="bi bi-person-fill-gear" viewBox="0 0 16 16">
                            <path
                                d="M11 5a3 3 0 1 1-6 0 3 3 0 0 1 6 0m-9 8c0 1 1 1 1 1h5.256A4.5 4.5 0 0 1 8 12.5a4.5 4.5 0 0 1 1.544-3.393Q8.844 9.002 8 9c-5 0-6 3-6 4m9.886-3.54c.18-.613 1.048-.613 1.229 0l.043.148a.64.64 0 0 0 .921.382l.136-.074c.561-.306 1.175.308.87.869l-.075.136a.64.64 0 0 0 .382.92l.149.045c.612.18.612 1.048 0 1.229l-.15.043a.64.64 0 0 0-.38.921l.074.136c.305.561-.309 1.175-.87.87l-.136-.075a.64.64 0 0 0-.92.382l-.045.149c-.18.612-1.048.612-1.229 0l-.043-.15a.64.64 0 0 0-.921-.38l-.136.074c-.561.305-1.175-.309-.87-.87l.075-.136a.64.64 0 0 0-.382-.92l-.148-.045c-.613-.18-.613-1.048 0-1.229l.148-.043a.64.64 0 0 0 .382-.921l-.074-.136c-.306-.561.308-1.175.869-.87l.136.075a.64.64 0 0 0 .92-.382zM14 12.5a1.5 1.5 0 1 0-3 0 1.5 1.5 0 0 0 3 0" />
                        </svg>
                        <span class="hidden sm:inline">Manage Roles</span>
                    </a>
                    <a href="{{ route('admin.users.deleted') }}"
                        class="inline-flex items-center gap-2 rounded-lg border border-gray-200 bg-white hover:bg-gray-50 text-sm px-3 py-2 ml-3">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 text-slate-700" viewBox="0 0 24 24"
                            fill="currentColor">
                            <path
                                d="M3 6h18v2H3V6zm2 3h14l-1.1 12.2c-.08.9-.86 1.6-1.76 1.6H8.86c-.9 0-1.68-.7-1.76-1.6L6 9zM9 4V3h6v1h5v2H4V4h5z" />
                        </svg>
                        <span class="hidden sm:inline">Trash</span>
                    </a>
                    <button id="openCreateModalBtn" type="button"
                        class="inline-flex items-center gap-2 rounded-lg bg-blue-600 hover:bg-blue-700 text-white text-sm font-medium px-3 py-2"
                        aria-label="Add Staff">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" viewBox="0 0 24 24" fill="currentColor">
                            <path d="M11 11V5h2v6h6v2h-6v6h-2v-6H5v-2z" />
                        </svg>
                        <span class="hidden sm:inline">Add Staff</span>
                    </button>
                @endif
            </div>

            <!-- Mobile toolbar: icons only -->
            <div class="flex sm:hidden items-center gap-2">
                @if (!$isDeletedView)
                    <button id="mobileSearchToggle" type="button"
                        class="p-2 rounded-lg bg-gray-100 hover:bg-gray-200 text-slate-700" aria-label="Search">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 24 24" fill="currentColor">
                            <path
                                d="M15.5 14h-.79l-.28-.27a6.5 6.5 0 10-.71.71l.27.28v.79L20 21.5 21.5 20l-6-6zM10 15a5 5 0 110-10 5 5 0 010 10z" />
                        </svg>
                    </button>

                    <a href="{{ route('admin.roles.index') }}" id="manageRolesBtnMobile"
                        class="p-2 rounded-lg bg-white border border-gray-200 text-slate-700 hover:bg-gray-50"
                        aria-label="Manage Roles (mobile)">
                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor"
                            class="bi bi-person-fill-gear" viewBox="0 0 16 16">
                            <path
                                d="M11 5a3 3 0 1 1-6 0 3 3 0 0 1 6 0m-9 8c0 1 1 1 1 1h5.256A4.5 4.5 0 0 1 8 12.5a4.5 4.5 0 0 1 1.544-3.393Q8.844 9.002 8 9c-5 0-6 3-6 4m9.886-3.54c.18-.613 1.048-.613 1.229 0l.043.148a.64.64 0 0 0 .921.382l.136-.074c.561-.306 1.175.308.87.869l-.075.136a.64.64 0 0 0 .382.92l.149.045c.612.18.612 1.048 0 1.229l-.15.043a.64.64 0 0 0-.38.921l.074.136c.305.561-.309 1.175-.87.87l-.136-.075a.64.64 0 0 0-.92.382l-.045.149c-.18.612-1.048.612-1.229 0l-.043-.15a.64.64 0 0 0-.921-.38l-.136.074c-.561.305-1.175-.309-.87-.87l.075-.136a.64.64 0 0 0-.382-.92l-.148-.045c-.613-.18-.613-1.048 0-1.229l.148-.043a.64.64 0 0 0 .382-.921l-.074-.136c-.306-.561.308-1.175.869-.87l.136.075a.64.64 0 0 0 .92-.382zM14 12.5a1.5 1.5 0 1 0-3 0 1.5 1.5 0 0 0 3 0" />
                        </svg>
                    </a>

                    <a href="{{ route('admin.users.deleted') }}" id="trashBtnMobile"
                        class="p-2 rounded-lg bg-white border border-gray-200 text-slate-700 hover:bg-gray-50"
                        aria-label="Trash (mobile)">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 24 24" fill="currentColor">
                            <path d="M3 6h18v2H3V6zm2 3h14l-1.1 12.2c-.08.9-.86 1.6-1.76 1.6H8.86c-.9 0-1.68-.7-1.76-1.6L6 9zm5 3v7h2v-7h-2zm4 0v7h2v-7h-2zM9 4V3h6v1h5v2H4V4h5z" />
                        </svg>
                    </a>

                    <button id="openCreateModalBtnMobile" type="button"
                        class="p-2 rounded-lg bg-blue-600 hover:bg-blue-700 text-white" aria-label="Add Staff (mobile)">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 24 24" fill="currentColor">
                            <path d="M11 11V5h2v6h6v2h-6v6h-2v-6H5v-2z" />
                        </svg>
                    </button>
                @endif
            </div>
        </div>
        <div class="mt-5">
            <!-- Desktop search -->
            <div class="hidden sm:flex items-center gap-3">
                <form method="GET" action="{{ route('admin.users.index') }}" class="flex items-center gap-2">
                    <label class="relative block">
                        <span class="sr-only">Search</span>
                        <span class="absolute inset-y-0 left-0 flex items-center pl-3 text-gray-400">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" viewBox="0 0 24 24" fill="currentColor">
                                <path
                                    d="M15.5 14h-.79l-.28-.27a6.5 6.5 0 10-.71.71l.27.28v.79L20 21.5 21.5 20l-6-6zM10 15a5 5 0 110-10 5 5 0 010 10z" />
                            </svg>
                        </span>
                        <input type="text" name="q" value="{{ $q ?? '' }}"
                            placeholder="Search name, email, role, category"
                            class="w-72 pl-9 pr-3 py-2 text-sm rounded-md border border-gray-200 focus:ring-2 focus:ring-blue-500 focus:border-blue-500" />
                    </label>
                    <button type="submit"
                        class="rounded-md border border-gray-300 bg-white hover:bg-gray-50 text-sm px-3 py-2">Search</button>
                    @if (($q ?? '') !== '')
                        <a href="{{ route('admin.users.index') }}"
                            class="text-sm text-slate-600 hover:text-slate-800">Clear</a>
                    @endif
                </form>
            </div>

            <!-- Mobile search area (toggled) -->
            <div id="mobileSearchArea" class="sm:hidden mt-3 hidden">
                <div class="flex items-center gap-2">
                    <input id="q_mobile" type="text" placeholder="Search name, email, role, category"
                        class="flex-1 pl-3 pr-3 py-2 rounded-md border border-gray-200 text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500" />
                    <button id="mobileSearchBtn" type="button"
                        class="rounded-md border border-gray-300 bg-white hover:bg-gray-50 text-sm px-3 py-2"
                        aria-label="Search">Search</button>
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
                            <tr class="hover:bg-gray-50 {{ $u->trashed() ? 'opacity-70' : '' }}">
                                <td class="py-3 pl-5 pr-3 align-top">
                                     <div class="flex items-center gap-3">
                                         <img src="{{ $u->profile_photo ? '/storage/' . $u->profile_photo : 'https://via.placeholder.com/32x32?text=No' }}" alt="Profile" class="w-8 h-8 rounded-full object-cover border border-gray-200">
                                         <div class="text-slate-900 font-medium">{{ $u->name }}</div>
                                     </div>
                                 </td>
                                 <td class="px-3 py-3 align-top">
                                     <div class="text-slate-900">{{ $u->email }}</div>
                                 </td>
                                 <td class="px-3 py-3 align-top">
                                     <span
                                         class="inline-flex items-center rounded-md px-2 py-0.5 text-xs font-medium ring-1 ring-inset {{ $u->role === 'Primary Administrator' ? 'text-purple-700 bg-purple-50 ring-purple-600/20' : 'text-slate-700 bg-slate-50 ring-slate-600/20' }}">
                                         {{ $u->role }}
                                     </span>
                                 </td>
                                  <td class="px-3 py-3 align-top">
                                      <div class="text-slate-900">{{ is_object($u->category) ? ($u->category->name ?? '—') : ($u->category ?? '—') }}</div>
                                  </td>
                                 <td class="py-3 pl-3 pr-5 align-top">
                                     <div class="flex items-center gap-2">
                                         @if ($u->trashed())
                                             <form method="POST" action="{{ route('admin.users.restore', $u) }}" class="restoreUserForm" data-user-name="{{ $u->name }}">
                                                 @csrf
                                                 <button type="submit"
                                                     class="inline-flex items-center gap-1 rounded-md border border-gray-200 bg-white px-3 py-1.5 text-sm font-medium text-gray-700 hover:bg-gray-50">
                                                     Restore
                                                 </button>
                                             </form>
                                         @else
                                             <button type="button"
                                                 class="openEditModalBtn inline-flex items-center justify-center rounded-md border border-gray-200 bg-white w-8 h-8 text-sm text-gray-700 hover:bg-gray-50"
                                                 data-id="{{ $u->id }}" data-name="{{ $u->name }}"
                                                 data-email="{{ $u->email }}" data-role="{{ $u->role }}"
                                                  data-category="{{ $u->category }}" data-category-id="{{ $u->category_id }}" data-profile-photo="{{ $u->profile_photo }}" aria-label="Edit user">
                                                 <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" viewBox="0 0 24 24"
                                                     fill="currentColor">
                                                     <path
                                                         d="M3 17.25V21h3.75L17.81 9.94l-3.75-3.75L3 17.25zM20.71 7.04a1.003 1.003 0 0 0 0-1.41l-2.34-2.34a1.003 1.003 0 0 0-1.41 0l-1.83 1.83 3.75 3.75 1.83-1.83z" />
                                                 </svg>
                                             </button>
                                             <form method="POST" action="{{ route('admin.users.destroy', $u) }}" class="deleteUserForm" data-user-name="{{ $u->name }}">
                                                 @csrf
                                                 @method('DELETE')
                                                 <button type="submit"
                                                     class="inline-flex items-center justify-center rounded-md border border-red-200 bg-white w-8 h-8 text-sm text-red-700 hover:bg-red-50"
                                                     aria-label="Delete user">
                                                     <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4"
                                                         viewBox="0 0 24 24" fill="currentColor">
                                                         <path
                                                             d="M3 6h18v2H3V6zm2 3h14l-1.1 12.2c-.08.9-.86 1.6-1.76 1.6H8.86c-.9 0-1.68-.7-1.76-1.6L6 9zm5 3v7h2v-7h-2zm4 0v7h2v-7h-2zM9 4V3h6v1h5v2H4V4h5z" />
                                                     </svg>
                                                 </button>
                                             </form>
                                         @endif
                                     </div>
                                 </td>
                             </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="px-5 py-10 text-center text-sm text-gray-500">{{ $isDeletedView ? 'No deleted users found.' : 'No staff users found.' }}</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div class="px-5 py-3 border-t border-gray-200">
                <div class="flex items-center justify-between">
                    <div class="text-sm text-slate-600">
                        Showing {{ $users->perPage() }} per page — {{ $users->total() }} total
                        @if ($users->total() > 0)
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
                    <button type="button" class="text-slate-500 hover:text-slate-700" data-close="create"
                        aria-label="Close">
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
                        <input type="email" name="email"
                            value="{{ old('form_context') === 'create' ? old('email') : '' }}" required
                            class="mt-1 w-full rounded-md border border-gray-300 px-3 py-2 text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500" />
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
                            <label class="block text-sm font-medium text-slate-700">Roles</label>
                            <div id="create_roles_container" class="mt-1 max-h-32 overflow-y-auto border border-gray-300 rounded-md p-2 space-y-1">
                                <p class="text-sm text-slate-500">Select a department first</p>
                            </div>
                            @if (old('form_context') === 'create')
                                @error('roles')
                                    <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                                @enderror
                            @endif
                        </div>
                    </div>
                    <div class="pt-2 flex items-center justify-end gap-3">
                        <button type="button"
                            class="rounded-md border border-gray-300 bg-white hover:bg-gray-50 text-sm px-4 py-2"
                            data-close="create">Cancel</button>
                        <button type="submit"
                            class="rounded-md bg-blue-600 hover:bg-blue-700 text-white text-sm font-medium px-4 py-2">Create
                            Staff</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Edit Staff Modal -->
    <div id="editStaffModal" class="fixed inset-0 z-50 hidden"
        data-update-template="{{ route('admin.users.update', ['user' => '__ID__']) }}">
        <div class="absolute inset-0 bg-black/40" data-close="edit"></div>
        <div class="absolute inset-0 flex items-center justify-center p-4">
            <div class="w-full max-w-lg bg-white rounded-lg shadow border border-gray-200">
                <div class="h-12 flex items-center justify-between px-4 border-b">
                    <div class="text-sm font-semibold text-slate-800">Edit Staff</div>
                    <button type="button" class="text-slate-500 hover:text-slate-700" data-close="edit"
                        aria-label="Close">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 24 24" fill="currentColor">
                            <path d="M6 18 18 6M6 6l12 12" />
                        </svg>
                    </button>
                </div>
                <form id="editStaffForm" method="POST" action="#" class="p-4 space-y-4">
                    @csrf
                    @method('PUT')
                    <input type="hidden" name="editing_user_id" id="edit_user_id"
                        value="{{ old('editing_user_id') }}">
                    <div class="flex justify-center mb-4">
                        <img id="edit_profile_photo" src="" alt="Profile Photo" class="w-20 h-20 rounded-md object-cover border border-gray-200">
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
                        <label class="block text-sm font-medium text-slate-700">Email</label>
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
                                @foreach($editDepartments as $dept)
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
                            <label class="block text-sm font-medium text-slate-700">Roles</label>
                            <div id="edit_roles_container" class="mt-1 max-h-32 overflow-y-auto border border-gray-300 rounded-md p-2 space-y-1">
                                <p class="text-sm text-slate-500">Select a department first</p>
                            </div>
                            @if (old('editing_user_id'))
                                @error('roles')
                                    <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                                @enderror
                            @endif
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

@endsection

<!-- Hidden state for JS (prevents Blade in JS parsing issues) -->
<div id="admin-users-state" class="hidden" data-has-errors="{{ $errors->any() ? '1' : '0' }}"
    data-old-edit-id="{{ old('editing_user_id') }}" data-old-form-context="{{ old('form_context') }}"
    data-old-name="{{ old('name') }}" data-old-email="{{ old('email') }}" data-old-role="{{ old('role') }}"
    data-old-category-id="{{ old('category_id') }}" data-old-profile-photo="{{ old('profile_photo') }}"></div>
@section('admin-scripts')
    <script>
        (function() {
            const $ = (sel, root = document) => root.querySelector(sel);
            const $$ = (sel, root = document) => Array.from(root.querySelectorAll(sel));
            const body = document.body;

            // SweetAlert2 flash toasts for success/errors
            try {
              if (typeof Swal !== 'undefined') {
                @if (session('status'))
                Swal.fire({
                  icon: 'success',
                  title: {!! json_encode(session('status')) !!},
                  toast: true,
                  position: 'top-end',
                  timer: 3000,
                  showConfirmButton: false
                });
                @endif
                @if ($errors->any())
                Swal.fire({
                  icon: 'error',
                  title: 'Validation error',
                  text: {!! json_encode($errors->first()) !!}
                });
                @endif
              }
            } catch (e) { /* ignore */ }

            // Delete confirmation via SweetAlert2 (fallback to native confirm if unavailable)
            $$('form.deleteUserForm').forEach((form) => {
              form.addEventListener('submit', function (e) {
                if (typeof Swal === 'undefined') {
                  if (!confirm('Delete this user? This action cannot be undone.')) {
                    e.preventDefault();
                  }
                  return;
                }
                e.preventDefault();
                const name = form.getAttribute('data-user-name') || 'this user';
                Swal.fire({
                  title: 'Delete ' + name + '?',
                  text: 'This will move the user to trash. You can restore them later.',
                  icon: 'warning',
                  showCancelButton: true,
                  confirmButtonColor: '#d33',
                  cancelButtonColor: '#6b7280',
                  confirmButtonText: 'Yes, delete',
                  cancelButtonText: 'Cancel'
                }).then((result) => {
                  if (result.isConfirmed) form.submit();
                });
              });
            });

            // Restore confirmation via SweetAlert2
            $$('form.restoreUserForm').forEach((form) => {
              form.addEventListener('submit', function (e) {
                if (typeof Swal === 'undefined') {
                  if (!confirm('Restore this user?')) {
                    e.preventDefault();
                  }
                  return;
                }
                e.preventDefault();
                const name = form.getAttribute('data-user-name') || 'this user';
                Swal.fire({
                  title: 'Restore ' + name + '?',
                  text: 'This will restore the user account.',
                  icon: 'question',
                  showCancelButton: true,
                  confirmButtonColor: '#3085d6',
                  cancelButtonColor: '#6b7280',
                  confirmButtonText: 'Yes, restore',
                  cancelButtonText: 'Cancel'
                }).then((result) => {
                  if (result.isConfirmed) form.submit();
                });
              });
            });

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
            const OLD_CATEGORY_ID = stateEl ? stateEl.getAttribute('data-old-category-id') : '';
            const OLD_PROFILE_PHOTO = stateEl ? stateEl.getAttribute('data-old-profile-photo') : '';

            if (HAS_ERRORS) {
                if (OLD_EDIT_ID) {
                    if (editId) editId.value = OLD_EDIT_ID || '';
                    if (editName) editName.value = OLD_NAME || '';
                    if (editEmail) editEmail.value = OLD_EMAIL || '';
                    if (editRole) editRole.value = OLD_ROLE || '';
                    if (editCategory) editCategory.value = OLD_CATEGORY_ID || '';
                    // Set profile photo
                    const photoEl = $('#edit_profile_photo');
                    if (photoEl) {
                        if (OLD_PROFILE_PHOTO) {
                            photoEl.src = '/storage/' + OLD_PROFILE_PHOTO;
                        } else {
                            photoEl.src = 'https://via.placeholder.com/80x80?text=No+Photo';
                        }
                    }
                    if (editForm && updateTemplate && OLD_EDIT_ID) {
                        editForm.setAttribute('action', updateTemplate.replace('__ID__', OLD_EDIT_ID));
                    }
                    // Load categories for the role
                    if (editRole && editRole.value && editCategory) {
                        updateCategories(editCategory, editRole.value);
                    }
                    openModal(editModal);
                } else if (OLD_FORM_CONTEXT === 'create') {
                    // If old create values exist, pre-load categories for the selected role and preserve selection
                    if (createRole && createRole.value && createCategory) {
                        // Use the same OLD_CATEGORY_ID stored in the state element
                        updateCategories(createCategory, createRole.value, OLD_CATEGORY_ID);
                    }
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

            // Conditional dropdowns for department-roles
            function updateRolesForDepartment(selectElement, departmentId, selectedRoles) {
                const container = selectElement; // This is the roles container div
                const isEdit = container.id === 'edit_roles_container';
                
                if (!departmentId) {
                    container.innerHTML = '<p class="text-sm text-slate-500">Select a department first</p>';
                    return;
                }
                
                fetch(`/admin/roles/by-department/${departmentId}`)
                    .then(response => response.json())
                    .then(roles => {
                        if (roles.length === 0) {
                            container.innerHTML = '<p class="text-sm text-slate-500">No roles in this department</p>';
                            return;
                        }
                        
                        let html = '';
                        roles.forEach(role => {
                            const isChecked = selectedRoles && selectedRoles.includes(role.id) ? 'checked' : '';
                            html += `
                                <div class="flex items-center gap-2">
                                    <input type="checkbox" name="roles[]" value="${role.id}" id="${isEdit ? 'edit' : 'create'}_role_${role.id}" class="rounded border-gray-300 text-blue-600 focus:ring-blue-500" ${isChecked}>
                                    <label for="${isEdit ? 'edit' : 'create'}_role_${role.id}" class="text-sm text-slate-700">${role.name}</label>
                                </div>
                            `;
                        });
                        container.innerHTML = html;
                    })
                    .catch(error => {
                        console.error('Error fetching roles:', error);
                        container.innerHTML = '<p class="text-sm text-red-500">Error loading roles</p>';
                    });
            }

            // Create modal department change
            const createDepartment = $('#create_department');
            const createRolesContainer = $('#create_roles_container');
            if (createDepartment && createRolesContainer) {
                createDepartment.addEventListener('change', () => {
                    updateRolesForDepartment(createRolesContainer, createDepartment.value, null);
                });
            }

            // Edit modal department change
            const editDepartment = $('#edit_department');
            const editRolesContainer = $('#edit_roles_container');
            if (editDepartment && editRolesContainer) {
                editDepartment.addEventListener('change', () => {
                    updateRolesForDepartment(editRolesContainer, editDepartment.value, null);
                });
            }

            // Load categories for edit modal on open
            $$('.openEditModalBtn').forEach(btn => {
                btn.addEventListener('click', () => {
                    const id = btn.getAttribute('data-id');
                    const name = btn.getAttribute('data-name') || '';
                    const email = btn.getAttribute('data-email') || '';
                    const role = btn.getAttribute('data-role') || '';
                    const category = btn.getAttribute('data-category') || '';
                    const categoryId = btn.getAttribute('data-category-id') || '';
                    const profilePhoto = btn.getAttribute('data-profile-photo') || '';

                    if (editId) editId.value = id || '';
                    if (editName) editName.value = name;
                    if (editEmail) editEmail.value = email;
                    if (editRole) editRole.value = role;
                    if (editCategory) editCategory.value = categoryId || '';

                    // Set profile photo
                    const photoEl = $('#edit_profile_photo');
                    if (photoEl) {
                        if (profilePhoto) {
                            photoEl.src = '/storage/' + profilePhoto;
                        } else {
                            photoEl.src = 'https://via.placeholder.com/80x80?text=No+Photo'; // Fallback placeholder
                        }
                    }

                    if (editForm && updateTemplate && id) {
                        editForm.setAttribute('action', updateTemplate.replace('__ID__', id));
                    }

                    // Load categories for the role (preserve selected)
                    if (editRole && editRole.value && editCategory) {
                        updateCategories(editCategory, editRole.value, editCategory.value);
                    }

                    openModal(editModal);
                });
            });

            // Also for auto-open on validation errors
            if (HAS_ERRORS && OLD_EDIT_ID && editRole && editRole.value && editCategory) {
                updateCategories(editCategory, editRole.value, OLD_CATEGORY_ID);
            }

            // Ensure form submits integer category_id only (protect against legacy name values)
            function sanitizeCategorySelectBeforeSubmit(form) {
                const sel = form.querySelector('select[name="category_id"]');
                if (!sel) return;
                // If selected value is not an integer, clear it so server receives null
                if (!/^\d+$/.test(String(sel.value))) {
                    sel.value = '';
                }
            }

            // Attach sanitizer to create/edit forms
            const createForm = createModal ? createModal.querySelector('form') : null;
            if (createForm) {
                createForm.addEventListener('submit', function () {
                    sanitizeCategorySelectBeforeSubmit(createForm);
                });
            }
            if (editForm) {
                editForm.addEventListener('submit', function () {
                    sanitizeCategorySelectBeforeSubmit(editForm);
                });
            }
        })();
    </script>
@endsection
