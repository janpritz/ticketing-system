<div id="resolvedTicketsModal" class="fixed inset-0 z-50 overflow-y-auto hidden">
    <div class="absolute inset-0 bg-black/60 backdrop-blur-md transition-opacity"
        onclick="closeModal('resolvedTicketsModal')"></div>
    <div class="relative mx-auto my-0 sm:my-8 w-full h-full sm:h-auto sm:w-[95%] max-w-2xl flex items-center">
        <div
            class="bg-white shadow-2xl w-full h-full sm:h-auto sm:max-w-2xl overflow-hidden sm:rounded-2xl flex flex-col">

            <div class="px-4 sm:px-6 py-4 border-b border-gray-100 flex items-center justify-between shrink-0">
                <div class="flex-1 min-w-0">
                    <h3 class="text-lg font-semibold text-gray-900">Resolved Tickets Details</h3>
                </div>
                <div class="flex items-center gap-2 ml-4">
                    <button onclick="closeModal('resolvedTicketsModal')"
                        class="text-gray-400 hover:text-gray-600 transition-colors p-1 rounded-lg hover:bg-gray-100">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24"
                            stroke="currentColor" stroke-width="2">
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
                                <th
                                    class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    ID</th>
                                <th
                                    class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Subject</th>
                                <th
                                    class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Resolution Time</th>
                                <th
                                    class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Resolved Date</th>
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
                                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                                        {{ $ticket->id }}</td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                        {{ $ticket->question }}</td>
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
                                            $minutes = intdiv($seconds % 3600, 60);
                                        @endphp
                                        @if ($hours > 0)
                                            {{ $hours }}h {{ $minutes }}m
                                        @else
                                            {{ $minutes }}m
                                        @endif
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                        {{ $ticket->updated_at->format('F j, Y g:i A') }}</td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="4" class="px-6 py-4 text-center text-sm text-gray-500">No resolved
                                        tickets found</td>
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
                        <button type="button" onclick="closeModal('resolvedTicketsModal')"
                            class="flex-1 sm:flex-none inline-flex items-center justify-center rounded-lg bg-white px-4 py-2 text-sm font-medium text-gray-700 ring-1 ring-gray-300 hover:bg-gray-50 transition-colors">
                            Close
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
