<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ config('app.name', 'Ticketing System') }} - @yield('title')</title>
    <meta http-equiv="Content-Security-Policy" content="upgrade-insecure-requests">

    <link rel="icon" type="image/png" href="{{ asset('logo.png') }}">
    <link rel="shortcut icon" href="{{ asset('logo.png') }}">
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
    <script type="module" src="https://unpkg.com/@rasahq/chat-widget-ui/dist/rasa-chatwidget/rasa-chatwidget.esm.js"></script>
    <!-- SweetAlert2 -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
</head>

<body class="font-sans antialiased bg-gray-100">
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
    <div id="toastContainer" aria-live="polite" aria-atomic="true" class="fixed top-4 right-4 z-50 space-y-2" style="z-index:2147483647; pointer-events:none;"></div>
    <script>
      // Global toast utility, available as window.showToast('success'|'error', message)
      window.showToast = (function () {
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
            try { document.body.appendChild(container); } catch (_) {}
          }
          return container;
        }
        function show(type, message) {
          const target = ensureContainer();
          const isSuccess = type === 'success';
          const icon = isSuccess
            ? '<svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-emerald-500" viewBox="0 0 24 24" fill="currentColor"><path d="M9 12.75l-2.25-2.25-1.5 1.5L9 15.75l9-9-1.5-1.5z"/></svg>'
            : '<svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-red-500" viewBox="0 0 24 24" fill="currentColor"><path d="M12 2a10 10 0 100 20 10 10 0 000-20zm.75 5.5h-1.5v7h1.5v-7zm0 8.5h-1.5v1.5h1.5V16z"/></svg>';
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
          if (closer) closer.addEventListener('click', () => { try { outer.remove(); } catch (_) {} });
          setTimeout(() => { try { outer.remove(); } catch (_) {} }, 5000);
        }
        return show;
      })();
    </script>
    <!-- Scripts -->
    @yield('scripts')
</body>

</html>