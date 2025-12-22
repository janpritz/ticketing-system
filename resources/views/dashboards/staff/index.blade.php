@extends('layouts.staff')

@section('title', 'Staff Dashboard')

@section('staff-content')
    <div class="min-h-full">
        <nav class="bg-white">
            
            <div class="md:hidden hidden" id="mobile-menu">
                <!-- Navigation Links -->
                <div class="border-t border-white/10 pt-4 pb-3">
                    <div class="space-y-1 px-2">
                        <a href="{{ route('staff.dashboard') }}"
                            class="block rounded-md px-3 py-2 text-base font-medium text-gray-900 hover:bg-white/5 hover:text-gray-900 {{ request()->routeIs('staff.dashboard') ? 'bg-gray-100' : '' }}"
                            style="color: #111827;">Dashboard</a>
                        <a href="{{ route('staff.tickets') }}"
                            class="block rounded-md px-3 py-2 text-base font-medium text-gray-900 hover:bg-white/5 hover:text-gray-900"
                            style="color: #111827;">Tickets</a>
                        <a href="{{ route('staff.reports.index') }}"
                            class="block rounded-md px-3 py-2 text-base font-medium text-gray-900 hover:bg-white/5 hover:text-gray-900"
                            style="color: #111827;">Reports</a>
                        <a href="{{ route('staff.faqs.index') }}"
                            class="block rounded-md px-3 py-2 text-base font-medium text-gray-900 hover:bg-white/5 hover:text-gray-900"
                            style="color: #111827;">FAQs</a>
                        <a href="{{ route('staff.announcements.index') }}"
                            class="block rounded-md px-3 py-2 text-base font-medium text-gray-900 hover:bg-white/5 hover:text-gray-900"
                            style="color: #111827;">Announcements</a>
                    </div>
                </div>
                <!-- User Menu -->
                <div class="border-t border-white/10 pt-4 pb-3">
                    <div class="flex items-center px-5">
                        <div class="shrink-0">
                            <img class="size-10 rounded-full outline -outline-offset-1 outline-white/10"
                                src="{{ $user->profile_photo ? asset('storage/' . $user->profile_photo) . '?v=' . optional($user->updated_at)->timestamp : 'https://ui-avatars.com/api/?background=E5E7EB&color=111827&name=' . urlencode($user->name) }}"
                                alt="{{ $user->name }}" />
                        </div>
                        <div class="ml-3">
                            <div class="text-base font-medium text-gray-900">{{ $user->name }}</div>
                            <div class="text-sm font-medium text-gray-700">{{ $user->email }}</div>
                        </div>
                        <button type="button"
                            class="relative ml-auto shrink-0 rounded-full p-1 text-gray-400 hover:text-white focus:outline-2 focus:outline-offset-2 focus:outline-indigo-500">
                            <span class="absolute -inset-1.5" />
                            <span class="sr-only">View notifications</span>
                            <svg class="size-6" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"
                                aria-hidden="true">
                                <path stroke-linecap="round" stroke-linejoin="round"
                                    d="M14.857 17.082a23.848 23.848 0 005.454-1.31A8.967 8.967 0 0118 9.75v-.7V9A6 6 0 006 9v.75a8.967 8.967 0 01-2.312 6.022c1.733.64 3.56 1.085 5.455 1.31m5.714 0a24.255 24.255 0 01-5.714 0m5.714 0a3 3 0 11-5.714 0" />
                            </svg>
                        </button>
                    </div>
                    <div class="mt-3 space-y-1 px-2">
                        <a href="{{ route('staff.profile') }}"
                            class="block rounded-md px-3 py-2 text-base font-medium text-gray-900 hover:bg-white/5 hover:text-gray-900"
                            style="color: #111827;">Your
                            profile</a>
                        <a href="{{ route('logout') }}"
                            class="block rounded-md px-3 py-2 text-base font-medium text-gray-900 hover:bg-white/5 hover:text-gray-900"
                            style="color: #111827;"
                            onclick="event.preventDefault(); document.getElementById('logout-form-mobile').submit();">
                            Sign out
                        </a>
                        <form id="logout-form-mobile" action="{{ route('logout') }}" method="POST" class="hidden">
                            @csrf
                        </form>
                    </div>
                </div>
            </div>
        </nav>
        <main>
            <div class="sm:px-2">
                <div class="grid grid-cols-12 gap-6">
                    <!-- Left rail -->
                    <aside class="col-span-12 md:col-span-3 space-y-6 row-start-2 md:row-start-auto">
                        <!-- Quick Filters -->

                        <!-- Weekly Throughput -->
                        <div class="bg-white rounded-xl shadow-sm ring-1 ring-slate-900/5 p-5">
                            <h3 class="text-sm font-semibold text-gray-700">Weekly Tickets</h3>
                            <div class="mt-5">
                                <div id="weeklyChart" class="h-40 flex items-end justify-between gap-2">
                                    @php
                                        $wt = $weeklyThroughput ?? ['series' => [], 'labels' => [], 'max' => 0];
                                        $series = $wt['series'] ?? [];
                                        $labels = $wt['labels'] ?? [];
                                        $max = (int) ($wt['max'] ?? 0);
                                    @endphp
                                    @for ($i = 0; $i < 7; $i++)
                                        @php
                                            $count = (int) ($series[$i] ?? 0);
                                            $label = $labels[$i] ?? '';
                                            $height = $max > 0 ? round(($count / $max) * 100) : 0;
                                        @endphp
                                        <div class="flex flex-col items-center w-8 h-full">
                                            <div class="mb-1 text-[10px] text-gray-500 weekly-label"
                                                data-index="{{ $i }}">{{ $label }}</div>
                                            <div class="w-6 bg-indigo-400 opacity-80 rounded-t weekly-bar mt-auto"
                                                data-index="{{ $i }}" data-count="{{ (int) $count }}"
                                                data-height="{{ (int) $height }}"
                                                title="{{ $label }}: {{ $count }}" style="height: 0%;">
                                            </div>
                                        </div>
                                    @endfor
                                </div>
                                <div class="mt-3 text-xs text-gray-500 flex justify-between">
                                    <span id="weeklyTotal">Week total: {{ array_sum($series) }}</span>
                                    <span id="weeklyMax">Peak: {{ $max }}</span>
                                </div>
                            </div>
                        </div>
                    </aside>

                    <!-- Main content -->
                    <section class="col-span-12 md:col-span-9 space-y-6 row-start-1 md:row-start-auto flex flex-col">
                        <!-- KPI cards -->
                        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 order-2 md:order-1 mt-4 md:mt-0">
                            <!-- Open -->
                            <div class="bg-white rounded-xl shadow-sm ring-1 ring-slate-900/5 p-5">
                                <div class="flex items-center justify-between">
                                    <div>
                                        <div class="text-sm text-gray-500">Open</div>
                                        <div class="mt-2 text-3xl font-semibold text-gray-900"><span
                                                id="openCount">{{ $openCount ?? 0 }}</span></div>
                                    </div>
                                    <div class="rounded-full bg-blue-50 text-blue-600 p-3 ring-1 ring-blue-600/10">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none"
                                            viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                                                d="M3 7.5h18M3 12h18M3 16.5h18" />
                                        </svg>
                                    </div>
                                </div>
                            </div>

                            <!-- Forwarded -->
                            <div class="bg-white rounded-xl shadow-sm ring-1 ring-slate-900/5 p-5">
                                <div class="flex items-center justify-between">
                                    <div>
                                        <div class="text-sm text-gray-500">Forwarded</div>
                                        <div class="mt-2 text-3xl font-semibold text-gray-900"><span
                                                id="inProgressCount">{{ $inProgressCount ?? 0 }}</span></div>
                                    </div>
                                    <div class="rounded-full bg-amber-50 text-amber-600 p-3 ring-1 ring-amber-600/10">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none"
                                            viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                                                d="M12 6v6l3 3" />
                                        </svg>
                                    </div>
                                </div>
                            </div>

                            <!-- Resolved -->
                            <div class="bg-white rounded-xl shadow-sm ring-1 ring-slate-900/5 p-5">
                                <div class="flex items-center justify-between">
                                    <div>
                                        <div class="text-sm text-gray-500">Resolved</div>
                                        <div class="mt-2 text-3xl font-semibold text-gray-900"><span
                                                id="closedCount">{{ $closedCount ?? 0 }}</span></div>
                                    </div>
                                    <div
                                        class="rounded-full bg-emerald-50 text-emerald-600 p-3 ring-1 ring-emerald-600/10">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none"
                                            viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                                                d="M4.5 12.75l6 6 9-13.5" />
                                        </svg>
                                    </div>
                                </div>
                            </div>

                            <!-- Total -->
                            <div class="bg-white rounded-xl shadow-sm ring-1 ring-slate-900/5 p-5">
                                <div class="flex items-center justify-between">
                                    <div>
                                        <div class="text-sm text-gray-500">Total</div>
                                        <div class="mt-2 text-3xl font-semibold text-gray-900"><span
                                                id="totalCount">{{ $totalCount ?? 0 }}</span></div>
                                    </div>
                                    <div class="rounded-full bg-slate-50 text-slate-600 p-3 ring-1 ring-slate-600/10">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none"
                                            viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                                                d="M4 6h16M4 12h16M4 18h16" />
                                        </svg>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Tickets Table -->
                        <div id="openTicketsSection"
                            class="bg-white rounded-xl shadow-sm ring-1 ring-slate-900/5 order-2 pb-6 md:pb-5">
                            <!-- Header -->
                            <div class="px-5 py-4 flex items-center justify-between">
                                <h2 id="ticketsHeading" class="text-base font-semibold text-gray-800">Open Tickets</h2>
                                <div class="flex items-center gap-4 flex-wrap justify-end">
                                    <div class="flex items-center gap-2">
                                        <input type="text" id="searchInput" placeholder="Search tickets..."
                                            class="rounded-md border-gray-300 text-sm focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 px-3 py-1">
                                    </div>
                                    <div class="flex items-center gap-2">
                                        <span class="hidden sm:inline text-sm text-gray-700">Show</span>
                                        <select id="perPageSelect"
                                            class="rounded-md border-gray-300 text-xs focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500">
                                            <option value="10">10</option>
                                            <option value="25">25</option>
                                            <option value="50">50</option>
                                        </select>
                                        <span class="hidden sm:inline text-sm text-gray-700">per page</span>
                                    </div>

                                </div>
                            </div>

                            <!-- Table -->
                            <div class="overflow-x-auto">
                                <table class="min-w-full text-sm">
                                    <thead class="bg-gray-50 text-gray-600">
                                        <tr>
                                            <th class="py-3 pl-5 pr-3 text-left font-medium">Ticket</th>
                                            <th class="px-3 py-3 text-left font-medium">Subject</th>
                                            <th class="px-3 py-3 text-left font-medium">Status</th>
                                            <th class="px-3 py-3 text-left font-medium">Assignee</th>
                                            <th class="py-3 pl-3 pr-5 text-left font-medium">Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody class="divide-y divide-gray-100" id="ticketsBody">
                                        @php
                                            $statusStyles = [
                                                'Open' => 'text-blue-700 bg-blue-50 ring-blue-600/20',
                                                'Forwarded' => 'text-amber-700 bg-amber-50 ring-amber-600/20',
                                                'Closed' => 'text-emerald-700 bg-emerald-50 ring-emerald-600/20',
                                            ];
                                        @endphp

                                        @forelse(($recentTickets ?? []) as $t)
                                            @php
                                                $style =
                                                    $statusStyles[$t->status] ??
                                                    'text-slate-700 bg-slate-50 ring-slate-600/20';
                                            @endphp
                                            <tr class="hover:bg-gray-50">
                                                <!-- Ticket -->
                                                <td class="py-4 pl-5 pr-3 align-top">
                                                    <div class="text-indigo-700 font-medium">{{ $t->id }}</div>
                                                    <div class="mt-1 text-xs text-gray-500">
                                                        {{ \Illuminate\Support\Carbon::parse($t->date_created)->format('Y-m-d h:i a') }}
                                                    </div>
                                                </td>

                                                <!-- Subject -->
                                                <td class="px-3 py-4 align-top">
                                                    <div class="text-gray-900">
                                                        {{ \Illuminate\Support\Str::limit($t->question, 80) }}</div>
                                                    <div class="mt-1 text-xs text-gray-500 flex items-center gap-2">
                                                        <span
                                                            class="inline-flex items-center rounded-full bg-gray-100 px-2 py-0.5 text-[11px] font-medium text-gray-700">{{ $t->category }}</span>
                                                    </div>
                                                </td>

                                                <!-- Status -->
                                                <td class="px-3 py-4 align-top">
                                                    <span
                                                        class="inline-flex items-center gap-1 rounded-full px-2.5 py-1 text-xs font-medium ring-1 {{ $style }}">
                                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-3.5 w-3.5"
                                                            viewBox="0 0 24 24" fill="currentColor">
                                                            <circle cx="12" cy="12" r="5"></circle>
                                                        </svg>
                                                        {{ $t->status }}
                                                    </span>
                                                </td>

                                                <!-- Assignee -->
                                                <td class="px-3 py-4 align-top">
                                                    <div class="text-gray-900">{{ optional($t->staff)->name ?? '-' }}
                                                    </div>
                                                    <div class="mt-1 text-xs text-gray-500">Updated
                                                        {{ \Illuminate\Support\Carbon::parse($t->updated_at)->format('Y-m-d h:i a') }}
                                                    </div>
                                                </td>

                                                <!-- Actions -->
                                                <td class="px-3 py-4 align-top">
                                                    <a href="#" data-action="view" data-id="{{ $t->id }}"
                                                        data-category="{{ $t->category }}"
                                                        data-question="{{ $t->question }}"
                                                        data-status="{{ $t->status }}"
                                                        data-staff="{{ optional($t->staff)->name }}"
                                                        data-date-created="{{ $t->date_created }}"
                                                        data-updated-at="{{ $t->updated_at }}"
                                                        data-email="{{ $t->email }}"
                                                        data-recepient="{{ $t->recepient_id }}"
                                                        data-response="{{ $t->response }}"
                                                        class="btn-view inline-flex items-center gap-2 rounded-lg border border-gray-200 bg-blue px-3 py-1.5 text-xs font-medium text-gray-700 hover:bg-gray-50">
                                                        View
                                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-3.5 w-3.5"
                                                            viewBox="0 0 24 24" fill="currentColor">
                                                            <path d="M9 5l7 7-7 7" />
                                                        </svg>
                                                    </a>
                                                </td>
                                            </tr>
                                        @empty
                                            <tr>
                                                <td colspan="5" class="px-5 py-10 text-center text-sm text-gray-500">
                                                    No tickets assigned yet.
                                                </td>
                                            </tr>
                                        @endforelse
                                    </tbody>
                                </table>
                            </div>
                            <div class="px-5 py-3 flex items-center justify-between border-t text-sm">
                                <button id="pagerPrev" type="button"
                                    class="rounded-md border border-gray-300 px-3 py-1.5 text-gray-700 disabled:opacity-50">Prev</button>
                                <div id="pagerInfo" class="text-gray-500"></div>
                                <button id="pagerNext" type="button"
                                    class="rounded-md border border-gray-300 px-3 py-1.5 text-gray-700 disabled:opacity-50">Next</button>
                            </div>
                        </div>
                    </section>
                </div>
            </div>
        @endsection

        <!-- Toast container -->
        <div id="toastContainer" aria-live="polite" aria-atomic="true" class="fixed top-4 right-4 z-50 space-y-2"
            style="z-index: 2147483647; pointer-events: none;"></div>

        @section('staff-scripts')
            <script>
                // Mobile menu toggle
                const mobileMenuButton = document.getElementById('mobile-menu-button');
                const mobileMenu = document.getElementById('mobile-menu');
                const mobileMenuOpen = document.getElementById('mobile-menu-open');
                const mobileMenuClose = document.getElementById('mobile-menu-close');

                if (mobileMenuButton && mobileMenu) {
                    mobileMenuButton.addEventListener('click', function() {
                        const isExpanded = mobileMenuButton.getAttribute('aria-expanded') === 'true';
                        mobileMenuButton.setAttribute('aria-expanded', !isExpanded);
                        mobileMenu.classList.toggle('hidden');

                        // Toggle icons
                        if (mobileMenu.classList.contains('hidden')) {
                            mobileMenuOpen.classList.remove('hidden');
                            mobileMenuClose.classList.add('hidden');
                        } else {
                            mobileMenuOpen.classList.add('hidden');
                            mobileMenuClose.classList.remove('hidden');
                        }
                    });
                }

                // User menu dropdown toggle
                const userMenuButton = document.getElementById('user-menu-button');
                const userMenu = document.getElementById('user-menu');

                if (userMenuButton && userMenu) {
                    userMenuButton.addEventListener('click', function() {
                        const isExpanded = userMenuButton.getAttribute('aria-expanded') === 'true';
                        userMenuButton.setAttribute('aria-expanded', !isExpanded);
                        userMenu.classList.toggle('hidden');
                    });

                    // Close dropdown when clicking outside
                    document.addEventListener('click', function(event) {
                        if (!userMenuButton.contains(event.target) && !userMenu.contains(event.target)) {
                            userMenu.classList.add('hidden');
                            userMenuButton.setAttribute('aria-expanded', 'false');
                        }
                    });
                }

                // Toast helper
                let toastContainer = document.getElementById('toastContainer');
                // Ensure toast container is attached directly to <body> to avoid stacking/transform issues
                try {
                    if (toastContainer && toastContainer.parentElement !== document.body) {
                        document.body.appendChild(toastContainer);
                    }
                } catch (_) {}

                function showToast(type, message) {
                    if (!toastContainer) {
                        try {
                            alert(message);
                        } catch (_) {}
                        return;
                    }
                    const isSuccess = type === 'success';
                    const icon = isSuccess ?
                        '<svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-emerald-500" viewBox="0 0 24 24" fill="currentColor"><path d="M9 12.75l-2.25-2.25-1.5 1.5L9 15.75l9-9-1.5-1.5z"/></svg>' :
                        '<svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-red-500" viewBox="0 0 24 24" fill="currentColor"><path d="M12 2a10 10 0 100 20 10 10 0 000-20zm.75 5.5h-1.5v7h1.5v-7zm0 8.5h-1.5v1.5h1.5V16z"/></svg>';
                    const outer = document.createElement('div');
                    outer.className = 'w-80 rounded-lg border bg-white px-4 py-3 shadow ring-1 ring-black/5';
                    outer.setAttribute('role', 'status');
                    outer.style.pointerEvents = 'auto';
                    outer.innerHTML = '<div class="flex items-start gap-2">' +
                        icon +
                        '<div class="flex-1 text-sm ' + (isSuccess ? 'text-emerald-800' : 'text-red-800') + '">' + String(message ||
                            '') + '</div>' +
                        '<button type="button" aria-label="Close" class="text-gray-400 hover:text-gray-600" data-close>&times;</button>' +
                        '</div>';
                    toastContainer.appendChild(outer);
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

                // ===== Overdue Tickets Toggle =====
                const overdueDropdownBtn = document.getElementById('overdueDropdownBtn');
                const overdueTicketsList = document.getElementById('overdueTicketsList');
                if (overdueDropdownBtn && overdueTicketsList) {
                    overdueDropdownBtn.addEventListener('click', () => {
                        overdueTicketsList.classList.toggle('hidden');
                        overdueDropdownBtn.textContent = overdueTicketsList.classList.contains('hidden') ? 'View Tickets' :
                            'Hide Tickets';
                    });
                }

                // ===== Live auto-sync for tickets & KPIs (polling) =====
                (function() {
                    const dataUrl = "{{ route('staff.dashboard.data') }}";
                    const openCountEl = document.getElementById('openCount');
                    const inProgressCountEl = document.getElementById('inProgressCount');
                    const closedCountEl = document.getElementById('closedCount');
                    const totalCountEl = document.getElementById('totalCount');
                    const ticketsBodyEl = document.getElementById('ticketsBody');
                    const toggleEl = document.getElementById('toggleViewAll');
                    const ticketsHeadingEl = document.getElementById('ticketsHeading');
                    // Pagination controls and per-page
                    const perPageSelect = document.getElementById('perPageSelect');
                    const pagerPrev = document.getElementById('pagerPrev');
                    const pagerNext = document.getElementById('pagerNext');
                    const pagerInfo = document.getElementById('pagerInfo');
                    const searchInput = document.getElementById('searchInput');
                    let currentPage = 1,
                        lastPage = 1;
                    let searchTerm = '';

                    function normalizePerPage(v) {
                        const n = Number(v || 10);
                        return [10, 25, 50].includes(n) ? n : 10;
                    }
                    let perPage = normalizePerPage(localStorage.getItem('ts_staff_perPage'));
                    if (perPageSelect) {
                        perPageSelect.value = String(perPage);
                    }
                    let ticketsMap = new Map();

                    const statusStyles = {
                        'Open': 'text-blue-700 bg-blue-50 ring-blue-600/20',
                        'Forwarded': 'text-amber-700 bg-amber-50 ring-amber-600/20',
                        'Closed': 'text-emerald-700 bg-emerald-50 ring-emerald-600/20',
                    };

                    let lastSnapshot = '';

                    function fmtDate(d) {
                        try {
                            const dt = new Date(d);
                            if (isNaN(dt.getTime())) return '';
                            return dt.toLocaleString();
                        } catch (_) {
                            return '';
                        }
                    }

                    function renderTickets(tickets) {
                        if (!ticketsBodyEl) return;
                        if (!Array.isArray(tickets)) tickets = [];

                        // Build HTML rows
                        const rows = tickets.map(t => {
                            const ticketNo = String(t.id);
                            const style = statusStyles[t.status] || 'text-slate-700 bg-slate-50 ring-slate-600/20';
                            const updatedAt = fmtDate(t.updated_at);
                            const createdAt = fmtDate(t.date_created || t.created_at);
                            const assignee = (t.staff && t.staff.name) ? t.staff.name : '-';
                            const category = t.category ?? '';

                            const subject = (t.question ?? '').length > 80 ? (t.question ?? '').slice(0, 77) + '...' : (
                                t.question ?? '');

                            return `
                    <tr class="hover:bg-gray-50">
                        <td class="py-4 pl-5 pr-3 align-top">
                            <div class="text-indigo-700 font-medium">${ticketNo}</div>
                            <div class="mt-1 text-xs text-gray-500">
                                ${createdAt}
                            </div>
                        </td>

                        <td class="px-3 py-4 align-top">
                            <div class="text-gray-900">${subject}</div>
                            <div class="mt-1 text-xs text-gray-500 flex items-center gap-2">
                                <span class="inline-flex items-center rounded-full bg-gray-100 px-2 py-0.5 text-[11px] font-medium text-gray-700">${category}</span>
                            </div>
                        </td>

                        <td class="px-3 py-4 align-top">
                            <span class="inline-flex items-center gap-1 rounded-full px-2.5 py-1 text-xs font-medium ring-1 ${style}">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-3.5 w-3.5" viewBox="0 0 24 24" fill="currentColor">
                                    <circle cx="12" cy="12" r="5"></circle>
                                </svg>
                                ${t.status ?? ''}
                            </span>
                        </td>

                        <td class="px-3 py-4 align-top">
                            <div class="text-gray-900">${assignee}</div>
                            <div class="mt-1 text-xs text-gray-500">Updated ${updatedAt}</div>
                        </td>

                        <td class="py-4 pl-3 pr-5 align-top">
                            <a href="#"
                               data-action="view"
                               data-id="${t.id}"
                               class="inline-flex items-center gap-2 rounded-lg bg-blue border border-gray-200 px-3 py-1.5 text-xs font-medium text-gray-700 hover:bg-gray">
                                View
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-3.5 w-3.5" viewBox="0 0 24 24" fill="currentColor"><path d="M9 5l7 7-7 7"/></svg>
                            </a>
                        </td>
                    </tr>
                `;
                        });

                        if (rows.length === 0) {
                            ticketsBodyEl.innerHTML = `
                    <tr>
                        <td colspan="5" class="px-5 py-10 text-center text-sm text-gray-500">
                            No tickets assigned yet.
                        </td>
                    </tr>
                `;
                        } else {
                            ticketsBodyEl.innerHTML = rows.join('');
                        }
                    }


                    async function fetchData() {
                        // Avoid background polling to save resources
                        if (document.hidden) return;

                        // Determine current filter (view all vs open-only) BEFORE the request
                        const viewAll = toggleEl ? toggleEl.checked : false;
                        const url = dataUrl + '?viewAll=' + (viewAll ? 'true' : 'false') + '&page=' + currentPage +
                            '&perPage=' + perPage + '&search=' + encodeURIComponent(searchTerm);

                        try {
                            const res = await fetch(url, {
                                method: 'GET',
                                headers: {
                                    'Accept': 'application/json'
                                },
                                credentials: 'same-origin'
                            });

                            if (res.status === 401) {
                                // Session expired or not authenticated
                                window.location.href = "{{ route('login') }}";
                                return;
                            }

                            if (!res.ok) return;

                            const data = await res.json();

                            // Update KPIs
                            if (openCountEl) openCountEl.textContent = data.openCount ?? 0;
                            if (inProgressCountEl) inProgressCountEl.textContent = data.inProgressCount ?? 0;
                            if (closedCountEl) closedCountEl.textContent = data.closedCount ?? 0;
                            if (totalCountEl) totalCountEl.textContent = data.totalCount ?? 0;

                            // Update heading based on filter
                            if (ticketsHeadingEl) {
                                ticketsHeadingEl.textContent = viewAll ? 'My Tickets' : 'Open Tickets';
                            }

                            // Server already applies the filter. Keep a safety client-side filter when viewAll=false.
                            const list = Array.isArray(data.recentTickets) ? data.recentTickets : [];
                            console.log('Recent tickets from API:', list);
                            // Keep a fast lookup for "View" modal
                            ticketsMap = new Map(list.map(t => [String(t.id), t]));
                            // Show both Open and Forwarded when not viewing all
                            const filtered = viewAll ? list : list.filter(t => (t.status === 'Open' || t.status ===
                                'Forwarded'));

                            // Update pagination UI
                            var pg = data.pagination || {};
                            currentPage = Number(pg.currentPage || 1);
                            lastPage = Number(pg.lastPage || 1);
                            if (pagerInfo) {
                                const totalTxt = (typeof pg.total !== 'undefined') ? (' • ' + pg.total + ' total') : '';
                                pagerInfo.textContent = 'Page ' + currentPage + ' of ' + (lastPage || 1) + totalTxt;
                            }
                            if (pagerPrev) pagerPrev.disabled = currentPage <= 1;
                            if (pagerNext) pagerNext.disabled = currentPage >= lastPage;

                            // Update pagination UI
                            var pg = data.pagination || {};
                            currentPage = Number(pg.currentPage || 1);
                            lastPage = Number(pg.lastPage || 1);
                            if (pagerInfo) {
                                const totalTxt = (typeof pg.total !== 'undefined') ? (' • ' + pg.total + ' total') : '';
                                pagerInfo.textContent = 'Page ' + currentPage + ' of ' + (lastPage || 1) + totalTxt;
                            }
                            if (pagerPrev) pagerPrev.disabled = currentPage <= 1;
                            if (pagerNext) pagerNext.disabled = currentPage >= lastPage;

                            // Render tickets only if changed (cheap diff by filter+IDs+counts JSON)
                            const snapshot = JSON.stringify({
                                mode: viewAll ? 'all' : 'open',
                                total: data.totalCount,
                                ids: filtered.map(t => t.id)
                            });
                            if (snapshot !== lastSnapshot) {
                                renderTickets(filtered);
                                lastSnapshot = snapshot;
                            }
                        } catch (e) {
                            // Silently ignore transient errors; next tick will retry
                        }
                    }

                    // Initial load only — polling disabled to avoid overloading the database.
                    // The dashboard will refresh when:
                    //  - a CRUD operation in any tab sets localStorage.ts_tickets_changed
                    //  - the current tab becomes visible AND a change was recorded
                    fetchData();

                    // If the page was opened with a ticket_id query param (from a push), attempt to open the ticket modal.
                    (function openTicketFromQuery() {
                        try {
                            const params = new URLSearchParams(window.location.search);
                            const ticketId = params.get('ticket_id');
                            if (!ticketId) return;

                            // Try to open the modal as soon as the ticket is present in ticketsMap.
                            // ticketsMap is populated by fetchData(); poll briefly until available.
                            let attempts = 0;
                            const maxAttempts = 20;
                            const interval = setInterval(() => {
                                attempts++;
                                if (ticketsMap && ticketsMap.has(String(ticketId))) {
                                    const t = ticketsMap.get(String(ticketId));
                                    try {
                                        openModalFrom(t);
                                    } catch (e) {
                                        /* ignore */
                                    }
                                    // Remove ticket_id from URL so reloading doesn't reopen modal
                                    try {
                                        const newSearch = window.location.search.replace(
                                            /([?&])ticket_id=[^&]*(&|$)/, (m, p1, p2) => p2 ? p1 : '');
                                        history.replaceState(null, '', window.location.pathname + newSearch);
                                    } catch (_) {}
                                    clearInterval(interval);
                                } else if (attempts >= maxAttempts) {
                                    clearInterval(interval);
                                }
                            }, 300);
                        } catch (e) {
                            // ignore
                        }
                    })();

                    // Cross-tab notification: refresh when other tabs set the flag
                    window.addEventListener('storage', (e) => {
                        if (e && e.key === 'ts_tickets_changed') {
                            fetchData();
                        }
                    });
                    // When tab becomes visible, refresh only if an external change was recorded
                    document.addEventListener('visibilitychange', () => {
                        try {
                            if (!document.hidden && localStorage.getItem('ts_tickets_changed')) {
                                fetchData();
                            }
                        } catch (_) {}
                    });

                    // React to toggle changes immediately
                    if (toggleEl) {
                        toggleEl.addEventListener('change', () => {
                            // reset pagination to first page
                            currentPage = 1;
                            lastSnapshot = '';
                            fetchData();
                        });
                    }
                    if (perPageSelect) {
                        perPageSelect.addEventListener('change', () => {
                            perPage = normalizePerPage(perPageSelect.value);
                            localStorage.setItem('ts_staff_perPage', String(perPage));
                            currentPage = 1;
                            lastSnapshot = '';
                            fetchData();
                        });
                    }
                    if (pagerPrev) {
                        pagerPrev.addEventListener('click', () => {
                            if (currentPage > 1) {
                                currentPage -= 1;
                                lastSnapshot = '';
                                fetchData();
                            }
                        });
                    }
                    if (pagerNext) {
                        pagerNext.addEventListener('click', () => {
                            if (currentPage < lastPage) {
                                currentPage += 1;
                                lastSnapshot = '';
                                fetchData();
                            }
                        });
                    }
                    // Search input with debounce
                    let searchTimeout;
                    if (searchInput) {
                        searchInput.addEventListener('input', () => {
                            clearTimeout(searchTimeout);
                            searchTimeout = setTimeout(() => {
                                searchTerm = searchInput.value.trim();
                                currentPage = 1;
                                lastSnapshot = '';
                                fetchData();
                            }, 300);
                        });
                    }

                    // ===== Modal & View logic =====
                    const modalEl = document.getElementById('ticketModal');
                    const modalBackdrop = modalEl ? modalEl.querySelector('[data-modal-backdrop]') : null;
                    const modalCloseBtns = modalEl ? modalEl.querySelectorAll('[data-modal-close]') : [];
                    const tmTicketNo = document.getElementById('tmTicketNo');
                    const tmDates = document.getElementById('tmDates');
                    const tmStatus = document.getElementById('tmStatus');
                    const tmCategory = document.getElementById('tmCategory');
                    const tmSubject = document.getElementById('tmSubject');
                    const tmQuestion = document.getElementById('tmQuestion');
                    const tmEmail = document.getElementById('tmEmail');
                    const tmRecepient = document.getElementById('tmRecepient');
                    const tmResponse = document.getElementById('tmResponse');
                    const tmSendResponse = document.getElementById('tmSendResponse');
                    const tmOptionsBtn = document.getElementById('tmOptionsBtn');
                    const tmOptionsMenu = document.getElementById('tmOptionsMenu');
                    const tmOptionForward = document.getElementById('tmOptionForward');
                    const tmStoredResponseBlock = document.getElementById('tmStoredResponseBlock');
                    const tmStoredResponse = document.getElementById('tmStoredResponse');

                    const csrfToken = '{{ csrf_token() }}';
                    const forwardBase = "{{ url('/staff/tickets') }}";
                    let currentTicketId = null;

                    function statusClassFor(s) {
                        const base = statusStyles[s] || 'text-slate-700 bg-slate-50 ring-slate-600/20';
                        return base;
                    }

                    function ensureHistorySection() {
                        let section = document.getElementById('tmHistorySection');
                        const list = document.getElementById('tmHistoryList');
                        return {
                            section,
                            list
                        };
                    }

                    function renderHistory(histArr) {
                        const {
                            section,
                            list
                        } = ensureHistorySection();
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

                    function openModalFrom(ticket) {
                        if (!modalEl || !ticket) return;
                        const ticketNo = String(ticket.id);
                        const createdAt = fmtDate(ticket.date_created || ticket.created_at);
                        const updatedAt = fmtDate(ticket.updated_at);
                        const category = ticket.category ?? '';
                        const subject = category ? `${category} - ${(ticket.question ?? '').slice(0, 80)}` : ((ticket
                            .question ?? '').slice(0, 80));
                        const question = ticket.question ?? '';
                        const email = ticket.email ?? '';
                        const recepient = ticket.recepient_id ?? '';

                        // Fill fields
                        if (tmTicketNo) tmTicketNo.textContent = 'Ticket #' + ticketNo;
                        if (tmDates) tmDates.textContent = createdAt ?
                            `Created ${createdAt}${updatedAt ? ' • Updated ' + updatedAt : ''}` : '';
                        if (tmStatus) {
                            tmStatus.className =
                                'inline-flex items-center gap-1.5 rounded-full px-3 py-1 text-xs font-medium ring-1 ' +
                                statusClassFor(ticket.status);
                            tmStatus.innerHTML =
                                `<svg xmlns="http://www.w3.org/2000/svg" class="h-3.5 w-3.5" viewBox="0 0 24 24" fill="currentColor"><circle cx="12" cy="12" r="5"></circle></svg> ${ticket.status ?? ''}`;
                        }
                        if (tmCategory) tmCategory.textContent = category;
                        if (tmSubject) tmSubject.textContent = subject;
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
                        console.log('Ticket attachments:', ticket.attachments);
                        if (attachmentsBlock && attachmentsList) {
                            attachmentsList.innerHTML = '';
                            if (ticket.attachments) {
                                let attachments = [];
                                try {
                                    attachments = JSON.parse(ticket.attachments);
                                    console.log('Parsed attachments:', attachments);
                                } catch (e) {
                                    console.error('Error parsing attachments:', e);
                                    attachments = [];
                                }
                                if (attachments.length > 0) {
                                    console.log('Showing attachments block');
                                    attachments.forEach((path, index) => {
                                        const img = document.createElement('img');
                                        img.src = '/storage/' + path;
                                        console.log('Image src:', img.src);
                                        img.alt = 'Attachment ' + (index + 1);
                                        img.className =
                                            'max-w-16 max-h-16 object-cover rounded cursor-pointer border border-gray-300 hover:border-indigo-400';
                                        img.onclick = () => openLightbox(attachments, index);
                                        attachmentsList.appendChild(img);
                                    });
                                    attachmentsBlock.classList.remove('hidden');
                                } else {
                                    console.log('No attachments to show');
                                    attachmentsBlock.classList.add('hidden');
                                }
                            } else {
                                console.log('No attachments field');
                                attachmentsBlock.classList.add('hidden');
                            }
                        }

                        // Hide forward controls initially
                        const tmForwardControls = document.getElementById('tmForwardControls');
                        if (tmForwardControls) tmForwardControls.classList.add('hidden');

                        // Prepare and render history; keep hidden by default until toggled in Options
                        const hsObj = ensureHistorySection();
                        if (hsObj.section) hsObj.section.classList.add('hidden');
                        const histories = ticket.routing_histories || ticket.routingHistories || [];
                        renderHistory(Array.isArray(histories) ? histories : []);

                        // Toggle forward option and response display based on status
                        const isClosed = (ticket.status === 'Closed');
                        if (tmOptionForward) tmOptionForward.classList.toggle('hidden', isClosed);
                        if (tmForwardControls) tmForwardControls.classList.add('hidden');
                        if (tmStoredResponseBlock) {
                            if (isClosed) {
                                tmStoredResponseBlock.classList.remove('hidden');
                                if (tmStoredResponse) tmStoredResponse.textContent = ticket.response ? String(ticket.response) :
                                    'No response on record.';
                            } else {
                                tmStoredResponseBlock.classList.add('hidden');
                                if (tmStoredResponse) tmStoredResponse.textContent = '';
                            }
                        }
                        if (tmResponse) {
                            tmResponse.disabled = isClosed;
                            tmResponse.placeholder = isClosed ? 'Ticket is closed. Response cannot be edited.' :
                                'Type your response message here...';
                        }
                        if (tmSendResponse) {
                            tmSendResponse.disabled = isClosed;
                            tmSendResponse.classList.toggle('opacity-50', isClosed);
                            tmSendResponse.classList.toggle('pointer-events-none', isClosed);
                        }

                        currentTicketId = ticket.id;

                        // Show modal
                        modalEl.classList.remove('hidden');
                        document.body.classList.add('overflow-hidden');
                    }

                    function closeModal() {
                        if (!modalEl) return;
                        modalEl.classList.add('hidden');
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
                        if (nextBtn) nextBtn.style.display = currentLightboxIndex < currentLightboxImages.length - 1 ? 'flex' :
                            'none';
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

                    // Delegate click on "View"
                    if (ticketsBodyEl) {
                        ticketsBodyEl.addEventListener('click', (e) => {
                            const a = e.target.closest('[data-action="view"]');
                            if (!a) return;
                            e.preventDefault();
                            const id = a.getAttribute('data-id');
                            let ticket = ticketsMap.get(String(id));
                            // Fallback to server-rendered data attributes if available
                            if (!ticket) {
                                ticket = {
                                    id: Number(id),
                                    category: a.getAttribute('data-category') || '',
                                    question: a.getAttribute('data-question') || '',
                                    status: a.getAttribute('data-status') || '',
                                    staff: {
                                        name: a.getAttribute('data-staff') || ''
                                    },
                                    date_created: a.getAttribute('data-date-created') || '',
                                    updated_at: a.getAttribute('data-updated-at') || '',
                                    email: a.getAttribute('data-email') || '',
                                    recepient_id: a.getAttribute('data-recepient') || '',
                                    response: a.getAttribute('data-response') || ''
                                };
                            }
                            openModalFrom(ticket);
                        });
                    }

                    // Close modal interactions
                    if (modalBackdrop) modalBackdrop.addEventListener('click', closeModal);
                    if (modalCloseBtns && modalCloseBtns.length) {
                        modalCloseBtns.forEach(btn => btn.addEventListener('click', closeModal));
                    }
                    document.addEventListener('keydown', (e) => {
                        if (e.key === 'Escape') closeModal();
                    });

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

                    // Toggle Details Section
                    const tmToggleDetails = document.getElementById('tmToggleDetails');
                    const tmDetailsContent = document.getElementById('tmDetailsContent');
                    const tmDetailsChevron = document.getElementById('tmDetailsChevron');

                    if (tmToggleDetails && tmDetailsContent && tmDetailsChevron) {
                        tmToggleDetails.addEventListener('click', () => {
                            const isHidden = tmDetailsContent.classList.contains('hidden');
                            tmDetailsContent.classList.toggle('hidden');
                            tmDetailsChevron.style.transform = isHidden ? 'rotate(180deg)' : 'rotate(0deg)';
                            tmToggleDetails.querySelector('span').textContent = isHidden ? 'Hide Details' :
                                'Show Details';
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
                                const tmForwardControls = document.getElementById('tmForwardControls');
                                if (tmForwardControls) tmForwardControls.classList.remove('hidden');
                            }
                        });
                    }

                    // Forward via select + apply
                    const tmForwardSelect = document.getElementById('tmForwardSelect');
                    const tmForwardApply = document.getElementById('tmForwardApply');
                    if (tmForwardApply) {
                        tmForwardApply.addEventListener('click', async () => {
                            if (!currentTicketId) return;
                            if (!tmForwardSelect || !tmForwardSelect.value) {
                                if (window.Swal) {
                                    Swal.fire({
                                        icon: 'warning',
                                        title: 'Selection Required',
                                        text: 'Please choose a role to forward to.',
                                        timer: 3000,
                                        showConfirmButton: false
                                    });
                                } else {
                                    showToast('error', 'Please choose a role to forward to.');
                                }
                                return;
                            }
                            const role = tmForwardSelect.value;

                            // Disable button and show loading
                            tmForwardApply.disabled = true;
                            tmForwardApply.classList.add('opacity-50', 'pointer-events-none');
                            const originalText = tmForwardApply.textContent;
                            tmForwardApply.innerHTML =
                                '<svg class="animate-spin -ml-1 mr-2 h-4 w-4 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg>Forwarding...';

                            try {
                                const res = await fetch(`${forwardBase}/${currentTicketId}/forward`, {
                                    method: 'POST',
                                    headers: {
                                        'Accept': 'application/json',
                                        'Content-Type': 'application/json',
                                        'X-CSRF-TOKEN': csrfToken
                                    },
                                    credentials: 'same-origin',
                                    body: JSON.stringify({
                                        role
                                    })
                                });
                                if (res.ok) {
                                    lastSnapshot = '';
                                    try {
                                        localStorage.setItem('ts_tickets_changed', String(Date.now()));
                                    } catch (e) {}
                                    fetchData();
                                    closeModal();
                                    setTimeout(() => {
                                        if (window.Swal) {
                                            Swal.fire({
                                                icon: 'success',
                                                title: 'Ticket Forwarded',
                                                text: 'The ticket has been successfully forwarded.',
                                                timer: 3000,
                                                showConfirmButton: false
                                            });
                                        } else {
                                            showToast('success', 'Ticket forwarded successfully.');
                                        }
                                    }, 500);
                                } else {
                                    console.error('Forward failed', await res.text());
                                    setTimeout(() => {
                                        if (window.Swal) {
                                            Swal.fire({
                                                icon: 'error',
                                                title: 'Forward Failed',
                                                text: 'Failed to forward the ticket. Please try again.',
                                                timer: 3000,
                                                showConfirmButton: false
                                            });
                                        } else {
                                            showToast('error', 'Failed to forward the ticket.');
                                        }
                                    }, 500);
                                }
                            } catch (err) {
                                console.error('Forward error', err);
                                setTimeout(() => {
                                    if (window.Swal) {
                                        Swal.fire({
                                            icon: 'error',
                                            title: 'Network Error',
                                            text: 'Network error during forward. Please check your connection.',
                                            timer: 3000,
                                            showConfirmButton: false
                                        });
                                    } else {
                                        showToast('error', 'Network error during forward.');
                                    }
                                }, 500);
                            } finally {
                                // Re-enable button
                                tmForwardApply.disabled = false;
                                tmForwardApply.classList.remove('opacity-50', 'pointer-events-none');
                                tmForwardApply.textContent = originalText;
                            }
                        });
                    }

                    // Send response (email via backend)
                    if (tmSendResponse && tmResponse) {
                        tmSendResponse.addEventListener('click', async () => {
                            const msg = tmResponse.value.trim();
                            if (!msg) {
                                alert('Please enter a response message.');
                                return;
                            }
                            if (!currentTicketId) {
                                alert('No ticket selected.');
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
                                    body: JSON.stringify({
                                        message: msg
                                    })
                                });
                                if (res.ok) {
                                    if (window.Swal) {
                                        Swal.fire({
                                            position: 'top-end',
                                            icon: 'success',
                                            title: 'Response email sent',
                                            showConfirmButton: false,
                                            timer: 1500
                                        });
                                    } else {
                                        (window.showToast || showToast)('success', 'Response email sent.');
                                    }
                                    tmResponse.value = '';
                                    // Refresh KPIs and table to reflect ticket Closed status
                                    lastSnapshot = '';
                                    try {
                                        localStorage.setItem('ts_tickets_changed', String(Date.now()));
                                    } catch (e) {}
                                    fetchData();
                                    closeModal();
                                } else {
                                    const txt = await res.text();
                                    console.error('Send response failed', txt);
                                    if (window.Swal) {
                                        Swal.fire({
                                            position: 'top-end',
                                            icon: 'error',
                                            title: 'Failed to send response',
                                            text: 'Please check mail configuration.',
                                            showConfirmButton: false,
                                            timer: 2000
                                        });
                                    } else {
                                        showToast('error',
                                            'Failed to send response. Please check mail configuration. ' + txt);
                                    }
                                }
                            } catch (err) {
                                console.error('Send response error', err);
                                if (window.Swal) {
                                    Swal.fire({
                                        position: 'top-end',
                                        icon: 'error',
                                        title: 'Network error while sending response',
                                        showConfirmButton: false,
                                        timer: 2000
                                    });
                                } else {
                                    (window.showToast || showToast)('error',
                                        'Network error while sending response.');
                                }
                            } finally {
                                tmSendResponse.disabled = false;
                                tmSendResponse.classList.remove('opacity-50', 'pointer-events-none');
                            }
                        });
                    }
                })();
            </script>
        @endsection

        <script>
            // Handle service-worker notification clicks forwarded to the page.
            // When the service worker focuses an existing client it posts a message:
            // { type: 'notification-click', url: 'https://.../staff/tickets/123?ticket_id=123' }
            // We navigate the focused tab to that URL so the staff sees the ticket detail.
            window.addEventListener('message', function(e) {
                try {
                    if (e.data && e.data.type === 'notification-click' && e.data.url) {
                        // Safely navigate to the provided URL in the current tab
                        window.location.href = e.data.url;
                    }
                } catch (_) {
                    /* ignore */
                }
            });
        </script>
