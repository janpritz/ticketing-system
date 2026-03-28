<div class="bg-white rounded-xl border border-gray-200 p-6">
    <div class="flex items-center justify-between mb-4">
        <h3 class="text-lg font-semibold text-gray-900">Current Workload</h3>
        <div class="text-sm text-gray-500">Assigned tickets by staff</div>
    </div>
    <div class="overflow-x-auto">
        <table class="min-w-full text-sm">
            <thead class="bg-gray-50 text-gray-600">
                <tr>
                    <th class="py-2 pl-3 pr-2 text-left font-medium">Staff</th>
                    <th class="px-2 py-2 text-left font-medium">Tickets</th>
                </tr>
            </thead>
            <tbody id="ticketsAssignedBody" class="divide-y divide-gray-100">
                @forelse(($ticketsAssigned ?? []) as $agent)
                    <tr class="hover:bg-gray-50">
                        <td class="py-2 pl-3 pr-2 align-top text-gray-900">{{ $agent['name'] }}</td>
                        <td class="px-2 py-2 align-top font-medium text-slate-900">{{ number_format($agent['count']) }}
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="2" class="px-3 py-6 text-center text-sm text-gray-500">No assigned tickets.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
