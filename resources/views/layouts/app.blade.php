<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <script>
        // Expose globals for push setup
        window.VAPID_PUBLIC_KEY = @json(config('webpush.public'));
        window.APP_AUTHENTICATED = @json(auth()->check());
    </script>

    <title>{{ config('app.name', 'Ticketing System') }} - @yield('title')</title>

    <!-- Favicons & PWA manifest: use proper icon sizes for add-to-home and modern platforms -->
    <link rel="icon" type="image/png" sizes="192x192" href="{{ asset('icon-192.png') }}">
    <link rel="icon" type="image/png" sizes="512x512" href="{{ asset('icon-512.png') }}">
    <link rel="icon" type="image/png" sizes="32x32" href="{{ asset('favicon.ico') }}">
    <link rel="icon" type="image/png" sizes="16x16" href="{{ asset('favicon.ico') }}">
    <link rel="apple-touch-icon" sizes="180x180" href="{{ asset('icon-512.png') }}">
    <link rel="manifest" href="{{ asset('manifest.webmanifest') }}">
    <meta name="theme-color" content="#184c1c">
    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=instrument-sans:400,500,600&display=swap" rel="stylesheet" />

    <!-- TailwindCSS -->
    <link rel="stylesheet" href="https://rsms.me/inter/inter.css" />

    <!-- Styles -->
    <link rel="stylesheet" href="https://unpkg.com/@rasahq/chat-widget-ui/dist/rasa-chatwidget/rasa-chatwidget.css" />
    <!-- <script src="https://cdn.tailwindcss.com"></script> -->

    <!-- Scripts -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])

    <!-- Alpine.js for dropdown functionality -->
    <script src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js" defer></script>
    <!-- Rasa Widget -->
    <script type="module" src="https://unpkg.com/@rasahq/chat-widget-ui/dist/rasa-chatwidget/rasa-chatwidget.esm.js">
    </script>
    <!-- SweetAlert2 -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
</head>

<body class="font-sans antialiased">
    <div class="min-h-screen">
        <!-- Navigation -->

        <!-- Page Heading -->
        @if (isset($header))
            <header class="bg-white shadow">
                <div class="max-w-7xl mx-auto py-6 px-4 sm:px-6 lg:px-8">
                    {{ $header }}
                </div>
            </header>
        @endif

        <!-- Page Content -->
        <main>
            @yield('content')
        </main>
    </div>

    <!-- Global Toasts -->
    <div id="toastContainer" aria-live="polite" aria-atomic="true" class="fixed top-4 right-4 z-50 space-y-2"
        style="z-index:2147483647; pointer-events:none;"></div>
    <script>
        // Global toast utility, available as window.showToast('success'|'error', message)
        window.showToast = (function() {
            let container = null;

            function ensureContainer() {
                container = document.getElementById('toastContainer') || container;
                if (!container) {
                    container = document.createElement('div');
                    container.id = 'toastContainer';
                    container.setAttribute('aria-live', 'polite');
                    container.setAttribute('aria-atomic', 'true');
                    container.className = 'fixed top-4 right-4 z-50 space-y-2';
                    container.style.zIndex = '2147483647';
                    container.style.pointerEvents = 'none';
                    document.body.appendChild(container);
                } else if (container.parentElement !== document.body) {
                    try {
                        document.body.appendChild(container);
                    } catch (_) {}
                }
                return container;
            }

            function show(type, message) {
                const target = ensureContainer();
                const isSuccess = type === 'success';
                const icon = isSuccess ?
                    '<svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-emerald-500" viewBox="0 0 24 24" fill="currentColor"><path d="M9 12.75l-2.25-2.25-1.5 1.5L9 15.75l9-9-1.5-1.5z"/></svg>' :
                    '<svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-red-500" viewBox="0 0 24 24" fill="currentColor"><path d="M12 2a10 10 0 100 20 10 10 0 000-20zm.75 5.5h-1.5v7h1.5v-7zm0 8.5h-1.5v1.5h1.5V16z"/></svg>';
                const outer = document.createElement('div');
                outer.className = 'w-80 rounded-lg border bg-white px-4 py-3 shadow ring-1 ring-black/5';
                outer.setAttribute('role', 'status');
                outer.style.pointerEvents = 'auto';
                outer.innerHTML =
                    '<div class="flex items-start gap-2">' +
                    icon +
                    '<div class="flex-1 text-sm ' +
                    (isSuccess ? 'text-emerald-800' : 'text-red-800') +
                    '">' +
                    String(message || '') +
                    '</div>' +
                    '<button type="button" aria-label="Close" class="text-gray-400 hover:text-gray-600" data-close>&times;</button>' +
                    '</div>';
                target.appendChild(outer);
                const closer = outer.querySelector('[data-close]');
                if (closer) closer.addEventListener('click', () => {
                    try {
                        outer.remove();
                    } catch (_) {}
                });
                setTimeout(() => {
                    try {
                        outer.remove();
                    } catch (_) {}
                }, 5000);
            }
            return show;
        })();
    </script>
    <script>
            // PWA update toast functionality only (no automatic refresh)
            function showUpdateToast(worker) {
                const toast = document.getElementById('pwa-update-toast');
                toast.classList.remove('translate-y-32');
                document.getElementById('pwa-update-btn').onclick = () => {
                    worker.postMessage({
                        type: 'SKIP_WAITING'
                    });
                };
            }
        </script>

    <div id="pwa-update-toast"
        class="fixed bottom-5 left-5 right-5 md:left-auto md:right-5 md:w-80 bg-gray-900 text-white p-4 rounded-lg shadow-2xl transform translate-y-32 transition-transform duration-300 flex flex-col gap-3 z-50">
        <div class="flex items-center gap-3">
            <div class="bg-orange-500 p-2 rounded-full">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="id=123"></path>
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15">
                    </path>
                </svg>
            </div>
            <p class="text-sm font-medium">New version available!</p>
        </div>
        <div class="flex gap-2">
            <button id="pwa-update-btn"
                class="bg-orange-500 hover:bg-orange-600 text-white text-xs font-bold py-2 px-4 rounded">
                Update Now
            </button>
            <button onclick="document.getElementById('pwa-update-toast').classList.add('translate-y-32')"
                class="text-gray-400 hover:text-white text-xs py-2 px-2">
                Later
            </button>
        </div>
    </div>
    <footer class="p-4 text-center text-gray-500 text-xs">
        Sangkay TS &copy; {{ date('Y') }} |
        <span class="bg-gray-200 px-2 py-1 rounded">v{{ config('app.version') }}</span>
    </footer>

    <!-- Scripts -->
    @yield('scripts')
</body>

</html>
