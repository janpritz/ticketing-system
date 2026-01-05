@extends('layouts.staff')

@section('title', 'Staff Reports')

@section('staff-content')
<div class="sm:px-2">
    <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4 mb-6">
        <div>
            <h1 class="text-2xl font-semibold text-slate-900">Reports</h1>
            <p class="text-sm text-slate-500 mt-1">Performance metrics and analysis for your tickets</p>
        </div>
        <div class="flex items-center gap-3">
            <div class="flex items-center gap-2">
                <label class="text-sm font-medium text-gray-700">Time Range:</label>
                <select id="timeRangeSelect" class="rounded-md border border-gray-300 bg-white px-3 py-2 text-sm">
                    <option value="7" {{ ($days ?? 7) == 7 ? 'selected' : '' }}>Last 7 Days</option>
                    <option value="30" {{ ($days ?? 7) == 30 ? 'selected' : '' }}>Last 30 Days</option>
                    <option value="90" {{ ($days ?? 7) == 90 ? 'selected' : '' }}>Last 90 Days</option>
                </select>
            </div>
        </div>
    </div>
</div>

<!-- Performance Dashboard -->
<div class="grid grid-cols-1 md:grid-cols-4 gap-4">
    <!-- Total Tickets -->
    <div class="bg-white rounded-xl border border-gray-200 p-4 cursor-pointer hover:shadow-md transition-shadow" onclick="openModal('totalTicketsModal')">
        <div class="flex items-start justify-between">
            <div>
                <div class="text-xs font-medium text-slate-500">Total Tickets</div>
                <div class="mt-2 text-3xl font-semibold text-slate-900">{{ $performanceMetrics['total_tickets'] }}</div>
            </div>
            <div class="rounded-md bg-blue-50 p-2 text-blue-500 border border-blue-100">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 24 24" fill="currentColor">
                    <path d="M4 4h16v2H4V4zm0 6h16v2H4v-2zm0 6h12v2H4v-2z" />
                </svg>
            </div>
        </div>
    </div>
    <!-- Resolved Tickets -->
    <div class="bg-white rounded-xl border border-gray-200 p-4 cursor-pointer hover:shadow-md transition-shadow" onclick="openModal('resolvedTicketsModal')">
        <div class="flex items-start justify-between">
            <div>
                <div class="text-xs font-medium text-slate-500">Resolved Tickets</div>
                <div class="mt-2 text-3xl font-semibold text-slate-900">{{ $performanceMetrics['resolved_tickets'] }}</div>
            </div>
            <div class="rounded-md bg-green-50 p-2 text-green-500 border border-green-100">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 24 24" fill="currentColor">
                    <path d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
            </div>
        </div>
    </div>
    <!-- Average Resolution Time -->
    <div class="bg-white rounded-xl border border-gray-200 p-4 cursor-pointer hover:shadow-md transition-shadow" onclick="openModal('avgResolutionTimeModal')">
        <div class="flex items-start justify-between">
            <div>
                <div class="text-xs font-medium text-slate-500">Avg Resolution Time</div>
                <div class="mt-2 text-3xl font-semibold text-slate-900">{{ number_format($performanceMetrics['avg_resolution_time'], 1) }}h</div>
            </div>
            <div class="rounded-md bg-yellow-50 p-2 text-yellow-500 border border-yellow-100">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 24 24" fill="currentColor">
                    <path d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
            </div>
        </div>
    </div>
    <!-- Resolution Rate -->
    <div class="bg-white rounded-xl border border-gray-200 p-4 cursor-pointer hover:shadow-md transition-shadow" onclick="openModal('resolutionRateModal')">
        <div class="flex items-start justify-between">
            <div>
                <div class="text-xs font-medium text-slate-500">Resolution Rate</div>
                <div class="mt-2 text-3xl font-semibold text-slate-900">{{ $performanceMetrics['total_tickets'] > 0 ? number_format(($performanceMetrics['resolved_tickets'] / $performanceMetrics['total_tickets']) * 100, 1) : 0 }}%</div>
            </div>
            <div class="rounded-md bg-purple-50 p-2 text-purple-500 border border-purple-100">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 24 24" fill="currentColor">
                    <path d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" />
                </svg>
            </div>
        </div>
    </div>
</div>

<!-- Charts -->
<div class="grid grid-cols-1 lg:grid-cols-2 gap-4">
    <!-- Ticket Status Chart -->
    <div class="bg-white rounded-xl border border-gray-200 p-4">
        <div class="flex items-center justify-between mb-2">
            <h3 class="text-sm font-semibold text-slate-800">Ticket Status Overview</h3>
        </div>
        <div class="h-48">
            <canvas id="ticketStatusChart" class="w-full h-full"></canvas>
        </div>
    </div>

    <!-- Recently Forwarded (to me) card - simple rendering from $recentForwarders passed by controller -->
    <div class="bg-white rounded-xl border border-gray-200 p-4">
        <div class="flex items-center justify-between mb-2">
            <h3 class="text-sm font-semibold text-slate-800">Recently Forwarded (to me)</h3>
        </div>

        <div class="max-h-44 overflow-y-auto mt-2">
            <ul class="divide-y divide-gray-100">
                @if(!empty($recentForwarders))
                    @foreach($recentForwarders as $item)
                        <li class="py-2 text-sm text-gray-800">{{ $item['name'] }} &mdash; <span class="text-gray-500">{{ $item['category'] }}</span></li>
                    @endforeach
                @else
                    <li class="py-4 text-sm text-gray-500">No recently forwarded tickets.</li>
                @endif
            </ul>
        </div>

        @if(isset($totalForwardsCount) && $totalForwardsCount > count($recentForwarders))
            <div class="mt-3 text-right">
                <a href="{{ route('staff.reports.index') }}" class="text-sm text-indigo-600 hover:underline">View all forwards</a>
            </div>
        @endif
    </div>
</div>

<!-- Weekly Tickets Chart -->
<div class="bg-white rounded-xl border border-gray-200 p-4">
    <h3 class="text-sm font-semibold text-slate-800 mb-4">Weekly Ticket Volume</h3>
    <div class="mt-5">
        <div id="weeklyChart" class="h-40 flex items-end justify-between gap-2">
            @php
                $wt = $weeklyThroughput ?? ['series' => [], 'labels' => [], 'max' => 0];
                $series = $wt['series'] ?? [];
                $labels = $wt['labels'] ?? [];
                $max = (int) ($wt['max'] ?? 0);
            @endphp
            @php $dCount = $days ?? 7; @endphp
            @for ($i = 0; $i < $dCount; $i++)
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

<!-- Overdue Tickets -->
<div class="bg-white rounded-xl border border-gray-200 overflow-hidden">
    <div class="px-5 py-4 border-b border-gray-300">
        <h3 class="text-sm font-semibold text-slate-800">Overdue Tickets</h3>
    </div>
    <div class="overflow-x-auto">
        <table class="min-w-full text-sm">
            <thead class="bg-gray-50 text-gray-600">
                <tr>
                    <th class="py-3 pl-5 pr-3 text-left font-medium">Ticket ID</th>
                    <th class="px-3 py-3 text-left font-medium">Subject</th>
                    <th class="px-3 py-3 text-left font-medium">Created</th>
                    <th class="px-3 py-3 text-left font-medium">Status</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100">
                @forelse($overdueTickets as $ticket)
                <tr class="hover:bg-gray-50">
                    <td class="py-3 pl-5 pr-3 align-top">
                        <div class="text-indigo-700 font-medium">{{ $ticket->id }}</div>
                    </td>
                    <td class="px-3 py-3 align-top">
                        <div class="text-gray-900">{{ $ticket->question }}</div>
                    </td>
                    <td class="px-3 py-3 align-top">
                        <div class="text-gray-900">{{ $ticket->created_at->format('Y-m-d H:i') }}</div>
                    </td>
                    <td class="px-3 py-3 align-top">
                        <span class="inline-flex items-center gap-1 rounded-full px-2.5 py-1 text-xs font-medium ring-1 text-amber-700 bg-amber-50 ring-amber-600/20">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-3.5 w-3.5" viewBox="0 0 24 24" fill="currentColor">
                                <circle cx="12" cy="12" r="5"></circle>
                            </svg>
                            {{ $ticket->status }}
                        </span>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="4" class="px-5 py-10 text-center text-sm text-gray-500">No overdue tickets.</td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

{{-- FAQ Analysis --}}
{{-- <div class="bg-white rounded-xl border border-gray-200 p-4">
    <h3 class="text-sm font-semibold text-slate-800 mb-4">Frequently Asked Questions Analysis</h3>
    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
        <div>
            <div class="text-xs font-medium text-slate-500">Processed FAQs</div>
            <div class="mt-2 text-2xl font-semibold text-slate-900">{{ $faqAnalysis['processed_faqs'] }}</div>
        </div>
        <div>
            <div class="text-xs font-medium text-slate-500">Total FAQs</div>
            <div class="mt-2 text-2xl font-semibold text-slate-900">{{ $faqAnalysis['total_faqs'] }}</div>
        </div>
    </div>
</div> --}}

<!-- Modals -->
<!-- Total Tickets Modal -->
<div id="totalTicketsModal" class="fixed inset-0 z-50 overflow-y-auto hidden">
    <div class="absolute inset-0 bg-black/60 backdrop-blur-md transition-opacity" onclick="closeModal('totalTicketsModal')"></div>
    <!-- Centered panel with modern minimal design -->
    <div class="relative mx-auto my-0 sm:my-8 w-full h-full sm:h-auto sm:w-[95%] max-w-2xl flex items-center">
        <div class="bg-white shadow-2xl w-full h-full sm:h-auto sm:max-w-2xl overflow-hidden sm:rounded-2xl flex flex-col">

            <!-- Header - Minimal & Clean -->
            <div class="px-4 sm:px-6 py-4 border-b border-gray-100 flex items-center justify-between shrink-0">
                <div class="flex-1 min-w-0">
                    <h3 class="text-lg font-semibold text-gray-900">Total Tickets Details</h3>
                </div>
                <div class="flex items-center gap-2 ml-4">
                    <button onclick="closeModal('totalTicketsModal')" class="text-gray-400 hover:text-gray-600 transition-colors p-1 rounded-lg hover:bg-gray-100">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </button>
                </div>
            </div>

            <!-- Content - Scrollable -->
            <div class="flex-1 overflow-y-auto px-6 py-5">
                <p class="text-sm text-gray-500 mb-4">All tickets assigned to you</p>
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">ID</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Subject</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Created</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            @php
                                $allTickets = \App\Models\Ticket::where('staff_id', auth()->id())->get();
                            @endphp
                            @forelse($allTickets as $ticket)
                            <tr>
                                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">{{ $ticket->id }}</td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ $ticket->question }}</td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium {{ $ticket->status === 'Closed' ? 'bg-green-100 text-green-800' : 'bg-yellow-100 text-yellow-800' }}">
                                        {{ $ticket->status }}
                                    </span>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ $ticket->created_at->format('Y-m-d H:i') }}</td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="4" class="px-6 py-4 text-center text-sm text-gray-500">No tickets found</td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Footer - Actions -->
            <div class="px-6 py-4 border-t border-gray-100 bg-gray-50 shrink-0">
                <div class="flex flex-col sm:flex-row items-stretch sm:items-center justify-between gap-3">
                    <div class="flex items-center gap-2"></div>
                    <div class="flex items-center gap-2">
                        <button type="button" onclick="closeModal('totalTicketsModal')" class="flex-1 sm:flex-none inline-flex items-center justify-center rounded-lg bg-white px-4 py-2 text-sm font-medium text-gray-700 ring-1 ring-gray-300 hover:bg-gray-50 transition-colors">
                            Close
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Resolved Tickets Modal -->
<div id="resolvedTicketsModal" class="fixed inset-0 z-50 overflow-y-auto hidden">
    <div class="absolute inset-0 bg-black/60 backdrop-blur-md transition-opacity" onclick="closeModal('resolvedTicketsModal')"></div>
    <div class="relative mx-auto my-0 sm:my-8 w-full h-full sm:h-auto sm:w-[95%] max-w-2xl flex items-center">
        <div class="bg-white shadow-2xl w-full h-full sm:h-auto sm:max-w-2xl overflow-hidden sm:rounded-2xl flex flex-col">

            <div class="px-4 sm:px-6 py-4 border-b border-gray-100 flex items-center justify-between shrink-0">
                <div class="flex-1 min-w-0">
                    <h3 class="text-lg font-semibold text-gray-900">Resolved Tickets Details</h3>
                </div>
                <div class="flex items-center gap-2 ml-4">
                    <button onclick="closeModal('resolvedTicketsModal')" class="text-gray-400 hover:text-gray-600 transition-colors p-1 rounded-lg hover:bg-gray-100">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </button>
                </div>
            </div>

            <div class="flex-1 overflow-y-auto px-6 py-5">
                <p class="text-sm text-gray-500 mb-4">All resolved tickets assigned to you</p>
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">ID</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Subject</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Resolution Time</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Resolved Date</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            @php
                                $resolvedTickets = \App\Models\Ticket::where('staff_id', auth()->id())
                                    ->where('status', 'Closed')
                                    ->get();
                            @endphp
                            @forelse($resolvedTickets as $ticket)
                            <tr>
                                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">{{ $ticket->id }}</td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ $ticket->question }}</td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                    @php
                                        // Show resolution time in minutes and seconds; ensure non-negative
                                        $created = $ticket->created_at ? $ticket->created_at->getTimestamp() : null;
                                        $updated = $ticket->updated_at ? $ticket->updated_at->getTimestamp() : null;
                                        if ($created === null || $updated === null) {
                                            $seconds = 0;
                                        } else {
                                            $seconds = max(0, $updated - $created);
                                        }
                                        $hours = intdiv($seconds, 3600);
                                        $minutes = intdiv(($seconds % 3600), 60);
                                    @endphp
                                    @if($hours > 0)
                                        {{ $hours }}h {{ $minutes }}m
                                    @else
                                        {{ $minutes }}m
                                    @endif
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ $ticket->updated_at->format('F j, Y g:i A') }}</td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="4" class="px-6 py-4 text-center text-sm text-gray-500">No resolved tickets found</td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

            <div class="px-6 py-4 border-t border-gray-100 bg-gray-50 shrink-0">
                <div class="flex flex-col sm:flex-row items-stretch sm:items-center justify-between gap-3">
                    <div class="flex items-center gap-2"></div>
                    <div class="flex items-center gap-2">
                        <button type="button" onclick="closeModal('resolvedTicketsModal')" class="flex-1 sm:flex-none inline-flex items-center justify-center rounded-lg bg-white px-4 py-2 text-sm font-medium text-gray-700 ring-1 ring-gray-300 hover:bg-gray-50 transition-colors">
                            Close
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Average Resolution Time Modal -->
<div id="avgResolutionTimeModal" class="fixed inset-0 z-50 overflow-y-auto hidden">
    <div class="absolute inset-0 bg-black/60 backdrop-blur-md transition-opacity" onclick="closeModal('avgResolutionTimeModal')"></div>
    <div class="relative mx-auto my-0 sm:my-8 w-full h-full sm:h-auto sm:w-[95%] max-w-2xl flex items-center">
        <div class="bg-white shadow-2xl w-full h-full sm:h-auto sm:max-w-2xl overflow-hidden sm:rounded-2xl flex flex-col">

            <div class="px-4 sm:px-6 py-4 border-b border-gray-100 flex items-center justify-between shrink-0">
                <div class="flex-1 min-w-0">
                    <h3 class="text-lg font-semibold text-gray-900">Average Resolution Time Details</h3>
                </div>
                <div class="flex items-center gap-2 ml-4">
                    <button onclick="closeModal('avgResolutionTimeModal')" class="text-gray-400 hover:text-gray-600 transition-colors p-1 rounded-lg hover:bg-gray-100">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </button>
                </div>
            </div>

            <div class="flex-1 overflow-y-auto px-6 py-5">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <h4 class="text-md font-semibold mb-2">Average Resolution Time</h4>
                        <div class="text-3xl font-bold text-blue-600">{{ number_format($performanceMetrics['avg_resolution_time'], 1) }} hours</div>
                        <p class="text-sm text-gray-500 mt-2">Based on resolved tickets</p>
                    </div>
                    <div>
                        <h4 class="text-md font-semibold mb-2">Resolution Time Distribution</h4>
                        <div class="h-48">
                            <canvas id="resolutionTimeChart"></canvas>
                        </div>
                    </div>
                </div>
            </div>

            <div class="px-6 py-4 border-t border-gray-100 bg-gray-50 shrink-0">
                <div class="flex flex-col sm:flex-row items-stretch sm:items-center justify-between gap-3">
                    <div class="flex items-center gap-2"></div>
                    <div class="flex items-center gap-2">
                        <button type="button" onclick="closeModal('avgResolutionTimeModal')" class="flex-1 sm:flex-none inline-flex items-center justify-center rounded-lg bg-white px-4 py-2 text-sm font-medium text-gray-700 ring-1 ring-gray-300 hover:bg-gray-50 transition-colors">
                            Close
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Resolution Rate Modal -->
<div id="resolutionRateModal" class="fixed inset-0 z-50 overflow-y-auto hidden">
    <div class="absolute inset-0 bg-black/60 backdrop-blur-md transition-opacity" onclick="closeModal('resolutionRateModal')"></div>
    <div class="relative mx-auto my-0 sm:my-8 w-full h-full sm:h-auto sm:w-[95%] max-w-2xl flex items-center">
        <div class="bg-white shadow-2xl w-full h-full sm:h-auto sm:max-w-2xl overflow-hidden sm:rounded-2xl flex flex-col">

            <div class="px-4 sm:px-6 py-4 border-b border-gray-100 flex items-center justify-between shrink-0">
                <div class="flex-1 min-w-0">
                    <h3 class="text-lg font-semibold text-gray-900">Resolution Rate Details</h3>
                </div>
                <div class="flex items-center gap-2 ml-4">
                    <button onclick="closeModal('resolutionRateModal')" class="text-gray-400 hover:text-gray-600 transition-colors p-1 rounded-lg hover:bg-gray-100">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </button>
                </div>
            </div>

            <div class="flex-1 overflow-y-auto px-6 py-5">
                <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                    <div class="text-center">
                        <div class="text-4xl font-bold text-green-600">{{ $performanceMetrics['resolved_tickets'] }}</div>
                        <div class="text-sm text-gray-500">Resolved</div>
                    </div>
                    <div class="text-center">
                        <div class="text-4xl font-bold text-blue-600">{{ $performanceMetrics['total_tickets'] }}</div>
                        <div class="text-sm text-gray-500">Total</div>
                    </div>
                    <div class="text-center">
                        <div class="text-4xl font-bold text-purple-600">{{ $performanceMetrics['total_tickets'] > 0 ? number_format(($performanceMetrics['resolved_tickets'] / $performanceMetrics['total_tickets']) * 100, 1) : 0 }}%</div>
                        <div class="text-sm text-gray-500">Resolution Rate</div>
                    </div>
                </div>
                <div class="mt-6">
                    <h4 class="text-md font-semibold mb-2">Resolution Rate Chart</h4>
                    <div class="h-48">
                        <canvas id="resolutionRateChart"></canvas>
                    </div>
                </div>
            </div>

            <div class="px-6 py-4 border-t border-gray-100 bg-gray-50 shrink-0">
                <div class="flex flex-col sm:flex-row items-stretch sm:items-center justify-between gap-3">
                    <div class="flex items-center gap-2"></div>
                    <div class="flex items-center gap-2">
                        <button type="button" onclick="closeModal('resolutionRateModal')" class="flex-1 sm:flex-none inline-flex items-center justify-center rounded-lg bg-white px-4 py-2 text-sm font-medium text-gray-700 ring-1 ring-gray-300 hover:bg-gray-50 transition-colors">
                            Close
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

@endsection

@section('staff-scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.1/dist/chart.umd.min.js"></script>
<script>
(function () {
    // Ticket Status Chart
    const statusEl = document.getElementById('ticketStatusChart');
    if (statusEl) {
        const resolved = {{ $performanceMetrics['resolved_tickets'] }};
        const total = {{ $performanceMetrics['total_tickets'] }};
        const open = total - resolved;

        new Chart(statusEl, {
            type: 'doughnut',
            data: {
                labels: ['Resolved', 'Open'],
                datasets: [{
                    data: [resolved, open],
                    backgroundColor: ['#10B981', '#EF4444'],
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

    // FAQ Chart
    // const faqEl = document.getElementById('faqChart');
    // if (faqEl) {
    //     const processed = {{ $faqAnalysis['processed_faqs'] }};
    //     const total = {{ $faqAnalysis['total_faqs'] }};
    //     const unprocessed = total - processed;

    //     new Chart(faqEl, {
    //         type: 'bar',
    //         data: {
    //             labels: ['Processed', 'Total Available'],
    //             datasets: [{
    //                 label: 'FAQs',
    //                 data: [processed, total],
    //                 backgroundColor: ['#3B82F6', '#E5E7EB'],
    //                 borderRadius: 6,
    //                 maxBarThickness: 40
    //             }]
    //         },
    //         options: {
    //             responsive: true,
    //             maintainAspectRatio: false,
    //             scales: {
    //                 x: { grid: { display: false } },
    //                 y: { beginAtZero: true, ticks: { precision: 0 }, grid: { color: '#f1f5f9' } }
    //             },
    //             plugins: { legend: { display: false } }
    //         }
    //     });
    // }

    // Weekly Chart Animation
    const weeklyChartEl = document.getElementById('weeklyChart');
    if (weeklyChartEl) {
        // Initialize weekly bar heights from server-rendered data-height (and ensure minimum visible bar)
        function applyInitialWeeklyHeights() {
            weeklyChartEl.querySelectorAll('.weekly-bar').forEach(el => {
                const v = Number(el.getAttribute('data-height') || 0);
                const countVal = Number(el.getAttribute('data-count') || 0);
                const pct = v > 0 ? v : (countVal > 0 ? 4 : 0); // show a tiny bar when count > 0
                el.style.height = (Number.isFinite(pct) ? pct : 0) + '%';
            });
        }
        applyInitialWeeklyHeights();
        // Re-apply after first paint in case styles load late
        if (typeof requestAnimationFrame === 'function') {
            requestAnimationFrame(applyInitialWeeklyHeights);
        }
    }

    // Modal functions
    window.openModal = function(modalId) {
        document.getElementById(modalId).classList.remove('hidden');
    };

    window.closeModal = function(modalId) {
        document.getElementById(modalId).classList.add('hidden');
    };

    // Close modal when clicking outside
    document.addEventListener('click', function(event) {
        if (event.target.classList.contains('bg-black/60')) {
            event.target.parentElement.classList.add('hidden');
        }
    });

    // Time range selector (reload page with days param)
    const timeRangeSelect = document.getElementById('timeRangeSelect');
    if (timeRangeSelect) {
        timeRangeSelect.addEventListener('change', function(e) {
            const days = e.target.value;
            const url = new URL(window.location.href);
            url.searchParams.set('days', days);
            window.location.href = url.toString();
        });
    }

    // Resolution Time Chart for modal
    const resolutionTimeEl = document.getElementById('resolutionTimeChart');
    if (resolutionTimeEl) {
        @php
            $resolvedTickets = \App\Models\Ticket::where('staff_id', auth()->id())
                ->where('status', 'Closed')
                ->get();
            $timeRanges = ['0-1h' => 0, '1-2h' => 0, '2-4h' => 0, '4-8h' => 0, '8h+' => 0];
            foreach ($resolvedTickets as $ticket) {
                $hours = $ticket->updated_at->diffInHours($ticket->created_at);
                if ($hours <= 1) $timeRanges['0-1h']++;
                elseif ($hours <= 2) $timeRanges['1-2h']++;
                elseif ($hours <= 4) $timeRanges['2-4h']++;
                elseif ($hours <= 8) $timeRanges['4-8h']++;
                else $timeRanges['8h+']++;
            }
        @endphp

        new Chart(resolutionTimeEl, {
            type: 'bar',
            data: {
                labels: Object.keys({!! json_encode($timeRanges) !!}),
                datasets: [{
                    label: 'Tickets',
                    data: Object.values({!! json_encode($timeRanges) !!}),
                    backgroundColor: '#3B82F6',
                    borderRadius: 4
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                scales: {
                    y: { beginAtZero: true, ticks: { precision: 0 } }
                },
                plugins: { legend: { display: false } }
            }
        });
    }

    // Resolution Rate Chart for modal
    const resolutionRateEl = document.getElementById('resolutionRateChart');
    if (resolutionRateEl) {
        const resolved = {{ $performanceMetrics['resolved_tickets'] }};
        const total = {{ $performanceMetrics['total_tickets'] }};
        const open = total - resolved;

        new Chart(resolutionRateEl, {
            type: 'pie',
            data: {
                labels: ['Resolved', 'Open'],
                datasets: [{
                    data: [resolved, open],
                    backgroundColor: ['#10B981', '#EF4444']
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: { position: 'bottom' }
                }
            }
        });
    }
})();
</script>
@endsection
