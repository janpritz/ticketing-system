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
                @if (!empty($recentForwarders))
                    @foreach ($recentForwarders as $item)
                        <li class="py-2 text-sm text-gray-800">{{ $item['name'] }} &mdash; <span
                                class="text-gray-500">{{ $item['category'] }}</span></li>
                    @endforeach
                @else
                    <li class="py-4 text-sm text-gray-500">No recently forwarded tickets.</li>
                @endif
            </ul>
        </div>

        @if (isset($totalForwardsCount) && $totalForwardsCount > count($recentForwarders))
            <div class="mt-3 text-right">
                <a href="{{ route('staff.reports.index') }}" class="text-sm text-indigo-600 hover:underline">View all
                    forwards</a>
            </div>
        @endif
    </div>
</div>
