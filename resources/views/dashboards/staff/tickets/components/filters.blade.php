<div class="mb-6">
    <div class="sm:border-b sm:border-gray-100">
        <div class="overflow-x-auto pb-px no-scrollbar">
            <nav class="flex space-x-4 sm:space-x-8 min-w-max px-1" aria-label="Tabs">

                <button data-filter="all"
                    class="filter-tab group inline-flex items-center gap-2 border-b-2 border-transparent py-3 px-1 text-sm font-semibold text-slate-500 transition-all hover:text-slate-700 whitespace-nowrap active-tab">
                    <span>All Tickets</span>
                    <span id="count-all"
                        class="rounded-full bg-slate-100 px-2 py-0.5 text-[10px] font-bold text-slate-600 transition-colors group-hover:bg-slate-200">0</span>
                </button>

                <button data-filter="open"
                    class="filter-tab group inline-flex items-center gap-2 border-b-2 border-transparent py-3 px-1 text-sm font-semibold text-slate-500 transition-all hover:text-slate-700 whitespace-nowrap">
                    <span>Open</span>
                    <span id="count-open"
                        class="rounded-full bg-blue-50 px-2 py-0.5 text-[10px] font-bold text-blue-600">0</span>
                </button>

                <button data-filter="forwarded"
                    class="filter-tab group inline-flex items-center gap-2 border-b-2 border-transparent py-3 px-1 text-sm font-semibold text-slate-500 transition-all hover:text-slate-700 whitespace-nowrap">
                    <span>Forwarded</span>
                    <span id="count-forwarded"
                        class="rounded-full bg-amber-50 px-2 py-0.5 text-[10px] font-bold text-amber-600">0</span>
                </button>

                <button data-filter="closed"
                    class="filter-tab group inline-flex items-center gap-2 border-b-2 border-transparent py-3 px-1 text-sm font-semibold text-slate-500 transition-all hover:text-slate-700 whitespace-nowrap">
                    <span>Closed</span>
                    <span id="count-closed"
                        class="rounded-full bg-emerald-50 px-2 py-0.5 text-[10px] font-bold text-emerald-600">0</span>
                </button>

            </nav>
        </div>
    </div>
</div>

<style>
    /* Hide scrollbar for Chrome, Safari and Opera */
    .no-scrollbar::-webkit-scrollbar {
        display: none;
    }

    /* Hide scrollbar for IE, Edge and Firefox */
    .no-scrollbar {
        -ms-overflow-style: none;
        /* IE and Edge */
        scrollbar-width: none;
        /* Firefox */
    }
</style>
