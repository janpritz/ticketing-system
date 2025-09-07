@extends('layouts.app')

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
        <a href="#" class="flex items-center p-2 text-gray-900 rounded-lg hover:bg-gray-100 group">
          <svg class="w-5 h-5 text-gray-500 transition duration-75 group-hover:text-gray-900" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="currentColor" viewBox="0 0 18 18">
            <path d="M6.143 0H1.857A1.857 1.857 0 0 0 0 1.857v4.286C0 7.169.831 8 1.857 8h4.286A1.857 1.857 0 0 0 8 6.143V1.857A1.857 1.857 0 0 0 6.143 0Zm10 0h-4.286A1.857 1.857 0 0 0 10 1.857v4.286C10 7.169 10.831 8 11.857 8h4.286A1.857 1.857 0 0 0 18 6.143V1.857A1.857 1.857 0 0 0 16.143 0Zm-10 10H1.857A1.857 1.857 0 0 0 0 11.857v4.286C0 17.169.831 18 1.857 18h4.286A1.857 1.857 0 0 0 8 16.143v-4.286A1.857 1.857 0 0 0 6.143 10Zm10 0h-4.286A1.857 1.857 0 0 0 10 11.857v4.286c0 1.026.831 1.857 1.857 1.857h4.286A1.857 1.857 0 0 0 18 16.143v-4.286A1.857 1.857 0 0 0 16.143 10Z"/>
          </svg>
          <span class="ms-3">User Management</span>
        </a>
      </li>
      <li>
        <a href="{{ route('tickets.index') }}" class="flex items-center p-2 text-gray-900 rounded-lg hover:bg-gray-100 group">
          <svg class="w-5 h-5 text-gray-500 transition duration-75 group-hover:text-gray-900" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="currentColor" viewBox="0 0 20 20">
            <path d="M2 5h16v2H2V5zm0 4h16v2H2V9zm0 4h12v2H2v-2z"/>
          </svg>
          <span class="ms-3">Ticket Management</span>
        </a>
      </li>
      <li>
        <a href="#" class="flex items-center p-2 text-gray-900 rounded-lg hover:bg-gray-100 group">
          <svg class="w-5 h-5 text-gray-500 transition duration-75 group-hover:text-gray-900" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="currentColor" viewBox="0 0 20 20">
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
<div id="content-wrapper" class="px-10  sm:ml-64 transition-all duration-300">
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

    <!-- Content -->
    <main class="mt-4 space-y-4">
        <div class="ps-2">
            <h1 class="text-2xl font-semibold text-slate-900">Dashboard</h1>
            <p class="text-sm text-slate-500">Overview of your ticketing system</p>
        </div>

        <!-- Metrics -->
        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
            <!-- Total Open Tickets -->
            <div class="bg-white rounded-xl border border-gray-200 p-4">
                <div class="flex items-start justify-between">
                    <div>
                        <div class="text-xs font-medium text-slate-500">Total Open Tickets</div>
                        <div class="mt-2 text-3xl font-semibold text-slate-900"><span id="openTicketsCount">{{ number_format($openTickets ?? 0) }}</span></div>
                        <div class="mt-1 text-xs text-emerald-600 flex items-center gap-1">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-3.5 w-3.5" viewBox="0 0 24 24" fill="currentColor">
                                <path d="M12 4l6 6h-4v10h-4V10H6l6-6z" />
                            </svg>
                            <span>+3 from yesterday</span>
                        </div>
                    </div>
                    <div class="rounded-md bg-red-50 p-2 text-red-500 border border-red-100">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 24 24" fill="currentColor">
                            <path d="M4 4h16v2H4V4zm0 6h16v10H4V10zm2 2v6h12v-6H6z" />
                        </svg>
                    </div>
                </div>
            </div>
            <!-- Total FAQs -->
            <div class="bg-white rounded-xl border border-gray-200 p-4">
                <div class="flex items-start justify-between">
                    <div>
                        <div class="text-xs font-medium text-slate-500">Total FAQs</div>
                        <div class="mt-2 text-3xl font-semibold text-slate-900"><span id="faqCountValue">{{ number_format($faqCount ?? 0) }}</span></div>
                        <div class="mt-1 text-xs text-emerald-600 flex items-center gap-1">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-3.5 w-3.5" viewBox="0 0 24 24" fill="currentColor">
                                <path d="M12 4l6 6h-4v10h-4V10H6l6-6z" />
                            </svg>
                            <span>+12 trained today</span>
                        </div>
                    </div>
                    <div class="rounded-md bg-blue-50 p-2 text-blue-600 border border-blue-100">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 24 24" fill="currentColor">
                            <path d="M12 3l9 4.5V12c0 5-4 9-9 9s-9-4-9-9V7.5L12 3zm0 2.2L5 8.1V12c0 3.9 3.1 7 7 7s7-3.1 7-7V8.1l-7-2.9z" />
                        </svg>
                    </div>
                </div>
            </div>
            <!-- Total Users -->
            <div class="bg-white rounded-xl border border-gray-200 p-4">
                <div class="flex items-start justify-between">
                    <div>
                        <div class="text-xs font-medium text-slate-500">Total Users</div>
                        <div class="mt-2 text-3xl font-semibold text-slate-900"><span id="userCountValue">{{ number_format($userCount ?? 0) }}</span></div>
                        <div class="mt-1 text-xs text-emerald-600 flex items中心 gap-1">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-3.5 w-3.5" viewBox="0 0 24 24" fill="currentColor">
                                <path d="M12 4l6 6h-4v10h-4V10H6l6-6z" />
                            </svg>
                            <span>+15 new users</span>
                        </div>
                    </div>
                    <div class="rounded-md bg-emerald-50 p-2 text-emerald-600 border border-emerald-100">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 24 24" fill="currentColor">
                            <path d="M16 11c1.66 0 2.99-1.34 2.99-3S17.66 5 16 5s-3 1.34-3 3 1.34 3 3 3zM8 11c1.66 0 2.99-1.34 2.99-3S9.66 5 8 5 5 6.34 5 8s1.34 3 3 3zm0 2c-2.33 0-7 1.17-7 3.5V19h14v-2.5C15 14.17 10.33 13 8 13zm8 0c-.29 0-.62.02-.97.05 1.16.84 1.97 1.97 1.97 3.45V19h6v-2.5c0-2.33-4.67-3.5-7-3.5z" />
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
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-4 mb-10">
            @php
            $badge = fn($status) => match($status) {
            'Open' => 'text-blue-700 bg-blue-50 ring-blue-600/20',
            'In-Progress' => 'text-amber-700 bg-amber-50 ring-amber-600/20',
            'Closed' => 'text-emerald-700 bg-emerald-50 ring-emerald-600/20',
            default => 'text-slate-700 bg-slate-50 ring-slate-600/20',
            };
            @endphp

            <!-- Open Tickets -->
            <div class="bg-white rounded-xl border border-gray-200 overflow-hidden">
                <div class="px-5 py-4 border-b border-gray-300">
                    <h3 class="text-sm font-semibold text-slate-800">Open Tickets</h3>
                </div>
                <div class="overflow-x-auto">
                    <table class="min-w-full text-sm">
                        <thead class="bg-gray-50 text-gray-600">
                            <tr>
                                <th class="py-3 pl-5 pr-3 text-left font-medium">Ticket</th>
                                <th class="px-3 py-3 text-left font-medium">User</th>
                                <th class="px-3 py-3 text-left font-medium">Status</th>
                                <th class="py-3 pl-3 pr-5 text-left font-medium">Actions</th>
                            </tr>
                        </thead>
                        <tbody id="openListBody" class="divide-y divide-gray-100">
                            @forelse(($openList ?? []) as $t)
                            @php
                            $year = \Illuminate\Support\Carbon::parse($t->date_created ?? $t->created_at)->format('Y');
                            $ticketNo = 'T-' . $year . '-' . str_pad($t->id, 4, '0', STR_PAD_LEFT);
                            @endphp
                            <tr class="hover:bg-gray-50">
                                <td class="py-3 pl-5 pr-3 align-top">
                                    <div class="text-indigo-700 font-medium">{{ $ticketNo }}</div>
                                    <div class="mt-1 text-xs text-gray-500">
                                        {{ \Illuminate\Support\Carbon::parse($t->date_created ?? $t->created_at)->format('n/j/Y, g:i A') }}
                                    </div>
                                </td>
                                <td class="px-3 py-3 align-top">
                                    <div class="text-gray-900">{{ $t->email ?? '—' }}</div>
                                    <div class="text-xs text-gray-500">{{ $t->category ?? '' }}</div>
                                </td>
                                <td class="px-3 py-3 align-top">
                                    <span class="inline-flex items-center gap-1 rounded-full px-2.5 py-1 text-xs font-medium ring-1 {{ $badge($t->status) }}">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-3.5 w-3.5" viewBox="0 0 24 24" fill="currentColor">
                                            <circle cx="12" cy="12" r="5"></circle>
                                        </svg>
                                        {{ $t->status }}
                                    </span>
                                </td>
                                <td class="py-3 pl-3 pr-5 align-top">
                                    <a href="#" class="inline-flex items-center gap-2 rounded-lg border border-gray-200 bg-white px-3 py-1.5 text-xs font-medium text-gray-700 hover:bg-gray-50">
                                        View
                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-3.5 w-3.5" viewBox="0 0 24 24" fill="currentColor">
                                            <path d="M9 5l7 7-7 7" />
                                        </svg>
                                    </a>
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="4" class="px-5 py-10 text-center text-sm text-gray-500">No open tickets.</td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- In Progress Tickets -->
            <div class="bg-white rounded-xl border border-gray-200 overflow-hidden">
                <div class="px-5 py-4 border-b border-gray-300">
                    <h3 class="text-sm font-semibold text-slate-800">In Progress Tickets</h3>
                </div>
                <div class="overflow-x-auto">
                    <table class="min-w-full text-sm">
                        <thead class="bg-gray-50 text-gray-600">
                            <tr>
                                <th class="py-3 pl-5 pr-3 text-left font-medium">Ticket</th>
                                <th class="px-3 py-3 text-left font-medium">User</th>
                                <th class="px-3 py-3 text-left font-medium">Status</th>
                                <th class="py-3 pl-3 pr-5 text-left font-medium">Actions</th>
                            </tr>
                        </thead>
                        <tbody id="inProgressListBody" class="divide-y divide-gray-100">
                            @forelse(($inProgressList ?? []) as $t)
                            @php
                            $year = \Illuminate\Support\Carbon::parse($t->date_created ?? $t->created_at)->format('Y');
                            $ticketNo = 'T-' . $year . '-' . str_pad($t->id, 4, '0', STR_PAD_LEFT);
                            @endphp
                            <tr class="hover:bg-gray-50">
                                <td class="py-3 pl-5 pr-3 align-top">
                                    <div class="text-indigo-700 font-medium">{{ $ticketNo }}</div>
                                    <div class="mt-1 text-xs text-gray-500">
                                        Updated {{ \Illuminate\Support\Carbon::parse($t->updated_at ?? $t->date_created)->format('n/j/Y, g:i A') }}
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
                                <td class="py-3 pl-3 pr-5 align-top">
                                    <a href="#" class="inline-flex items-center gap-2 rounded-lg border border-gray-200 bg-white px-3 py-1.5 text-xs font-medium text-gray-700 hover:bg-gray-50">
                                        View
                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-3.5 w-3.5" viewBox="0 0 24 24" fill="currentColor">
                                            <path d="M9 5l7 7-7 7" />
                                        </svg>
                                    </a>
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="4" class="px-5 py-10 text-center text-sm text-gray-500">No in-progress tickets.</td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </main>
</div>
@endsection


<!-- Serialized analytics data for charts (avoid Blade directives inside JS) -->
<div id="analytics-data" class="hidden"
     data-week-labels='@json($weekLabels ?? [])'
     data-week-data='@json($weekData ?? [])'
     data-category-labels='@json($categoryLabels ?? [])'
     data-category-data='@json($categoryData ?? [])'
     data-admin-url="{{ route('admin.dashboard.data') }}"></div>
@section('scripts')
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

        function updateCounts(payload) {
            const elOpen = document.getElementById('openTicketsCount');
            const elFaq = document.getElementById('faqCountValue');
            const elUser = document.getElementById('userCountValue');
            if (elOpen) elOpen.textContent = fmt.format(payload.openTickets ?? 0);
            if (elFaq) elFaq.textContent = fmt.format(payload.faqCount ?? 0);
            if (elUser) elUser.textContent = fmt.format(payload.userCount ?? 0);
        }

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
                return dt.toLocaleString();
            } catch (_) { return ''; }
        }
        function adminBadgeClass(status) {
            switch (status) {
                case 'Open': return 'text-blue-700 bg-blue-50 ring-blue-600/20';
                case 'In-Progress': return 'text-amber-700 bg-amber-50 ring-amber-600/20';
                case 'Closed': return 'text-emerald-700 bg-emerald-50 ring-emerald-600/20';
                default: return 'text-slate-700 bg-slate-50 ring-slate-600/20';
            }
        }
        function updateOpenList(list) {
            const tbody = document.getElementById('openListBody');
            if (!tbody) return;
            const rows = Array.isArray(list) ? list.map(t => {
                const year = t.date_created ? new Date(t.date_created).getFullYear() : (t.created_at ? new Date(t.created_at).getFullYear() : new Date().getFullYear());
                const ticketNo = `T-${year}-${adminPad(t.id, 4)}`;
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
                        <a href="#" class="inline-flex items-center gap-2 rounded-lg border border-gray-200 bg-white px-3 py-1.5 text-xs font-medium text-gray-700 hover:bg-gray-50">
                            View
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-3.5 w-3.5" viewBox="0 0 24 24" fill="currentColor"><path d="M9 5l7 7-7 7" /></svg>
                        </a>
                    </td>
                </tr>`;
            }) : [];
            tbody.innerHTML = rows.length ? rows.join('') : `<tr><td colspan="4" class="px-5 py-10 text-center text-sm text-gray-500">No open tickets.</td></tr>`;
        }
        function updateInProgressList(list) {
            const tbody = document.getElementById('inProgressListBody');
            if (!tbody) return;
            const rows = Array.isArray(list) ? list.map(t => {
                const year = t.date_created ? new Date(t.date_created).getFullYear() : (t.created_at ? new Date(t.created_at).getFullYear() : new Date().getFullYear());
                const ticketNo = `T-${year}-${adminPad(t.id, 4)}`;
                const updatedAt = adminFmtDate(t.updated_at || t.date_created || t.created_at);
                const email = t.email || '—';
                const staffName = t.staff && t.staff.name ? `Staff: ${t.staff.name}` : '';
                const badge = adminBadgeClass(t.status);
                return `
                <tr class="hover:bg-gray-50">
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
                    <td class="py-3 pl-3 pr-5 align-top">
                        <a href="#" class="inline-flex items-center gap-2 rounded-lg border border-gray-200 bg-white px-3 py-1.5 text-xs font-medium text-gray-700 hover:bg-gray-50">
                            View
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-3.5 w-3.5" viewBox="0 0 24 24" fill="currentColor"><path d="M9 5l7 7-7 7" /></svg>
                        </a>
                    </td>
                </tr>`;
            }) : [];
            tbody.innerHTML = rows.length ? rows.join('') : `<tr><td colspan="4" class="px-5 py-10 text-center text-sm text-gray-500">No in-progress tickets.</td></tr>`;
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
                updateInProgressList(data.inProgressList || []);
            } catch (e) {
                // swallow errors to avoid UI disruption
                console.debug('Admin auto-refresh failed', e);
            }
        }

        // Initial fetch and interval (every 10s)
        setTimeout(refreshAdminData, 250);
        const ADMIN_POLL_MS = 10000;
        setInterval(refreshAdminData, ADMIN_POLL_MS);

        // Refresh on tab focus and when other tabs signal ticket changes
        window.addEventListener('focus', refreshAdminData);
        document.addEventListener('visibilitychange', () => {
            if (!document.hidden) refreshAdminData();
        });
        window.addEventListener('storage', (e) => {
            if (e && e.key === 'ts_tickets_changed') {
                refreshAdminData();
            }
        });
    })();
</script>

<!-- Sidebar collapse/expand for desktop as well -->
<script>
  (function () {
    const toggleBtn = document.getElementById('sidebar-toggle');
    const sidebar = document.getElementById('default-sidebar');
    const content = document.getElementById('content-wrapper');

    if (!toggleBtn || !sidebar || !content) return;

    function toggleSidebar() {
      // Collapse on >= sm by forcing sm:-translate-x-full and removing sm:translate-x-0
      const isCollapsed = sidebar.classList.contains('sm:-translate-x-full');

      if (isCollapsed) {
        // Expand
        sidebar.classList.remove('sm:-translate-x-full');
        sidebar.classList.add('sm:translate-x-0');
        content.classList.add('sm:ml-64');
        content.classList.remove('ml-0');
      } else {
        // Collapse
        sidebar.classList.add('sm:-translate-x-full');
        sidebar.classList.remove('sm:translate-x-0');
        content.classList.remove('sm:ml-64');
        content.classList.add('ml-0');
      }
    }

    toggleBtn.addEventListener('click', function (e) {
      // Keep Flowbite behavior for mobile, but ensure desktop collapse works
      e.preventDefault();
      toggleSidebar();
    });
  })();
</script>
@endsection
