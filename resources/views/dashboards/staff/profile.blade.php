@extends('layouts.app')

@section('title', 'My Profile')

@section('content')
    <div class="min-h-screen bg-gradient-to-br from-gray-50 to-gray-100 py-8 px-4 sm:px-6 lg:px-8">
        <div class="mx-auto max-w-5xl">
            <!-- Header with Back Button -->
            <div class="mb-6">
                <a href="{{ route('staff.dashboard') }}"
                    class="inline-flex items-center gap-2 rounded-lg px-4 py-2 text-sm font-medium text-gray-700 hover:bg-white hover:shadow-sm transition-all duration-200">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" viewBox="0 0 24 24" fill="currentColor">
                        <path d="M10.828 11H20a1 1 0 1 1 0 2h-9.172l3.536 3.536a1 1 0 1 1-1.414 1.414l-5.243-5.243a1 1 0 0 1 0-1.414l5.243-5.243a1 1 0 1 1 1.414 1.414L10.828 11Z" />
                    </svg>
                    <span>Back to Dashboard</span>
                </a>
            </div>

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
                                <img id="photoPreview" 
                                    class="h-24 w-24 rounded-full object-cover ring-4 ring-white shadow-lg"
                                    src="{{ $photo ?: 'https://ui-avatars.com/api/?background=E5E7EB&color=111827&name=' . urlencode($user->name) }}"
                                    alt="Profile Photo">
                                <h2 class="mt-4 text-xl font-semibold text-gray-900">{{ $user->name }}</h2>
                                <p class="text-sm text-gray-500">{{ $user->role }}</p>
                                <div class="mt-3 inline-flex items-center gap-2 rounded-full bg-indigo-50 px-3 py-1 text-xs font-medium text-indigo-700">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 13.255A23.931 23.931 0 0112 15c-3.183 0-6.22-.62-9-1.745M16 6V4a2 2 0 00-2-2h-4a2 2 0 00-2 2v2m4 6h.01M5 20h14a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" />
                                    </svg>
                                    {{ $user->category ?: 'General' }}
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Quick Stats Card -->
                    <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6">
                        <h3 class="text-sm font-semibold text-gray-900 mb-4 flex items-center gap-2">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 text-indigo-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" />
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
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 text-blue-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" />
                                    </svg>
                                </div>
                            </div>
                            <div class="flex items-center justify-between p-3 rounded-lg bg-emerald-50 border border-emerald-100">
                                <div>
                                    <p class="text-xs font-medium text-emerald-900">Resolved</p>
                                    <p class="text-2xl font-bold text-emerald-700 mt-1">{{ (int) $resolvedCount }}</p>
                                </div>
                                <div class="h-12 w-12 rounded-full bg-emerald-100 flex items-center justify-center">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 text-emerald-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                                    </svg>
                                </div>
                            </div>
                            <div class="flex items-center justify-between p-3 rounded-lg bg-purple-50 border border-purple-100">
                                <div>
                                    <p class="text-xs font-medium text-purple-900">Success Rate</p>
                                    @php
                                        $rate = $assignedCount > 0 ? round(($resolvedCount / max(1, $assignedCount)) * 100) : 0;
                                    @endphp
                                    <p class="text-2xl font-bold text-purple-700 mt-1">{{ $rate }}%</p>
                                </div>
                                <div class="h-12 w-12 rounded-full bg-purple-100 flex items-center justify-center">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 text-purple-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6" />
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
                        <div class="px-6 py-4 border-b border-gray-100 bg-gradient-to-r from-gray-50 to-white">
                            <h2 class="text-lg font-semibold text-gray-900 flex items-center gap-2">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-indigo-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                                </svg>
                                Profile Information
                            </h2>
                        </div>
                        
                        <form method="POST" action="{{ route('staff.profile.update') }}" enctype="multipart/form-data" class="p-6">
                            @csrf
                            
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                                <!-- Full Name -->
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-2" for="name">
                                        Full Name
                                    </label>
                                    <input type="text" id="name" name="name" value="{{ old('name', $user->name) }}"
                                        class="w-full rounded-lg border-gray-300 shadow-sm focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition-all"
                                        required>
                                </div>

                                <!-- Email (Read-only) -->
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-2" for="email">
                                        Email Address
                                    </label>
                                    <input type="email" id="email" value="{{ $user->email }}" readonly
                                        class="w-full rounded-lg border-gray-200 bg-gray-50 text-gray-600 shadow-sm cursor-not-allowed">
                                    <p class="mt-1 text-xs text-gray-500">Contact administrator to update email</p>
                                </div>

                                <!-- Role (Read-only) -->
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-2">
                                        Role
                                    </label>
                                    <input type="text" value="{{ $user->role }}" readonly
                                        class="w-full rounded-lg border-gray-200 bg-gray-50 text-gray-600 shadow-sm cursor-not-allowed">
                                </div>

                                <!-- Category -->
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-2" for="category">
                                        Category
                                    </label>
                                    <input type="text" id="category" name="category"
                                        value="{{ old('category', $user->category) }}"
                                        class="w-full rounded-lg border-gray-300 shadow-sm focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition-all"
                                        placeholder="e.g., IT Support">
                                </div>

                                <!-- Date Joined (Read-only) -->
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-2">
                                        Date Joined
                                    </label>
                                    <input type="text" value="{{ optional($user->created_at)->format('M d, Y h:i A') }}" readonly
                                        class="w-full rounded-lg border-gray-200 bg-gray-50 text-gray-600 shadow-sm cursor-not-allowed">
                                </div>

                                <!-- Upload Photo -->
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-2" for="photo">
                                        Update Profile Photo
                                    </label>
                                    <input type="file" id="photo" name="photo" accept=".jpg,.jpeg,.png"
                                        class="block w-full text-sm text-gray-700 file:mr-4 file:py-2 file:px-4 file:rounded-lg file:border-0 file:text-sm file:font-medium file:bg-indigo-50 file:text-indigo-700 hover:file:bg-indigo-100 file:cursor-pointer border border-gray-300 rounded-lg cursor-pointer focus:outline-none focus:ring-2 focus:ring-indigo-500">
                                </div>
                            </div>

                            <!-- Action Buttons -->
                            <div class="flex flex-col sm:flex-row items-stretch sm:items-center justify-between gap-3 mt-8 pt-6 border-t border-gray-100">
                                <a href="{{ route('staff.profile.password') }}"
                                    class="inline-flex items-center justify-center gap-2 text-sm font-medium text-indigo-600 hover:text-indigo-700 transition-colors">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 7a2 2 0 012 2m4 0a6 6 0 01-7.743 5.743L11 17H9v2H7v2H4a1 1 0 01-1-1v-2.586a1 1 0 01.293-.707l5.964-5.964A6 6 0 1121 9z" />
                                    </svg>
                                    Change Password
                                </a>
                                <button type="submit"
                                    class="inline-flex items-center justify-center gap-2 rounded-lg bg-indigo-600 px-6 py-2.5 text-sm font-medium text-white hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 transition-all shadow-sm">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                                    </svg>
                                    Save Changes
                                </button>
                            </div>
                        </form>
                    </div>

                    <!-- Recent Activity -->
                    <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
                        <div class="px-6 py-4 border-b border-gray-100 bg-gradient-to-r from-gray-50 to-white">
                            <h2 class="text-lg font-semibold text-gray-900 flex items-center gap-2">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-indigo-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                                </svg>
                                Recent Activity
                            </h2>
                            <p class="text-sm text-gray-500 mt-1">Last 5 resolved tickets</p>
                        </div>
                        
                        <div class="overflow-x-auto">
                            <table class="min-w-full divide-y divide-gray-200">
                                <thead class="bg-gray-50">
                                    <tr>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Ticket ID</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Category</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Updated</th>
                                    </tr>
                                </thead>
                                <tbody class="bg-white divide-y divide-gray-200">
                                    @forelse($recentTickets as $t)
                                        <tr class="hover:bg-gray-50 transition-colors">
                                            <td class="px-6 py-4 whitespace-nowrap">
                                                <span class="text-sm font-medium text-indigo-600">#{{ $t->id }}</span>
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap">
                                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-gray-100 text-gray-800">
                                                    {{ $t->category ?? '-' }}
                                                </span>
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap">
                                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-emerald-100 text-emerald-800">
                                                    {{ $t->status }}
                                                </span>
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                                {{ \Illuminate\Support\Carbon::parse($t->date_closed ?? $t->updated_at ?? $t->date_created)->format('M d, Y h:i A') }}
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="4" class="px-6 py-12 text-center">
                                                <svg xmlns="http://www.w3.org/2000/svg" class="h-12 w-12 mx-auto text-gray-300 mb-3" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
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
                    <div id="pushNotificationCard" class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
                        <div class="px-6 py-4 border-b border-gray-100 bg-gradient-to-r from-emerald-50 to-white flex flex-col sm:flex-row sm:items-center justify-between gap-3">
                            <div>
                                <h2 class="text-lg font-semibold text-gray-900 flex items-center gap-2">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-emerald-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9" />
                                    </svg>
                                    Push Notifications
                                </h2>
                                <p class="text-sm text-gray-500 mt-1">Stay updated with real-time alerts</p>
                            </div>
                            <button onclick="askForPermission()"
                                class="inline-flex items-center justify-center gap-2 bg-emerald-600 hover:bg-emerald-700 text-white text-sm font-medium px-5 py-2.5 rounded-lg focus:outline-none focus:ring-2 focus:ring-emerald-500 focus:ring-offset-2 transition-all shadow-sm">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9" />
                                </svg>
                                Enable Notifications
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('scripts')
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

    @if(session('status'))
        <script>
            document.addEventListener('DOMContentLoaded', function () {
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
                        (window.showToast || function(t,m){ alert(m); })('success', msg || 'Your profile was updated.');
                    }
                } catch (e) {
                    console.error('Profile update notification failed', e);
                }
            });
        </script>
    @endif
@endsection
