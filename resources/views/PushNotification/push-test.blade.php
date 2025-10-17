@extends('layouts.app')

@section('title', 'Push Test')

@section('content')
    <div class="max-w-7xl mx-auto p-4">
        <div class="bg-white shadow rounded-lg">
            <div class="px-4 py-3 border-b border-gray-200 flex items-center justify-between">
                <h2 class="text-sm font-semibold text-gray-700">Push Notification Test</h2>
                <button onclick="askForPermission()"
                    class="inline-flex items-center gap-2 bg-emerald-600 hover:bg-emerald-700 text-white text-sm font-medium px-4 py-2 rounded-md focus:outline-none focus:ring-2 focus:ring-emerald-500 focus:ring-offset-2">
                    Enable Notification
                </button>
            </div>
            <div class="p-4">
                <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                    <div>
                        <label for='title' class="block text-sm font-medium text-gray-700">{{ __('title') }}</label>
                        <input type='text'
                            class='mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm'
                            id='title' name='title'>
                    </div>
                    <div>
                        <label for='body' class="block text-sm font-medium text-gray-700">{{ __('body') }}</label>
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
                            <p class="mt-2 text-xs text-gray-500">Please enable push notifications before sending.</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
@section('scripts')
    <script>
        // Register service worker relative to current path so it resolves correctly
        // when the app is served under a subpath like /public/.
        navigator.serviceWorker.register('sw.js', {
            scope: './'
        });

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
                            console.log(JSON.stringify(subscription));
                            saveSub(JSON.stringify(subscription));
                        });
                    });
                }
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

            const body = { subscription: payload };

            if (window.axios && typeof window.axios.post === 'function') {
                window.axios.post('staff/push/subscribe', body)
                    .then(function (response) {
                        console.log('Subscription saved', response.data);
                    })
                    .catch(function (error) {
                        console.error('Failed to save subscription via axios:', error);
                    });
            } else {
                // Fallback to fetch (include CSRF token)
                fetch('staff/push/subscribe', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                    },
                    body: JSON.stringify(body)
                })
                .then(r => r.json())
                .then(data => console.log('Subscription saved (fetch)', data))
                .catch(err => console.error('Failed to save subscription via fetch:', err));
            }
        }
        


        function sendNotification() {
            if (window.axios && typeof window.axios.post === 'function') {
                window.axios.post('staff/push/send', {
                    title: document.getElementById('title').value,
                    body: document.getElementById('body').value,
                    idOfProduct: document.getElementById('idOfProduct').value
                }).then(function (response) {
                    alert('Send successful');
                    console.log(response.data);
                }).catch(function (error) {
                    console.error('Send failed:', error);
                    alert('Send failed');
                });
            } else {
                // Fallback to fetch if axios isn't available
                fetch('staff/push/send', {
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
                }).then(r => r.json()).then(data => { alert('Send successful'); console.log(data); }).catch(err => { console.error(err); alert('Send failed'); });
            }
        }
    </script>
@endsection
