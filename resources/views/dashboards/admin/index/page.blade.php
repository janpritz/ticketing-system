@extends('layouts.admin')

@section('title', 'Admin Dashboard')

@section('admin-content')
    <!-- Document Training Alert -->
    <div id="trainingAlert" class="hidden bg-orange-50 border-l-4 border-orange-400 p-4 mb-4">
        <div class="flex items-center justify-between">
            <div class="flex items-center">
                <div class="flex-shrink-0">
                    <svg class="h-5 w-5 text-orange-400" viewBox="0 0 20 20" fill="currentColor">
                        <path fill-rule="evenodd"
                            d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z"
                            clip-rule="evenodd" />
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
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M13 10V3L4 14h7v7l9-11h-7z" />
                    </svg>
                    <span id="trainBtnText">Train Rasa</span>
                    <svg class="ml-2 h-4 w-4 hidden" id="trainSpinner" fill="none" viewBox="0 0 24 24">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor"
                            stroke-width="4"></circle>
                        <path class="opacity-75" fill="currentColor"
                            d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z">
                        </path>
                    </svg>
                </button>
            </div>
        </div>
    </div>

    <!-- Metrics -->
    <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
        <!-- Total Open Tickets -->
        <a href="{{ route('admin.tickets.index') }}"
            class="block bg-white rounded-xl border border-gray-200 p-4 hover:bg-gray-50 transition-colors">
            <div class="flex items-start justify-between">
                <div>
                    <div class="text-xs font-medium text-slate-500">Total Open Tickets</div>
                    <div class="mt-2"><span id="openTicketsCount"
                            class="text-2xl sm:text-2xl font-bold text-slate-900">{{ number_format($openTickets ?? 0) }}</span>
                    </div>
                    @if (($openTicketsDelta ?? 0) > 0)
                        <div id="openTicketsDeltaWrap" class="mt-1 text-xs text-emerald-600 flex items-center gap-1">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-3.5 w-3.5" viewBox="0 0 24 24"
                                fill="currentColor">
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
        <div id="activeStaffCard" class="bg-white rounded-xl border border-gray-200 p-4 cursor-pointer hover:bg-gray-50"
            role="button" tabindex="0" aria-label="Open active staff list">
            <div class="flex items-start justify-between">
                <div>
                    <div class="text-xs font-medium text-slate-500">Active Staff (last 10 min)</div>
                    <div class="mt-2 flex items-center gap-2">
                        <div id="activeStaffDot"
                            class="w-4 h-4 rounded-full {{ ($activeStaffCount ?? 0) > 0 ? '' : 'hidden' }}"></div>
                        <span id="activeStaffCountText"
                            class="text-2xl sm:text-2xl font-bold text-slate-900">{{ $activeStaffCount ?? 0 }}</span>
                    </div>
                </div>
                <div class="rounded-md bg-purple-50 p-2 text-purple-600 border border-purple-100">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 24 24" fill="currentColor">
                        <path
                            d="M16 11c1.66 0 2.99-1.34 2.99-3S17.66 5 16 5s-3 1.34-3 3 1.34 3 3 3zM8 11c1.66 0 2.99-1.34 2.99-3S9.66 5 8 5 5 6.34 5 8s1.34 3 3 3zm0 2c-2.33 0-7 1.17-7 3.5V19h14v-2.5C15 14.17 10.33 13 8 13zm8 0c-.29 0-.62.02-.97.05 1.16.84 1.97 1.97 1.97 3.45V19h6v-2.5c0-2.33-4.67-3.5-7-3.5z" />
                    </svg>
                </div>
            </div>
        </div>
        <!-- Last Rasa Training -->
        <div class="block bg-white rounded-xl border border-gray-200 p-4 hover:bg-gray-50 transition-colors">
            <div class="flex items-start justify-between">
                <div>
                    <div class="text-xs font-medium text-slate-500">Last Rasa Training</div>
                    <div class="mt-2"><span id="lastTrainingValue"
                            class="text-2xl sm:text-2xl font-bold text-slate-900">{{ $lastTraining ?? 'Never' }}</span>
                    </div>
                </div>
                <div class="rounded-md bg-purple-50 p-2 text-purple-600 border border-purple-100">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 24 24" fill="currentColor">
                        <path
                            d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm-2 15l-5-5 1.41-1.41L10 14.17l7.59-7.59L19 8l-9 9z" />
                    </svg>
                </div>
            </div>
        </div>
        <!-- Rasa Server Status -->
        <div class="block bg-white rounded-xl border border-gray-200 p-4 hover:bg-gray-50 transition-colors cursor-pointer"
            id="rasaStatusCard">
            <div class="flex items-start justify-between">
                <div>
                    <div class="text-xs font-medium text-slate-500">Rasa Server Status</div>
                    <div class="mt-2"><span id="rasaStatusText"
                            class="text-2xl sm:text-2xl font-bold text-slate-900">Checking...</span></div>
                </div>
                <div class="rounded-md p-2 border" id="rasaStatusIcon">
                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor"
                        class="bi bi-globe" viewBox="0 0 16 16">
                        <path
                            d="M0 8a8 8 0 1 1 16 0A8 8 0 0 1 0 8m7.5-6.923c-.67.204-1.335.82-1.887 1.855A8 8 0 0 0 5.145 4H7.5zM4.09 4a9.3 9.3 0 0 1 .64-1.539 7 7 0 0 1 .597-.933A7.03 7.03 0 0 0 2.255 4zm-.582 3.5c.03-.877.138-1.718.312-2.5H1.674a7 7 0 0 0-.656 2.5zM4.847 5a12.5 12.5 0 0 0-.338 2.5H7.5V5zM8.5 5v2.5h2.99a12.5 12.5 0 0 0-.337-2.5zM4.51 8.5a12.5 12.5 0 0 0 .337 2.5H7.5V8.5zm3.99 0V11h2.653c.187-.765.306-1.608.338-2.5zM5.145 12q.208.58.468 1.068c.552 1.035 1.218 1.65 1.887 1.855V12zm.182 2.472a7 7 0 0 1-.597-.933A9.3 9.3 0 0 1 4.09 12H2.255a7 7 0 0 0 3.072 2.472M3.82 11a13.7 13.7 0 0 1-.312-2.5h-2.49c.062.89.291 1.733.656 2.5zm6.853 3.472A7 7 0 0 0 13.745 12H11.91a9.3 9.3 0 0 1-.64 1.539 7 7 0 0 1-.597.933M8.5 12v2.923c.67-.204 1.335-.82 1.887-1.855q.26-.487.468-1.068zm3.68-1h2.146c.365-.767.594-1.61.656-2.5h-2.49a13.7 13.7 0 0 1-.312 2.5m2.802-3.5a7 7 0 0 0-.656-2.5H12.18c.174.782.282 1.623.312 2.5zM11.27 2.461c.247.464.462.98.64 1.539h1.835a7 7 0 0 0-3.072-2.472c.218.284.418.598.597.933M10.855 4a8 8 0 0 0-.468-1.068C9.835 1.897 9.17 1.282 8.5 1.077V4z" />
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
                                <div class="text-gray-900">{{ $row['email'] ?: '—' }}</div>
                            </td>
                            <td class="px-3 py-3 align-top">
                                <span class="font-medium text-slate-900">{{ (int) ($row['count'] ?? 0) }}</span>
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
            $badge = fn($status) => match ($status) {
                'Open' => 'text-blue-700 bg-blue-50 ring-blue-600/20',
                'Forwarded' => 'text-amber-700 bg-amber-50 ring-amber-600/20',
                'Closed' => 'text-emerald-700 bg-emerald-50 ring-emerald-600/20',
                default => 'text-slate-700 bg-slate-50 ring-slate-600/20',
            };
        @endphp


        <!-- Unassigned Tickets -->
        <div class="bg-white rounded-xl border border-gray-200 overflow-hidden">
            <div class="px-5 py-4 border-b border-gray-300 flex items-center justify-between">
                <h3 class="text-sm font-semibold text-slate-800">Unassigned Tickets</h3>
                <button type="button" id="refreshUnassignedBtn"
                    class="inline-flex items-center gap-2 rounded-lg border border-gray-200 bg-white px-3 py-1.5 text-xs font-medium text-gray-700 hover:bg-gray-50 transition-colors"
                    title="Refresh unassigned tickets">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" viewBox="0 0 24 24" fill="currentColor">
                        <path
                            d="M17.65 6.35C16.2 4.9 14.21 4 12 4c-4.42 0-7.99 3.58-7.99 8s3.57 8 7.99 8c3.73 0 6.84-2.55 7.73-6h-2.08c-.82 2.33-3.04 4-5.65 4-3.31 0-6-2.69-6-6s2.69-6 6-6c1.66 0 3.14.69 4.22 1.78L13 11h7V4l-2.35 2.35z" />
                    </svg>
                    Refresh
                </button>
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
                    <tbody id="unassignedTicketsListBody" class="divide-y divide-gray-100">
                        @forelse(($unassignedTickets ?? []) as $t)
                            @php
                            @endphp
                            <tr class="hover:bg-gray-50 cursor-pointer btn-view" data-id="{{ $t['id'] }}">
                                <td class="py-3 pl-5 pr-3 align-top">
                                    <div class="text-indigo-700 font-medium">{{ $t['id'] }}</div>
                                    <div class="mt-1 text-xs text-gray-500">
                                        Updated
                                        {{ \Illuminate\Support\Carbon::parse($t['updated_at'] ?? $t['date_created'])->format('Y-m-d h:i a') }}
                                    </div>
                                </td>
                                <td class="px-3 py-3 align-top">
                                    <div class="text-gray-900">{{ $t['email'] ?? '—' }}</div>
                                    <div class="text-xs text-gray-500">
                                        {{ $t['staff'] ? 'Staff: ' . $t['staff']['name'] : '' }}</div>
                                </td>
                                <td class="px-3 py-3 align-top">
                                    <span
                                        class="inline-flex items-center gap-1 rounded-full px-2.5 py-1 text-xs font-medium ring-1 {{ $badge($t['status']) }}">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-3.5 w-3.5" viewBox="0 0 24 24"
                                            fill="currentColor">
                                            <circle cx="12" cy="12" r="5"></circle>
                                        </svg>
                                        {{ $t['status'] }}
                                    </span>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="3" class="px-5 py-10 text-center text-sm text-gray-500">No unassigned
                                    tickets.</td>
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
            <div
                class="bg-white shadow-2xl w-full h-full sm:h-auto sm:max-h-[95vh] sm:max-w-2xl overflow-hidden sm:rounded-2xl flex flex-col">

                <!-- Header - Minimal & Clean -->
                <div class="px-4 sm:px-6 py-4 border-b border-gray-100 flex items-center justify-between shrink-0">
                    <div class="flex-1 min-w-0">
                        <div class="flex items-center gap-3 mb-2">
                            <h3 id="tmTicketNo" class="text-lg font-semibold text-gray-900">Ticket #</h3>
                            <span id="tmStatus"
                                class="inline-flex items-center gap-1.5 rounded-full px-3 py-1 text-xs font-medium ring-1"></span>
                        </div>
                    </div>
                    <div class="flex items-center gap-2 ml-4">
                        <!-- Options Menu in Header -->
                        <div class="relative">
                            <button type="button" id="tmOptionsBtn"
                                class="inline-flex items-center gap-2 rounded-lg px-2 py-2 text-sm font-medium text-gray-700 hover:bg-gray-100 transition-colors"
                                aria-haspopup="true" aria-expanded="false">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" viewBox="0 0 24 24"
                                    fill="currentColor">
                                    <path
                                        d="M5 12a2 2 0 114 0 2 2 0 01-4 0zm5 0a2 2 0 114 0 2 2 0 01-4 0zm5 0a2 2 0 114 0 2 2 0 01-4 0z" />
                                </svg>
                                <span class="hidden sm:inline">Options</span>
                            </button>
                            <div id="tmOptionsMenu"
                                class="absolute right-0 top-full mt-2 w-48 bg-white rounded-lg shadow-lg ring-1 ring-black/5 hidden z-10 overflow-hidden">
                                <button type="button"
                                    class="w-full text-left px-4 py-2.5 text-sm text-gray-700 hover:bg-gray-50 transition-colors"
                                    data-option="toggle-history">Show History</button>
                                <button type="button" id="tmOptionAssign"
                                    class="w-full text-left px-4 py-2.5 text-sm text-gray-700 hover:bg-gray-50 transition-colors border-t border-gray-100"
                                    data-option="show-forward">Assign to a Staff</button>
                                <button type="button" id="tmOptionHideForward"
                                    class="w-full text-left px-4 py-2.5 text-sm text-gray-700 hover:bg-gray-50 transition-colors border-t border-gray-100 hidden"
                                    data-option="hide-forward">Hide Forward Controls</button>
                            </div>
                        </div>
                        <button type="button"
                            class="text-gray-400 hover:text-gray-600 transition-colors p-1 rounded-lg hover:bg-gray-100"
                            aria-label="Close" data-modal-close>
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24"
                                stroke="currentColor" stroke-width="2">
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
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 text-gray-500" fill="none"
                                viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M8.228 9c.549-1.165 2.03-2 3.772-2 2.21 0 4 1.343 4 3 0 1.4-1.278 2.575-3.006 2.907-.542.104-.994.54-.994 1.093m0 3h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                            <span class="text-xs font-medium text-gray-500 uppercase tracking-wide">Issue</span>
                        </div>
                        <div id="tmQuestion" class="text-sm text-gray-900 whitespace-pre-wrap leading-relaxed"></div>
                    </div>

                    <!-- Attachments - Visible when present -->
                    <div id="tmAttachmentsBlock" class="hidden">
                        <div class="flex items-center gap-2 mb-3">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 text-gray-500" fill="none"
                                viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M15.172 7l-6.586 6.586a2 2 0 102.828 2.828l6.414-6.586a4 4 0 00-5.656-5.656l-6.415 6.585a6 6 0 108.486 8.486L20.5 13" />
                            </svg>
                            <span class="text-xs font-medium text-gray-700">Attachments</span>
                        </div>
                        <div id="tmAttachmentsList" class="flex flex-wrap gap-2"></div>
                    </div>

                    <!-- Sent Response - For closed tickets -->
                    <div id="tmStoredResponseBlock" class="hidden bg-emerald-50 rounded-xl p-4 border border-emerald-200">
                        <div class="flex items-center gap-2 mb-2">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 text-emerald-600" fill="none"
                                viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                            <span class="text-xs font-semibold text-emerald-700 uppercase tracking-wide">Sent
                                Response</span>
                        </div>
                        <div id="tmStoredResponse" class="text-sm text-gray-800 whitespace-pre-wrap leading-relaxed">
                        </div>
                    </div>

                    <!-- Collapsible Details Section -->
                    <div id="tmDetailsSection" class="border-t border-gray-100 pt-4">
                        <button type="button" id="tmToggleDetails"
                            class="flex items-center justify-between w-full text-left group">
                            <span class="text-sm font-medium text-gray-700 group-hover:text-gray-900">Show Details</span>
                            <svg id="tmDetailsChevron" xmlns="http://www.w3.org/2000/svg"
                                class="h-4 w-4 text-gray-500 transition-transform" fill="none" viewBox="0 0 24 24"
                                stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M19 9l-7 7-7-7" />
                            </svg>
                        </button>

                        <div id="tmDetailsContent" class="hidden mt-4 space-y-4 pb-2">
                            <!-- Category & Dates -->
                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                                <div>
                                    <label
                                        class="text-xs font-medium text-gray-500 uppercase tracking-wide">Category</label>
                                    <div id="tmCategory"
                                        class="mt-1 inline-flex items-center rounded-full bg-gray-100 px-2.5 py-1 text-xs font-medium text-gray-700">
                                    </div>
                                </div>
                                <div>
                                    <label
                                        class="text-xs font-medium text-gray-500 uppercase tracking-wide">Timeline</label>
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
                                    <label class="text-xs font-medium text-gray-500 uppercase tracking-wide">Recipient
                                        ID</label>
                                    <div id="tmRecepient" class="mt-1 text-sm text-gray-800"></div>
                                </div>
                            </div>

                            <!-- Routing History -->
                            <div id="tmHistorySection" class="hidden">
                                <label class="text-xs font-medium text-gray-500 uppercase tracking-wide">Routing
                                    History</label>
                                <ul id="tmHistoryList" class="mt-2 space-y-2"></ul>
                            </div>
                        </div>
                    </div>

                    <!-- Response Input -->
                    <div class="bg-gradient-to-br from-indigo-50 to-blue-50 rounded-xl p-4 border border-indigo-100">
                        <label for="tmResponse" class="flex items-center gap-2 text-sm font-medium text-indigo-900 mb-2">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24"
                                stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" />
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
                            <label for="tmForwardSelect" class="text-xs font-medium text-gray-700" id="tmForwardLabel">Forward to:</label>
                            <select id="tmForwardSelect"
                                class="flex-1 sm:flex-none rounded-lg border-gray-300 text-sm focus:ring-2 focus:ring-indigo-500">
                                @if (!empty($users) && count($users) > 0)
                                    @foreach ($users as $user)
                                        <option value="{{ $user->id }}">{{ $user->name }}
                                            &lt;{{ $user->email }}&gt;</option>
                                    @endforeach
                                @else
                                    <option disabled>No users available</option>
                                @endif
                            </select>
                            <button type="button" id="tmForwardApply"
                                class="inline-flex items-center justify-center rounded-lg bg-amber-600 px-4 py-2 text-sm font-medium text-white hover:bg-amber-700 transition-colors">
                                <span id="tmForwardButtonText">Forward Ticket</span>
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
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" viewBox="0 0 24 24"
                                    fill="currentColor">
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
            <button id="lightboxPrev"
                class="absolute left-4 top-1/2 transform -translate-y-1/2 text-white text-4xl hover:text-gray-300 bg-black bg-opacity-50 rounded-full w-12 h-12 flex items-center justify-center">&larr;</button>
            <button id="lightboxNext"
                class="absolute right-4 top-1/2 transform -translate-y-1/2 text-white text-4xl hover:text-gray-300 bg-black bg-opacity-50 rounded-full w-12 h-12 flex items-center justify-center">&rarr;</button>
            <button id="lightboxClose"
                class="absolute top-4 right-4 text-white text-4xl hover:text-gray-300 bg-black bg-opacity-50 rounded-full w-12 h-12 flex items-center justify-center">&times;</button>
        </div>
    </div>

    <!-- Secondary right-side Contacts nav (hidden by default; toggled by Active Staff card) -->
    <aside id="contacts-aside"
        class="hidden fixed top-0 right-0 z-30 w-72 h-screen bg-white border-l border-gray-200 flex-col">
        <div class="h-12 flex items-center justify-between px-4 border-b">
            <div class="text-[11px] font-semibold text-slate-700 tracking-wide">CONTACTS</div>
        </div>
        <div id="contactsList" class="flex-1 overflow-y-auto p-2 space-y-1">
            <!-- Filled by JS -->
        </div>
    </aside>
    @include('dashboards.admin.index.scripts')
@endsection



<!-- Right-side drawer for Active Staff -->
<div id="activeStaffDrawer" class="fixed inset-0 z-50 hidden" aria-hidden="true">
    <div id="asdOverlay" class="absolute inset-0 bg-black/40"></div>
    <div class="absolute right-0 top-0 h-full w-80 bg-white shadow-xl border-l border-gray-200 flex flex-col">
        <div class="h-12 flex items-center justify-between px-4 border-b">
            <div class="text-sm font-semibold text-slate-800">Active Staff</div>
            <button id="asdClose" type="button" class="text-slate-500 hover:text-slate-700" aria-label="Close">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 24 24" fill="currentColor">
                    <path d="M6 18 18 6M6 6l12 12" />
                </svg>
            </button>
        </div>
        <div id="asdList" class="flex-1 overflow-y-auto p-3 text-sm">
            <!-- Filled dynamically -->
        </div>
    </div>
</div>
<!-- Serialized analytics data for charts (avoid Blade directives inside JS) -->
<div id="analytics-data" class="hidden" data-week-labels='@json($weekLabels ?? [])'
    data-week-data='@json($weekData ?? [])' data-category-labels='@json($categoryLabels ?? [])'
    data-category-data='@json($categoryData ?? [])' data-active-staff='@json($activeStaff ?? [])'
    data-staff-contacts='@json($staffContacts ?? [])' data-admin-url="{{ route('admin.dashboard.data') }}"></div>
<div id="faq-updater-data" class="hidden" data-secret="{{ $faqUpdaterSecret ?? '' }}"
    data-url="{{ $faqUpdaterUrl ?? '' }}"></div>
@section('scripts')

@endsection
