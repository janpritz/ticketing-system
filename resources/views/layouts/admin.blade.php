@extends('layouts.app')

@section('title', trim($__env->yieldContent('title', 'Admin')))

@section('content')
<!-- Admin Shell: Sidebar + Header -->
<aside id="default-sidebar" class="fixed top-0 left-0 z-40 w-64 h-screen transition-transform -translate-x-full sm:translate-x-0" aria-label="Sidebar">
  <div class="h-full px-3 py-4 overflow-y-auto bg-gray-50">
    <a href="{{ route('admin.dashboard') }}" class="flex items-center gap-2 p-2 rounded-md hover:bg-gray-100">
      <img src="{{ asset('logo.png') }}" alt="Logo" class="w-8 h-8">
      <span class="text-sm font-semibold text-gray-900">Sangkay Ticketing System</span>
    </a>
    <div class="h-px bg-gray-200 my-3"></div>
    <ul class="space-y-2 font-medium">
      <li>
        <a href="{{ route('admin.dashboard') }}"
           class="flex items-center p-2 rounded-lg hover:bg-gray-100 group {{ request()->routeIs('admin.dashboard') ? 'bg-gray-100 text-gray-900' : 'text-gray-900' }}">
          <svg class="w-5 h-5 {{ request()->routeIs('admin.dashboard') ? 'text-gray-900' : 'text-gray-500 group-hover:text-gray-900' }}" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="currentColor" viewBox="0 0 22 21">
            <path d="M16.975 11H10V4.025a1 1 0 0 0-1.066-.998 8.5 8.5 0 1 0 9.039 9.039.999.999 0 0 0-1-1.066h.002Z"/>
            <path d="M12.5 0c-.157 0-.311.01-.565.027A1 1 0 0 0 11 1.02V10h8.975a1 1 0 0 0 1-.935c.013-.188.028-.374.028-.565A8.51 8.51 0 0 0 12.5 0Z"/>
          </svg>
          <span class="ms-3">Dashboard</span>
        </a>
      </li>
      <li>
        <a href="{{ route('admin.users.index') }}"
           class="flex items-center p-2 rounded-lg hover:bg-gray-100 group {{ request()->routeIs('admin.users.*') ? 'bg-gray-100 text-gray-900' : 'text-gray-900' }}">
          <svg class="w-5 h-5 {{ request()->routeIs('admin.users.*') ? 'text-gray-900' : 'text-gray-500 group-hover:text-gray-900' }}" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="currentColor" viewBox="0 0 18 18">
            <path d="M6.143 0H1.857A1.857 1.857 0 0 0 0 1.857v4.286C0 7.169.831 8 1.857 8h4.286A1.857 1.857 0 0 0 8 6.143V1.857A1.857 1.857 0 0 0 6.143 0Zm10 0h-4.286A1.857 1.857 0 0 0 10 1.857v4.286C10 7.169 10.831 8 11.857 8h4.286A1.857 1.857 0 0 0 18 6.143V1.857A1.857 1.857 0 0 0 16.143 0Zm-10 10H1.857A1.857 1.857 0 0 0 0 11.857v4.286C0 17.169.831 18 1.857 18h4.286A1.857 1.857 0 0 0 8 16.143v-4.286A1.857 1.857 0 0 0 6.143 10Zm10 0h-4.286A1.857 1.857 0 0 0 10 11.857v4.286c0 1.026.831 1.857 1.857 1.857h4.286A1.857 1.857 0 0 0 18 16.143v-4.286A1.857 1.857 0 0 0 16.143 10Z"/>
          </svg>
          <span class="ms-3">User Management</span>
        </a>
      </li>
      <li>
        <a href="{{ route('tickets.index') }}"
           class="flex items-center p-2 rounded-lg hover:bg-gray-100 group text-gray-900">
          <svg class="w-5 h-5 text-gray-500 transition duration-75 group-hover:text-gray-900" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="currentColor" viewBox="0 0 20 20">
            <path d="M2 5h16v2H2V5zm0 4h16v2H2V9zm0 4h12v2H2v-2z"/>
          </svg>
          <span class="ms-3">Ticket Management</span>
        </a>
      </li>
      <li>
        <!-- Use direct URL to avoid route-name resolution issues in some environments -->
        <a href="{{ url('/admin/faqs') }}"
           class="flex items-center p-2 rounded-lg hover:bg-gray-100 group {{ request()->routeIs('admin.faqs.*') ? 'bg-gray-100 text-gray-900' : 'text-gray-900' }}">
          <svg class="w-5 h-5 {{ request()->routeIs('admin.faqs.*') ? 'text-gray-900' : 'text-gray-500 group-hover:text-gray-900' }}" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="currentColor" viewBox="0 0 20 20">
            <path d="m17.418 3.623-.018-.008a6.713 6.713 0 0 0-2.4-.569V2h1a1 1 0 1 0 0-2h-2a1 1 0 0 0-1 1v2H9.89A6.977 6.977 0 0 1 12 8v5h-2V8A5 5 0 1 0 0 8v6a1 1 0 0 0 1 1h8v4a1 1 0 0 0 1 1h2a1 1 0 0 0 1-1v-4h6a1 1 0 0 0 1-1V8a5 5 0 0 0-2.582-4.377Z"/>
          </svg>
          <span class="ms-3">FAQ Management</span>
        </a>
      </li>
      <li>
        <form method="POST" action="{{ route('logout') }}">
          @csrf
          <button type="submit" class="w-full flex items-center p-2 text-gray-900 rounded-lg hover:bg-gray-100 group">
            <svg class="w-5 h-5 text-gray-500 transition duration-75 group-hover:text-gray-900" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 18 16">
              <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M1 8h11m0 0L8 4m4 4-4 4m4-11h3a2 2 0 0 1 2 2v10a2 2 0 0 1-2 2h-3"/>
            </svg>
            <span class="ms-3">Logout</span>
          </button>
        </form>
      </li>
    </ul>
  </div>
</aside>
<div id="sidebar-backdrop" class="fixed inset-0 z-30 bg-black/40 hidden"></div>
<div id="content-wrapper" class="px-10 sm:ml-64 transition-all duration-300">
  <!-- Top Bar -->
  <header class="bg-white border border-gray-200 rounded-md">
    <div class="px-4 sm:px-6 lg:px-8 h-12 flex items-center justify-between gap-4">
      <div class="flex-1 max-w-xl flex items-center gap-2">
        <button id="sidebar-toggle" aria-controls="default-sidebar" type="button" class="inline-flex items-center p-2 text-sm text-gray-500 rounded-lg hover:bg-gray-100 focus:outline-none focus:ring-2 focus:ring-gray-200" title="Toggle sidebar">
          <span class="sr-only">Toggle sidebar</span>
          <svg class="w-5 h-5" aria-hidden="true" fill="currentColor" viewBox="0 0 20 20" xmlns="http://www.w3.org/2000/svg">
            <path clip-rule="evenodd" fill-rule="evenodd" d="M2 4.75A.75.75 0 012.75 4h14.5a.75.75 0 010 1.5H2.75A.75.75 0 012 4.75zm0 10.5a.75.75 0 01.75-.75h7.5a.75.75 0 010 1.5h-7.5a.75.75 0 01-.75-.75zM2 10a.75.75 0 01.75-.75h14.5a.75.75 0 010 1.5H2.75A.75.75 0 012 10z"></path>
          </svg>
        </button>
        <label class="relative block">
          <span class="sr-only">Search</span>
          <span class="absolute inset-y-0 left-0 flex items-center pl-3 text-gray-400">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" viewBox="0 0 24 24" fill="currentColor">
              <path d="M15.5 14h-.79l-.28-.27a6.5 6.5 0 10-.71.71l.27.28v.79L20 21.5 21.5 20l-6-6zM10 15a5 5 0 110-10 5 5 0 010 10z" />
            </svg>
          </span>
          <input type="text" placeholder="Search tickets, users, FAQs..." class="w-full pl-9 pr-3 py-2 text-sm rounded-md border border-gray-200 focus:ring-2 focus:ring-blue-500 focus:border-blue-500" />
        </label>
      </div>
      <div class="flex items-center gap-4">
        <button class="relative text-gray-500 hover:text-gray-700" title="Notifications">
          <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 24 24" fill="currentColor">
            <path d="M12 22a2 2 0 002-2H10a2 2 0 002 2zm6-6V9a6 6 0 10-12 0v7l-2 2v1h16v-1l-2-2z" />
          </svg>
        </button>
        <div class="text-right">
          <div class="text-xs text-slate-500">Welcome back,</div>
          <div class="text-sm font-medium text-slate-900">{{ auth()->user()->name ?? 'Admin User' }}</div>
        </div>
      </div>
    </div>
  </header>

  <!-- Page-specific content -->
  <main class="mt-4 space-y-4 mb-10">
    @yield('admin-content')
  </main>
</div>
@endsection

@section('scripts')
  @parent
  <!-- Sidebar collapse/expand for mobile + desktop (mobile as overlay, desktop pushes content) -->
  <script>
    (function () {
      const toggleBtn = document.getElementById('sidebar-toggle');
      const sidebar = document.getElementById('default-sidebar');
      const content = document.getElementById('content-wrapper');
      const backdrop = document.getElementById('sidebar-backdrop');

      if (!toggleBtn || !sidebar || !content) return;

      const mq = window.matchMedia('(max-width: 639.98px)'); // Tailwind < sm

      function isMobile() { return mq.matches; }

      // Desktop behaviors (>= sm): push content
      function openDesktop() {
        sidebar.classList.remove('sm:-translate-x-full');
        sidebar.classList.add('sm:translate-x-0');
        content.classList.add('sm:ml-64');
        content.classList.remove('ml-0');
        if (backdrop) backdrop.classList.add('hidden');
        document.body.classList.remove('overflow-hidden');
      }
      function closeDesktop() {
        sidebar.classList.add('sm:-translate-x-full');
        sidebar.classList.remove('sm:translate-x-0');
        content.classList.remove('sm:ml-64');
        content.classList.add('ml-0');
        if (backdrop) backdrop.classList.add('hidden');
        document.body.classList.remove('overflow-hidden');
      }

      // Mobile behaviors (< sm): overlay on top of content
      function openMobile() {
        sidebar.classList.remove('-translate-x-full');
        sidebar.classList.add('translate-x-0');
        if (backdrop) backdrop.classList.remove('hidden');
        document.body.classList.add('overflow-hidden');
      }
      function closeMobile() {
        sidebar.classList.add('-translate-x-full');
        sidebar.classList.remove('translate-x-0');
        if (backdrop) backdrop.classList.add('hidden');
        document.body.classList.remove('overflow-hidden');
      }

      function toggleSidebar() {
        if (isMobile()) {
          const isHidden = sidebar.classList.contains('-translate-x-full');
          if (isHidden) openMobile(); else closeMobile();
        } else {
          const isCollapsed = sidebar.classList.contains('sm:-translate-x-full');
          if (isCollapsed) openDesktop(); else closeDesktop();
        }
      }

      toggleBtn.addEventListener('click', function (e) {
        e.preventDefault();
        toggleSidebar();
      });

      if (backdrop) {
        backdrop.addEventListener('click', closeMobile);
      }
      document.addEventListener('keydown', (e) => {
        if (e.key === 'Escape' && isMobile()) {
          closeMobile();
        }
      });

      // Keep state coherent on breakpoint change
      mq.addEventListener('change', () => {
        if (!isMobile()) {
          // Leaving mobile: ensure desktop-open baseline
          sidebar.classList.remove('translate-x-0');
          sidebar.classList.add('-translate-x-full'); // keep mobile base hidden
          openDesktop();
        } else {
          // Entering mobile: keep content unshifted and sidebar hidden
          content.classList.remove('sm:ml-64');
          if (backdrop) backdrop.classList.add('hidden');
          sidebar.classList.add('-translate-x-full');
          sidebar.classList.remove('translate-x-0');
        }
      });
    })();
  </script>

  @yield('admin-scripts')
@endsection