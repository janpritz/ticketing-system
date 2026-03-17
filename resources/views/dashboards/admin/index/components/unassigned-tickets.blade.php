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
