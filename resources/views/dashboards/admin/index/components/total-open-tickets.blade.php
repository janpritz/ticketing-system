<a href="{{ route('admin.tickets.index') }}"
    class="block bg-white rounded-xl border border-gray-200 p-4 hover:bg-gray-100 transition-colors">
    <div class="flex items-start justify-between">
        <div>
            <div class="text-xs font-medium text-slate-500">Total Open Tickets</div>
            <div class="mt-2"><span id="openTicketsCount"
                    class="text-2xl sm:text-2xl font-bold text-slate-900">{{ number_format($openTickets ?? 0) }}</span>
            </div>
            @if (($openTicketsDelta ?? 0) > 0)
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
