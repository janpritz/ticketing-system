<div id="activeStaffCard" class="bg-white rounded-xl border border-gray-200 p-4 cursor-pointer hover:bg-gray-100"
    role="button" tabindex="0" aria-label="Open active staff list">
    <div class="flex items-start justify-between">
        <div>
            <div class="text-xs font-medium text-slate-500">Active Staff (last 10 min)</div>
            <div class="mt-2 flex items-center gap-2">
                <div id="activeStaffDot" class="w-4 h-4 rounded-full {{ ($activeStaffCount ?? 0) > 0 ? '' : 'hidden' }}">
                </div>
                <span id="activeStaffCountText"
                    class="text-2xl sm:text-2xl font-bold text-slate-900">{{ $activeStaffCount ?? 0 }}</span>
            </div>
        </div>
        <div class="rounded-md bg-purple-50 p-2 text-purple-600 border border-purple-100">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 24 24" fill="currentColor">
                <path
                    d="M16 11c1.66 0 2.99-1.34 2.99-3S17.66 5 16 5s-3 1.34-3 3 1.34 3 3 3zM8 11c1.66 0 2.99-1.34 2.99-3S9.66 5 8 5 5 6.34 5 8s1.34 3 3 3zm0 2c-2.33 0-7 1.17-7 3.5V19h14v-2.5C15 14.17 10.33 13 8 13zm8 0c-.29 0-.62.02-.97.05 1.16.84 1.97 1.97 1.97 3.45V19h6v-2.5c0-2.33-4.67-3.5-7-3.5z" />
            </svg>
        </div>
    </div>
</div>
