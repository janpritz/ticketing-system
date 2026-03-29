<div class="grid grid-cols-1 md:grid-cols-4 gap-4">
    <!-- Total Tickets -->
    <div class="bg-white rounded-xl border border-gray-200 p-4 cursor-pointer hover:shadow-md transition-shadow"
        onclick="openModal('totalTicketsModal')">
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
    <div class="bg-white rounded-xl border border-gray-200 p-4 cursor-pointer hover:shadow-md transition-shadow"
        onclick="openModal('resolvedTicketsModal')">
        <div class="flex items-start justify-between">
            <div>
                <div class="text-xs font-medium text-slate-500">Resolved Tickets</div>
                <div class="mt-2 text-3xl font-semibold text-slate-900">{{ $performanceMetrics['resolved_tickets'] }}
                </div>
            </div>
            <div class="rounded-md bg-green-50 p-2 text-green-500 border border-green-100">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 24 24" fill="currentColor">
                    <path d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
            </div>
        </div>
    </div>
    <!-- Average Resolution Time -->
    <div class="bg-white rounded-xl border border-gray-200 p-4 cursor-pointer hover:shadow-md transition-shadow"
        onclick="openModal('avgResolutionTimeModal')">
        <div class="flex items-start justify-between">
            <div>
                <div class="text-xs font-medium text-slate-500">Avg Resolution Time</div>
                <div class="mt-2 text-3xl font-semibold text-slate-900">
                    {{ number_format($performanceMetrics['avg_resolution_time'], 1) }}h</div>
            </div>
            <div class="rounded-md bg-yellow-50 p-2 text-yellow-500 border border-yellow-100">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 24 24" fill="currentColor">
                    <path d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
            </div>
        </div>
    </div>
    <!-- Resolution Rate -->
    <div class="bg-white rounded-xl border border-gray-200 p-4 cursor-pointer hover:shadow-md transition-shadow"
        onclick="openModal('resolutionRateModal')">
        <div class="flex items-start justify-between">
            <div>
                <div class="text-xs font-medium text-slate-500">Resolution Rate</div>
                <div class="mt-2 text-3xl font-semibold text-slate-900">
                    {{ $performanceMetrics['total_tickets'] > 0 ? number_format(($performanceMetrics['resolved_tickets'] / $performanceMetrics['total_tickets']) * 100, 1) : 0 }}%
                </div>
            </div>
            <div class="rounded-md bg-purple-50 p-2 text-purple-500 border border-purple-100">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 24 24" fill="currentColor">
                    <path
                        d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" />
                </svg>
            </div>
        </div>
    </div>
</div>
