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
