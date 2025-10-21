@extends('layouts.app')

@section('title', 'My Profile')

@section('content')
    <div class="mx-auto max-w-4xl px-4 py-8">
        <!-- Back to Dashboard -->
        <div class="mb-4">
            <a href="{{ route('staff.dashboard') }}"
                class="inline-flex items-center gap-2 rounded-md border border-gray-300 bg-white px-3 py-1.5 text-sm font-medium text-gray-700 hover:bg-gray-50">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 text-gray-600" viewBox="0 0 24 24" fill="currentColor">
                    <path
                        d="M10.828 11H20a1 1 0 1 1 0 2h-9.172l3.536 3.536a1 1 0 1 1-1.414 1.414l-5.243-5.243a1 1 0 0 1 0-1.414l5.243-5.243a1 1 0 1 1 1.414 1.414L10.828 11Z" />
                </svg>
                <span>Back to Dashboard</span>
            </a>
        </div>
        
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
            <!-- Left: Photo -->
            <div class="md:col-span-1">
                <div class="bg-white rounded-xl shadow-sm ring-1 ring-slate-900/5 p-5">
                    <h2 class="text-sm font-semibold text-gray-800 mb-4">Profile Photo</h2>
                    <div class="flex flex-col items-center gap-3">
                        @php
                            $ver = optional($user->updated_at)->timestamp;
                            $photo = $user->profile_photo
                                ? asset('storage/' . $user->profile_photo) . '?v=' . $ver
                                : null;
                        @endphp
                        <img id="photoPreview" class="h-28 w-28 rounded-full object-cover ring-1 ring-slate-900/10"
                            src="{{ $photo ?: 'https://ui-avatars.com/api/?background=E5E7EB&color=111827&name=' . urlencode($user->name) }}"
                            alt="Profile Photo">
                        <p class="text-xs text-gray-500 text-center">
                            JPG, JPEG, or PNG up to 5MB.
                        </p>
                    </div>
                </div>
            </div>

            <!-- Right: Details -->
            <div class="md:col-span-2">
                <form method="POST" action="{{ route('staff.profile.update') }}" enctype="multipart/form-data"
                    class="bg-white rounded-xl shadow-sm ring-1 ring-slate-900/5 p-5 space-y-5">
                    @csrf
                    <h2 class="text-sm font-semibold text-gray-800">Profile Information</h2>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-xs text-gray-600 mb-1" for="name">Full Name</label>
                            <input type="text" id="name" name="name" value="{{ old('name', $user->name) }}"
                                class="w-full rounded-md border-gray-300 text-sm focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500"
                                required>
                        </div>
                        <div>
                            <label class="block text-xs text-gray-600 mb-1" for="email">Email (read-only)</label>
                            <input type="email" id="email" value="{{ $user->email }}" readonly
                                class="w-full rounded-md border-gray-200 bg-gray-50 text-sm text-gray-600">
                        </div>
                        <div>
                            <label class="block text-xs text-gray-600 mb-1">Role</label>
                            <input type="text" value="{{ $user->role }}" readonly
                                class="w-full rounded-md border-gray-200 bg-gray-50 text-sm text-gray-600">
                        </div>
                        <div>
                            <label class="block text-xs text-gray-600 mb-1" for="category">Category</label>
                            <input type="text" id="category" name="category"
                                value="{{ old('category', $user->category) }}"
                                class="w-full rounded-md border-gray-300 text-sm focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500"
                                placeholder="e.g., IT Support">
                        </div>
                        <div>
                            <label class="block text-xs text-gray-600 mb-1">Date Joined</label>
                            <input type="text" value="{{ optional($user->created_at)->format('Y-m-d h:i a') }}" readonly
                                class="w-full rounded-md border-gray-200 bg-gray-50 text-sm text-gray-600">
                        </div>
                        <div>
                            <label class="block text-xs text-gray-600 mb-1" for="photo">Upload / Update Photo</label>
                            <input type="file" id="photo" name="photo" accept=".jpg,.jpeg,.png"
                                class="block w-full text-xs text-gray-700 file:mr-3 file:py-1.5 file:px-3 file:rounded-md file:border-0 file:text-sm file:font-medium file:bg-indigo-50 file:text-indigo-700 hover:file:bg-indigo-100">
                        </div>
                    </div>

                    <div class="flex items-center justify-between pt-2">
                        <a href="{{ route('staff.profile.password') }}"
                            class="text-sm text-indigo-600 hover:text-indigo-700">Change Password</a>
                        <button type="submit"
                            class="inline-flex items-center rounded-md bg-indigo-600 px-4 py-2 text-sm font-medium text-white hover:bg-indigo-700">
                            Save Changes
                        </button>
                    </div>
                </form>

                <!-- Activity Snapshot -->
                <div class="mt-6 bg-white rounded-xl shadow-sm ring-1 ring-slate-900/5 p-5">
                    <h2 class="text-sm font-semibold text-gray-800 mb-4">User Activity</h2>
                    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4 mb-4">
                        <div class="rounded-lg border border-gray-200 p-4">
                            <div class="text-xs text-gray-500">Assigned Tickets</div>
                            <div class="text-2xl font-semibold text-gray-900">{{ (int) $assignedCount }}</div>
                        </div>
                        <div class="rounded-lg border border-gray-200 p-4">
                            <div class="text-xs text-gray-500">Resolved Tickets</div>
                            <div class="text-2xl font-semibold text-gray-900">{{ (int) $resolvedCount }}</div>
                        </div>
                        <div class="rounded-lg border border-gray-200 p-4">
                            <div class="text-xs text-gray-500">Resolution Rate</div>
                            @php
                                $rate = $assignedCount > 0 ? round(($resolvedCount / max(1, $assignedCount)) * 100) : 0;
                            @endphp
                            <div class="text-2xl font-semibold text-gray-900">{{ $rate }}%</div>
                        </div>
                    </div>

                    <div>
                        <div class="text-sm font-semibold text-gray-700 mb-2">Last 5 Resolved Tickets</div>
                        <div class="overflow-x-auto">
                            <table class="min-w-full text-sm">
                                <thead class="bg-gray-50 text-gray-600">
                                    <tr>
                                        <th class="py-2 px-3 text-left font-medium">ID</th>
                                        <th class="py-2 px-3 text-left font-medium">Category</th>
                                        <th class="py-2 px-3 text-left font-medium">Status</th>
                                        <th class="py-2 px-3 text-left font-medium">Updated</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-gray-100">
                                    @forelse($recentTickets as $t)
                                        <tr class="hover:bg-gray-50">
                                            <td class="py-2 px-3">
                                                {{ $t->id }}
                                            </td>
                                            <td class="py-2 px-3">{{ $t->category ?? '-' }}</td>
                                            <td class="py-2 px-3">{{ $t->status }}</td>
                                            <td class="py-2 px-3">
                                                {{ \Illuminate\Support\Carbon::parse($t->date_closed ?? $t->updated_at ?? $t->date_created)->format('Y-m-d h:i a') }}
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="4" class="py-6 px-3 text-center text-sm text-gray-500">No recently resolved tickets.</td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>

                <div id="pushNotificationCard" class="mt-6 bg-white rounded-xl shadow-sm ring-1 ring-slate-900/5 p-5">
                    <div class="px-4 py-3 border-b border-gray-200 flex items-center justify-between">
                        <h2 class="text-sm font-semibold text-gray-700">Push Notifications</h2>
                        <button onclick="askForPermission()"
                            class="inline-flex items-center gap-2 bg-emerald-600 hover:bg-emerald-700 text-white text-sm font-medium px-4 py-2 rounded-md focus:outline-none focus:ring-2 focus:ring-emerald-500 focus:ring-offset-2">
                            Enable Notification
                        </button>
                    </div>
                    {{-- <div class="p-4">
                        <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                            <div>
                                <label for='title'
                                    class="block text-sm font-medium text-gray-700">{{ __('title') }}</label>
                                <input type='text'
                                    class='mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm'
                                    id='title' name='title'>
                            </div>
                            <div>
                                <label for='body'
                                    class="block text-sm font-medium text-gray-700">{{ __('body') }}</label>
                                <input type='text'
                                    class='mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm'
                                    id='body' name='body'>
                            </div>
                            <div>
                                <label for='idOfProduct'
                                    class="block text-sm font-medium text-gray-700">{{ __('ID Of Product') }}</label>
                                <input type='text'
                                    class='mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm'
                                    id='idOfProduct' name='idOfProduct'>
                            </div>
                            <div class="flex items-end">
                                <div>
                                    <input type="button" value="{{ 'Send Notification' }}" onclick="sendNotification()"
                                        class="inline-flex items-center bg-sky-600 hover:bg-sky-700 text-white text-sm font-medium px-4 py-2 rounded-md focus:outline-none focus:ring-2 focus:ring-sky-500 focus:ring-offset-2" />
                                    <p class="mt-2 text-xs text-gray-500">Please enable push notifications before sending.
                                    </p>
                                </div>
                            </div>
                        </div>
                    </div> --}}
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
