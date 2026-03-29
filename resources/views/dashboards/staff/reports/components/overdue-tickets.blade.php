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
                            <span
                                class="inline-flex items-center gap-1 rounded-full px-2.5 py-1 text-xs font-medium ring-1 text-amber-700 bg-amber-50 ring-amber-600/20">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-3.5 w-3.5" viewBox="0 0 24 24"
                                    fill="currentColor">
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
