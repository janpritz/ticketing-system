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
        @if (session('status'))
            <div class="mb-4 rounded-md border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-800">
                {{ session('status') }}
            </div>
        @endif

        @if ($errors->any())
            <div class="mb-4 rounded-md border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-700">
                <div class="font-semibold mb-1">Please fix the following:</div>
                <ul class="list-disc list-inside">
                    @foreach ($errors->all() as $err)
                        <li>{{ $err }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

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
                        <div class="text-sm font-semibold text-gray-700 mb-2">Last 5 Tickets</div>
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
                                                T-{{ \Illuminate\Support\Carbon::parse($t->date_created ?? $t->created_at)->format('Y') }}-{{ str_pad($t->id, 4, '0', STR_PAD_LEFT) }}
                                            </td>
                                            <td class="py-2 px-3">{{ $t->category ?? '-' }}</td>
                                            <td class="py-2 px-3">{{ $t->status }}</td>
                                            <td class="py-2 px-3">
                                                {{ \Illuminate\Support\Carbon::parse($t->updated_at ?? $t->date_created)->format('Y-m-d h:i a') }}
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="4" class="py-6 px-3 text-center text-sm text-gray-500">No recent
                                                tickets.</td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>

                <!-- Push Notifications -->
                <div id="pushNotificationCard" class="mt-6 bg-white rounded-xl shadow-sm ring-1 ring-slate-900/5 p-5">
                    <h2 class="text-sm font-semibold text-gray-800 mb-2">Push Notifications</h2>
                    <p class="text-xs text-gray-500 mb-3">Enable browser & mobile push notifications to receive ticket
                        updates and important alerts.</p>
                    <div class="flex items-center gap-3 flex-wrap">
                        <button id="enablePushBtn" type="button"
                            class="inline-flex items-center rounded-md bg-indigo-600 px-4 py-2 text-sm font-medium text-white hover:bg-indigo-700">
                            Enable Push Notifications
                        </button>
                        <button id="testPushBtn" type="button"
                            class="inline-flex items-center rounded-md bg-gray-200 px-3 py-1.5 text-sm font-medium text-gray-700 hover:bg-gray-300">
                            Send Test Push
                        </button>
                        <div id="pushStatus" class="text-sm text-gray-600">Status: <span id="pushStatusText"
                                class="font-medium">Not enabled</span></div>
                    </div>
                    <p class="text-[11px] text-gray-400 mt-2">Supported on modern browsers (desktop & mobile). You will be
                        asked to give permission and, when applicable, register a service worker for background delivery.
                    </p>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('scripts')
    <script>
        (function () {
            // Photo preview (kept separate and intact)
            const input = document.getElementById('photo');
            const preview = document.getElementById('photoPreview');
            if (input && preview) {
                input.addEventListener('change', function () {
                    const file = this.files && this.files[0];
                    if (!file) return;
                    if (!/^image\/(png|jpeg|jpg)$/.test(file.type)) {
                        alert('Invalid file type. Please select a JPG or PNG image.');
                        this.value = '';
                        return;
                    }
                    if (file.size > 5 * 1024 * 1024) {
                        alert('File is too large. Maximum size is 5MB.');
                        this.value = '';
                        return;
                    }
                    const reader = new FileReader();
                    reader.onload = e => { preview.src = e.target.result; };
                    reader.readAsDataURL(file);
                });
            }

            // Push Notifications enable flow (runs only on user click to ensure permission prompt)
            const enableBtn = document.getElementById('enablePushBtn');
            const statusText = document.getElementById('pushStatusText');
            const publicVapidKey = @json(env('PUBLIC_KEY', config('webpush.public')));

            function urlBase64ToUint8Array(base64String) {
                const padding = '='.repeat((4 - base64String.length % 4) % 4);
                const base64 = (base64String + padding).replace(/-/g, '+').replace(/_/g, '/');
                const rawData = atob(base64);
                const outputArray = new Uint8Array(rawData.length);
                for (let i = 0; i < rawData.length; ++i) {
                    outputArray[i] = rawData.charCodeAt(i);
                }
                return outputArray;
            }

            async function registerServiceWorker() {
                if (!('serviceWorker' in navigator)) throw new Error('Service workers not supported');
                return navigator.serviceWorker.register('/sw.js', { scope: '/' });
            }

            async function sendSubscriptionToServer(subscription) {
                await fetch('{{ route('staff.push.subscribe') }}', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}'
                    },
                    credentials: 'same-origin',
                    body: JSON.stringify({ subscription })
                });
            }

            async function subscribeUser() {
                try {
                    if (!('Notification' in window)) {
                        if (statusText) statusText.textContent = 'Not supported';
                        return;
                    }

                    // Must be triggered by a user gesture to show the permission prompt
                    const permission = await Notification.requestPermission();
                    if (permission !== 'granted') {
                        if (statusText) statusText.textContent = permission === 'denied' ? 'Blocked' : 'Not enabled';
                        return;
                    }

                    const reg = await registerServiceWorker();

                    let sub = await reg.pushManager.getSubscription();
                    if (!sub) {
                        if (!publicVapidKey) {
                            console.error('VAPID_PUBLIC_KEY is not configured.');
                            if (statusText) statusText.textContent = 'VAPID key missing';
                            return;
                        }
                        sub = await reg.pushManager.subscribe({
                            userVisibleOnly: true,
                            applicationServerKey: urlBase64ToUint8Array(publicVapidKey)
                        });
                    }

                    await sendSubscriptionToServer(sub);

                    if (statusText) statusText.textContent = 'Enabled';
                    if (enableBtn) enableBtn.disabled = true;
                } catch (err) {
                    console.error('Subscription error', err);
                    if (statusText) statusText.textContent = 'Error';
                }
            }

            // Proactively ensure the service worker is registered (no permission prompt required)
            if ('serviceWorker' in navigator) {
                navigator.serviceWorker.getRegistration().then(function(reg) {
                    if (!reg) {
                        registerServiceWorker().catch(function(e){ console.warn('SW registration failed', e); });
                    }
                }).catch(function(e){ console.warn('SW getRegistration failed', e); });
            }

            const testPushBtn = document.getElementById('testPushBtn');

            if (enableBtn) {
                enableBtn.addEventListener('click', subscribeUser);
            }
            if (testPushBtn) {
                testPushBtn.addEventListener('click', async function () {
                    try {
                        if (!('Notification' in window)) {
                            alert('Notifications are not supported by this browser.');
                            return;
                        }

                        // Ensure permission and an active subscription before sending a test push
                        if (Notification.permission !== 'granted') {
                            await subscribeUser();
                            if (Notification.permission !== 'granted') {
                                alert('Please click "Enable Push Notifications" and allow permission first.');
                                return;
                            }
                        }

                        // Double-check service worker registration and subscription exist
                        const reg = await navigator.serviceWorker.getRegistration() || await registerServiceWorker();
                        let sub = reg ? await reg.pushManager.getSubscription() : null;
                        if (!sub) {
                            if (!publicVapidKey) {
                                console.error('VAPID public key missing; cannot subscribe.');
                                alert('VAPID key missing; contact administrator.');
                                return;
                            }
                            sub = await reg.pushManager.subscribe({
                                userVisibleOnly: true,
                                applicationServerKey: urlBase64ToUint8Array(publicVapidKey)
                            });
                            await sendSubscriptionToServer(sub);
                        }

                        const res = await fetch('{{ route('staff.push.test') }}', {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'X-CSRF-TOKEN': '{{ csrf_token() }}'
                            },
                            credentials: 'same-origin',
                            body: JSON.stringify({
                                title: 'Test notification',
                                body: 'Push is working!',
                                data: { url: '/staff/dashboard' }
                            })
                        });
                        if (!res.ok) {
                            try { console.warn('Test push failed', await res.text()); } catch(_) {}
                            alert('Test push failed. Check server logs.');
                        } else {
                            alert('Test push sent. If permission is granted and SW is active, a notification should appear.');
                        }
                    } catch (e) {
                        console.error('Test push error', e);
                        alert('Network or permission error while sending test push.');
                    }
                });
            }

            // Initialize UI state
            if (statusText) {
                if (!('Notification' in window)) {
                    statusText.textContent = 'Not supported';
                    if (enableBtn) enableBtn.disabled = true;
                } else if (Notification.permission === 'granted') {
                    statusText.textContent = 'Enabled';
                    if (enableBtn) enableBtn.disabled = true;
                } else if (Notification.permission === 'denied') {
                    statusText.textContent = 'Blocked';
                } else {
                    statusText.textContent = 'Not enabled';
                }
            }
        })();
    </script>
@endsection
