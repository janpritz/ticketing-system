@extends('layouts.admin')

@section('title', 'Admin Dashboard')

@section('content')
<!-- Flowbite drawer toggle (mobile) -->

<!-- Flowbite sidebar -->
<aside id="default-sidebar" class="fixed top-0 left-0 z-40 w-64 h-screen transition-transform -translate-x-full sm:translate-x-0" aria-label="Sidebar">
  <div class="h-full px-3 py-4 overflow-y-auto bg-gray-50">
    <a href="{{ route('admin.dashboard') }}" class="flex items-center gap-2 p-2 rounded-md hover:bg-gray-100">
      <img src="{{ asset('logo.png') }}" alt="Logo" class="w-8 h-8">
      <span class="text-sm font-semibold text-gray-900">Sangkay Ticketing System</span>
    </a>
    <div class="h-px bg-gray-200 my-3"></div>
    <ul class="space-y-2 font-medium">
      <li>
        <a href="{{ route('admin.dashboard') }}" class="flex items-center p-2 text-gray-900 rounded-lg hover:bg-gray-100 group">
          <svg class="w-5 h-5 text-gray-500 transition duration-75 group-hover:text-gray-900" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="currentColor" viewBox="0 0 22 21">
            <path d="M16.975 11H10V4.025a1 1 0 0 0-1.066-.998 8.5 8.5 0 1 0 9.039 9.039.999.999 0 0 0-1-1.066h.002Z"/>
            <path d="M12.5 0c-.157 0-.311.01-.565.027A1 1 0 0 0 11 1.02V10h8.975a1 1 0 0 0 1-.935c.013-.188.028-.374.028-.565A8.51 8.51 0 0 0 12.5 0Z"/>
          </svg>
          <span class="ms-3">Dashboard</span>
        </a>
      </li>
      <li>
        <button type="button" id="userManagementDropdown" class="w-full flex items-center justify-between p-2 text-gray-900 rounded-lg hover:bg-gray-100 group" aria-expanded="false">
          <div class="flex items-center">
            <svg class="w-5 h-5 text-gray-500 transition duration-75 group-hover:text-gray-900" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="currentColor" viewBox="0 0 18 18">
              <path d="M6.143 0H1.857A1.857 1.857 0 0 0 0 1.857v4.286C0 7.169.831 8 1.857 8h4.286A1.857 1.857 0 0 0 8 6.143V1.857A1.857 1.857 0 0 0 6.143 0Zm10 0h-4.286A1.857 1.857 0 0 0 10 1.857v4.286C10 7.169 10.831 8 11.857 8h4.286A1.857 1.857 0 0 0 18 6.143V1.857A1.857 1.857 0 0 0 16.143 0Zm-10 10H1.857A1.857 1.857 0 0 0 0 11.857v4.286C0 17.169.831 18 1.857 18h4.286A1.857 1.857 0 0 0 8 16.143v-4.286A1.857 1.857 0 0 0 6.143 10Zm10 0h-4.286A1.857 1.857 0 0 0 10 11.857v4.286c0 1.026.831 1.857 1.857 1.857h4.286A1.857 1.857 0 0 0 18 16.143v-4.286A1.857 1.857 0 0 0 16.143 10Z"/>
            </svg>
            <span class="ms-3">User Management</span>
          </div>
          <svg id="userManagementChevron" class="w-4 h-4 text-gray-500 transition-transform duration-200" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="currentColor" viewBox="0 0 24 24">
            <path d="M7 10l5 5 5-5z"/>
          </svg>
        </button>
        <div id="userManagementMenu" class="hidden pl-4 mt-1 space-y-1">
          <a href="{{ route('admin.users.index') }}" class="flex items-center p-2 text-sm text-gray-700 rounded-lg hover:bg-gray-100 group">
            <svg class="w-4 h-4 text-gray-500 transition duration-75 group-hover:text-gray-900" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="currentColor" viewBox="0 0 18 18">
              <path d="M6.143 0H1.857A1.857 1.857 0 0 0 0 1.857v4.286C0 7.169.831 8 1.857 8h4.286A1.857 1.857 0 0 0 8 6.143V1.857A1.857 1.857 0 0 0 6.143 0Zm10 0h-4.286A1.857 1.857 0 0 0 10 1.857v4.286C10 7.169 10.831 8 11.857 8h4.286A1.857 1.857 0 0 0 18 6.143V1.857A1.857 1.857 0 0 0 16.143 0Zm-10 10H1.857A1.857 1.857 0 0 0 0 11.857v4.286C0 17.169.831 18 1.857 18h4.286A1.857 1.857 0 0 0 8 16.143v-4.286A1.857 1.857 0 0 0 6.143 10Zm10 0h-4.286A1.857 1.857 0 0 0 10 11.857v4.286c0 1.026.831 1.857 1.857 1.857h4.286A1.857 1.857 0 0 0 18 16.143v-4.286A1.857 1.857 0 0 0 16.143 10Z"/>
            </svg>
            <span class="ms-3">Users</span>
          </a>
          <a href="{{ route('admin.roles.index') }}" class="flex items-center p-2 text-sm text-gray-700 rounded-lg hover:bg-gray-100 group">
            <svg class="w-4 h-4 text-gray-500 transition duration-75 group-hover:text-gray-900" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="currentColor" viewBox="0 0 20 20">
              <path d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
            </svg>
            <span class="ms-3">Role Management</span>
          </a>
          <a href="{{ route('admin.categories.index') }}" class="flex items-center p-2 text-sm text-gray-700 rounded-lg hover:bg-gray-100 group">
            <svg class="w-4 h-4 text-gray-500 transition duration-75 group-hover:text-gray-900" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="currentColor" viewBox="0 0 20 20">
              <path d="M5 3a2 2 0 00-2 2v2a2 2 0 002 2h2a2 2 0 002-2V5a2 2 0 00-2-2H5zM5 11a2 2 0 00-2 2v2a2 2 0 002 2h2a2 2 0 002-2v-2a2 2 0 00-2-2H5zM11 5a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V5zM11 13a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2v-2z"/>
            </svg>
            <span class="ms-3">Category Management</span>
          </a>
        </div>
      </li>
      <li>
        <a href="{{ route('admin.tickets.index') }}" class="flex items-center p-2 text-gray-900 rounded-lg hover:bg-gray-100 group">
          <svg class="w-5 h-5 text-gray-500 transition duration-75 group-hover:text-gray-900" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="currentColor" viewBox="0 0 20 20">
            <path d="M2 5h16v2H2V5zm0 4h16v2H2V9zm0 4h12v2H2v-2z"/>
          </svg>
          <span class="ms-3">Ticket Management</span>
        </a>
      </li>
      <li>
        <button type="button" id="faqManagementDropdown" class="w-full flex items-center justify-between p-2 rounded-lg hover:bg-gray-100 group {{ request()->routeIs('admin.faqs.*') || request()->routeIs('admin.announcements.*') ? 'bg-gray-100 text-gray-900' : 'text-gray-900' }}" aria-expanded="false">
          <div class="flex items-center">
            <svg class="w-5 h-5 {{ request()->routeIs('admin.faqs.*') || request()->routeIs('admin.announcements.*') ? 'text-gray-900' : 'text-gray-500 group-hover:text-gray-900' }}" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="currentColor" viewBox="0 0 20 20">
              <path d="m17.418 3.623-.018-.008a6.713 6.713 0 0 0-2.4-.569V2h1a1 1 0 1 0 0-2h-2a1 1 0 0 0-1 1v2H9.89A6.977 6.977 0 0 1 12 8v5h-2V8A5 5 0 1 0 0 8v6a1 1 0 0 0 1 1h8v4a1 1 0 0 0 1 1h2a1 1 0 0 0 1-1v-4h6a1 1 0 0 0 1-1V8a5 5 0 0 0-2.582-4.377Z"/>
            </svg>
            <span class="ms-3">FAQ Management</span>
          </div>
          <svg id="faqManagementChevron" class="w-4 h-4 transition-transform duration-200 {{ request()->routeIs('admin.faqs.*') || request()->routeIs('admin.announcements.*') ? 'text-gray-900' : 'text-gray-500 group-hover:text-gray-900' }}" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="currentColor" viewBox="0 0 24 24">
            <path d="M7 10l5 5 5-5z"/>
          </svg>
        </button>
        <div id="faqManagementMenu" class="hidden pl-4 mt-1 space-y-1">
          <a href="{{ route('admin.faqs.index') }}" class="flex items-center p-2 text-sm rounded-lg hover:bg-gray-100 group {{ request()->routeIs('admin.faqs.*') ? 'bg-gray-100 text-gray-900' : 'text-gray-700' }}">
            <svg class="w-4 h-4 {{ request()->routeIs('admin.faqs.*') ? 'text-gray-900' : 'text-gray-500 group-hover:text-gray-900' }}" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="currentColor" viewBox="0 0 20 20">
              <path d="m17.418 3.623-.018-.008a6.713 6.713 0 0 0-2.4-.569V2h1a1 1 0 1 0 0-2h-2a1 1 0 0 0-1 1v2H9.89A6.977 6.977 0 0 1 12 8v5h-2V8A5 5 0 1 0 0 8v6a1 1 0 0 0 1 1h8v4a1 1 0 0 0 1 1h2a1 1 0 0 0 1-1v-4h6a1 1 0 0 0 1-1V8a5 5 0 0 0-2.582-4.377Z"/>
            </svg>
            <span class="ms-3">FAQS</span>
          </a>
          <a href="{{ route('admin.announcements.index') }}" class="flex items-center p-2 text-sm rounded-lg hover:bg-gray-100 group {{ request()->routeIs('admin.announcements.*') ? 'bg-gray-100 text-gray-900' : 'text-gray-700' }}">
            <svg class="w-4 h-4 {{ request()->routeIs('admin.announcements.*') ? 'text-gray-900' : 'text-gray-500 group-hover:text-gray-900' }}" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="currentColor" viewBox="0 0 20 20">
              <path d="M10 2a1 1 0 011 1v1.323l3.954 1.582 1.496-.94a1 1 0 011.342.02l.086.075a1 1 0 01.02 1.342l-1.496.94.48 3.058H14a1 1 0 01-1-1V8H9v.5a1 1 0 01-1 1H5.562l.48-3.058-1.496-.94a1 1 0 01.02-1.342l.086-.075a1 1 0 011.342-.02l1.496.94L10 4.323V3a1 1 0 011-1zm0 4.5a1.5 1.5 0 100 3 1.5 1.5 0 000-3z"/>
            </svg>
            <span class="ms-3">Announcements</span>
          </a>
          <a href="{{ route('admin.logs') }}" class="flex items-center p-2 text-sm rounded-lg hover:bg-gray-100 group {{ request()->routeIs('admin.logs') ? 'bg-gray-100 text-gray-900' : 'text-gray-700' }}">
            <svg class="w-4 h-4 {{ request()->routeIs('admin.logs') ? 'text-gray-900' : 'text-gray-500 group-hover:text-gray-900' }}" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="currentColor" viewBox="0 0 20 20">
              <path fill-rule="evenodd" d="M3 4a1 1 0 011-1h12a1 1 0 011 1v2a1 1 0 01-1 1H4a1 1 0 01-1-1V4zM3 10a1 1 0 011-1h6a1 1 0 011 1v6a1 1 0 01-1 1H4a1 1 0 01-1-1v-6zM14 9a1 1 0 00-1 1v6a1 1 0 001 1h2a1 1 0 001-1v-6a1 1 0 00-1-1h-2z" clip-rule="evenodd" />
            </svg>
            <span class="ms-3">Logs</span>
          </a>
        </div>
      </li>
      <li>
        <a href="{{ route('admin.rasa-server.index') }}"
            class="flex items-center p-2 text-gray-900 rounded-lg hover:bg-gray-100 group">
          <svg class="w-5 h-5 text-gray-500 transition duration-75 group-hover:text-gray-900" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="currentColor" viewBox="0 0 20 20">
            <path fill-rule="evenodd" d="M12.395 2.553a1 1 0 00-1.45-.385c-.345.23-.614.558-.822.88-.214.33-.403.713-.57 1.116-.334.804-.614 1.768-.84 2.734a31.365 31.365 0 00-.613 3.58 2.64 2.64 0 01-.945-1.067c-.328-.68-.398-1.534-.398-2.654A1 1 0 005.05 6.05 6.981 6.981 0 003 11a7 7 0 1011.95-4.95c-.592-.591-.98-.985-1.348-1.467-.363-.476-.724-1.063-1.207-2.03zM12.12 15.12A3 3 0 017 13s.879.5 2.5.5c0-1 .5-4 1.25-4.5.5 1 .786 1.293 1.371 1.879A2.99 2.99 0 0113 13a2.99 2.99 0 01-.879 2.121z" clip-rule="evenodd" />
          </svg>
          <span class="ms-3">Rasa Server Manager</span>
        </a>
      </li>
      <li>
        <a href="{{ route('admin.reports.index') }}"
            class="flex items-center p-2 text-gray-900 rounded-lg hover:bg-gray-100 group">
          <svg class="w-5 h-5 text-gray-500 transition duration-75 group-hover:text-gray-900" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="currentColor" viewBox="0 0 20 20">
            <path d="M3 4a1 1 0 011-1h12a1 1 0 011 1v2a1 1 0 01-1 1H4a1 1 0 01-1-1V4zM3 10a1 1 0 011-1h6a1 1 0 011 1v6a1 1 0 01-1 1H4a1 1 0 01-1-1v-6zM14 9a1 1 0 00-1 1v6a1 1 0 001 1h2a1 1 0 001-1v-6a1 1 0 00-1-1h-2z"/>
          </svg>
          <span class="ms-3">Reports</span>
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
                    <div class="text-sm font-medium text-slate-900">{{ auth()->user()?->name ?? 'Admin User' }}</div>
                </div>
            </div>
        </div>
    </header>

    <!-- Content -->
    <main class="mt-4 space-y-4">
        <div class="ps-2">
            <h1 class="text-2xl font-semibold text-slate-900">Dashboard</h1>
            <p class="text-sm text-slate-500">Overview of your ticketing system</p>
        </div>

        <!-- Document Training Alert -->
        <div id="trainingAlert" class="hidden bg-orange-50 border-l-4 border-orange-400 p-4 mb-4">
            <div class="flex items-center justify-between">
                <div class="flex items-center">
                    <div class="flex-shrink-0">
                        <svg class="h-5 w-5 text-orange-400" viewBox="0 0 20 20" fill="currentColor">
                            <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd" />
                        </svg>
                    </div>
                    <div class="ml-3">
                        <p class="text-sm text-orange-700">
                            <strong>Training Required:</strong> Documents have been modified and need Rasa retraining.
                        </p>
                    </div>
                </div>
                <div class="ml-4">
                    <button id="trainRasaBtn"
                            class="inline-flex items-center px-3 py-2 border border-transparent text-sm leading-4 font-medium rounded-md text-orange-700 bg-orange-100 hover:bg-orange-200 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-orange-500 transition-colors duration-200">
                        <svg class="mr-2 h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z" />
                        </svg>
                        <span id="trainBtnText">Train Rasa</span>
                        <svg class="ml-2 h-4 w-4 hidden" id="trainSpinner" fill="none" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                        </svg>
                    </button>
                </div>
            </div>
        </div>

        <!-- Metrics -->
        <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
            <!-- Total Open Tickets -->
            <a href="{{ route('admin.tickets.index') }}" class="block bg-white rounded-xl border border-gray-200 p-4 hover:bg-gray-50 transition-colors">
                <div class="flex items-start justify-between">
                    <div>
                        <div class="text-xs font-medium text-slate-500">Total Open Tickets</div>
                        <div class="mt-2 text-3xl font-semibold text-slate-900"><span id="openTicketsCount">{{ number_format($openTickets ?? 0) }}</span></div>
                        @if(($openTicketsDelta ?? 0) > 0)
                        <div id="openTicketsDeltaWrap" class="mt-1 text-xs text-emerald-600 flex items-center gap-1">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-3.5 w-3.5" viewBox="0 0 24 24" fill="currentColor">
                                <path d="M12 4l6 6h-4v10h-4V10H6l6-6z" />
                            </svg>
                            <span id="openTicketsDelta">+{{ number_format($openTicketsDelta ?? 0) }} from yesterday</span>
                        </div>
                        @endif
                    </div>
                    <div class="rounded-md bg-red-50 p-2 text-red-500 border border-red-100">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 24 24" fill="currentColor">
                            <path d="M4 4h16v2H4V4zm0 6h16v10H4V10zm2 2v6h12v-6H6z" />
                        </svg>
                    </div>
                </div>
            </a>
            <!-- Active Staff (last 10 min) -->
            <div id="activeStaffCard" class="bg-white rounded-xl border border-gray-200 p-4 cursor-pointer hover:bg-gray-50" role="button" tabindex="0" aria-label="Open active staff list">
                <div class="flex items-start justify-between">
                    <div>
                        <div class="text-xs font-medium text-slate-500">Active Staff (last 10 min)</div>
                        <div class="mt-2 flex items-center gap-2">
                            <div id="activeStaffDot" class="w-4 h-4 rounded-full bg-green-500 {{ ($activeStaffCount ?? 0) > 0 ? '' : 'hidden' }}"></div>
                            <span id="activeStaffCountText" class="text-sm text-slate-700">{{ $activeStaffCount ?? 0 }}</span>
                        </div>
                    </div>
                    <div class="rounded-md bg-purple-50 p-2 text-purple-600 border border-purple-100">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 24 24" fill="currentColor">
                            <path d="M16 11c1.66 0 2.99-1.34 2.99-3S17.66 5 16 5s-3 1.34-3 3 1.34 3 3 3zM8 11c1.66 0 2.99-1.34 2.99-3S9.66 5 8 5 5 6.34 5 8s1.34 3 3 3zm0 2c-2.33 0-7 1.17-7 3.5V19h14v-2.5C15 14.17 10.33 13 8 13zm8 0c-.29 0-.62.02-.97.05 1.16.84 1.97 1.97 1.97 3.45V19h6v-2.5c0-2.33-4.67-3.5-7-3.5z" />
                        </svg>
                    </div>
                </div>
            </div>
            <!-- Last Rasa Training -->
            <div class="block bg-white rounded-xl border border-gray-200 p-4 hover:bg-gray-50 transition-colors">
                <div class="flex items-start justify-between">
                    <div>
                        <div class="text-xs font-medium text-slate-500">Last Rasa Training</div>
                        <div class="mt-2 text-lg font-semibold text-slate-900" id="lastTrainingValue">{{ $lastTraining ?? 'Never' }}</div>
                    </div>
                    <div class="rounded-md bg-purple-50 p-2 text-purple-600 border border-purple-100">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 24 24" fill="currentColor">
                            <path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm-2 15l-5-5 1.41-1.41L10 14.17l7.59-7.59L19 8l-9 9z"/>
                        </svg>
                    </div>
                </div>
            </div>
            <!-- Rasa Server Status -->
            <div class="block bg-white rounded-xl border border-gray-200 p-4 hover:bg-gray-50 transition-colors cursor-pointer" id="rasaStatusCard">
                <div class="flex items-start justify-between">
                    <div>
                        <div class="text-xs font-medium text-slate-500">Rasa Server Status</div>
                        <div class="mt-2 text-2xl sm:text-3xl font-semibold text-slate-900" id="rasaStatusText">Checking...</div>
                    </div>
                    <div class="rounded-md p-2 border" id="rasaStatusIcon">
                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-globe" viewBox="0 0 16 16">
                            <path d="M0 8a8 8 0 1 1 16 0A8 8 0 0 1 0 8m7.5-6.923c-.67.204-1.335.82-1.887 1.855A8 8 0 0 0 5.145 4H7.5zM4.09 4a9.3 9.3 0 0 1 .64-1.539 7 7 0 0 1 .597-.933A7.03 7.03 0 0 0 2.255 4zm-.582 3.5c.03-.877.138-1.718.312-2.5H1.674a7 7 0 0 0-.656 2.5zM4.847 5a12.5 12.5 0 0 0-.338 2.5H7.5V5zM8.5 5v2.5h2.99a12.5 12.5 0 0 0-.337-2.5zM4.51 8.5a12.5 12.5 0 0 0 .337 2.5H7.5V8.5zm3.99 0V11h2.653c.187-.765.306-1.608.338-2.5zM5.145 12q.208.58.468 1.068c.552 1.035 1.218 1.65 1.887 1.855V12zm.182 2.472a7 7 0 0 1-.597-.933A9.3 9.3 0 0 1 4.09 12H2.255a7 7 0 0 0 3.072 2.472M3.82 11a13.7 13.7 0 0 1-.312-2.5h-2.49c.062.89.291 1.733.656 2.5zm6.853 3.472A7 7 0 0 0 13.745 12H11.91a9.3 9.3 0 0 1-.64 1.539 7 7 0 0 1-.597.933M8.5 12v2.923c.67-.204 1.335-.82 1.887-1.855q.26-.487.468-1.068zm3.68-1h2.146c.365-.767.594-1.61.656-2.5h-2.49a13.7 13.7 0 0 1-.312 2.5m2.802-3.5a7 7 0 0 0-.656-2.5H12.18c.174.782.282 1.623.312 2.5zM11.27 2.461c.247.464.462.98.64 1.539h1.835a7 7 0 0 0-3.072-2.472c.218.284.418.598.597.933M10.855 4a8 8 0 0 0-.468-1.068C9.835 1.897 9.17 1.282 8.5 1.077V4z"/>
                        </svg>
                    </div>
                </div>
            </div>
        </div>

        <!-- Analytics -->
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-4">
            <!-- Weekly Tickets -->
            <div class="bg-white rounded-xl border border-gray-200 p-4">
                <div class="flex items-center justify-between mb-2">
                    <h3 class="text-sm font-semibold text-slate-800">Weekly Tickets (Mon–Sun)</h3>
                </div>
                <div class="h-48">
                    <canvas id="weeklyTicketsChart" class="w-full h-full"></canvas>
                </div>
            </div>

            <!-- Tickets by Category -->
            <div class="bg-white rounded-xl border border-gray-200 p-4">
                <div class="flex items-center justify-between mb-2">
                    <h3 class="text-sm font-semibold text-slate-800">Tickets by Category</h3>
                </div>
                <div class="h-48">
                    <canvas id="ticketCategoryChart" class="w-full h-full"></canvas>
                </div>
            </div>
        </div>

        <!-- Top Senders -->
        <div class="bg-white rounded-xl border border-gray-200 p-4">
            <div class="flex items-center justify-between mb-2">
                <h3 class="text-sm font-semibold text-slate-800">Top Senders (by Email)</h3>
            </div>
            <div class="overflow-x-auto">
                <table class="min-w-full text-sm">
                    <thead class="bg-gray-50 text-gray-600">
                        <tr>
                            <th class="py-3 pl-5 pr-3 text-left font-medium">#</th>
                            <th class="px-3 py-3 text-left font-medium">Email</th>
                            <th class="px-3 py-3 text-left font-medium">Tickets</th>
                        </tr>
                    </thead>
                    <tbody id="topSendersBody" class="divide-y divide-gray-100">
                        @forelse(($topSenders ?? []) as $idx => $row)
                        <tr class="hover:bg-gray-50">
                            <td class="py-3 pl-5 pr-3 align-top">{{ $idx + 1 }}</td>
                            <td class="px-3 py-3 align-top">
                                <div class="text-gray-900">{{ $row->email ?: '—' }}</div>
                            </td>
                            <td class="px-3 py-3 align-top">
                                <span class="font-medium text-slate-900">{{ (int)($row->c ?? 0) }}</span>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="3" class="px-5 py-10 text-center text-sm text-gray-500">No data.</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Tables -->
        <div class="grid grid-cols-1 gap-4 mb-10">
            @php
            $badge = fn($status) => match($status) {
            'Open' => 'text-blue-700 bg-blue-50 ring-blue-600/20',
            'Forwarded' => 'text-amber-700 bg-amber-50 ring-amber-600/20',
            'Closed' => 'text-emerald-700 bg-emerald-50 ring-emerald-600/20',
            default => 'text-slate-700 bg-slate-50 ring-slate-600/20',
            };
            @endphp


            <!-- Unassigned Tickets (assigned to Primary Administrator) -->
            <div class="bg-white rounded-xl border border-gray-200 overflow-hidden">
                <div class="px-5 py-4 border-b border-gray-300">
                    <h3 class="text-sm font-semibold text-slate-800">Unassigned Tickets</h3>
                </div>
                <div class="overflow-x-auto">
                    <table class="min-w-full text-sm">
                        <thead class="bg-gray-50 text-gray-600">
                            <tr>
                                <th class="py-3 pl-5 pr-3 text-left font-medium">Ticket</th>
                                <th class="px-3 py-3 text-left font-medium">User</th>
                                <th class="px-3 py-3 text-left font-medium">Status</th>
                            </tr>
                        </thead>
                        <tbody id="myTicketsListBody" class="divide-y divide-gray-100">
                            @forelse(($myTicketsList ?? []) as $t)
                            @php
                            @endphp
                            <tr class="hover:bg-gray-50 cursor-pointer btn-view" data-id="{{ $t->id }}">
                                <td class="py-3 pl-5 pr-3 align-top">
                                    <div class="text-indigo-700 font-medium">{{ $t->id }}</div>
                                    <div class="mt-1 text-xs text-gray-500">
                                        Updated {{ \Illuminate\Support\Carbon::parse($t->updated_at ?? $t->date_created)->format('Y-m-d h:i a') }}
                                    </div>
                                </td>
                                <td class="px-3 py-3 align-top">
                                    <div class="text-gray-900">{{ $t->email ?? '—' }}</div>
                                    <div class="text-xs text-gray-500">{{ optional($t->staff)->name ? 'Staff: '.optional($t->staff)->name : '' }}</div>
                                </td>
                                <td class="px-3 py-3 align-top">
                                    <span class="inline-flex items-center gap-1 rounded-full px-2.5 py-1 text-xs font-medium ring-1 {{ $badge($t->status) }}">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-3.5 w-3.5" viewBox="0 0 24 24" fill="currentColor">
                                            <circle cx="12" cy="12" r="5"></circle>
                                        </svg>
                                        {{ $t->status }}
                                    </span>
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="3" class="px-5 py-10 text-center text-sm text-gray-500">No unassigned tickets.</td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </main>
</div>

<!-- Ticket Details Modal - Redesigned with Minimal Aesthetic -->
<div id="ticketModal" class="fixed inset-0 z-50 hidden overflow-y-auto">
    <div class="absolute inset-0 bg-black/60 backdrop-blur-md transition-opacity" data-modal-backdrop></div>
    <!-- Centered panel with modern minimal design -->
    <div class="relative mx-auto my-0 sm:my-8 w-full h-full sm:h-auto sm:w-[95%] max-w-2xl flex items-center">
        <div class="bg-white shadow-2xl w-full h-full sm:h-auto sm:max-h-[95vh] sm:max-w-2xl overflow-hidden sm:rounded-2xl flex flex-col">

            <!-- Header - Minimal & Clean -->
            <div class="px-4 sm:px-6 py-4 border-b border-gray-100 flex items-center justify-between shrink-0">
                <div class="flex-1 min-w-0">
                    <div class="flex items-center gap-3 mb-2">
                        <h3 id="tmTicketNo" class="text-lg font-semibold text-gray-900">Ticket #</h3>
                        <span id="tmStatus" class="inline-flex items-center gap-1.5 rounded-full px-3 py-1 text-xs font-medium ring-1"></span>
                    </div>
                </div>
                <div class="flex items-center gap-2 ml-4">
                    <!-- Options Menu in Header -->
                    <div class="relative">
                        <button type="button" id="tmOptionsBtn"
                            class="inline-flex items-center gap-2 rounded-lg px-2 py-2 text-sm font-medium text-gray-700 hover:bg-gray-100 transition-colors"
                            aria-haspopup="true" aria-expanded="false">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" viewBox="0 0 24 24" fill="currentColor">
                                <path d="M5 12a2 2 0 114 0 2 2 0 01-4 0zm5 0a2 2 0 114 0 2 2 0 01-4 0zm5 0a2 2 0 114 0 2 2 0 01-4 0z" />
                            </svg>
                            <span class="hidden sm:inline">Options</span>
                        </button>
                        <div id="tmOptionsMenu"
                            class="absolute right-0 top-full mt-2 w-48 bg-white rounded-lg shadow-lg ring-1 ring-black/5 hidden z-10 overflow-hidden">
                            <button type="button"
                                class="w-full text-left px-4 py-2.5 text-sm text-gray-700 hover:bg-gray-50 transition-colors"
                                data-option="toggle-history">Show History</button>
                            <button type="button" id="tmOptionForward"
                                class="w-full text-left px-4 py-2.5 text-sm text-gray-700 hover:bg-gray-50 transition-colors border-t border-gray-100"
                                data-option="show-forward">Forward Ticket</button>
                        </div>
                    </div>
                    <button type="button" class="text-gray-400 hover:text-gray-600 transition-colors p-1 rounded-lg hover:bg-gray-100" aria-label="Close" data-modal-close>
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </button>
                </div>
            </div>

            <!-- Content - Scrollable -->
            <div class="flex-1 overflow-y-auto px-6 py-5 space-y-5">
                
                <!-- Question/Issue - Primary Focus -->
                <div class="bg-gray-50 rounded-xl p-4 border border-gray-100">
                    <div class="flex items-center gap-2 mb-2">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 text-gray-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8.228 9c.549-1.165 2.03-2 3.772-2 2.21 0 4 1.343 4 3 0 1.4-1.278 2.575-3.006 2.907-.542.104-.994.54-.994 1.093m0 3h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                        <span class="text-xs font-medium text-gray-500 uppercase tracking-wide">Issue</span>
                    </div>
                    <div id="tmQuestion" class="text-sm text-gray-900 whitespace-pre-wrap leading-relaxed"></div>
                </div>

                <!-- Attachments - Visible when present -->
                <div id="tmAttachmentsBlock" class="hidden">
                    <div class="flex items-center gap-2 mb-3">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 text-gray-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.172 7l-6.586 6.586a2 2 0 102.828 2.828l6.414-6.586a4 4 0 00-5.656-5.656l-6.415 6.585a6 6 0 108.486 8.486L20.5 13" />
                        </svg>
                        <span class="text-xs font-medium text-gray-700">Attachments</span>
                    </div>
                    <div id="tmAttachmentsList" class="flex flex-wrap gap-2"></div>
                </div>

                <!-- Sent Response - For closed tickets -->
                <div id="tmStoredResponseBlock" class="hidden bg-emerald-50 rounded-xl p-4 border border-emerald-200">
                    <div class="flex items-center gap-2 mb-2">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 text-emerald-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                        <span class="text-xs font-semibold text-emerald-700 uppercase tracking-wide">Sent Response</span>
                    </div>
                    <div id="tmStoredResponse" class="text-sm text-gray-800 whitespace-pre-wrap leading-relaxed"></div>
                </div>

                <!-- Collapsible Details Section -->
                <div id="tmDetailsSection" class="border-t border-gray-100 pt-4">
                    <button type="button" id="tmToggleDetails" class="flex items-center justify-between w-full text-left group">
                        <span class="text-sm font-medium text-gray-700 group-hover:text-gray-900">Show Details</span>
                        <svg id="tmDetailsChevron" xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 text-gray-500 transition-transform" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                        </svg>
                    </button>
                    
                    <div id="tmDetailsContent" class="hidden mt-4 space-y-4 pb-2">
                        <!-- Category & Dates -->
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                            <div>
                                <label class="text-xs font-medium text-gray-500 uppercase tracking-wide">Category</label>
                                <div id="tmCategory" class="mt-1 inline-flex items-center rounded-full bg-gray-100 px-2.5 py-1 text-xs font-medium text-gray-700"></div>
                            </div>
                            <div>
                                <label class="text-xs font-medium text-gray-500 uppercase tracking-wide">Timeline</label>
                                <div id="tmDates" class="mt-1 text-xs text-gray-700"></div>
                            </div>
                        </div>

                        <!-- Contact Info -->
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                            <div>
                                <label class="text-xs font-medium text-gray-500 uppercase tracking-wide">Email</label>
                                <div id="tmEmail" class="mt-1 text-sm text-gray-800"></div>
                            </div>
                            <div>
                                <label class="text-xs font-medium text-gray-500 uppercase tracking-wide">Recipient ID</label>
                                <div id="tmRecepient" class="mt-1 text-sm text-gray-800"></div>
                            </div>
                        </div>

                        <!-- Routing History -->
                        <div id="tmHistorySection" class="hidden">
                            <label class="text-xs font-medium text-gray-500 uppercase tracking-wide">Routing History</label>
                            <ul id="tmHistoryList" class="mt-2 space-y-2"></ul>
                        </div>
                    </div>
                </div>

                <!-- Response Input -->
                <div class="bg-gradient-to-br from-indigo-50 to-blue-50 rounded-xl p-4 border border-indigo-100">
                    <label for="tmResponse" class="flex items-center gap-2 text-sm font-medium text-indigo-900 mb-2">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" />
                        </svg>
                        Your Response
                    </label>
                    <textarea id="tmResponse"
                        class="w-full rounded-lg border-indigo-200 text-sm focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 bg-white shadow-sm resize-none"
                        rows="4" placeholder="Type your response message here..."></textarea>
                </div>

                <!-- Forward Controls -->
                <div id="tmForwardControls" class="hidden">
                    <div class="flex flex-col sm:flex-row sm:items-center gap-2">
                        <label for="tmForwardSelect" class="text-xs font-medium text-gray-700">Forward to:</label>
                        <select id="tmForwardSelect" class="flex-1 sm:flex-none rounded-lg border-gray-300 text-sm focus:ring-2 focus:ring-indigo-500">
                            <option value="" selected disabled>Select user</option>
                        </select>
                        <button type="button" id="tmForwardApply"
                            class="inline-flex items-center justify-center rounded-lg bg-amber-600 px-4 py-2 text-sm font-medium text-white hover:bg-amber-700 transition-colors">
                            Forward Ticket
                        </button>
                    </div>
                </div>
            </div>

            <!-- Footer - Actions -->
            <div class="px-6 py-4 border-t border-gray-100 bg-gray-50 shrink-0">
                <!-- Main Actions -->
                <div class="flex flex-col sm:flex-row items-stretch sm:items-center justify-between gap-3">
                    <div class="flex items-center gap-2">
                      
                    </div>

                    <div class="flex items-center gap-2">
                        <button type="button"
                            class="flex-1 sm:flex-none inline-flex items-center justify-center rounded-lg bg-white px-4 py-2 text-sm font-medium text-gray-700 ring-1 ring-gray-300 hover:bg-gray-50 transition-colors"
                            data-modal-close>Cancel</button>
                        <button type="button" title="Send response" aria-label="Send response"
                            class="flex-1 sm:flex-none inline-flex items-center justify-center gap-2 rounded-lg bg-indigo-600 px-5 py-2 text-sm font-medium text-white hover:bg-indigo-700 transition-colors shadow-sm"
                            id="tmSendResponse">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" viewBox="0 0 24 24" fill="currentColor">
                                <path d="M3 12l18-9-9 18-2-7-7-2z" />
                            </svg>
                            Send Response
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Image Lightbox -->
<div id="imageLightbox" class="fixed inset-0 z-60 hidden bg-black bg-opacity-75 flex items-center justify-center">
  <div class="relative w-full h-full">
    <img id="lightboxImage" src="" alt="" class="w-full h-full object-contain">
    <button id="lightboxPrev" class="absolute left-4 top-1/2 transform -translate-y-1/2 text-white text-4xl hover:text-gray-300 bg-black bg-opacity-50 rounded-full w-12 h-12 flex items-center justify-center">&larr;</button>
    <button id="lightboxNext" class="absolute right-4 top-1/2 transform -translate-y-1/2 text-white text-4xl hover:text-gray-300 bg-black bg-opacity-50 rounded-full w-12 h-12 flex items-center justify-center">&rarr;</button>
    <button id="lightboxClose" class="absolute top-4 right-4 text-white text-4xl hover:text-gray-300 bg-black bg-opacity-50 rounded-full w-12 h-12 flex items-center justify-center">&times;</button>
  </div>
</div>

<!-- Secondary right-side Contacts nav (hidden by default; toggled by Active Staff card) -->
<aside id="contacts-aside" class="hidden fixed top-0 right-0 z-30 w-72 h-screen bg-white border-l border-gray-200 flex-col">
  <div class="h-12 flex items-center justify-between px-4 border-b">
    <div class="text-[11px] font-semibold text-slate-700 tracking-wide">CONTACTS</div>
  </div>
  <div id="contactsList" class="flex-1 overflow-y-auto p-2 space-y-1">
    <!-- Filled by JS -->
  </div>
</aside>
@endsection

<!-- Right-side drawer for Active Staff -->
<div id="activeStaffDrawer" class="fixed inset-0 z-50 hidden" aria-hidden="true">
  <div id="asdOverlay" class="absolute inset-0 bg-black/40"></div>
  <div class="absolute right-0 top-0 h-full w-80 bg-white shadow-xl border-l border-gray-200 flex flex-col">
    <div class="h-12 flex items-center justify-between px-4 border-b">
      <div class="text-sm font-semibold text-slate-800">Active Staff</div>
      <button id="asdClose" type="button" class="text-slate-500 hover:text-slate-700" aria-label="Close">
        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 24 24" fill="currentColor"><path d="M6 18 18 6M6 6l12 12"/></svg>
      </button>
    </div>
    <div id="asdList" class="flex-1 overflow-y-auto p-3 text-sm">
      <!-- Filled dynamically -->
    </div>
  </div>
</div>


<!-- Serialized analytics data for charts (avoid Blade directives inside JS) -->
<div id="analytics-data" class="hidden"
      data-week-labels='@json($weekLabels ?? [])'
      data-week-data='@json($weekData ?? [])'
      data-category-labels='@json($categoryLabels ?? [])'
      data-category-data='@json($categoryData ?? [])'
      data-active-staff='@json($activeStaff ?? [])'
      data-staff-contacts='@json($staffContacts ?? [])'
      data-admin-url="{{ route('admin.dashboard.data') }}"></div>
<div id="faq-updater-data" class="hidden" data-secret="{{ $faqUpdaterSecret ?? '' }}" data-url="{{ $faqUpdaterUrl ?? '' }}"></div>
@section('scripts')
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script src="https://cdn.jsdelivr.net/npm/flowbite@2.5.2/dist/flowbite.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.1/dist/chart.umd.min.js"></script>
<script>
    (function () {
        // Data from backend (read from hidden element to avoid Blade-in-JS parsing issues)
        const analyticsEl = document.getElementById('analytics-data');
        const weekLabels = analyticsEl ? JSON.parse(analyticsEl.getAttribute('data-week-labels') || '[]') : [];
        const weekData = analyticsEl ? JSON.parse(analyticsEl.getAttribute('data-week-data') || '[]') : [];
        const categoryLabels = analyticsEl ? JSON.parse(analyticsEl.getAttribute('data-category-labels') || '[]') : [];
        const categoryData = analyticsEl ? JSON.parse(analyticsEl.getAttribute('data-category-data') || '[]') : [];
        const activeStaffSeed = analyticsEl ? JSON.parse(analyticsEl.getAttribute('data-active-staff') || '[]') : [];
        let activeStaffList = Array.isArray(activeStaffSeed) ? activeStaffSeed : [];
        const staffContactsSeed = analyticsEl ? JSON.parse(analyticsEl.getAttribute('data-staff-contacts') || '[]') : [];
        let staffContactsList = Array.isArray(staffContactsSeed) ? staffContactsSeed : [];
        // Chart instances (assigned after init so refresh can update them)
        let weeklyChart, catChart;

        // Weekly Tickets Chart
        const weeklyEl = document.getElementById('weeklyTicketsChart');
        if (weeklyEl) {
            weeklyChart = new Chart(weeklyEl, {
                type: 'bar',
                data: {
                    labels: weekLabels,
                    datasets: [{
                        label: 'Tickets',
                        data: weekData,
                        backgroundColor: '#3B82F6',
                        borderRadius: 6,
                        maxBarThickness: 28
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    scales: {
                        x: { grid: { display: false } },
                        y: { beginAtZero: true, ticks: { precision: 0 }, grid: { color: '#f1f5f9' } }
                    },
                    plugins: { legend: { display: false } }
                }
            });
        }

        // Tickets by Category Chart
        const catEl = document.getElementById('ticketCategoryChart');
        if (catEl) {
            const palette = ['#6366F1','#10B981','#F59E0B','#EF4444','#06B6D4','#84CC16','#F472B6','#FB7185'];
            const colors = categoryLabels.map((_, i) => palette[i % palette.length]);

            catChart = new Chart(catEl, {
                type: 'doughnut',
                data: {
                    labels: categoryLabels,
                    datasets: [{
                        data: categoryData,
                        backgroundColor: colors,
                        borderWidth: 1
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: { position: 'bottom' }
                    },
                    cutout: '60%'
                }
            });
        }

        // Auto-refresh admin dashboard data
        const fmt = new Intl.NumberFormat('en-US');

        // Background fetch data every 5 minutes (reduced since we have real-time updates)
        setInterval(() => {
            refreshAdminData();
        }, 300000);

        function updateCounts(payload) {
            const elOpen = document.getElementById('openTicketsCount');
            const elOpenDelta = document.getElementById('openTicketsDelta');
            const wrapOpen = document.getElementById('openTicketsDeltaWrap');

            const elFaq = document.getElementById('faqCountValue');
            const elFaqNew = document.getElementById('faqNewCount');
            const wrapFaq = document.getElementById('faqNewWrap');

            const elUser = document.getElementById('userCountValue');
            const elUserNew = document.getElementById('userNewCount');
            const wrapUser = document.getElementById('userNewWrap');

            const elLastTraining = document.getElementById('lastTrainingValue');

            if (elOpen) elOpen.textContent = fmt.format(payload.openTickets ?? 0);

            const d = Number(payload.openTicketsDelta ?? 0);
            if (wrapOpen) wrapOpen.style.display = d > 0 ? 'flex' : 'none';
            if (elOpenDelta) {
                const sign = d > 0 ? '+' : '';
                elOpenDelta.textContent = `${sign}${fmt.format(d)} from yesterday`;
            }

            if (elFaq) elFaq.textContent = fmt.format(payload.faqCount ?? 0);
            const fn = Number(payload.faqNewCount ?? 0);
            if (wrapFaq) wrapFaq.style.display = fn > 0 ? 'flex' : 'none';
            if (elFaqNew) elFaqNew.textContent = fmt.format(fn);

            if (elUser) elUser.textContent = fmt.format(payload.userCount ?? 0);
            const nu = Number(payload.newUsers ?? 0);
            if (wrapUser) wrapUser.style.display = nu > 0 ? 'flex' : 'none';
            if (elUserNew) {
                const signU = nu > 0 ? '+' : '';
                elUserNew.textContent = `${signU}${fmt.format(nu)} new users`;
            }

            if (elLastTraining) elLastTraining.textContent = payload.lastTraining ?? 'Never';
        }

        function updateRasaStatus() {
            const secretEl = document.getElementById('faq-updater-data');
            const secret = secretEl ? secretEl.getAttribute('data-secret') : '';
            const url = secretEl ? secretEl.getAttribute('data-url') : '';
            if (!secret || !url) {
                console.debug('No FAQ_UPDATER_SECRET or URL available');
                return;
            }

            fetch(url, {
                method: 'GET',
                headers: {
                    'X-FAQ-UPDATER-TOKEN': secret,
                    'Accept': 'application/json'
                }
            })
            .then(res => res.json())
            .then(data => {
                const statusText = document.getElementById('rasaStatusText');
                const statusIcon = document.getElementById('rasaStatusIcon');

                if (data.ok && data.running) {
                    if (statusText) statusText.textContent = 'Server Running';
                    if (statusIcon) {
                        statusIcon.className = 'rounded-md bg-emerald-50 p-2 text-emerald-600 border border-emerald-100';
                    }
                } else {
                    if (statusText) statusText.textContent = 'Server Offline';
                    if (statusIcon) {
                        statusIcon.className = 'rounded-md bg-red-50 p-2 text-red-600 border border-red-100';
                    }
                }
            })
            .catch(err => {
                console.debug('Failed to check Rasa status:', err);
                const statusText = document.getElementById('rasaStatusText');
                const statusIcon = document.getElementById('rasaStatusIcon');
                if (statusText) statusText.textContent = 'Check Failed';
                if (statusIcon) {
                    statusIcon.className = 'rounded-md bg-gray-50 p-2 text-gray-600 border border-gray-100';
                }
            });
        }

        // Click handler for Rasa Server Status
        document.getElementById('rasaStatusCard').addEventListener('click', async () => {
            const statusText = document.getElementById('rasaStatusText');
            if (statusText.textContent !== 'Server Offline') return;

            // Show loading state
            statusText.textContent = 'Starting...';

            try {
                const res = await fetch('{{ route("admin.document-changes.start-rasa-api") }}', {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': '{{ csrf_token() }}',
                        'X-Requested-With': 'XMLHttpRequest',
                        'Accept': 'application/json'
                    }
                });
                const data = await res.json();
                if (data.success) {
                    statusText.textContent = 'Server Running';
                    const statusIcon = document.getElementById('rasaStatusIcon');
                    if (statusIcon) {
                        statusIcon.className = 'rounded-md bg-emerald-50 p-2 text-emerald-600 border border-emerald-100';
                    }
                } else {
                    statusText.textContent = 'Start Failed';
                }
            } catch (e) {
                console.error('Failed to start Rasa server:', e);
                statusText.textContent = 'Start Failed';
            }
        });

        function updateTopSenders(payload) {
            const tbody = document.getElementById('topSendersBody');
            if (!tbody || !Array.isArray(payload.topSenders)) return;
            tbody.innerHTML = '';
            payload.topSenders.forEach((row, idx) => {
                const tr = document.createElement('tr');
                tr.className = 'hover:bg-gray-50';
                tr.innerHTML = `
                    <td class="py-3 pl-5 pr-3 align-top">${idx + 1}</td>
                    <td class="px-3 py-3 align-top"><div class="text-gray-900">${row.email || '—'}</div></td>
                    <td class="px-3 py-3 align-top"><span class="font-medium text-slate-900">${fmt.format(row.count || 0)}</span></td>
                `;
                tbody.appendChild(tr);
            });
            if (payload.topSenders.length === 0) {
                const tr = document.createElement('tr');
                tr.innerHTML = '<td colspan="3" class="px-5 py-10 text-center text-sm text-gray-500">No data.</td>';
                tbody.appendChild(tr);
            }
        }

        // Contacts aside rendering
        const contactsListEl = document.getElementById('contactsList');
        function initialsOf(name) {
            if (!name) return '?';
            const parts = String(name).trim().split(/\s+/).slice(0, 2);
            return parts.map(p => (p && p[0] ? p[0].toUpperCase() : '')).join('') || '?';
        }
        function escapeHtml(s) {
            return String(s || '')
                .replace(/&/g, '&')
                .replace(/</g, '<')
                .replace(/>/g, '>')
                .replace(/"/g, '"')
                .replace(/'/g, '&#039;');
        }
        function renderContacts(list) {
            // Use the initial staffContactsList for displaying active status
            // The list parameter from broadcast events may have incorrect active status
            const displayList = staffContactsList;
            if (!contactsListEl) return;
            const arr = Array.isArray(displayList) ? displayList.slice() : [];
            if (!arr.length) {
                contactsListEl.innerHTML = '<div class="p-3 text-xs text-slate-500">No staff found.</div>';
                return;
            }
            // Sort by active first, then name
            arr.sort((a, b) => {
                const aa = a.is_active ? 0 : 1;
                const bb = b.is_active ? 0 : 1;
                if (aa !== bb) return aa - bb;
                return String(a.name || '').localeCompare(String(b.name || ''));
            });
            const html = arr.map(u => {
                // Debug: log the is_active value
                console.log('Rendering contact:', u.name, 'is_active:', u.is_active, 'typeof:', typeof u.is_active);
                
                // More robust check for is_active - handle different data types
                const isActive = Boolean(u.is_active) ||
                                u.is_active === true ||
                                u.is_active === 'true' ||
                                u.is_active === 1 ||
                                u.is_active === '1' ||
                                u.is_active === '1.0' ||
                                u.is_active === 'yes' ||
                                u.is_active === 'YES' ||
                                u.is_active === 'Yes' ||
                                (typeof u.is_active === 'string' && u.is_active.toLowerCase() === 'true') ||
                                (typeof u.is_active === 'number' && u.is_active > 0);
                const dot = isActive ? 'bg-emerald-500' : 'bg-slate-300';
                
                // Debug logging for active status
                console.log('Processing user:', u.name, 'is_active:', u.is_active, 'type:', typeof u.is_active, 'isActive:', isActive, 'dot class:', dot);
                
                console.log('Final isActive check:', isActive, 'dot class:', dot);
                
                const initials = initialsOf(u.name);
                const name = escapeHtml(u.name);
                const email = escapeHtml(u.email);
                return `
                <div class="flex items-center gap-3 px-2 py-2 rounded-md hover:bg-gray-50">
                  <div class="relative">
                    <div class="w-8 h-8 rounded-full bg-slate-100 text-slate-700 flex items-center justify-center text-xs font-semibold">${initials}</div>
                    <span class="absolute -bottom-0 -right-0 w-2.5 h-2.5 rounded-full ring-2 ring-white ${dot}"></span>
                  </div>
                  <div class="min-w-0 flex-1">
                    <div class="text-sm text-slate-900 truncate">${name}</div>
                    <div class="text-xs text-slate-500 truncate">${email}</div>
                  </div>
                </div>`;
            }).join('');
            contactsListEl.innerHTML = html;
        }
        // Initial render
        renderContacts(staffContactsList);

        // Helpers for lists rendering
        const adminPad = (num, size = 4) => {
            num = String(num ?? '');
            while (num.length < size) num = '0' + num;
            return num;
        };
        function adminFmtDate(d) {
            try {
                const dt = new Date(d);
                if (isNaN(dt.getTime())) return '';
                // yyyy-mm-dd hh:mm am/pm
                const yyyy = dt.getFullYear();
                const mm = String(dt.getMonth() + 1).padStart(2, '0');
                const dd = String(dt.getDate()).padStart(2, '0');
                let hours = dt.getHours();
                const minutes = String(dt.getMinutes()).padStart(2, '0');
                const ampm = hours >= 12 ? 'pm' : 'am';
                hours = hours % 12;
                if (hours === 0) hours = 12;
                const hh = String(hours).padStart(2, '0');
                return `${yyyy}-${mm}-${dd} ${hh}:${minutes} ${ampm}`;
            } catch (_) { return ''; }
        }
        function adminBadgeClass(status) {
            switch (status) {
                case 'Open': return 'text-blue-700 bg-blue-50 ring-blue-600/20';
                case 'Forwarded': return 'text-amber-700 bg-amber-50 ring-amber-600/20';
                case 'Closed': return 'text-emerald-700 bg-emerald-50 ring-emerald-600/20';
                default: return 'text-slate-700 bg-slate-50 ring-slate-600/20';
            }
        }
        function updateOpenList(list) {
            const tbody = document.getElementById('openListBody');
            if (!tbody) return;
            const rows = Array.isArray(list) ? list.map(t => {
                const ticketNo = String(t.id);
                const createdAt = adminFmtDate(t.date_created || t.created_at);
                const email = t.email || '—';
                const category = t.category || '';
                const badge = adminBadgeClass(t.status);
                return `
                <tr class="hover:bg-gray-50">
                    <td class="py-3 pl-5 pr-3 align-top">
                        <div class="text-indigo-700 font-medium">${ticketNo}</div>
                        <div class="mt-1 text-xs text-gray-500">${createdAt}</div>
                    </td>
                    <td class="px-3 py-3 align-top">
                        <div class="text-gray-900">${email}</div>
                        <div class="text-xs text-gray-500">${category}</div>
                    </td>
                    <td class="px-3 py-3 align-top">
                        <span class="inline-flex items-center gap-1 rounded-full px-2.5 py-1 text-xs font-medium ring-1 ${badge}">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-3.5 w-3.5" viewBox="0 0 24 24" fill="currentColor"><circle cx="12" cy="12" r="5"></circle></svg>
                            ${t.status ?? ''}
                        </span>
                    </td>
                    <td class="py-3 pl-3 pr-5 align-top">
                        <button type="button" data-id="${t.id}" class="btn-view inline-flex items-center gap-2 rounded-lg border border-gray-200 bg-white px-3 py-1.5 text-xs font-medium text-gray-700 hover:bg-gray-50">
                            View
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-3.5 w-3.5" viewBox="0 0 24 24" fill="currentColor"><path d="M9 5l7 7-7 7" /></svg>
                        </button>
                    </td>
                </tr>`;
            }) : [];
            tbody.innerHTML = rows.length ? rows.join('') : `<tr><td colspan="4" class="px-5 py-10 text-center text-sm text-gray-500">No open tickets.</td></tr>`;
        }
        function updateMyTicketsList(list) {
            const tbody = document.getElementById('myTicketsListBody');
            if (!tbody) return;
            const rows = Array.isArray(list) ? list.map(t => {
                const ticketNo = String(t.id);
                const updatedAt = adminFmtDate(t.updated_at || t.date_created || t.created_at);
                const email = t.email || '—';
                const staffName = t.staff && t.staff.name ? `Staff: ${t.staff.name}` : '';
                const badge = adminBadgeClass(t.status);
                return `
                <tr class="hover:bg-gray-50 cursor-pointer btn-view" data-id="${t.id}">
                    <td class="py-3 pl-5 pr-3 align-top">
                        <div class="text-indigo-700 font-medium">${ticketNo}</div>
                        <div class="mt-1 text-xs text-gray-500">Updated ${updatedAt}</div>
                    </td>
                    <td class="px-3 py-3 align-top">
                        <div class="text-gray-900">${email}</div>
                        <div class="text-xs text-gray-500">${staffName}</div>
                    </td>
                    <td class="px-3 py-3 align-top">
                        <span class="inline-flex items-center gap-1 rounded-full px-2.5 py-1 text-xs font-medium ring-1 ${badge}">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-3.5 w-3.5" viewBox="0 0 24 24" fill="currentColor"><circle cx="12" cy="12" r="5"></circle></svg>
                            ${t.status ?? ''}
                        </span>
                    </td>
                </tr>`;
            }) : [];
            tbody.innerHTML = rows.length ? rows.join('') : `<tr><td colspan="3" class="px-5 py-10 text-center text-sm text-gray-500">No tickets assigned to you.</td></tr>`;
        }
        async function refreshAdminData() {
            const url = analyticsEl ? analyticsEl.getAttribute('data-admin-url') : null;
            if (!url) return;
            try {
                const res = await fetch(url, {
                    method: 'GET',
                    headers: { 'Accept': 'application/json' },
                    credentials: 'same-origin',
                    cache: 'no-store'
                });
                if (!res.ok) return;
                const data = await res.json();

                // Update metrics
                updateCounts(data);

                // Update weekly chart
                if (weeklyChart) {
                    weeklyChart.data.labels = data.weekLabels || [];
                    weeklyChart.data.datasets[0].data = data.weekData || [];
                    weeklyChart.update();
                }

                // Update category chart
                if (catChart) {
                    catChart.data.labels = data.categoryLabels || [];
                    const palette = ['#6366F1','#10B981','#F59E0B','#EF4444','#06B6D4','#84CC16','#F472B6','#FB7185'];
                    catChart.data.datasets[0].data = data.categoryData || [];
                    catChart.data.datasets[0].backgroundColor = (data.categoryLabels || []).map((_, i) => palette[i % palette.length]);
                    catChart.update();
                }

                // Update top senders table and lists
                updateTopSenders(data);
                updateOpenList(data.openList || []);
                updateMyTicketsList(data.myTicketsList || []);

                // Update right-side contacts
                // Note: We do NOT update the contact drawer with API data as it may have incorrect active status
                // The contact drawer should only use the initial staffContactsList with correct active status
                if (Array.isArray(data.staffContacts)) {
                    staffContactsList = data.staffContacts;
                    // DO NOT call renderContacts here - this was causing the second incorrect render
                    // The contact drawer uses the initial staffContactsList which has correct active status
                }

                // Update Rasa status
                updateRasaStatus();
            } catch (e) {
                // swallow errors to avoid UI disruption
                console.debug('Admin auto-refresh failed', e);
            }
        }

        // Document Training Alert Management
        async function trainRasa() {
            const btn = document.getElementById('trainRasaBtn');
            const spinner = document.getElementById('trainSpinner');
            const btnText = document.getElementById('trainBtnText');

            // Show loading state
            btn.disabled = true;
            spinner.classList.remove('hidden');
            btnText.textContent = 'Training...';

            try {
                const csrf = '{{ csrf_token() }}';
                const res = await fetch('{{ route("admin.document-changes.train-rasa") }}', {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': csrf,
                        'X-Requested-With': 'XMLHttpRequest'
                    }
                });

                const data = await res.json();

                if (!res.ok || !data.success) {
                    throw new Error(data.message || 'Training failed');
                }

                // Show success message
                Swal.fire({
                    toast: true,
                    position: 'top-end',
                    icon: 'success',
                    title: 'Rasa training completed successfully!',
                    showConfirmButton: false,
                    timer: 3000,
                    timerProgressBar: true
                });

                // Hide the training alert
                document.getElementById('trainingAlert').classList.add('hidden');

            } catch (err) {
                console.error('[DEBUG] Training error:', err);
                Swal.fire({
                    icon: 'error',
                    title: 'Training Failed',
                    text: `Training failed: ${err.message}`,
                    confirmButtonText: 'OK'
                });
            } finally {
                // Reset button state
                btn.disabled = false;
                spinner.classList.add('hidden');
                btnText.textContent = 'Train Rasa';
            }
        }

        async function checkTrainingStatus() {
            try {
                const res = await fetch('{{ route("admin.document-changes.training-status") }}', {
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest'
                    }
                });

                if (res.ok) {
                    const data = await res.json();
                    const alertEl = document.getElementById('trainingAlert');

                    if (data.requires_training) {
                        // Show training alert
                        alertEl.classList.remove('hidden');
                    } else {
                        // Hide training alert
                        alertEl.classList.add('hidden');
                    }
                }
            } catch (err) {
                console.error('[DEBUG] Error checking training status:', err);
            }
        }

        // Add event listener for train button
        const trainBtn = document.getElementById('trainRasaBtn');
        if (trainBtn) {
            trainBtn.addEventListener('click', trainRasa);
        }

        // Check training status on page load
        checkTrainingStatus();

        // Listen for real-time active staff updates
        if (typeof Echo !== 'undefined') {
            Echo.channel('active-staff').listen('.active-staff.updated', (e) => {
                console.log('Active staff count received from broadcast event:', e.count);
                console.log('Staff contacts data:', e.staffContacts);
                
                const dot = document.getElementById('activeStaffDot');
                const countText = document.getElementById('activeStaffCountText');
                if (dot) {
                    if (e.count > 0) {
                        dot.classList.remove('hidden');
                        // Ensure it's green when there are active staff
                        dot.className = 'w-4 h-4 rounded-full bg-green-500';
                        console.log('Green dot shown');
                    } else {
                        dot.classList.add('hidden');
                        console.log('Green dot hidden');
                    }
                }
                if (countText) {
                    countText.textContent = e.count;
                }

                // DO NOT update the contact drawer with broadcast data
                // The broadcast event data may have incorrect active status
                // Keep using the initial staffContactsList for contact drawer rendering
                // to maintain correct active status display
            });
        }

        // Initial fetch (once) - polling disabled to avoid overloading the database.
        // The dashboard will only refresh when a CRUD operation signals a change
        // via localStorage (key: 'ts_tickets_changed') or when the user focuses the tab
        // and a change has been recorded.
        setTimeout(() => {
            refreshAdminData();
            updateRasaStatus();
            
            // Ensure green dot is shown if there are active staff initially
            const initialActiveCount = parseInt(document.getElementById('activeStaffCountText')?.textContent || '0');
            const initialDot = document.getElementById('activeStaffDot');
            if (initialDot && initialActiveCount > 0) {
                initialDot.classList.remove('hidden');
                initialDot.className = 'w-4 h-4 rounded-full bg-green-500';
            }
        }, 250);
    
        // Refresh on tab focus only if a change was recorded by another tab/window
        window.addEventListener('focus', () => {
            try {
                if (localStorage.getItem('ts_tickets_changed')) refreshAdminData();
            } catch (_) {}
        });
    
        // Refresh on visibility change only if a change was recorded
        document.addEventListener('visibilitychange', () => {
            try {
                if (!document.hidden && localStorage.getItem('ts_tickets_changed')) refreshAdminData();
            } catch (_) {}
        });
    
        // Cross-tab notification: when other tabs perform CRUD they should set
        // localStorage.ts_tickets_changed to notify this tab to refresh.
        window.addEventListener('storage', (e) => {
            if (e && e.key === 'ts_tickets_changed') {
                refreshAdminData();
            }
        });
    })();
</script>

<!-- Toggle Contacts Aside via Active Staff card -->
<script>
  (function () {
    const activeCard = document.getElementById('activeStaffCard');
    const contactsAside = document.getElementById('contacts-aside');
    const content = document.getElementById('content-wrapper');

    if (!activeCard || !contactsAside || !content) return;

    function isShown() {
      return !contactsAside.classList.contains('hidden');
    }
    function showAside() {
      contactsAside.classList.remove('hidden');
      contactsAside.classList.add('flex');        // ensure flex layout when visible
      content.classList.add('sm:mr-72');          // reserve space on right for >= sm
    }
    function hideAside() {
      contactsAside.classList.add('hidden');
      contactsAside.classList.remove('flex');
      content.classList.remove('sm:mr-72');
    }
    function toggleAside() {
      if (isShown()) {
        console.log('Hiding contacts aside');
        hideAside();
      } else {
        console.log('Showing contacts aside');
        showAside();
      }
    }

    // Click to toggle
    activeCard.addEventListener('click', () => {
      console.log('Active Staff card clicked, toggling aside');
      toggleAside();
    });
    // Keyboard support (Enter/Space)
    activeCard.addEventListener('keydown', (e) => {
      if (e.key === 'Enter' || e.key === ' ') {
        e.preventDefault();
        console.log('Active Staff card keyboard activated, toggling aside');
        toggleAside();
      }
    });
  })();
</script>

<!-- Sidebar collapse/expand for mobile + desktop -->
<script>
  (function () {
    const toggleBtn = document.getElementById('sidebar-toggle');
    const sidebar = document.getElementById('default-sidebar');
    const content = document.getElementById('content-wrapper');
    const backdrop = document.getElementById('sidebar-backdrop');

    if (!toggleBtn || !sidebar || !content) return;

    const mq = window.matchMedia('(max-width: 639.98px)'); // Tailwind sm breakpoint

    function isMobile() {
      return mq.matches;
    }

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

    // Ensure correct state on resize
    mq.addEventListener('change', () => {
      if (!isMobile()) {
        // Leaving mobile: hide backdrop and ensure desktop-open by default
        sidebar.classList.remove('translate-x-0');
        sidebar.classList.add('-translate-x-full'); // keep base mobile hidden
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
<script>
(function(){
  const ticketModal = document.getElementById('ticketModal');
  const modalBackdrop = ticketModal ? ticketModal.querySelector('[data-modal-backdrop]') : null;
  const modalCloseBtns = ticketModal ? ticketModal.querySelectorAll('[data-modal-close]') : [];
  const tmTicketNo = document.getElementById('tmTicketNo');
  const tmStatus = document.getElementById('tmStatus');
  const tmQuestion = document.getElementById('tmQuestion');
  const tmResponse = document.getElementById('tmResponse');
  const tmCategory = document.getElementById('tmCategory');
  const tmDates = document.getElementById('tmDates');
  const tmEmail = document.getElementById('tmEmail');
  const tmRecepient = document.getElementById('tmRecepient');
  const tmStoredResponseBlock = document.getElementById('tmStoredResponseBlock');
  const tmStoredResponse = document.getElementById('tmStoredResponse');
  const tmSendResponse = document.getElementById('tmSendResponse');
  const tmOptionsBtn = document.getElementById('tmOptionsBtn');
  const tmOptionsMenu = document.getElementById('tmOptionsMenu');
  const tmOptionForward = document.getElementById('tmOptionForward');
  const tmForwardControls = document.getElementById('tmForwardControls');
  const tmForwardSelect = document.getElementById('tmForwardSelect');
  const tmForwardApply = document.getElementById('tmForwardApply');
  
  const csrfToken = '{{ csrf_token() }}';
  const forwardBase = "{{ url('/admin/tickets') }}";
  let currentTicketId = null;
  let currentIsAssigning = false;

  const statusStyles = {
    'Open': 'text-blue-700 bg-blue-50 ring-blue-600/20',
    'Forwarded': 'text-amber-700 bg-amber-50 ring-amber-600/20',
    'Closed': 'text-emerald-700 bg-emerald-50 ring-emerald-600/20',
  };

  function statusClassFor(s) {
    return statusStyles[s] || 'text-slate-700 bg-slate-50 ring-slate-600/20';
  }

  function fmtDate(d) {
    try {
      const dt = new Date(d);
      if (isNaN(dt.getTime())) return '';
      return dt.toLocaleString();
    } catch (_) {
      return '';
    }
  }

  function escapeHtml(s){ if (s==null) return ''; return String(s).replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;').replace(/"/g,'&quot;').replace(/'/g,"&#039;"); }

  function ensureHistorySection() {
    let section = document.getElementById('tmHistorySection');
    const list = document.getElementById('tmHistoryList');
    return { section, list };
  }

  function renderHistory(histArr) {
    const { section, list } = ensureHistorySection();
    if (!section || !list) return;
    if (!Array.isArray(histArr) || histArr.length === 0) {
      list.innerHTML = '<li class="text-xs text-gray-500">No routing history.</li>';
      return;
    }
    const items = histArr.map(h => {
      const when = fmtDate(h.routed_at || h.created_at);
      const who = (h.staff && h.staff.name) ? h.staff.name : '-';
      const status = h.status || '';
      const notes = h.notes || '';
      return `
        <li class="text-xs text-gray-700">
          <div class="flex items-start justify-between">
            <div>
              <span class="font-medium">${status}</span>
              <span class="text-gray-500"> • ${who}</span>
            </div>
            <div class="text-gray-500">${when}</div>
          </div>
          ${notes ? `<div class="text-gray-600 mt-0.5">${notes}</div>` : ''}
        </li>
      `;
    });
    list.innerHTML = items.join('');
  }

  async function loadAndShowTicket(id){
    currentTicketId = id;
    if (!id) return;
    const url = "{{ url('/admin/tickets') }}/" + encodeURIComponent(id);
    try {
      const res = await fetch(url, { headers: { 'X-Requested-With': 'XMLHttpRequest' }, credentials: 'same-origin' });
      if (!res.ok) {
        console.error('Dashboard: failed to load ticket', res.status);
        return;
      }
      const t = await res.json();
      if (!t) return;

      const ticketNo = String(t.id);
      const createdAt = fmtDate(t.date_created || t.created_at);
      const updatedAt = fmtDate(t.updated_at);
      const category = t.category ?? '';
      const question = t.question ?? '';
      const email = t.email ?? '';
      const recepient = t.recepient_id ?? '';

      // Fill fields
      if (tmTicketNo) tmTicketNo.textContent = 'Ticket #' + ticketNo;
      if (tmDates) tmDates.textContent = createdAt ? `Created ${createdAt}${updatedAt ? ' • Updated ' + updatedAt : ''}` : '';
      if (tmStatus) {
        tmStatus.className = 'inline-flex items-center gap-1.5 rounded-full px-3 py-1 text-xs font-medium ring-1 ' + statusClassFor(t.status);
        tmStatus.innerHTML = `<svg xmlns="http://www.w3.org/2000/svg" class="h-3.5 w-3.5" viewBox="0 0 24 24" fill="currentColor"><circle cx="12" cy="12" r="5"></circle></svg> ${t.status ?? ''}`;
      }
      if (tmCategory) tmCategory.textContent = category;
      if (tmQuestion) tmQuestion.textContent = question;
      if (tmEmail) tmEmail.textContent = email;
      if (tmRecepient) tmRecepient.textContent = recepient;
      if (tmResponse) tmResponse.value = '';

      // Reset details section to collapsed state
      const detailsContent = document.getElementById('tmDetailsContent');
      const detailsChevron = document.getElementById('tmDetailsChevron');
      const toggleDetailsBtn = document.getElementById('tmToggleDetails');
      if (detailsContent) detailsContent.classList.add('hidden');
      if (detailsChevron) detailsChevron.style.transform = 'rotate(0deg)';
      if (toggleDetailsBtn) toggleDetailsBtn.querySelector('span').textContent = 'Show Details';

      // Handle attachments
      const attachmentsBlock = document.getElementById('tmAttachmentsBlock');
      const attachmentsList = document.getElementById('tmAttachmentsList');
      if (attachmentsBlock && attachmentsList) {
        attachmentsList.innerHTML = '';
        if (t.attachments) {
          let attachments = [];
          try {
            attachments = JSON.parse(t.attachments);
          } catch (e) {
            attachments = [];
          }
          if (attachments.length > 0) {
            attachments.forEach((path, index) => {
              const img = document.createElement('img');
              img.src = '/storage/' + path;
              img.alt = 'Attachment ' + (index + 1);
              img.className = 'max-w-16 max-h-16 object-cover rounded cursor-pointer border border-gray-300 hover:border-indigo-400';
              img.onclick = () => openLightbox(attachments, index);
              attachmentsList.appendChild(img);
            });
            attachmentsBlock.classList.remove('hidden');
          } else {
            attachmentsBlock.classList.add('hidden');
          }
        } else {
          attachmentsBlock.classList.add('hidden');
        }
      }

      // Hide forward controls initially
      if (tmForwardControls) tmForwardControls.classList.add('hidden');

      // Prepare and render history; keep hidden by default until toggled in Options
      const hsObj = ensureHistorySection();
      if (hsObj.section) hsObj.section.classList.add('hidden');
      const histories = t.routing_histories || t.routingHistories || [];
      renderHistory(Array.isArray(histories) ? histories : []);

      // Toggle forward option and response display based on status
      const isClosed = (t.status === 'Closed');
      const hasStaff = t.staff && t.staff.name;
      currentIsAssigning = !hasStaff;
      if (tmOptionForward) {
        tmOptionForward.classList.toggle('hidden', isClosed);
        tmOptionForward.textContent = hasStaff ? 'Forward Ticket' : 'Assign to a Staff';
      }
      if (tmForwardControls) tmForwardControls.classList.add('hidden');

      // Update labels based on assignment status
      const forwardLabel = document.querySelector('label[for="tmForwardSelect"]');
      if (forwardLabel) forwardLabel.textContent = hasStaff ? 'Forward to:' : 'Assign to:';
      if (tmForwardApply) tmForwardApply.textContent = hasStaff ? 'Forward Ticket' : 'Assign';
      if (tmStoredResponseBlock) {
        if (isClosed) {
          tmStoredResponseBlock.classList.remove('hidden');
          if (tmStoredResponse) tmStoredResponse.textContent = t.response ? String(t.response) : 'No response on record.';
        } else {
          tmStoredResponseBlock.classList.add('hidden');
          if (tmStoredResponse) tmStoredResponse.textContent = '';
        }
      }
      if (tmResponse) {
        tmResponse.disabled = isClosed;
        tmResponse.placeholder = isClosed ? 'Ticket is closed. Response cannot be edited.' : 'Type your response message here...';
      }
      if (tmSendResponse) {
        tmSendResponse.disabled = isClosed;
        tmSendResponse.classList.toggle('opacity-50', isClosed);
        tmSendResponse.classList.toggle('pointer-events-none', isClosed);
      }

      // Populate forward select with users
      if (tmForwardSelect && t.users) {
        tmForwardSelect.innerHTML = '<option value="" selected disabled>Select user</option>';
        t.users.forEach(user => {
          const option = document.createElement('option');
          option.value = user.id;
          option.textContent = user.name;
          tmForwardSelect.appendChild(option);
        });
      }

      if (ticketModal) {
        ticketModal.classList.remove('hidden');
        document.body.classList.add('overflow-hidden');
      }
    } catch (err) {
      console.error('Dashboard: error loading ticket', err);
    }
  }

  function closeModal() {
    if (!ticketModal) return;
    ticketModal.classList.add('hidden');
    document.body.classList.remove('overflow-hidden');
    currentTicketId = null;
  }

  // Lightbox functions
  let currentLightboxImages = [];
  let currentLightboxIndex = 0;

  function openLightbox(images, index) {
    currentLightboxImages = images;
    currentLightboxIndex = index;
    const lightbox = document.getElementById('imageLightbox');
    const img = document.getElementById('lightboxImage');
    if (lightbox && img) {
      img.src = '/storage/' + images[index];
      lightbox.classList.remove('hidden');
      document.body.classList.add('overflow-hidden');
      updateLightboxButtons();
    }
  }

  function closeLightbox() {
    const lightbox = document.getElementById('imageLightbox');
    if (lightbox) {
      lightbox.classList.add('hidden');
      document.body.classList.remove('overflow-hidden');
    }
  }

  function updateLightboxButtons() {
    const prevBtn = document.getElementById('lightboxPrev');
    const nextBtn = document.getElementById('lightboxNext');
    if (prevBtn) prevBtn.style.display = currentLightboxIndex > 0 ? 'flex' : 'none';
    if (nextBtn) nextBtn.style.display = currentLightboxIndex < currentLightboxImages.length - 1 ? 'flex' : 'none';
  }

  function prevImage() {
    if (currentLightboxIndex > 0) {
      currentLightboxIndex--;
      const img = document.getElementById('lightboxImage');
      if (img) img.src = '/storage/' + currentLightboxImages[currentLightboxIndex];
      updateLightboxButtons();
    }
  }

  function nextImage() {
    if (currentLightboxIndex < currentLightboxImages.length - 1) {
      currentLightboxIndex++;
      const img = document.getElementById('lightboxImage');
      if (img) img.src = '/storage/' + currentLightboxImages[currentLightboxIndex];
      updateLightboxButtons();
    }
  }

  // Open modal for "View" buttons in Open Tickets and My Tickets tables
  document.addEventListener('click', function (e) {
    const btn = e.target && e.target.closest ? e.target.closest('.btn-view') : null;
    if (!btn) return;
    const id = btn.getAttribute('data-id') || btn.dataset.id;
    if (!id) return;
    loadAndShowTicket(id);
  });

  // Close modal interactions
  if (modalBackdrop) modalBackdrop.addEventListener('click', closeModal);
  if (modalCloseBtns && modalCloseBtns.length) {
    modalCloseBtns.forEach(btn => btn.addEventListener('click', closeModal));
  }
  document.addEventListener('keydown', (e) => {
    if (e.key === 'Escape' && ticketModal && !ticketModal.classList.contains('hidden')) {
      closeModal();
    }
  });

  // Toggle Details Section
  const tmToggleDetails = document.getElementById('tmToggleDetails');
  const tmDetailsContent = document.getElementById('tmDetailsContent');
  const tmDetailsChevron = document.getElementById('tmDetailsChevron');
  
  if (tmToggleDetails && tmDetailsContent && tmDetailsChevron) {
    tmToggleDetails.addEventListener('click', () => {
      const isHidden = tmDetailsContent.classList.contains('hidden');
      tmDetailsContent.classList.toggle('hidden');
      tmDetailsChevron.style.transform = isHidden ? 'rotate(180deg)' : 'rotate(0deg)';
      tmToggleDetails.querySelector('span').textContent = isHidden ? 'Hide Details' : 'Show Details';
    });
  }

  // Options dropdown
  if (tmOptionsBtn && tmOptionsMenu) {
    tmOptionsBtn.addEventListener('click', (e) => {
      e.stopPropagation();
      const isOpen = !tmOptionsMenu.classList.contains('hidden');
      tmOptionsMenu.classList.toggle('hidden', isOpen);
      tmOptionsBtn.setAttribute('aria-expanded', String(!isOpen));
    });

    document.addEventListener('click', (e) => {
      if (!tmOptionsMenu.contains(e.target) && !tmOptionsBtn.contains(e.target)) {
        tmOptionsMenu.classList.add('hidden');
        tmOptionsBtn.setAttribute('aria-expanded', 'false');
      }
    });

    tmOptionsMenu.addEventListener('click', (e) => {
      const btn = e.target.closest('[data-option]');
      if (!btn) return;
      const action = btn.getAttribute('data-option');

      // Hide menu after action
      tmOptionsMenu.classList.add('hidden');
      tmOptionsBtn.setAttribute('aria-expanded', 'false');

      if (action === 'toggle-history') {
        const hs = ensureHistorySection().section;
        if (hs) {
          const willShow = hs.classList.contains('hidden');
          hs.classList.toggle('hidden');
          btn.textContent = willShow ? 'Hide History' : 'Show History';
        }
      } else if (action === 'show-forward') {
        if (tmForwardControls) tmForwardControls.classList.remove('hidden');
      }
    });
  }

  // Forward via select + apply
  if (tmForwardApply && tmForwardSelect) {
    tmForwardApply.addEventListener('click', async () => {
      if (!currentTicketId) return;
      if (!tmForwardSelect.value) {
        Swal.fire({
          icon: 'warning',
          title: 'Selection Required',
          text: 'Please choose a user to forward to.',
          confirmButtonText: 'OK'
        });
        return;
      }
      const userId = tmForwardSelect.value;
      const originalButtonText = tmForwardApply.textContent;
      try {
        tmForwardApply.disabled = true;
        tmForwardApply.innerHTML = '<svg class="animate-spin h-4 w-4 mr-2" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg> Processing...';
        const res = await fetch(`${forwardBase}/${currentTicketId}/forward`, {
          method: 'POST',
          headers: {
            'Accept': 'application/json',
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': csrfToken
          },
          credentials: 'same-origin',
          body: JSON.stringify({ user_id: userId })
        });
        console.log('Forward request sent to:', `${forwardBase}/${currentTicketId}/forward`);
        console.log('Response status:', res.status, res.statusText);
        if (res.ok) {
          const data = await res.json();
          console.log('Forward successful:', data);
          Swal.fire({
            icon: 'success',
            title: currentIsAssigning ? 'Ticket Assigned' : 'Ticket Forwarded',
            text: currentIsAssigning ? 'Ticket has been assigned successfully!' : 'Ticket has been forwarded successfully!',
            timer: 3000,
            timerProgressBar: true,
            showConfirmButton: false,
            position: 'top-end',
            toast: true
          });
          closeModal();
          // Refresh dashboard data
          if (typeof refreshAdminData === 'function') refreshAdminData();
        } else {
          const errorText = await res.text();
          console.error('Forward failed', res.status, errorText);
          Swal.fire({
            icon: 'error',
            title: currentIsAssigning ? 'Assign Failed' : 'Forward Failed',
            text: (currentIsAssigning ? 'Failed to assign ticket. ' : 'Failed to forward ticket. ') + 'Please try again. Error: ' + res.status + ' ' + res.statusText,
            confirmButtonText: 'OK'
          });
        }
      } catch (err) {
        console.error('Forward error', err);
        alert('Network error during forward.');
      } finally {
        tmForwardApply.disabled = false;
        tmForwardApply.innerHTML = originalButtonText;
      }
    });
  }

  // Lightbox event listeners
  const lightboxCloseBtn = document.getElementById('lightboxClose');
  const lightboxPrevBtn = document.getElementById('lightboxPrev');
  const lightboxNextBtn = document.getElementById('lightboxNext');
  const lightboxEl = document.getElementById('imageLightbox');

  if (lightboxCloseBtn) lightboxCloseBtn.addEventListener('click', closeLightbox);
  if (lightboxPrevBtn) lightboxPrevBtn.addEventListener('click', prevImage);
  if (lightboxNextBtn) lightboxNextBtn.addEventListener('click', nextImage);

  // Close lightbox on background click
  if (lightboxEl) {
    lightboxEl.addEventListener('click', (e) => {
      if (e.target === lightboxEl) closeLightbox();
    });
  }

  // Keyboard navigation for lightbox
  document.addEventListener('keydown', (e) => {
    if (lightboxEl && !lightboxEl.classList.contains('hidden')) {
      if (e.key === 'Escape') closeLightbox();
      else if (e.key === 'ArrowLeft') prevImage();
      else if (e.key === 'ArrowRight') nextImage();
    }
  });

  // Send response (email via backend)
  if (tmSendResponse && tmResponse) {
    tmSendResponse.addEventListener('click', async () => {
      const msg = tmResponse.value.trim();
      if (!msg) {
        Swal.fire({
          icon: 'warning',
          title: 'Message Required',
          text: 'Please enter a response message.',
          confirmButtonText: 'OK'
        });
        return;
      }
      if (!currentTicketId) {
        Swal.fire({
          icon: 'error',
          title: 'No Ticket Selected',
          text: 'No ticket selected.',
          confirmButtonText: 'OK'
        });
        return;
      }
      try {
        tmSendResponse.disabled = true;
        tmSendResponse.classList.add('opacity-50', 'pointer-events-none');
        const res = await fetch(`${forwardBase}/${currentTicketId}/respond`, {
          method: 'POST',
          headers: {
            'Accept': 'application/json',
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': csrfToken
          },
          credentials: 'same-origin',
          body: JSON.stringify({ message: msg })
        });
        if (res.ok) {
          Swal.fire({
            icon: 'success',
            title: 'Response Sent',
            text: 'Response email sent successfully!',
            timer: 3000,
            timerProgressBar: true,
            showConfirmButton: false,
            position: 'top-end',
            toast: true
          });
          tmResponse.value = '';
          closeModal();
          // Refresh dashboard data
          if (typeof refreshAdminData === 'function') refreshAdminData();
        } else {
          const txt = await res.text();
          console.error('Send response failed', txt);
          Swal.fire({
            icon: 'error',
            title: 'Failed to Send Response',
            text: 'Failed to send response. Please check mail configuration.',
            confirmButtonText: 'OK'
          });
        }
      } catch (err) {
        console.error('Send response error', err);
        alert('Network error while sending response.');
      } finally {
        tmSendResponse.disabled = false;
        tmSendResponse.classList.remove('opacity-50', 'pointer-events-none');
      }
    });
  }
})();
</script>

<!-- User Management Dropdown Script -->
<script>
(function () {
  const dropdownBtn = document.getElementById('userManagementDropdown');
  const dropdownMenu = document.getElementById('userManagementMenu');
  const chevron = document.getElementById('userManagementChevron');

  if (!dropdownBtn || !dropdownMenu || !chevron) return;

  dropdownBtn.addEventListener('click', function (e) {
    e.preventDefault();

    const isOpen = !dropdownMenu.classList.contains('hidden');

    // Toggle menu visibility
    dropdownMenu.classList.toggle('hidden', isOpen);

    // Update aria-expanded
    dropdownBtn.setAttribute('aria-expanded', String(!isOpen));

    // Rotate chevron
    chevron.style.transform = isOpen ? 'rotate(0deg)' : 'rotate(90deg)';
  });

  // Close dropdown when clicking outside
  document.addEventListener('click', function (e) {
    if (!dropdownBtn.contains(e.target) && !dropdownMenu.contains(e.target)) {
      dropdownMenu.classList.add('hidden');
      dropdownBtn.setAttribute('aria-expanded', 'false');
      chevron.style.transform = 'rotate(0deg)';
    }
  });
})();
</script>

<!-- FAQ Management Dropdown Script -->
<script>
(function () {
  const dropdownBtn = document.getElementById('faqManagementDropdown');
  const dropdownMenu = document.getElementById('faqManagementMenu');
  const chevron = document.getElementById('faqManagementChevron');

  if (!dropdownBtn || !dropdownMenu || !chevron) return;

  dropdownBtn.addEventListener('click', function (e) {
    e.preventDefault();

    const isOpen = !dropdownMenu.classList.contains('hidden');

    // Toggle menu visibility
    dropdownMenu.classList.toggle('hidden', isOpen);

    // Update aria-expanded
    dropdownBtn.setAttribute('aria-expanded', String(!isOpen));

    // Rotate chevron
    chevron.style.transform = isOpen ? 'rotate(0deg)' : 'rotate(90deg)';
  });

  // Close dropdown when clicking outside
  document.addEventListener('click', function (e) {
    if (!dropdownBtn.contains(e.target) && !dropdownMenu.contains(e.target)) {
      dropdownMenu.classList.add('hidden');
      dropdownBtn.setAttribute('aria-expanded', 'false');
      chevron.style.transform = 'rotate(0deg)';
    }
  });
})();
</script>


@endsection
