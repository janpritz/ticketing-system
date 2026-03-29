<div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
    <div
        class="px-6 py-4 border-b border-gray-100 bg-gradient-to-r from-gray-50 to-white flex items-center justify-between">
        <h2 class="text-lg font-semibold text-gray-900 flex items-center gap-2">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-indigo-600" fill="none" viewBox="0 0 24 24"
                stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                    d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
            </svg>
            Profile Information
        </h2>
        <button type="button" data-modal-target="editProfileModal" data-modal-toggle="editProfileModal"
            class="inline-flex items-center justify-center gap-2 text-sm font-medium text-indigo-600 hover:text-indigo-700 transition-colors focus:outline-none">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24"
                stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                    d="M11 5h6M11 12h6M11 19h6M4 6h.01M4 13h.01M4 20h.01" />
            </svg>
            Edit Profile
        </button>
    </div>

    <div class="p-6">
        <div class="flex items-start justify-between">
            <div class="text-sm text-gray-500">Profile details are read-only. Use Edit to make changes.
            </div>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-5 mt-6">
            <div>
                <p class="text-xs text-gray-500">Full Name</p>
                <p class="mt-1 text-gray-900 font-medium">{{ $user->name }}</p>
            </div>

            <div>
                <p class="text-xs text-gray-500">Email Address</p>
                <p class="mt-1 text-gray-900 font-medium">{{ $user->email }}</p>
                <p class="mt-1 text-xs text-gray-500">Contact administrator to update email</p>
            </div>

            <div class="space-y-2">
                <p class="text-xs tracking-wider text-slate-400">Role Assignment</p>

                <div class="flex flex-wrap gap-1.5">
                    @forelse (($profile_roles ?? collect()) as $role)
                        <div
                            class="inline-flex items-center gap-2 rounded-lg px-2.5 py-1 text-xs font-medium 
                {{ !empty($role['is_primary']) ? 'bg-indigo-50 text-indigo-700' : 'bg-slate-50 text-slate-600' }}">

                            @if (!empty($role['is_primary']))
                                <span class="relative flex h-2 w-2">
                                    <span
                                        class="animate-ping absolute inline-flex h-full w-full rounded-full bg-indigo-400 opacity-75"></span>
                                    <span class="relative inline-flex rounded-full h-2 w-2 bg-indigo-600"></span>
                                </span>
                            @endif

                            <span>{{ $role['name'] }}</span>

                            @if (!empty($role['is_primary']))
                                <span class="text-[10px] opacity-60 font-bold italic tracking-tight">
                                    (Primary)
                                </span>
                            @endif
                        </div>
                    @empty
                        <p class="text-sm text-slate-400 italic">No roles assigned</p>
                    @endforelse
                </div>
            </div>

            <div>
                <p class="text-xs text-gray-500">Department</p>
                <p class="mt-1 text-gray-900 font-medium">
                    {{ $user->department->name ?? 'Unassigned' }}
                </p>
            </div>

            <div>
                <p class="text-xs text-gray-500">Date Joined</p>
                <p class="mt-1 text-gray-900 font-medium">
                    {{ optional($user->created_at)->format('M d, Y h:i A') }}</p>
            </div>

            <!-- Profile photo intentionally omitted here (displayed on left name card) -->
            <div></div>
        </div>
    </div>
</div>
