<div class="overflow-x-auto">
    <table class="min-w-full text-sm">
        <thead class="bg-gray-50 text-gray-600">
            <tr>
                <th class="py-3 pl-5 pr-3 text-left font-medium">Name</th>
                <th class="px-3 py-3 text-left font-medium">Email</th>
                <th class="px-3 py-3 text-left font-medium">Department</th>
                <th class="px-3 py-3 text-left font-medium">Roles</th>
                <th class="py-3 pl-3 pr-5 text-left font-medium">Actions</th>
            </tr>
        </thead>
        <tbody class="divide-y divide-gray-100">
            @forelse($users as $u)
                <tr class="hover:bg-gray-50 {{ $u->trashed() ? 'opacity-70' : '' }}">
                    <td class="py-3 pl-5 pr-3 align-top">
                        <div class="flex items-center gap-3">
                            <img src="{{ $u->profile_photo ? '/storage/' . $u->profile_photo : 'https://via.placeholder.com/32x32?text=No' }}"
                                alt="Profile" class="w-8 h-8 rounded-full object-cover border border-gray-200">
                            <div class="text-slate-900 font-medium">{{ $u->name }}</div>
                        </div>
                    </td>
                    <td class="px-3 py-3 align-top">
                        <div class="text-slate-900">{{ $u->email }}</div>
                    </td>
                    <td class="px-3 py-3 align-top">
                        <div class="text-slate-900">{{ $u->department ?? '—' }}</div>
                    </td>
                    <td class="px-3 py-3 align-top">
                        <div class="flex flex-wrap gap-1">
                            @php
                                $roles = $u->roles;
                            @endphp
                            @foreach ($roles as $index => $role)
                                @php
                                    $isPrimary = $role->pivot->is_primary_role ?? false;
                                    $bgColor =
                                        $role->name === 'Primary Administrator'
                                            ? 'bg-purple-50 text-purple-700 ring-purple-600/20'
                                            : 'bg-slate-50 text-slate-700 ring-slate-600/20';
                                @endphp
                                <span
                                    class="inline-flex items-center rounded-md px-2 py-0.5 text-xs font-medium ring-1 ring-inset {{ $bgColor }} {{ $isPrimary ? 'ring-1 ring-yellow-400' : '' }}"
                                    title="{{ $isPrimary ? 'Primary Role' : 'Additional Role' }}">
                                    {{ $role->name }}
                                </span>
                            @endforeach
                        </div>
                    </td>
                    <td class="py-3 pl-3 pr-5 align-top">
                        <div class="flex items-center gap-2">
                            @if ($u->trashed())
                                <form method="POST" action="{{ route('admin.users.restore', $u) }}"
                                    class="restoreUserForm" data-user-name="{{ $u->name }}">
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
                                    data-category="{{ $u->category }}" data-category-id="{{ $u->category_id }}"
                                    data-profile-photo="{{ $u->profile_photo }}"
                                    data-email-verified-at="{{ $u->email_verified_at }}" aria-label="Edit user">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" viewBox="0 0 24 24"
                                        fill="currentColor">
                                        <path
                                            d="M3 17.25V21h3.75L17.81 9.94l-3.75-3.75L3 17.25zM20.71 7.04a1.003 1.003 0 0 0 0-1.41l-2.34-2.34a1.003 1.003 0 0 0-1.41 0l-1.83 1.83 3.75 3.75 1.83-1.83z" />
                                    </svg>
                                </button>
                                <form method="POST" action="{{ route('admin.users.destroy', $u) }}"
                                    class="deleteUserForm" data-user-name="{{ $u->name }}">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit"
                                        class="inline-flex items-center justify-center rounded-md border border-red-200 bg-white w-8 h-8 text-sm text-red-700 hover:bg-red-50"
                                        aria-label="Delete user">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" viewBox="0 0 24 24"
                                            fill="currentColor">
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
                    <td colspan="5" class="px-5 py-10 text-center text-sm text-gray-500">
                        {{ $isDeletedView ? 'No deleted users found.' : 'No staff users found.' }}</td>
                </tr>
            @endforelse
        </tbody>
    </table>
</div>
