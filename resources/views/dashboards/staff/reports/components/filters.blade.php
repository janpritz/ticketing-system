<div class="flex items-center gap-3">
    <div class="flex items-center gap-2">
        <label class="text-sm font-medium text-gray-700">Time Range:</label>
        <select id="timeRangeSelect" class="rounded-md border border-gray-300 bg-white px-3 py-2 text-sm">
            <option value="7" {{ ($days ?? 7) == 7 ? 'selected' : '' }}>Last 7 Days</option>
            <option value="30" {{ ($days ?? 7) == 30 ? 'selected' : '' }}>Last 30 Days</option>
            <option value="90" {{ ($days ?? 7) == 90 ? 'selected' : '' }}>Last 90 Days</option>
        </select>
    </div>
</div>
