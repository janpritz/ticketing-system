@extends('layouts.app')

@section('title', 'Staff Dashboard')

@section('content')
<div class="min-h-full">
    <nav class="bg-white pt-2">
        <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
            <div class="flex h-16 items-center justify-between">
                <div class="flex items-center">
                    <div class="shrink-0">
                        <img class="size-8" src="/logo.png" alt="Sangkay Logo" />
                    </div>
                    <div class="md:block">
                        <div class="rounded-md  px-3 py-2 text-sm font-medium">Sangkay Chatbot Integrated Ticketing System</div>
                    </div>
                </div>
                <div class="hidden md:block">
                    <div class="flex items-center md:ml-6">
                        <div class="hidden md:block mr-3 text-sm text-gray-700">
                            Welcome back, <span class="font-medium text-gray-900">{{ $user->name }}</span>
                        </div>
                        <!-- Profile dropdown -->
                        <div class="relative ml-3">
                            <div>
                                <button type="button" class="relative flex max-w-xs items-center rounded-full focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-indigo-500" id="user-menu-button" aria-expanded="false" aria-haspopup="true">
                                    <span class="absolute -inset-1.5"></span>
                                    <span class="sr-only">Open user menu</span>
                                    <img class="size-40 rounded-full object-cover w-8 h-8" src="https://images.unsplash.com/photo-1472099645785-5658abf4ff4e?ixlib=rb-1.2.1&ixid=eyJhcHBfaWQiOjEyMDd9&auto=format&fit=facearea&facepad=2&w=256&h=256&q=80" alt="" />
                                </button>
                            </div>
                            <!-- Dropdown menu -->
                            <div class="absolute right-0 z-10 mt-2 w-48 origin-top-right rounded-md bg-white py-1 shadow-lg outline-1 outline-black/5 dark:bg-gray-800 dark:shadow-none dark:-outline-offset-1 dark:outline-white/10 hidden" role="menu" aria-orientation="vertical" aria-labelledby="user-menu-button" id="user-menu">
                                <a href="#" class="block px-4 py-2 text-sm text-gray-700 dark:text-gray-300 hover:bg-gray-100 outline-hidden dark:hover:bg-white/5" role="menuitem">Your profile</a>
                                <a href="{{ route('logout') }}" class="block px-4 py-2 text-sm text-gray-700 dark:text-gray-300 hover:bg-gray-100 outline-hidden dark:hover:bg-white/5" role="menuitem"
                                    onclick="event.preventDefault(); document.getElementById('logout-form').submit();">
                                    Sign out
                                </a>
                                <form id="logout-form" action="{{ route('logout') }}" method="POST" class="hidden">
                                    @csrf
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="-mr-2 flex md:hidden">
                    <!-- Mobile menu button -->
                    <button type="button" class="relative inline-flex items-center justify-center rounded-md p-2 text-gray-400 hover:bg-white/5 hover:text-white focus:outline-2 focus:outline-offset-2 focus:outline-indigo-500" id="mobile-menu-button">
                        <span class="absolute -inset-0.5" />
                        <span class="sr-only">Open main menu</span>
                        <svg id="mobile-menu-open" class="block size-6" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M3.75 6.75h16.5M3.75 12h16.5m-16.5 5.25h16.5" />
                        </svg>
                        <svg id="mobile-menu-close" class="hidden size-6" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </button>
                </div>
            </div>
        </div>

        <div class="md:hidden hidden" id="mobile-menu">
            <div class="border-t border-white/10 pt-4 pb-3">
                <div class="flex items-center px-5">
                    <div class="shrink-0">
                        <img class="size-10 rounded-full outline -outline-offset-1 outline-white/10" src="https://images.unsplash.com/photo-1472099645785-5658abf4ff4e?ixlib=rb-1.2.1&ixid=eyJhcHBfaWQiOjEyMDd9&auto=format&fit=facearea&facepad=2&w=256&h=256&q=80" alt="" />
                    </div>
                    <div class="ml-3">
                        <div class="text-base/5 font-medium text-white">{{ $user->name }}</div>
                        <div class="text-sm font-medium text-gray-400">{{ $user->email }}</div>
                    </div>
                    <button type="button" class="relative ml-auto shrink-0 rounded-full p-1 text-gray-400 hover:text-white focus:outline-2 focus:outline-offset-2 focus:outline-indigo-500">
                        <span class="absolute -inset-1.5" />
                        <span class="sr-only">View notifications</span>
                        <svg class="size-6" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M14.857 17.082a23.848 23.848 0 005.454-1.31A8.967 8.967 0 0118 9.75v-.7V9A6 6 0 006 9v.75a8.967 8.967 0 01-2.312 6.022c1.733.64 3.56 1.085 5.455 1.31m5.714 0a24.255 24.255 0 01-5.714 0m5.714 0a3 3 0 11-5.714 0" />
                        </svg>
                    </button>
                </div>
                <div class="mt-3 space-y-1 px-2">
                    <a href="#" class="block rounded-md px-3 py-2 text-base font-medium text-gray-400 hover:bg-white/5 hover:text-white">Your profile</a>
                    <a href="#" class="block rounded-md px-3 py-2 text-base font-medium text-gray-400 hover:bg-white/5 hover:text-white">Settings</a>
                    <a href="{{ route('logout') }}" class="block rounded-md px-3 py-2 text-base font-medium text-gray-400 hover:bg-white/5 hover:text-white"
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
        <div class="mx-auto max-w-7xl px-4 py-6 sm:px-6 lg:px-8">
            <div class="grid grid-cols-12 gap-6">
                <!-- Left rail -->
                <aside class="col-span-12 md:col-span-3 space-y-6">
                    <!-- Quick Filters -->
                    <div class="bg-white rounded-xl shadow-sm ring-1 ring-slate-900/5 p-5">
                        <h3 class="text-sm font-semibold text-gray-700 mb-4">Quick Filters</h3>

                        <div class="space-y-5">
                            <!-- Status -->
                            <!-- <div>
                                <label class="block text-xs text-gray-500 mb-1">Status</label>
                                <select class="w-full rounded-lg border-gray-200 text-sm focus:ring-2 focus:ring-indigo-500">
                                    <option>All</option>
                                    <option>Open</option>
                                    <option>In Progress</option>
                                    <option>Resolved</option>
                                </select>
                            </div> -->

                            <!-- Priority -->
                            <!-- <div>
                                <label class="block text-xs text-gray-500 mb-1">Priority</label>
                                <select class="w-full rounded-lg border-gray-200 text-sm focus:ring-2 focus:ring-indigo-500">
                                    <option>All</option>
                                    <option>Urgent</option>
                                    <option>High</option>
                                    <option>Medium</option>
                                    <option>Low</option>
                                </select>
                            </div> -->

                            <!-- Category -->
                            <!-- <div>
                                <label class="block text-xs text-gray-500 mb-1">Category</label>
                                <select class="w-full rounded-lg border-gray-200 text-sm focus:ring-2 focus:ring-indigo-500">
                                    <option>All</option>
                                    <option>Enrollment</option>
                                    <option>Finance and Payments</option>
                                    <option>Scholarships</option>
                                    <option>Academic Concerns</option>
                                    <option>Exams</option>
                                    <option>Student Services</option>
                                    <option>Library Services</option>
                                    <option>IT Support</option>
                                    <option>Graduation</option>
                                    <option>Athletics and Sports</option>
                                </select>
                            </div>  -->
                            <!-- Only my tickets -->
                            <div class="flex items-center justify-between">
                                <span class="ms-3 text-sm font-medium text-gray-900 dark:text-gray-300">View All My Tickets</span>
                                <label class="inline-flex items-center cursor-pointer">
                                    <input type="checkbox" id="toggleViewAll" aria-label="View all my tickets" class="sr-only peer">
                                    <div class="relative w-11 h-6 bg-gray-200 peer-focus:outline-none peer-focus:ring-4 peer-focus:ring-blue-300 dark:peer-focus:ring-blue-800 rounded-full peer dark:bg-gray-700 peer-checked:after:translate-x-full rtl:peer-checked:after:-translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:start-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all dark:border-gray-600 peer-checked:bg-blue-600 dark:peer-checked:bg-blue-600"></div>
                                </label>
                            </div>
                        </div>
                    </div>

                    <!-- Weekly Throughput -->
                    <div class="bg-white rounded-xl shadow-sm ring-1 ring-slate-900/5 p-5">
                        <h3 class="text-sm font-semibold text-gray-700">Weekly Throughput</h3>
                        <div class="mt-5">
                            <!-- Tiny bar chart placeholder -->
                            <div class="h-40 flex items-end justify-between">
                                <div class="w-6 bg-indigo-200 rounded-t" style="height: 60%"></div>
                                <div class="w-6 bg-indigo-300 rounded-t" style="height: 40%"></div>
                                <div class="w-6 bg-indigo-200 rounded-t" style="height: 25%"></div>
                                <div class="w-6 bg-indigo-400 rounded-t" style="height: 80%"></div>
                                <div class="w-6 bg-indigo-300 rounded-t" style="height: 35%"></div>
                                <div class="w-6 bg-indigo-400 rounded-t" style="height: 70%"></div>
                                <div class="w-6 bg-indigo-200 rounded-t" style="height: 30%"></div>
                            </div>
                            <div class="mt-3 text-xs text-gray-500 flex justify-between">
                                <span>This Week</span>
                                <span>2 Weeks Ago</span>
                            </div>
                        </div>
                    </div>
                </aside>

                <!-- Main content -->
                <section class="col-span-12 md:col-span-9 space-y-6">
                    <!-- KPI cards -->
                    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
                        <!-- Open -->
                        <div class="bg-white rounded-xl shadow-sm ring-1 ring-slate-900/5 p-5">
                            <div class="flex items-center justify-between">
                                <div>
                                    <div class="text-sm text-gray-500">Open</div>
                                    <div class="mt-2 text-3xl font-semibold text-gray-900"><span id="openCount">{{ $openCount ?? 0 }}</span></div>
                                </div>
                                <div class="rounded-full bg-blue-50 text-blue-600 p-3 ring-1 ring-blue-600/10">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M3 7.5h18M3 12h18M3 16.5h18" />
                                    </svg>
                                </div>
                            </div>
                        </div>

                        <!-- In Progress -->
                        <div class="bg-white rounded-xl shadow-sm ring-1 ring-slate-900/5 p-5">
                            <div class="flex items-center justify-between">
                                <div>
                                    <div class="text-sm text-gray-500">In Progress</div>
                                    <div class="mt-2 text-3xl font-semibold text-gray-900"><span id="inProgressCount">{{ $inProgressCount ?? 0 }}</span></div>
                                </div>
                                <div class="rounded-full bg-amber-50 text-amber-600 p-3 ring-1 ring-amber-600/10">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 6v6l3 3" />
                                    </svg>
                                </div>
                            </div>
                        </div>

                        <!-- Resolved -->
                        <div class="bg-white rounded-xl shadow-sm ring-1 ring-slate-900/5 p-5">
                            <div class="flex items-center justify-between">
                                <div>
                                    <div class="text-sm text-gray-500">Resolved</div>
                                    <div class="mt-2 text-3xl font-semibold text-gray-900"><span id="closedCount">{{ $closedCount ?? 0 }}</span></div>
                                </div>
                                <div class="rounded-full bg-emerald-50 text-emerald-600 p-3 ring-1 ring-emerald-600/10">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M4.5 12.75l6 6 9-13.5" />
                                    </svg>
                                </div>
                            </div>
                        </div>

                        <!-- Total -->
                        <div class="bg-white rounded-xl shadow-sm ring-1 ring-slate-900/5 p-5">
                            <div class="flex items-center justify-between">
                                <div>
                                    <div class="text-sm text-gray-500">Total</div>
                                    <div class="mt-2 text-3xl font-semibold text-gray-900"><span id="totalCount">{{ $totalCount ?? 0 }}</span></div>
                                </div>
                                <div class="rounded-full bg-slate-50 text-slate-600 p-3 ring-1 ring-slate-600/10">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M4 6h16M4 12h16M4 18h16" />
                                    </svg>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Tickets Table -->
                    <div class="bg-white rounded-xl shadow-sm ring-1 ring-slate-900/5">
                        <!-- Header -->
                        <div class="px-5 py-4 flex items-center justify-between">
                            <h2 id="ticketsHeading" class="text-base font-semibold text-gray-800">Open Tickets</h2>
                            <!-- <div class="flex items-center gap-3">
                                <button type="button" class="inline-flex items-center gap-2 rounded-lg border border-gray-200 bg-white px-3 py-2 text-sm text-gray-700 hover:bg-gray-50">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" viewBox="0 0 24 24" fill="currentColor"><path d="M3 5.25A.75.75 0 013.75 4.5h16.5a.75.75 0 01.6 1.2l-5.4 7.2v5.55a.75.75 0 01-1.05.69l-3-1.2a.75.75 0 01-.45-.69v-4.35L3.15 5.7a.75.75 0 01-.15-.45z"/></svg>
                                    More Filters
                                </button>
                                <a href="{{ route('tickets.create') }}" class="inline-flex items-center gap-2 rounded-lg bg-indigo-600 px-3 py-2 text-sm font-medium text-white hover:bg-indigo-700">
                                    <span class="inline-flex size-5 items-center justify-center rounded-md bg-white/10">+</span>
                                    New Ticket
                                </a>
                            </div> -->
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
                                    'In-Progress' => 'text-amber-700 bg-amber-50 ring-amber-600/20',
                                    'Closed' => 'text-emerald-700 bg-emerald-50 ring-emerald-600/20',
                                    ];
                                    @endphp

                                    @forelse(($recentTickets ?? []) as $t)
                                    @php
                                    $ticketNo = 'T-' . \Illuminate\Support\Carbon::parse($t->date_created)->format('Y') . '-' . str_pad($t->id, 4, '0', STR_PAD_LEFT);
                                    $style = $statusStyles[$t->status] ?? 'text-slate-700 bg-slate-50 ring-slate-600/20';
                                    @endphp
                                    <tr class="hover:bg-gray-50">
                                        <!-- Ticket -->
                                        <td class="py-4 pl-5 pr-3 align-top">
                                            <div class="text-indigo-700 font-medium">{{ $ticketNo }}</div>
                                            <div class="mt-1 text-xs text-gray-500">
                                                {{ \Illuminate\Support\Carbon::parse($t->date_created)->format('n/j/Y, g:i A') }}
                                            </div>
                                        </td>

                                        <!-- Subject -->
                                        <td class="px-3 py-4 align-top">
                                            <div class="text-gray-900">{{ \Illuminate\Support\Str::limit($t->question, 80) }}</div>
                                            <div class="mt-1 text-xs text-gray-500 flex items-center gap-2">
                                                <span class="inline-flex items-center rounded-full bg-gray-100 px-2 py-0.5 text-[11px] font-medium text-gray-700">{{ $t->category }}</span>
                                            </div>
                                        </td>

                                        <!-- Status -->
                                        <td class="px-3 py-4 align-top">
                                            <span class="inline-flex items-center gap-1 rounded-full px-2.5 py-1 text-xs font-medium ring-1 {{ $style }}">
                                                <svg xmlns="http://www.w3.org/2000/svg" class="h-3.5 w-3.5" viewBox="0 0 24 24" fill="currentColor">
                                                    <circle cx="12" cy="12" r="5"></circle>
                                                </svg>
                                                {{ $t->status }}
                                            </span>
                                        </td>

                                        <!-- Assignee -->
                                        <td class="px-3 py-4 align-top">
                                            <div class="text-gray-900">{{ optional($t->staff)->name ?? '-' }}</div>
                                            <div class="mt-1 text-xs text-gray-500">Updated {{ \Illuminate\Support\Carbon::parse($t->updated_at)->format('n/j/Y, g:i A') }}</div>
                                        </td>

                                        <!-- Actions -->
                                        <td class="px-3 py-4 align-top">
                                            <a href="#"
                                               data-action="view"
                                               data-id="{{ $t->id }}"
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
                                                <svg xmlns="http://www.w3.org/2000/svg" class="h-3.5 w-3.5" viewBox="0 0 24 24" fill="currentColor">
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
                    </div>
                </section>
            </div>
        </div>
    </main>
</div>
@endsection

<!-- Ticket View Modal -->
<div id="ticketModal" class="fixed inset-0 z-50 hidden">
  <div class="absolute inset-0 bg-black/50" data-modal-backdrop></div>
  <div class="relative mx-auto my-10 w-[90%] max-w-3xl">
    <div class="bg-white rounded-xl shadow-xl ring-1 ring-black/5">
      <div class="flex items-center justify-between px-5 py-4 border-b">
        <div>
          <div id="tmTicketNo" class="text-sm font-semibold text-gray-900">Ticket</div>
          <div id="tmDates" class="text-xs text-gray-500"></div>
        </div>
        <div class="relative flex items-center gap-2">
          <div class="relative">
            <button type="button" id="tmOptionsBtn" class="inline-flex items-center justify-center rounded-md p-1.5 text-gray-600 hover:bg-gray-100" aria-haspopup="true" aria-expanded="false" title="Options">
              <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 24 24" fill="currentColor"><path d="M5 12a2 2 0 114 0 2 2 0 01-4 0zm5 0a2 2 0 114 0 2 2 0 01-4 0zm5 0a2 2 0 114 0 2 2 0 01-4 0z"/></svg>
            </button>
            <div id="tmOptionsMenu" class="absolute right-0 mt-2 w-40 bg-white rounded-md shadow-lg ring-1 ring-black/5 hidden z-10">
              <button type="button" class="w-full text-left px-3 py-2 text-xs text-gray-700 hover:bg-gray-50" data-option="toggle-history">Show History</button>
              <button type="button" id="tmOptionReroute" class="w-full text-left px-3 py-2 text-xs text-gray-700 hover:bg-gray-50" data-option="show-reroute">Reroute…</button>
            </div>
          </div>
          <button type="button" class="text-gray-500 hover:text-gray-700" aria-label="Close" data-modal-close>
            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
            </svg>
          </button>
        </div>
      </div>
      <div class="px-5 py-4 space-y-4">
        <div class="flex items-center gap-2">
          <span id="tmStatus" class="inline-flex items-center gap-1 rounded-full px-2.5 py-1 text-xs font-medium ring-1"></span>
          <span id="tmCategory" class="inline-flex items-center rounded-full bg-gray-100 px-2 py-0.5 text-[11px] font-medium text-gray-700"></span>
        </div>
        <div>
          <div class="text-xs text-gray-500">Subject</div>
          <div id="tmSubject" class="text-sm font-medium text-gray-900"></div>
        </div>
        <div>
          <div class="text-xs text-gray-500">Question</div>
          <div id="tmQuestion" class="text-sm text-gray-800 whitespace-pre-wrap"></div>
        </div>
        <div class="grid grid-cols-2 gap-4">
          <div>
            <div class="text-xs text-gray-500">Email</div>
            <div id="tmEmail" class="text-sm text-gray-800"></div>
          </div>
          <div>
            <div class="text-xs text-gray-500">Recipient ID</div>
            <div id="tmRecepient" class="text-sm text-gray-800"></div>
          </div>
        </div>
        <div id="tmStoredResponseBlock" class="rounded-lg border border-emerald-200 bg-emerald-50/60 p-3 hidden">
          <div class="text-xs font-semibold text-emerald-700 mb-1">Sent Response</div>
          <div id="tmStoredResponse" class="text-sm text-gray-800 whitespace-pre-wrap"></div>
        </div>
        <div class="rounded-lg border border-indigo-200 bg-indigo-50/60 p-3">
          <label for="tmResponse" class="block text-xs font-semibold text-indigo-700 mb-1">Response Message</label>
          <textarea id="tmResponse" class="w-full rounded-md border-indigo-300 text-sm focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 bg-white shadow-sm" rows="5" placeholder="Type your response..."></textarea>
        </div>
      </div>
      <div class="px-5 py-3 border-t flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
        <div class="flex items-center gap-2">
          <label for="tmRerouteSelect" class="text-xs text-gray-500">Reroute to</label>
          @php
            $roles = ['Primary Administrator','Enrollment','Finance and Payments','Scholarships','Academic Concerns','Exams','Student Services','Library Services','IT Support','Graduation','Athletics and Sports'];
          @endphp
          <select id="tmRerouteSelect" class="rounded-md border-gray-300 text-xs focus:ring-2 focus:ring-indigo-500">
            <option value="" selected disabled>Select a role</option>
            @foreach($roles as $role)
              <option value="{{ $role }}">{{ $role }}</option>
            @endforeach
          </select>
          <button type="button" class="inline-flex items-center rounded-md bg-white px-2.5 py-1.5 text-xs font-medium text-gray-700 ring-1 ring-gray-200 hover:bg-gray-50" id="tmRerouteApply">Apply</button>
        </div>
        <div class="flex items-center gap-2 ml-auto">
          <button type="button" class="inline-flex items-center rounded-md bg-white px-3 py-1.5 text-xs font-medium text-gray-700 ring-1 ring-gray-200 hover:bg-gray-50" data-modal-close>Close</button>
          <button type="button" title="Send response" aria-label="Send response" class="inline-flex items-center justify-center rounded-full bg-indigo-600 size-8 text-white hover:bg-indigo-700" id="tmSendResponse">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" viewBox="0 0 24 24" fill="currentColor"><path d="M3 12l18-9-9 18-2-7-7-2z"/></svg>
          </button>
        </div>
      </div>
    </div>
  </div>
</div>

@section('scripts')
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
        let ticketsMap = new Map();
 
        const statusStyles = {
            'Open': 'text-blue-700 bg-blue-50 ring-blue-600/20',
            'In-Progress': 'text-amber-700 bg-amber-50 ring-amber-600/20',
            'Closed': 'text-emerald-700 bg-emerald-50 ring-emerald-600/20',
        };

        let lastSnapshot = '';

        function pad(num, size) {
            num = String(num);
            while (num.length < size) num = '0' + num;
            return num;
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

        function renderTickets(tickets) {
            if (!ticketsBodyEl) return;
            if (!Array.isArray(tickets)) tickets = [];

            // Build HTML rows
            const rows = tickets.map(t => {
                const year = t.date_created ? new Date(t.date_created).getFullYear() : (new Date(t.created_at || Date.now())).getFullYear();
                const ticketNo = `T-${year}-${pad(t.id, 4)}`;
                const style = statusStyles[t.status] || 'text-slate-700 bg-slate-50 ring-slate-600/20';
                const updatedAt = fmtDate(t.updated_at);
                const createdAt = fmtDate(t.date_created || t.created_at);
                const assignee = (t.staff && t.staff.name) ? t.staff.name : '-';
                const category = t.category ?? '';

                const subject = (t.question ?? '').length > 80 ? (t.question ?? '').slice(0, 77) + '...' : (t.question ?? '');

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
            const url = dataUrl + '?viewAll=' + (viewAll ? 'true' : 'false');

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
                // Keep a fast lookup for "View" modal
                ticketsMap = new Map(list.map(t => [String(t.id), t]));
                // Show both Open and In-Progress when not viewing all
                const filtered = viewAll ? list : list.filter(t => (t.status === 'Open' || t.status === 'In-Progress'));
  
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

        // Initial load + polling interval
        fetchData();
        const POLL_MS = 5000; // 5 seconds
        setInterval(fetchData, POLL_MS);
        document.addEventListener('visibilitychange', fetchData);
 
        // React to toggle changes immediately
        if (toggleEl) {
            toggleEl.addEventListener('change', () => {
                // force re-render next tick
                lastSnapshot = '';
                fetchData();
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
        const tmOptionReroute = document.getElementById('tmOptionReroute');
        const tmStoredResponseBlock = document.getElementById('tmStoredResponseBlock');
        const tmStoredResponse = document.getElementById('tmStoredResponse');
 
        const csrfToken = '{{ csrf_token() }}';
        const rerouteBase = "{{ url('/staff/tickets') }}";
        let currentTicketId = null;

        function statusClassFor(s) {
            const base = statusStyles[s] || 'text-slate-700 bg-slate-50 ring-slate-600/20';
            return base;
        }
 
        function ensureHistorySection() {
            const resp = document.getElementById('tmResponse');
            const container = resp ? resp.closest('.space-y-4') : null;
            let section = document.getElementById('tmHistorySection');
            if (!section && container) {
                section = document.createElement('div');
                section.id = 'tmHistorySection';
                section.className = 'rounded-lg border border-gray-200 bg-gray-50/50 p-3 hidden';
                section.innerHTML = `
                  <div class="text-xs font-semibold text-gray-700 mb-2">Routing History</div>
                  <ul id="tmHistoryList" class="space-y-2"></ul>
                `;
                const responseBlock = resp ? resp.parentElement : null;
                if (responseBlock) container.insertBefore(section, responseBlock);
            }
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

        function openModalFrom(ticket) {
            if (!modalEl || !ticket) return;
            const year = ticket.date_created ? new Date(ticket.date_created).getFullYear() : (new Date(ticket.created_at || Date.now())).getFullYear();
            const ticketNo = `T-${year}-${pad(ticket.id, 4)}`;
            const createdAt = fmtDate(ticket.date_created || ticket.created_at);
            const updatedAt = fmtDate(ticket.updated_at);
            const category = ticket.category ?? '';
            const subject = category ? `${category} - ${(ticket.question ?? '').slice(0, 80)}` : ((ticket.question ?? '').slice(0, 80));
            const question = ticket.question ?? '';
            const email = ticket.email ?? '';
            const recepient = ticket.recepient_id ?? '';

            // Fill fields
            if (tmTicketNo) tmTicketNo.textContent = ticketNo;
            if (tmDates) tmDates.textContent = createdAt ? `Created ${createdAt}${updatedAt ? ' • Updated ' + updatedAt : ''}` : '';
            if (tmStatus) {
                tmStatus.className = 'inline-flex items-center gap-1 rounded-full px-2.5 py-1 text-xs font-medium ring-1 ' + statusClassFor(ticket.status);
                tmStatus.innerHTML = `<svg xmlns="http://www.w3.org/2000/svg" class="h-3.5 w-3.5" viewBox="0 0 24 24" fill="currentColor"><circle cx="12" cy="12" r="5"></circle></svg> ${ticket.status ?? ''}`;
            }
            if (tmCategory) tmCategory.textContent = category;
            if (tmSubject) tmSubject.textContent = subject;
            if (tmQuestion) tmQuestion.textContent = question;
            if (tmEmail) tmEmail.textContent = email;
            if (tmRecepient) tmRecepient.textContent = recepient;
            if (tmResponse) tmResponse.value = '';

            // Hide reroute controls initially
            const tmRerouteSelectEl = document.getElementById('tmRerouteSelect');
            const tmRerouteControls = tmRerouteSelectEl ? tmRerouteSelectEl.parentElement : null;
            if (tmRerouteControls) tmRerouteControls.classList.add('hidden');

            // Prepare and render history; keep hidden by default until toggled in Options
            const hsObj = ensureHistorySection();
            if (hsObj.section) hsObj.section.classList.add('hidden');
            const histories = ticket.routing_histories || ticket.routingHistories || [];
            renderHistory(Array.isArray(histories) ? histories : []);

            // Toggle reroute option and response display based on status
            const isClosed = (ticket.status === 'Closed');
            if (tmOptionReroute) tmOptionReroute.classList.toggle('hidden', isClosed);
            if (tmRerouteControls) tmRerouteControls.classList.toggle('hidden', isClosed);
            if (tmStoredResponseBlock) {
                if (isClosed) {
                    tmStoredResponseBlock.classList.remove('hidden');
                    if (tmStoredResponse) tmStoredResponse.textContent = ticket.response ? String(ticket.response) : 'No response on record.';
                } else {
                    tmStoredResponseBlock.classList.add('hidden');
                    if (tmStoredResponse) tmStoredResponse.textContent = '';
                }
            }
            if (tmResponse) {
                tmResponse.disabled = isClosed;
                tmResponse.placeholder = isClosed ? 'Ticket is closed. Response cannot be edited.' : 'Type your response...';
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
                        staff: { name: a.getAttribute('data-staff') || '' },
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
 
        // Options dropdown
        if (tmOptionsBtn && tmOptionsMenu) {
            tmOptionsBtn.addEventListener('click', (e) => {
                e.stopPropagation();
                const isOpen = !tmOptionsMenu.classList.contains('hidden');
                tmOptionsMenu.classList.toggle('hidden', isOpen);
                tmOptionsBtn.setAttribute('aria-expanded', String(!isOpen));
            });
 
            document.addEventListener('click', (e) => {
                if (!tmOptionsMenu.contains(e.target) && e.target !== tmOptionsBtn) {
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
                } else if (action === 'show-reroute') {
                    const tmRerouteSelectEl = document.getElementById('tmRerouteSelect');
                    const tmRerouteControls = tmRerouteSelectEl ? tmRerouteSelectEl.parentElement : null;
                    if (tmRerouteControls) tmRerouteControls.classList.remove('hidden');
                }
            });
        }

        // Reroute via select + apply
        const tmRerouteSelect = document.getElementById('tmRerouteSelect');
        const tmRerouteApply = document.getElementById('tmRerouteApply');
        if (tmRerouteApply) {
            tmRerouteApply.addEventListener('click', async () => {
                if (!currentTicketId) return;
                if (!tmRerouteSelect || !tmRerouteSelect.value) {
                    alert('Please choose a role to reroute to.');
                    return;
                }
                const role = tmRerouteSelect.value;
                try {
                    const res = await fetch(`${rerouteBase}/${currentTicketId}/reroute`, {
                        method: 'POST',
                        headers: {
                            'Accept': 'application/json',
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': csrfToken
                        },
                        credentials: 'same-origin',
                        body: JSON.stringify({ role })
                    });
                    if (res.ok) {
                        lastSnapshot = '';
                        fetchData();
                        closeModal();
                    } else {
                        console.error('Reroute failed', await res.text());
                        alert('Reroute failed. Please ensure backend route is available.');
                    }
                } catch (err) {
                    console.error('Reroute error', err);
                    alert('Network error during reroute.');
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
                    const res = await fetch(`${rerouteBase}/${currentTicketId}/respond`, {
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
                        alert('Response email sent.');
                        tmResponse.value = '';
                        // Refresh KPIs and table to reflect ticket Closed status
                        lastSnapshot = '';
                        fetchData();
                        closeModal();
                    } else {
                        const txt = await res.text();
                        console.error('Send response failed', txt);
                        alert('Failed to send response. Please check mail configuration. ' + txt);
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
@endsection