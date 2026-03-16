<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <meta http-equiv="Cache-Control" content="no-cache, no-store, must-revalidate">
    <meta http-equiv="Pragma" content="no-cache">
    <meta http-equiv="Expires" content="0">
    @include('components.rasa-widget-scripts')
    <script>
        window.AppConfig = {
            verifiedEmail: @json(Cookie::get('verified_email'))
        };
        window.VAPID_PUBLIC_KEY = @json(config('webpush.public'));
        window.APP_AUTHENTICATED = @json(auth()->check());
    </script>

    <title>{{ 'SangkayFAQs' }} - @yield('title')</title>

    <link rel="icon" type="image/png" sizes="192x192" href="{{ asset('icon-192.png') }}">
    <link rel="apple-touch-icon" href="{{ asset('icon-512.png') }}">
    <link rel="manifest" href="{{ asset('manifest.webmanifest') }}">
    <meta name="theme-color" content="#184c1c">

    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=instrument-sans:400,500,600&display=swap" rel="stylesheet" />

    @vite(['resources/css/app.css', 'resources/js/app.js'])

    <style>
        [x-cloak] {
            display: none !important;
        }
    </style>

    <script src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js" defer></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
</head>

<body class="font-sans antialiased bg-gray-50">

    <div class="min-h-screen" x-data="{ open: false }">

        <main>
            @yield('content')
        </main>

        <!-- Mobile Drawer -->
        <div x-show="open" x-transition:enter="transition ease-out duration-200"
            x-transition:enter-start="transform opacity-0 translate-x-4"
            x-transition:enter-end="transform opacity-100 translate-x-0"
            x-transition:leave="transition ease-in duration-150"
            x-transition:leave-start="transform opacity-100 translate-x-0"
            x-transition:leave-end="transform opacity-0 translate-x-4"
            class="fixed inset-y-0 right-0 w-64 bg-white shadow-2xl z-50 transform transition-transform duration-200"
            x-cloak>
            <div class="flex flex-col h-full px-6 py-5 overflow-y-auto">
                <div class="flex justify-between items-start mb-6">
                    <span class="text-xl font-bold text-gray-900">Menu</span>
                    <button @click="open = false" class="text-gray-500 hover:text-gray-700">
                        <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M6 18L18 6M6 6l12 12"></path>
                        </svg>
                    </button>
                </div>
                <nav class="mt-6 space-y-4 flex-1">
                    <div>
                        <a href="{{ route('faqs.index') }}"
                            class="text-lg font-medium text-gray-900 hover:text-gray-700 border-b-2 border-transparent px-1 py-2">Home</a>
                    </div>
                    <div>
                        <a href="{{ route('about') }}"
                            class="text-lg font-medium text-gray-900 hover:text-gray-700 border-b-2 border-transparent px-1 py-2">About
                            Us</a>
                    </div>
                    <div>
                        <a href="{{ route('tickets.verify-otp', ['identifier' => rand(100000, 999999)]) }}"
                            class="text-lg font-medium text-gray-900 hover:text-gray-700 border-b-2 border-transparent px-1 py-2">My
                            Tickets</a>
                    </div>
                </nav>
            </div>
        </div>
    </div>

    <div id="toastContainer" class="fixed top-4 right-4 z-50 space-y-2 pointer-events-none"></div>

    <footer class="py-8 text-center text-gray-400 text-xs">
        Sangkay TS &copy; {{ date('Y') }} |
        <span class="bg-gray-200 px-2 py-0.5 rounded text-gray-600">v{{ config('app.version') }}</span>
    </footer>

    @stack('scripts')

    <script>
        // Global Toast Logic
        window.showToast = (function() {
            let container = document.getElementById('toastContainer');
            return function(type, message) {
                const outer = document.createElement('div');
                outer.className =
                    'w-80 rounded-lg border bg-white px-4 py-3 shadow-lg ring-1 ring-black/5 pointer-events-auto';
                outer.innerHTML = `
                    <div class="flex items-start gap-2">
                        <div class="flex-1 text-sm text-gray-800">${message}</div>
                        <button type="button" class="text-gray-400 hover:text-gray-600" onclick="this.parentElement.parentElement.remove()">&times;</button>
                    </div>
                `;
                container.appendChild(outer);
                setTimeout(() => outer.remove(), 5000);
            };
        })();
    </script>
</body>

</html>
