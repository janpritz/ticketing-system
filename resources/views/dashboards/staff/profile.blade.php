@extends('layouts.staff')

@section('title', 'Staff Dashboard')

@section('staff-content')
    <div class="min-h-screen bg-gradient-to-br from-gray-50 to-gray-100 sm:px-2 lg:px-2">
        <div class="mx-auto max-w-5xl">
            <!-- Main Content Grid -->
            <div class="grid grid-cols-1 lg:grid-cols-12 gap-6">

                <!-- Left Sidebar - Profile Photo & Quick Stats -->
                <div class="lg:col-span-4 space-y-6">
                    <!-- Profile Photo Card -->
                    <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
                        <div class="bg-gradient-to-br from-indigo-500 to-purple-600 h-24"></div>
                        <div class="px-6 pb-6">
                            <div class="flex flex-col items-center -mt-12">
                                @php
                                    $ver = optional($user->updated_at)->timestamp;
                                    $photo = $user->profile_photo
                                        ? asset('storage/' . $user->profile_photo) . '?v=' . $ver
                                        : null;
                                @endphp
                                <img id="photoPreview" class="h-24 w-24 rounded-full object-cover ring-4 ring-white shadow-lg"
                                    src="{{ $photo ?: 'https://ui-avatars.com/api/?background=E5E7EB&color=111827&name=' . urlencode($user->name) }}"
                                    alt="Profile Photo">
                                <h2 class="mt-4 text-xl font-semibold text-gray-900">{{ $user->name }}</h2>
                                <p class="text-sm text-gray-500">{{ $user->role }}</p>
                                <div
                                    class="mt-3 inline-flex items-center gap-2 rounded-full bg-indigo-50 px-3 py-1 text-xs font-medium text-indigo-700">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-3.5 w-3.5" fill="none"
                                        viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M21 13.255A23.931 23.931 0 0112 15c-3.183 0-6.22-.62-9-1.745M16 6V4a2 2 0 00-2-2h-4a2 2 0 00-2 2v2m4 6h.01M5 20h14a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" />
                                    </svg>
                                    {{ is_object($user->category) ? $user->category->name ?? 'General' : ($user->category ?: 'General') }}
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Quick Stats Card -->
                    <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6">
                        <h3 class="text-sm font-semibold text-gray-900 mb-4 flex items-center gap-2">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 text-indigo-600" fill="none"
                                viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" />
                            </svg>
                            Performance Overview
                        </h3>
                        <div class="space-y-4">
                            <div class="flex items-center justify-between p-3 rounded-lg bg-blue-50 border border-blue-100">
                                <div>
                                    <p class="text-xs font-medium text-blue-900">Assigned</p>
                                    <p class="text-2xl font-bold text-blue-700 mt-1">{{ (int) $assignedCount }}</p>
                                </div>
                                <div class="h-12 w-12 rounded-full bg-blue-100 flex items-center justify-center">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 text-blue-600" fill="none"
                                        viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" />
                                    </svg>
                                </div>
                            </div>
                            <div
                                class="flex items-center justify-between p-3 rounded-lg bg-emerald-50 border border-emerald-100">
                                <div>
                                    <p class="text-xs font-medium text-emerald-900">Closed</p>
                                    <p class="text-2xl font-bold text-emerald-700 mt-1">{{ (int) $resolvedCount }}</p>
                                </div>
                                <div class="h-12 w-12 rounded-full bg-emerald-100 flex items-center justify-center">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 text-emerald-600" fill="none"
                                        viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                                    </svg>
                                </div>
                            </div>
                            <div
                                class="flex items-center justify-between p-3 rounded-lg bg-purple-50 border border-purple-100">
                                <div>
                                    <p class="text-xs font-medium text-purple-900">Success Rate</p>
                                    @php
                                        $rate =
                                            $assignedCount > 0
                                                ? round(($resolvedCount / max(1, $assignedCount)) * 100)
                                                : 0;
                                    @endphp
                                    <p class="text-2xl font-bold text-purple-700 mt-1">{{ $rate }}%</p>
                                </div>
                                <div class="h-12 w-12 rounded-full bg-purple-100 flex items-center justify-center">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 text-purple-600" fill="none"
                                        viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6" />
                                    </svg>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Right Content - Profile Form & Activity -->
                <div class="lg:col-span-8 space-y-6">

                    <!-- Profile Information Form -->
                    <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
                        <div
                            class="px-6 py-4 border-b border-gray-100 bg-gradient-to-r from-gray-50 to-white flex items-center justify-between">
                            <h2 class="text-lg font-semibold text-gray-900 flex items-center gap-2">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-indigo-600" fill="none"
                                    viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                                </svg>
                                Profile Information
                            </h2>
                            <button type="button" data-modal-toggle="editProfileModal"
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

                                <div>
                                    <p class="text-xs text-gray-500">Role</p>
                                    <p class="mt-1 text-gray-900 font-medium">{{ $user->role }}</p>
                                </div>

                                <div>
                                    <p class="text-xs text-gray-500">Category</p>
                                    <p class="mt-1 text-gray-900 font-medium">
                                        {{ is_object($user->category) ? $user->category->name ?? 'Unassigned' : ($user->category ?: 'Unassigned') }}
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

                    <!-- Recent Activity -->
                    <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
                        <div class="px-6 py-4 border-b border-gray-100 bg-gradient-to-r from-gray-50 to-white">
                            <h2 class="text-lg font-semibold text-gray-900 flex items-center gap-2">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-indigo-600" fill="none"
                                    viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                                </svg>
                                Recent Activity
                            </h2>
                            <p class="text-sm text-gray-500 mt-1">Last 5 resolved tickets</p>
                        </div>

                        <div class="overflow-x-auto">
                            <table class="min-w-full divide-y divide-gray-200">
                                <thead class="bg-gray-50">
                                    <tr>
                                        <th
                                            class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                            Ticket ID</th>
                                        <th
                                            class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                            Category</th>
                                        <th
                                            class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                            Status</th>
                                        <th
                                            class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                            Updated</th>
                                    </tr>
                                </thead>
                                <tbody class="bg-white divide-y divide-gray-200">
                                    @forelse($recentTickets as $t)
                                        <tr class="hover:bg-gray-50 transition-colors btn-view cursor-pointer" data-id="{{ $t->id }}">
                                            <td class="px-6 py-4 whitespace-nowrap">
                                                <span
                                                    class="text-sm font-medium text-indigo-600">#{{ $t->id }}</span>
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap">
                                                <span
                                                    class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-gray-100 text-gray-800">
                                                    {{ is_object($t->category) ? ($t->category->name ?? ($t->getAttribute('category') ?? '-')) : ($t->getAttribute('category') ?? '-') }}
                                                </span>
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap">
                                                <span
                                                    class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-emerald-100 text-emerald-800">
                                                    {{ $t->status }}
                                                </span>
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                                {{ \Illuminate\Support\Carbon::parse($t->date_closed ?? ($t->updated_at ?? $t->date_created))->format('M d, Y h:i A') }}
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="4" class="px-6 py-12 text-center">
                                                <svg xmlns="http://www.w3.org/2000/svg"
                                                    class="h-12 w-12 mx-auto text-gray-300 mb-3" fill="none"
                                                    viewBox="0 0 24 24" stroke="currentColor">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                        d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                                                </svg>
                                                <p class="text-sm text-gray-500">No recently resolved tickets</p>
                                            </td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>

                    <!-- Push Notifications -->
                    <div id="pushNotificationCard"
                        class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
                        <div
                            class="px-6 py-4 border-b border-gray-100 bg-gradient-to-r from-emerald-50 to-white flex flex-col sm:flex-row sm:items-center justify-between gap-3">
                            <div>
                                <h2 class="text-lg font-semibold text-gray-900 flex items-center gap-2">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-emerald-600"
                                        fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9" />
                                    </svg>
                                    Push Notifications
                                </h2>
                                <p class="text-sm text-gray-500 mt-1">Stay updated with real-time alerts</p>
                            </div>
                            <button onclick="askForPermission()"
                                class="inline-flex items-center justify-center gap-2 bg-emerald-600 hover:bg-emerald-700 text-white text-sm font-medium px-5 py-2.5 rounded-lg focus:outline-none focus:ring-2 focus:ring-emerald-500 focus:ring-offset-2 transition-all shadow-sm">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none"
                                    viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9" />
                                </svg>
                                Enable Notifications
                            </button>
                        </div>
                    </div>
                    <!-- Email Notifications -->
                    <div id="emailNotificationCard" class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden mt-4">
                        <div class="px-6 py-4 border-b border-gray-100 bg-gradient-to-r from-indigo-50 to-white flex flex-col sm:flex-row sm:items-center justify-between gap-3">
                            <div>
                                <h2 class="text-lg font-semibold text-gray-900 flex items-center gap-2">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-indigo-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" />
                                    </svg>
                                    Email Notifications
                                </h2>
                                <p class="text-sm text-gray-500 mt-1">Receive email notifications for ticket updates</p>
                            </div>
                            <div>
                                <button id="emailNotifToggle" role="switch" aria-checked="false" type="button" class="inline-flex items-center px-3 py-1.5 rounded-full text-sm font-medium bg-gray-200 text-gray-700 focus:outline-none">
                                    <span id="emailNotifDot" class="inline-block w-3 h-3 rounded-full bg-white shadow-sm mr-2"></span>
                                    <span id="emailNotifLabel">Off</span>
                                </button>
                            </div>
                        </div>
                    </div>
                    {{-- Change Password Card --}}
                    <div id="changePasswordCard"
                        class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
                        <div
                            class="px-6 py-4 border-b border-gray-100 bg-gradient-to-r from-indigo-50 to-white flex flex-col sm:flex-row sm:items-center justify-between gap-3">
                            <div>
                                <h2 class="text-lg font-semibold text-gray-900 flex items-center gap-2">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-indigo-600"
                                        fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M15 7a2 2 0 012 2m4 0a6 6 0 01-7.743 5.743L11 17H9v2H7v2H4a1 1 0 01-1-1v-2.586a1 1 0 01.293-.707l5.964-5.964A6 6 0 1121 9z" />
                                    </svg>
                                    Security Settings
                                </h2>
                                <p class="text-sm text-gray-500 mt-1">Manage your account password and security</p>
                            </div>

                            <button type="button" data-modal-toggle="passwordModal"
                                class="inline-flex items-center justify-center gap-2 text-sm font-medium text-indigo-600 hover:text-indigo-700 transition-colors focus:outline-none">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none"
                                    viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M15 7a2 2 0 012 2m4 0a6 6 0 01-7.743 5.743L11 17H9v2H7v2H4a1 1 0 01-1-1v-2.586a1 1 0 01.293-.707l5.964-5.964A6 6 0 1121 9z" />
                                </svg>
                                Change Password
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

<!-- Edit Profile Modal - modern admin-style modal (matches admin layout) -->
<div id="editProfileModal" class="fixed inset-0 z-50 hidden overflow-y-auto" aria-hidden="true">
    <div class="absolute inset-0 bg-black/60 backdrop-blur-md transition-opacity" data-modal-backdrop></div>
    <div class="relative mx-auto my-0 sm:my-8 w-full h-full sm:h-auto sm:w-[95%] max-w-2xl flex items-center">
        <div
            class="bg-white shadow-2xl w-full h-full sm:h-auto sm:max-h-[95vh] sm:max-w-2xl overflow-hidden sm:rounded-2xl flex flex-col">
            <!-- Header -->
            <div class="px-4 sm:px-6 py-4 border-b border-gray-100 flex items-center justify-between shrink-0">
                <h3 class="text-xl font-semibold text-gray-900">Edit Profile</h3>
                <button type="button" class="text-gray-400 hover:text-gray-600 p-1 rounded-lg" aria-label="Close"
                    data-modal-hide="editProfileModal">
                    <svg class="h-5 w-5" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="none"
                        viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>
            <form method="POST" action="{{ route('staff.profile.update') }}" enctype="multipart/form-data">
                @csrf
                <div class="p-6 space-y-6">
                    @if ($errors->any())
                        <div class="mb-4">
                            <div class="text-sm text-red-700 font-medium">Please fix the following errors:</div>
                            <ul class="mt-2 text-sm text-red-600 list-disc list-inside">
                                @foreach ($errors->all() as $err)
                                    <li>{{ $err }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2" for="name_modal">Full
                                Name</label>
                            <input type="text" id="name_modal" name="name"
                                value="{{ old('name', $user->name) }}" required
                                class="w-full rounded-lg border-gray-300 shadow-sm focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition-all">
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Email Address</label>
                            <input type="email" value="{{ $user->email }}" readonly
                                class="w-full rounded-lg border-gray-200 bg-gray-50 text-gray-600 shadow-sm cursor-not-allowed">
                            <p class="mt-1 text-xs text-gray-500">Contact administrator to update email</p>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Role</label>
                            <input type="text" value="{{ $user->role }}" readonly
                                class="w-full rounded-lg border-gray-200 bg-gray-50 text-gray-600 shadow-sm cursor-not-allowed">
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2"
                                for="category_id_modal">Category</label>
                            <select id="category_id_modal" name="category_id"
                                class="w-full rounded-lg border-gray-300 shadow-sm focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition-all">
                                <option value="">Unassigned</option>
                                @foreach ($categories_for_role ?? collect() as $c)
                                    <option value="{{ $c->id }}"
                                        {{ (string) old('category_id', $user->category_id) === (string) $c->id ? 'selected' : '' }}>
                                        {{ $c->name }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Date Joined</label>
                            <input type="text" value="{{ optional($user->created_at)->format('M d, Y h:i A') }}"
                                readonly
                                class="w-full rounded-lg border-gray-200 bg-gray-50 text-gray-600 shadow-sm cursor-not-allowed">
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2" for="photo">Update Profile
                                Photo</label>
                            <input type="file" id="photo" name="photo" accept=".jpg,.jpeg,.png"
                                class="block w-full text-sm text-gray-700 file:mr-4 file:py-2 file:px-4 file:rounded-lg file:border-0 file:text-sm file:font-medium file:bg-indigo-50 file:text-indigo-700 hover:file:bg-indigo-100 file:cursor-pointer border border-gray-300 rounded-lg cursor-pointer focus:outline-none focus:ring-2 focus:ring-indigo-500">
                        </div>
                    </div>
                </div>
                <!-- Footer -->
                <div
                    class="px-6 py-4 border-t border-gray-100 bg-gray-50 shrink-0 flex items-center justify-end gap-3">
                    <button type="button" data-modal-hide="editProfileModal"
                        class="flex inline-flex items-center justify-center rounded-lg bg-white px-4 py-2 text-sm font-medium text-gray-700 ring-1 ring-gray-300 hover:bg-gray-50">Cancel</button>
                    <button type="submit"
                        class="inline-flex items-center justify-center gap-2 rounded-lg bg-indigo-600 px-5 py-2 text-sm font-medium text-white hover:bg-indigo-700">Save
                        Changes</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Password Change Modal (admin-style) -->
<div id="passwordModal" class="fixed inset-0 z-50 hidden overflow-y-auto" aria-hidden="true">
    <div class="absolute inset-0 bg-black/60 backdrop-blur-md transition-opacity" data-modal-backdrop></div>
    <div class="relative mx-auto my-0 sm:my-8 w-full h-full sm:h-auto sm:w-[95%] max-w-lg flex items-center">
        <div class="bg-white shadow-2xl w-full h-full sm:h-auto sm:max-h-[95vh] sm:max-w-lg overflow-hidden sm:rounded-2xl flex flex-col">
            <div class="px-4 sm:px-6 py-4 border-b border-gray-100 flex items-center justify-between shrink-0">
                <h3 class="text-lg font-semibold text-gray-900">Change Password</h3>
                <button type="button" class="text-gray-400 hover:text-gray-600 p-1 rounded-lg" aria-label="Close" data-modal-hide="passwordModal">
                    <svg class="h-5 w-5" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/></svg>
                </button>
            </div>
            <form method="POST" action="{{ route('staff.profile.password.update') }}">
                @csrf
                <div class="p-6 space-y-4">
                    <!-- Current password removed by request: only new password + confirmation required -->

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2" for="password">New Password</label>
                        <div class="relative">
                            <input id="password" name="password" type="password" required class="w-full rounded-lg border-gray-300 shadow-sm focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 pr-12">
                            <button type="button" id="toggleNewPassword" style="right:0; left:auto;" class="absolute inset-y-0 flex items-center text-gray-500 px-2" aria-label="Show password">
                                <svg id="toggleNewPasswordIcon" xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path d="M2.458 12C3.732 7.943 7.523 5 12 5c4.477 0 8.268 2.943 9.542 7-1.274 4.057-5.065 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                            </button>
                        </div>
                        @if($errors->has('password'))
                            <p class="mt-2 text-xs text-red-600">{{ $errors->first('password') }}</p>
                        @endif
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2" for="password_confirmation">Confirm Password</label>
                        <div class="relative">
                            <input id="password_confirmation" name="password_confirmation" type="password" required class="w-full rounded-lg border-gray-300 shadow-sm focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 pr-12">
                            <button type="button" id="toggleConfirmPassword" style="right:0; left:auto;" class="absolute inset-y-0 flex items-center text-gray-500 px-2" aria-label="Show confirm password">
                                <svg id="toggleConfirmPasswordIcon" xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path d="M2.458 12C3.732 7.943 7.523 5 12 5c4.477 0 8.268 2.943 9.542 7-1.274 4.057-5.065 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                            </button>
                        </div>
                    </div>
                </div>
                <div class="px-6 py-4 border-t border-gray-100 bg-gray-50 flex items-center justify-end gap-3">
                    <button type="button" data-modal-hide="passwordModal" class="inline-flex items-center justify-center rounded-lg bg-white px-4 py-2 text-sm font-medium text-gray-700 ring-1 ring-gray-300 hover:bg-gray-50">Cancel</button>
                    <button type="submit" class="inline-flex items-center justify-center gap-2 rounded-lg bg-indigo-600 px-5 py-2 text-sm font-medium text-white hover:bg-indigo-700">Change Password</button>
                </div>
            </form>
        </div>
    </div>
</div>

@section('staff-scripts')
    <script>
        function askForPermission() {
            Notification.requestPermission().then((permission) => {
                if (permission === 'granted') {
                    // get service worker
                    navigator.serviceWorker.ready.then((sw) => {
                        // subscribe
                        sw.pushManager.subscribe({
                            userVisibleOnly: true,
                            applicationServerKey: '{{ env('PUBLIC_KEY') }}'
                        }).then((subscription) => {
                            try {
                                console.log(JSON.stringify(subscription));
                            } catch (_) {}
                            saveSub(JSON.stringify(subscription));
                        }).catch(function(err) {
                            console.error('Push subscription failed', err);
                            alert('Push subscription failed: ' + (err && err.message ? err.message :
                                'unknown'));
                        });
                    }).catch(function(err) {
                        console.error('Service worker ready failed', err);
                        alert('Service worker not ready: ' + (err && err.message ? err.message :
                            'unknown'));
                    });
                } else {
                    // Permission was denied or dismissed - no action required
                    console.info('Notification permission result:', permission);
                }
            }).catch(err => {
                console.error('Permission request failed', err);
                alert('Permission request failed: ' + (err && err.message ? err.message : 'unknown'));
            });
        }

        // Save subscription to DB
        function saveSub(sub) {
            // sub may be a JSON-stringified subscription or an object
            let payload;
            try {
                payload = (typeof sub === 'string') ? JSON.parse(sub) : sub;
            } catch (e) {
                console.error('Invalid subscription payload', e);
                return;
            }

            const body = {
                subscription: payload
            };

            if (window.axios && typeof window.axios.post === 'function') {
                window.axios.post("{{ route('push.subscribe') }}", body)
                    .then(function(response) {
                        console.log('Subscription saved', response.data);
                        // Optionally show a small success hint
                        try {
                            alert('Push subscription saved');
                        } catch (_) {}
                    })
                    .catch(function(error) {
                        console.error('Failed to save subscription via axios:', error);
                        alert('Failed to save subscription');
                    });
            } else {
                // Fallback to fetch (include CSRF token)
                fetch("{{ route('push.subscribe') }}", {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                        },
                        body: JSON.stringify(body)
                    })
                    .then(r => r.json())
                    .then(data => {
                        console.log('Subscription saved (fetch)', data);
                        try {
                            alert('Push subscription saved');
                        } catch (_) {}
                    })
                    .catch(err => {
                        console.error('Failed to save subscription via fetch:', err);
                        alert('Failed to save subscription');
                    });
            }
        }

        function sendNotification() {
            if (window.axios && typeof window.axios.post === 'function') {
                window.axios.post("{{ route('push.send') }}", {
                    title: document.getElementById('title').value,
                    body: document.getElementById('body').value,
                    idOfProduct: document.getElementById('idOfProduct').value
                }).then(function(response) {
                    alert('Send successful');
                    console.log(response.data);
                }).catch(function(error) {
                    console.error('Send failed:', error);
                    alert('Send failed');
                });
            } else {
                // Fallback to fetch if axios isn't available
                fetch("{{ route('push.send') }}", {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                    },
                    body: JSON.stringify({
                        title: document.getElementById('title').value,
                        body: document.getElementById('body').value,
                        idOfProduct: document.getElementById('idOfProduct').value
                    })
                }).then(r => r.json()).then(data => {
                    alert('Send successful');
                    console.log(data);
                }).catch(err => {
                    console.error(err);
                    alert('Send failed');
                });
            }
        }
    </script>
    <script>
        // Email notification toggle (UI only - will be wired to backend later)
        document.addEventListener('DOMContentLoaded', function() {
            const btn = document.getElementById('emailNotifToggle');
            if (!btn) return;
            const label = document.getElementById('emailNotifLabel');
            const dot = document.getElementById('emailNotifDot');
            // Initialize state from server-side user preference if available
            let enabled = {{ json_encode($user->email_notifications ?? false) }};

            function applyState(on) {
                btn.setAttribute('aria-checked', on ? 'true' : 'false');
                if (on) {
                    btn.classList.remove('bg-gray-200', 'text-gray-700');
                    btn.classList.add('bg-emerald-600', 'text-white');
                    label.textContent = 'On';
                    dot.classList.remove('bg-white');
                    dot.classList.add('bg-emerald-200');
                } else {
                    btn.classList.remove('bg-emerald-600', 'text-white');
                    btn.classList.add('bg-gray-200', 'text-gray-700');
                    label.textContent = 'Off';
                    dot.classList.remove('bg-emerald-200');
                    dot.classList.add('bg-white');
                }
            }

            applyState(enabled);

            btn.addEventListener('click', function () {
                const newState = !enabled;
                // Optimistically apply state in UI
                applyState(newState);

                // Persist to server
                fetch("{{ route('staff.profile.email_notifications') }}", {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                    },
                    body: JSON.stringify({ enabled: newState })
                }).then(r => r.json()).then(json => {
                    if (json && json.saved) {
                        enabled = !!json.enabled;
                        applyState(enabled);
                    } else {
                        // Revert on failure
                        applyState(enabled);
                        alert('Failed to save email notification preference');
                    }
                }).catch(err => {
                    console.error('Failed to save email notification preference', err);
                    applyState(enabled);
                    alert('Failed to save email notification preference');
                });
            });
        });
    </script>
    <script>
        // Ticket details modal for Recent Activity (simple copy of admin details)
        (function () {
            const staffTicketModal = document.createElement('div');
            staffTicketModal.id = 'staffTicketModal';
            staffTicketModal.className = 'fixed inset-0 z-50 hidden overflow-y-auto';
            staffTicketModal.innerHTML = `
                <div class="absolute inset-0 bg-black/60 backdrop-blur-md" data-modal-backdrop></div>
                <div class="relative mx-auto my-0 sm:my-8 w-full h-full sm:h-auto sm:w-[95%] max-w-2xl flex items-center">
                    <div class="bg-white shadow-2xl w-full h-full sm:h-auto sm:max-h-[95vh] sm:max-w-2xl overflow-hidden sm:rounded-2xl flex flex-col">
                        <div class="px-4 sm:px-6 py-4 border-b border-gray-100 flex items-center justify-between">
                            <h3 id="stTicketNo" class="text-lg font-semibold text-gray-900">Ticket #</h3>
                            <button type="button" id="stCloseTicketModal" class="text-gray-400 hover:text-gray-600 p-1 rounded-lg" aria-label="Close">&times;</button>
                        </div>
                        <div class="p-6 overflow-y-auto">
                            <div class="mb-4"><strong>Status:</strong> <span id="stStatus" class="text-sm text-gray-700"></span></div>
                            <div class="mb-4"><strong>Question:</strong><div id="stQuestion" class="mt-2 text-sm text-gray-900 whitespace-pre-wrap"></div></div>
                            <div class="mt-4"><strong>Category:</strong> <span id="stCategory" class="text-sm text-gray-700"></span></div>
                            <div class="mt-2"><strong>Updated:</strong> <span id="stDates" class="text-sm text-gray-700"></span></div>
                            <div class="mt-2"><strong>Email:</strong> <span id="stEmail" class="text-sm text-gray-700"></span></div>
                        </div>
                    </div>
                </div>
            `;
            document.body.appendChild(staffTicketModal);

            async function loadAndShowTicket(id) {
                if (!id) return;
                const url = `{{ url('/staff/tickets') }}/${encodeURIComponent(id)}`;
                try {
                    const res = await fetch(url, { headers: { 'X-Requested-With': 'XMLHttpRequest' }, credentials: 'same-origin' });
                    if (!res.ok) return;
                    const t = await res.json();
                    document.getElementById('stTicketNo').textContent = 'Ticket #' + (t.id || id);
                    document.getElementById('stQuestion').textContent = t.question || '';
                    document.getElementById('stStatus').textContent = t.status || '';
                    document.getElementById('stCategory').textContent = t.category || '';
                    document.getElementById('stDates').textContent = (t.updated_at || t.date_created) || '';
                    document.getElementById('stEmail').textContent = t.email || '';

                    staffTicketModal.classList.remove('hidden');
                    document.body.classList.add('overflow-hidden');
                } catch (err) {
                    console.error('Failed to load ticket', err);
                }
            }

            document.addEventListener('click', function (e) {
                const btn = e.target && e.target.closest ? e.target.closest('.btn-view') : null;
                if (!btn) return;
                const id = btn.getAttribute('data-id') || btn.dataset.id;
                if (!id) return;
                loadAndShowTicket(id);
            });

            document.addEventListener('click', function (e) {
                if (e.target && e.target.id === 'stCloseTicketModal') {
                    staffTicketModal.classList.add('hidden');
                    document.body.classList.remove('overflow-hidden');
                }
                if (e.target && e.target.closest && e.target.closest('[data-modal-backdrop]')) {
                    if (!staffTicketModal.classList.contains('hidden')) {
                        staffTicketModal.classList.add('hidden');
                        document.body.classList.remove('overflow-hidden');
                    }
                }
            });
        })();
    </script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            @if ($errors->any())
                try {
                    @if(old('password') || $errors->has('password'))
                        var modalEl = document.getElementById('passwordModal');
                    @else
                        var modalEl = document.getElementById('editProfileModal');
                    @endif
                    if (modalEl) {
                        modalEl.classList.remove('hidden');
                        modalEl.setAttribute('aria-hidden', 'false');
                        document.body.classList.add('overflow-hidden');
                    }
                } catch (e) {
                    console.error('Failed to show modal on validation errors', e);
                }
            @endif
        });
    </script>
    <script>
        (function() {
            // Photo preview (kept separate and intact)
            const input = document.getElementById('photo');
            const preview = document.getElementById('photoPreview');

            if (input && preview) {
                input.addEventListener('change', function() {
                    const file = this.files && this.files[0];
                    if (!file) return;

                    // Validate file type
                    if (!/^image\/(png|jpeg|jpg)$/.test(file.type)) {
                        alert('Invalid file type. Please select a JPG or PNG image.');
                        this.value = '';
                        return;
                    }

                    // Validate file size (max 5MB)
                    if (file.size > 5 * 1024 * 1024) {
                        alert('File is too large. Maximum size is 5MB.');
                        this.value = '';
                        return;
                    }

                    // Preview image
                    const reader = new FileReader();
                    reader.onload = e => {
                        preview.src = e.target.result;
                    };
                    reader.readAsDataURL(file);
                });
            }

            // Push notifications registration
            try {
                navigator.serviceWorker.register("{{ url('sw.js') }}", {
                    scope: './'
                });
            } catch (e) {
                console.warn('Service worker registration (profile) failed', e);
            }
        })();
    </script>

    @if (session('status'))
        <script>
            document.addEventListener('DOMContentLoaded', function() {
                try {
                    const msg = @json(session('status'));
                    if (typeof Swal === 'function') {
                        Swal.fire({
                            icon: 'success',
                            title: 'Profile updated',
                            text: msg || 'Your profile was updated.',
                            showConfirmButton: false,
                            timer: 1600
                        });
                    } else {
                        // Fallback to global toast if SweetAlert isn't available
                        (window.showToast || function(t, m) {
                            alert(m);
                        })('success', msg || 'Your profile was updated.');
                    }
                } catch (e) {
                    console.error('Profile update notification failed', e);
                }
            });
        </script>
    @endif
    <script>
        // Simple modal toggle for Edit Profile modal (robust, admin-style)
        document.addEventListener('DOMContentLoaded', function() {
            try {
                const modal = document.getElementById('editProfileModal');
                if (!modal) return;

                const toggleBtns = document.querySelectorAll('[data-modal-toggle="editProfileModal"]');
                const closeBtns = modal.querySelectorAll('[data-modal-hide="editProfileModal"]');
                const backdrop = modal.querySelector('[data-modal-backdrop]');

                function showModal() {
                    modal.classList.remove('hidden');
                    modal.setAttribute('aria-hidden', 'false');
                    document.body.classList.add('overflow-hidden');
                }

                function hideModal() {
                    modal.classList.add('hidden');
                    modal.setAttribute('aria-hidden', 'true');
                    document.body.classList.remove('overflow-hidden');
                }

                toggleBtns.forEach(btn => btn.addEventListener('click', (e) => {
                    e.preventDefault();
                    showModal();
                }));

                closeBtns.forEach(btn => btn.addEventListener('click', (e) => {
                    e.preventDefault();
                    hideModal();
                }));

                // Close when backdrop is clicked (admin style)
                if (backdrop) {
                    backdrop.addEventListener('click', function() {
                        hideModal();
                    });
                } else {
                    // Fallback: clicking outside the panel (modal element) should close
                    modal.addEventListener('click', function(e) {
                        if (e.target === modal) hideModal();
                    });
                }
            } catch (err) {
                console.error('Edit profile modal init failed', err);
            }
        });
    </script>
    <script>
        // Simple modal toggle for Password modal
        document.addEventListener('DOMContentLoaded', function() {
            try {
                const modal = document.getElementById('passwordModal');
                if (!modal) return;

                const toggleBtns = document.querySelectorAll('[data-modal-toggle="passwordModal"]');
                const closeBtns = modal.querySelectorAll('[data-modal-hide="passwordModal"]');
                const backdrop = modal.querySelector('[data-modal-backdrop]');

                function showModal() {
                    modal.classList.remove('hidden');
                    modal.setAttribute('aria-hidden', 'false');
                    document.body.classList.add('overflow-hidden');
                }
                function hideModal() {
                    modal.classList.add('hidden');
                    modal.setAttribute('aria-hidden', 'true');
                    document.body.classList.remove('overflow-hidden');
                }

                toggleBtns.forEach(btn => btn.addEventListener('click', (e) => { e.preventDefault(); showModal(); }));
                closeBtns.forEach(btn => btn.addEventListener('click', (e) => { e.preventDefault(); hideModal(); }));

                if (backdrop) {
                    backdrop.addEventListener('click', hideModal);
                } else {
                    modal.addEventListener('click', function(e) { if (e.target === modal) hideModal(); });
                }

                // Show/hide password toggles
                try {
                    const toggleNew = document.getElementById('toggleNewPassword');
                    const toggleConfirm = document.getElementById('toggleConfirmPassword');
                    const newInput = document.getElementById('password');
                    const confirmInput = document.getElementById('password_confirmation');
                    const eyeSvg = '<path d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path d="M2.458 12C3.732 7.943 7.523 5 12 5c4.477 0 8.268 2.943 9.542 7-1.274 4.057-5.065 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>';
                    const eyeOffSvg = '<path d="M3 3l18 18"/><path d="M10.58 10.58a3 3 0 104.83 4.83"/>';

                    if (toggleNew && newInput) {
                        toggleNew.addEventListener('click', function () {
                            if (newInput.type === 'password') {
                                newInput.type = 'text';
                                try { document.getElementById('toggleNewPasswordIcon').innerHTML = eyeOffSvg; } catch(_){}
                            } else {
                                newInput.type = 'password';
                                try { document.getElementById('toggleNewPasswordIcon').innerHTML = eyeSvg; } catch(_){}
                            }
                        });
                    }
                    if (toggleConfirm && confirmInput) {
                        toggleConfirm.addEventListener('click', function () {
                            if (confirmInput.type === 'password') {
                                confirmInput.type = 'text';
                                try { document.getElementById('toggleConfirmPasswordIcon').innerHTML = eyeOffSvg; } catch(_){}
                            } else {
                                confirmInput.type = 'password';
                                try { document.getElementById('toggleConfirmPasswordIcon').innerHTML = eyeSvg; } catch(_){}
                            }
                        });
                    }
                } catch (e) { /* non-fatal */ }
            } catch (err) {
                console.error('Password modal init failed', err);
            }
        });
    </script>
@endsection
