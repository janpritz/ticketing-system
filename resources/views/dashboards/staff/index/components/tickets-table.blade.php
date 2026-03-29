<div id="openTicketsSection" class="bg-white rounded-xl shadow-sm ring-1 ring-slate-900/5 order-2 pb-6 md:pb-5">
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
                    <th class="px-3 py-3 text-left font-medium">Concern</th>
                    <th class="px-3 py-3 text-left font-medium">Status</th>
                    <th class="px-3 py-3 text-left font-medium">Assignee</th>
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
                        $style = $statusStyles[$t->status] ?? 'text-slate-700 bg-slate-50 ring-slate-600/20';
                    @endphp
                    <tr class="hover:bg-gray-50" data-id="{{ $t->id }}" style="cursor: pointer;">
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
                                    class="inline-flex items-center rounded-full bg-gray-100 px-2 py-0.5 text-[11px] font-medium text-gray-700">{{ is_object($t->category) ? $t->category->name ?? ($t->getAttribute('category') ?? '') : $t->getAttribute('category') ?? '' }}</span>
                            </div>
                        </td>

                        <!-- Status -->
                        <td class="px-3 py-4 align-top">
                            <span
                                class="inline-flex items-center gap-1 rounded-full px-2.5 py-1 text-xs font-medium ring-1 {{ $style }}">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-3.5 w-3.5" viewBox="0 0 24 24"
                                    fill="currentColor">
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
                    </tr>
                @empty
                    <tr>
                        <td colspan="4" class="px-5 py-10 text-center text-sm text-gray-500">
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
