<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    {{-- Cache Control --}}
    <meta http-equiv="Cache-Control" content="no-cache, no-store, must-revalidate">
    <meta http-equiv="Pragma" content="no-cache">
    <meta http-equiv="Expires" content="0">

    <title>{{ 'Sangkay' }} - @yield('title')</title>

    {{-- 1. Global Configurations (Moved up so they are ready for any script) --}}
    <script>
        window.AppConfig = {
            verifiedEmail: @json(Cookie::get('verified_email'))
        };
        window.VAPID_PUBLIC_KEY = @json(config('webpush.public'));
        window.APP_AUTHENTICATED = @json(auth()->check());

        // Define showToast early so child views can use it immediately
        window.showToast = function(type, message) {
            let container = document.getElementById('toastContainer');
            if(!container) return; 
            const outer = document.createElement('div');
            outer.className = 'w-80 rounded-lg border bg-white px-4 py-3 shadow-lg ring-1 ring-black/5 pointer-events-auto';
            outer.innerHTML = `
                <div class="flex items-start gap-2">
                    <div class="flex-1 text-sm text-gray-800">${message}</div>
                    <button type="button" class="text-gray-400 hover:text-gray-600" onclick="this.parentElement.parentElement.remove()">&times;</button>
                </div>`;
            container.appendChild(outer);
            setTimeout(() => outer.remove(), 5000);
        };
    </script>

    {{-- 2. Assets & Fonts --}}
    <link rel="icon" type="image/png" sizes="192x192" href="{{ asset('icon-192.png') }}">
    <link rel="manifest" href="{{ asset('manifest.webmanifest') }}">
    <meta name="theme-color" content="#184c1c">
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=instrument-sans:400,500,600&display=swap" rel="stylesheet" />

    @vite(['resources/css/app.css', 'resources/js/app.js'])

    <style> [x-cloak] { display: none !important; } </style>

    {{-- 3. Third Party Scripts --}}
    @include('components.rasa-widget-scripts')
    <script src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js" defer></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
</head>

<body class="font-sans antialiased bg-gray-50">

    <div class="min-h-screen" x-data="{ open: false }">
        <main>
            @yield('content')
        </main>

        <div x-show="open"
             x-transition:enter="transition ease-out duration-300"
             x-transition:enter-start="transform translate-x-full"
             x-transition:enter-end="transform translate-x-0"
             x-transition:leave="transition ease-in duration-300"
             x-transition:leave-start="transform translate-x-0"
             x-transition:leave-end="transform translate-x-full"
             class="fixed inset-y-0 right-0 w-64 bg-white shadow-2xl z-50"
             x-cloak>
            <!-- Mobile Menu Header -->
            <div class="flex items-center justify-between p-4 border-b border-gray-200" style="background-color: #FF9D00;">
                <div class="flex items-center">
                    <img src="{{ asset('logo-white.png') }}" alt="Sangkay Logo" class="h-6 w-6">
                    <span class="text-white font-bold text-sm tracking-wider ml-2">SANGKAY</span>
                </div>
                <button @click="open = false" class="text-white hover:bg-white/20 p-2 rounded-md transition-colors">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                    </svg>
                </button>
            </div>

            <!-- Mobile Menu Items -->
            <div class="py-4">
                <a href="{{ route('faqs.index') }}" @click="open = false"
                   class="block px-6 py-3 text-gray-800 hover:bg-gray-100 transition-colors border-l-4 {{ request()->routeIs('faqs.index') ? 'border-orange-500 bg-orange-50' : 'border-transparent' }}">
                    Home
                </a>
                <a href="{{ route('about') }}" @click="open = false"
                   class="block px-6 py-3 text-gray-800 hover:bg-gray-100 transition-colors border-l-4 {{ request()->routeIs('about') ? 'border-orange-500 bg-orange-50' : 'border-transparent' }}">
                    About Us
                </a>
                <a href="{{ route('tickets.verify-otp', ['identifier' => rand(100000, 999999)]) }}" @click="open = false"
                   class="block px-6 py-3 text-gray-800 hover:bg-gray-100 transition-colors border-l-4 {{ request()->routeIs('tickets.*') ? 'border-orange-500 bg-orange-50' : 'border-transparent' }}">
                    My Tickets
                </a>
            </div>
        </div>
    </div>

    {{-- Toast Container stays at bottom for stacking --}}
    <div id="toastContainer" class="fixed top-4 right-4 z-50 space-y-2 pointer-events-none"></div>

    <footer class="py-8 text-center text-gray-400 text-xs">
        Sangkay TS &copy; {{ date('Y') }} |
        <span class="bg-gray-200 px-2 py-0.5 rounded text-gray-600">v{{ config('app.version') }}</span>
    </footer>

    {{-- 4. Page Specific Scripts --}}
    @stack('scripts')
</body>
</html>