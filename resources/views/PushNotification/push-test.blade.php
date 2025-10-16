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
        navigator.serviceWorker.register('/sw.js', {
            scope: '/'
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

        async function saveSub(sub) {
            try {
                const response = await fetch('https://fritzcabalhin.com/public/save-push-notification-sub', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': 'dj0Zuwg1MaXTpqHN01FnN1cthm5N9U22h70auY9j'
                    },
                    body: JSON.stringify({
                        sub
                    })
                });

                if (!response.ok) throw new Error(`HTTP error! ${response.status}`);

                const data = await response.json();
                console.log('[push] Subscription saved:', data);
            } catch (err) {
                console.error('[push] Failed to save subscription:', err);
            }
        }



        function sendNotification() {
            $.ajax({
                type: 'post',
                url: '{{ URL('send-push-notification') }}',
                data: {
                    '_token': "{{ csrf_token() }}",
                    'title': $("#title").val(),
                    'body': $("#body").val(),
                    'idOfProduct': $("#idOfProduct").val(),
                },
                success: function(data) {
                    alert('send Successfull');
                    console.log(data);
                }
            });
        }
    </script>
@endsection
