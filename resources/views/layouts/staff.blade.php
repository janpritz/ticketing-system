@extends('layouts.app')

@section('title', trim($__env->yieldContent('title', 'Staff Dashboard')))

@section('content')
<!-- Staff Shell: Sidebar + Header -->
<aside id="default-sidebar" class="fixed top-0 left-0 z-40 w-64 h-screen transition-transform -translate-x-full sm:translate-x-0" aria-label="Sidebar">
  <div class="h-full px-3 py-4 overflow-y-auto bg-gray-50">
    <a href="{{ route('staff.dashboard') }}" class="flex items-center gap-2 p-2 rounded-md hover:bg-gray-100">
      <img src="{{ asset('logo.png') }}" alt="Logo" class="w-8 h-8">
      <span class="text-sm font-semibold text-gray-900">Sangkay Ticketing System</span>
    </a>
    <div class="h-px bg-gray-200 my-3"></div>
    <ul class="space-y-2 font-medium">
      <li>
        <a href="{{ route('staff.dashboard') }}"
           class="flex items-center p-2 rounded-lg hover:bg-gray-100 group {{ request()->routeIs('staff.dashboard') ? 'bg-gray-100 text-gray-900' : 'text-gray-900' }}">
          <svg class="w-5 h-5 {{ request()->routeIs('staff.dashboard') ? 'text-gray-900' : 'text-gray-500 group-hover:text-gray-900' }}" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="currentColor" viewBox="0 0 22 21">
            <path d="M16.975 11H10V4.025a1 1 0 0 0-1.066-.998 8.5 8.5 0 1 0 9.039 9.039.999.999 0 0 0-1-1.066h.002Z"/>
            <path d="M12.5 0c-.157 0-.311.01-.565.027A1 1 0 0 0 11 1.02V10h8.975a1 1 0 0 0 1-.935c.013-.188.028-.374.028-.565A8.51 8.51 0 0 0 12.5 0Z"/>
          </svg>
          <span class="ms-3">Dashboard</span>
        </a>
      </li>
      <li>
        <a href="{{ route('staff.tickets') }}"
           class="flex items-center p-2 rounded-lg hover:bg-gray-100 group {{ request()->routeIs('staff.tickets') ? 'bg-gray-100 text-gray-900' : 'text-gray-900' }}">
          <svg class="w-5 h-5 {{ request()->routeIs('staff.tickets') ? 'text-gray-900' : 'text-gray-500 group-hover:text-gray-900' }}" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="currentColor" viewBox="0 0 20 20">
            <path d="M2 5h16v2H2V5zm0 4h16v2H2V9zm0 4h12v2H2v-2z"/>
          </svg>
          <span class="ms-3">Tickets</span>
        </a>
      </li>
      <li>
        <button type="button" id="knowledgebaseDropdown" class="w-full flex items-center justify-between p-2 rounded-lg hover:bg-gray-100 group {{ request()->routeIs('staff.knowledgebase.*') ? 'bg-gray-100 text-gray-900' : 'text-gray-900' }}" aria-expanded="false">
          <div class="flex items-center">
            <svg class="w-5 h-5 {{ request()->routeIs('staff.knowledgebase.*') ? 'text-gray-900' : 'text-gray-500 group-hover:text-gray-900' }}" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="currentColor" viewBox="0 0 20 20">
              <path d="m17.418 3.623-.018-.008a6.713 6.713 0 0 0-2.4-.569V2h1a1 1 0 1 0 0-2h-2a1 1 0 0 0-1 1v2H9.89A6.977 6.977 0 0 1 12 8v5h-2V8A5 5 0 1 0 0 8v6a1 1 0 0 0 1 1h8v4a1 1 0 0 0 1 1h2a1 1 0 0 0 1-1v-4h6a1 1 0 0 0 1-1V8a5 5 0 0 0-2.582-4.377Z"/>
            </svg>
            <span class="ms-3">Knowledgebase</span>
          </div>
          <svg id="knowledgebaseChevron" class="w-4 h-4 transition-transform duration-200 {{ request()->routeIs('staff.knowledgebase.*') ? 'text-gray-900' : 'text-gray-500 group-hover:text-gray-900' }}" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="currentColor" viewBox="0 0 24 24">
            <path d="M7 10l5 5 5-5z"/>
          </svg>
        </button>
        <div id="knowledgebaseMenu" class="hidden pl-4 mt-1 space-y-1">
          <a href="{{ route('staff.knowledgebase.index') }}" class="flex items-center p-2 text-sm rounded-lg hover:bg-gray-100 group {{ request()->routeIs('staff.knowledgebase.index') ? 'bg-gray-100 text-gray-900' : 'text-gray-700' }}">
            <svg class="w-4 h-4 {{ request()->routeIs('staff.knowledgebase.index') ? 'text-gray-900' : 'text-gray-500 group-hover:text-gray-900' }}" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="currentColor" viewBox="0 0 20 20">
              <path d="m17.418 3.623-.018-.008a6.713 6.713 0 0 0-2.4-.569V2h1a1 1 0 1 0 0-2h-2a1 1 0 0 0-1 1v2H9.89A6.977 6.977 0 0 1 12 8v5h-2V8A5 5 0 1 0 0 8v6a1 1 0 0 0 1 1h8v4a1 1 0 0 0 1 1h2a1 1 0 0 0 1-1v-4h6a1 1 0 0 0 1-1V8a5 5 0 0 0-2.582-4.377Z"/>
            </svg>
            <span class="ms-3">FAQs</span>
          </a>
          <a href="{{ route('staff.knowledgebase.create') }}" class="flex items-center p-2 text-sm rounded-lg hover:bg-gray-100 group {{ request()->routeIs('staff.knowledgebase.create') ? 'bg-gray-100 text-gray-900' : 'text-gray-700' }}">
            <svg class="w-4 h-4 {{ request()->routeIs('staff.knowledgebase.create') ? 'text-gray-900' : 'text-gray-500 group-hover:text-gray-900' }}" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="currentColor" viewBox="0 0 20 20">
              <path d="M10 5a1 1 0 011 1v3h3a1 1 0 110 2h-3v3a1 1 0 11-2 0v-3H6a1 1 0 110-2h3V6a1 1 0 011-1z"/>
            </svg>
            <span class="ms-3">Create FAQ</span>
          </a>
          <a href="{{ route('staff.knowledgebase.announcements.index') }}" class="flex items-center p-2 text-sm rounded-lg hover:bg-gray-100 group {{ request()->routeIs('staff.knowledgebase.announcements.*') ? 'bg-gray-100 text-gray-900' : 'text-gray-700' }}">
            <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4 {{ request()->routeIs('staff.knowledgebase.announcements.*') ? 'text-gray-900' : 'text-gray-500 group-hover:text-gray-900' }}" fill="currentColor" viewBox="0 0 16 16">
              <path d="M13 2.5a1.5 1.5 0 0 1 3 0v11a1.5 1.5 0 0 1-3 0zm-1 .724c-2.067.95-4.539 1.481-7 1.656v6.237a25 25 0 0 1 1.088.085c2.053.204 4.038.668 5.912 1.56zm-8 7.841V4.934c-.68.027-1.399.043-2.008.053A2.02 2.02 0 0 0 0 7v2c0 1.106.896 1.996 1.994 2.009l.496.008a64 64 0 0 1 1.51.048m1.39 1.081q.428.032.85.078l.253 1.69a1 1 0 0 1-.983 1.187h-.548a1 1 0 0 1-.916-.599l-1.314-2.48a66 66 0 0 1 1.692.064q.491.026.966.06"/>
            </svg>
            <span class="ms-3">Announcements</span>
          </a>
        </div>
      </li>
      <li>
        <a href="{{ route('staff.reports.index') }}"
           class="flex items-center p-2 rounded-lg hover:bg-gray-100 group {{ request()->routeIs('staff.reports.*') ? 'bg-gray-100 text-gray-900' : 'text-gray-900' }}">
          <svg class="w-5 h-5 {{ request()->routeIs('staff.reports.*') ? 'text-gray-900' : 'text-gray-500 group-hover:text-gray-900' }}" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="currentColor" viewBox="0 0 20 20">
            <path d="M3 4a1 1 0 011-1h12a1 1 0 011 1v2a1 1 0 01-1 1H4a1 1 0 01-1-1V4zM3 10a1 1 0 011-1h6a1 1 0 011 1v6a1 1 0 01-1 1H4a1 1 0 01-1-1v-6zM14 9a1 1 0 00-1 1v6a1 1 0 001 1h2a1 1 0 001-1v-6a1 1 0 00-1-1h-2z"/>
          </svg>
          <span class="ms-3">Reports</span>
        </a>
      </li>
      <li>
        <a href="{{ route('staff.profile') }}"
           class="flex items-center p-2 rounded-lg hover:bg-gray-100 group {{ request()->routeIs('staff.profile*') ? 'bg-gray-100 text-gray-900' : 'text-gray-900' }}">
          <svg class="w-5 h-5 {{ request()->routeIs('staff.profile*') ? 'text-gray-900' : 'text-gray-500 group-hover:text-gray-900' }}" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="currentColor" viewBox="0 0 20 20">
            <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-6-3a2 2 0 11-4 0 2 2 0 014 0zm-2 4a5 5 0 00-4.546 2.916A5.986 5.986 0 0010 16a5.986 5.986 0 004.546-2.084A5 5 0 0010 11z" clip-rule="evenodd" />
          </svg>
          <span class="ms-3">Profile</span>
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
      </div>
      <div class="flex items-center gap-4">
        <div class="text-right">
          <div class="text-xs text-slate-500">Welcome back,</div>
          <div class="text-sm font-medium text-slate-900">{{ auth()->user()->name ?? 'Staff User' }}</div>
        </div>
      </div>
    </div>
  </header>

  <!-- Page-specific content -->
  <main class="mt-4 space-y-4 mb-10">
    @yield('staff-content')
  </main>
</div>
@endsection

@section('scripts')
  @parent
  <!-- Sidebar collapse/expand for mobile + desktop -->
  <script>
    (function () {
      const toggleBtn = document.getElementById('sidebar-toggle');
      const sidebar = document.getElementById('default-sidebar');
      const content = document.getElementById('content-wrapper');
      const backdrop = document.getElementById('sidebar-backdrop');
      const STORAGE_KEY = 'staff.sidebar.open';

      if (!toggleBtn || !sidebar || !content) return;

      const mq = window.matchMedia('(max-width: 639.98px)');
      function isMobile() { return mq.matches; }

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

      function applyState(open) {
        if (open) {
          if (isMobile()) openMobile(); else openDesktop();
        } else {
          if (isMobile()) closeMobile(); else closeDesktop();
        }
      }

      function readState() {
        try {
          const v = localStorage.getItem(STORAGE_KEY);
          return v === 'true';
        } catch (e) {
          return false;
        }
      }
      function writeState(open) {
        try {
          localStorage.setItem(STORAGE_KEY, open ? 'true' : 'false');
        } catch (e) { /* ignore */ }
      }

      function toggleSidebar() {
        const currentlyOpen = readState();
        const next = !currentlyOpen;
        applyState(next);
        writeState(next);
      }

      (function init() {
        sidebar.classList.add('sm:-translate-x-full');
        sidebar.classList.remove('sm:translate-x-0');
        sidebar.classList.add('-translate-x-full');
        sidebar.classList.remove('translate-x-0');
        content.classList.remove('sm:ml-64');
        content.classList.add('ml-0');
        if (backdrop) backdrop.classList.add('hidden');
        document.body.classList.remove('overflow-hidden');

        const wasOpen = readState();
        applyState(wasOpen);
      })();

      toggleBtn.addEventListener('click', function (e) {
        e.preventDefault();
        toggleSidebar();
      });

      if (backdrop) {
        backdrop.addEventListener('click', function () {
          if (isMobile()) {
            closeMobile();
            writeState(false);
          }
        });
      }

      document.addEventListener('keydown', (e) => {
        if (e.key === 'Escape' && isMobile()) {
          closeMobile();
          writeState(false);
        }
      });

      mq.addEventListener('change', () => {
        const wasOpen = readState();
        applyState(wasOpen);
      });
    })();
  </script>

  <!-- Knowledgebase Dropdown Script -->
  <script>
    (function () {
      const dropdownBtn = document.getElementById('knowledgebaseDropdown');
      const dropdownMenu = document.getElementById('knowledgebaseMenu');
      const chevron = document.getElementById('knowledgebaseChevron');

      if (!dropdownBtn || !dropdownMenu || !chevron) return;

      dropdownBtn.addEventListener('click', function (e) {
        e.preventDefault();

        const isOpen = !dropdownMenu.classList.contains('hidden');

        dropdownMenu.classList.toggle('hidden', isOpen);
        dropdownBtn.setAttribute('aria-expanded', String(!isOpen));
        chevron.style.transform = isOpen ? 'rotate(0deg)' : 'rotate(90deg)';
      });

      document.addEventListener('click', function (e) {
        if (!dropdownBtn.contains(e.target) && !dropdownMenu.contains(e.target)) {
          dropdownMenu.classList.add('hidden');
          dropdownBtn.setAttribute('aria-expanded', 'false');
          chevron.style.transform = 'rotate(0deg)';
        }
      });
    })();
  </script>


  @yield('staff-scripts')
@endsection