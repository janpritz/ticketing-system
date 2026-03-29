<div class="bg-white rounded-xl shadow-sm ring-1 ring-slate-900/5 p-5">
    <div class="flex items-center justify-between">
        <div>
            <div class="text-sm text-gray-500">Forwarded</div>
            <div class="mt-2 text-3xl font-semibold text-gray-900"><span
                    id="inProgressCount">{{ $inProgressCount ?? 0 }}</span></div>
        </div>
        <div class="rounded-full bg-amber-50 text-amber-600 p-3 ring-1 ring-amber-600/10">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24"
                stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 6v6l3 3" />
            </svg>
        </div>
    </div>
</div>
