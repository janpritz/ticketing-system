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
                    <div class="mb-1 text-[10px] text-gray-500 weekly-label" data-index="{{ $i }}">
                        {{ $label }}</div>
                    <div class="w-6 bg-indigo-400 opacity-80 rounded-t weekly-bar mt-auto"
                        data-index="{{ $i }}" data-count="{{ (int) $count }}"
                        data-height="{{ (int) $height }}" title="{{ $label }}: {{ $count }}"
                        style="height: 0%;">
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
