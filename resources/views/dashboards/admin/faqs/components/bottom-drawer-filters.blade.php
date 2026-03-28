<div id="faqsBottomDrawer"
    class="fixed inset-x-0 bottom-0 z-50 bg-white border-t border-gray-200 shadow-lg transform translate-y-full transition-transform duration-200">
    <div class="px-4 py-3 flex items-center justify-between border-b">
        <div class="text-sm font-semibold text-slate-800">Filters</div>
        <button id="closeFiltersBtn" type="button"
            class="p-2 rounded-md text-slate-600 hover:text-slate-800 hover:bg-gray-50" aria-label="Close filters">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 24 24" fill="currentColor">
                <path d="M6 18 18 6M6 6l12 12" />
            </svg>
        </button>
    </div>
    <div class="px-4 py-3 grid grid-cols-1 sm:grid-cols-3 gap-3">
        <div>
            <label for="filterStatus" class="block text-xs text-slate-600 mb-1">Status</label>
            <select id="filterStatus" class="w-full rounded-md border border-gray-300 bg-white text-sm px-3 py-2">
                <option value="pending" selected>Pending</option>
                <option value="approved">Approved</option>
                <option value="rejected">Rejected</option>
            </select>
        </div>
        <div>
            <label for="filterPerPage" class="block text-xs text-slate-600 mb-1">Per page</label>
            <select id="filterPerPage" class="w-full rounded-md border border-gray-300 bg-white text-sm px-3 py-2">
                <option value="10">10</option>
                <option value="25" selected>25</option>
                <option value="50">50</option>
            </select>
        </div>
    </div>
    <div class="px-4 py-3 border-t flex items-center justify-end gap-2">
        <button id="resetFiltersBtn" type="button"
            class="rounded-md border border-gray-300 bg-white px-3 py-2 text-sm">Reset</button>
        <button id="applyFiltersBtn" type="button"
            class="rounded-md bg-blue-600 text-white px-4 py-2 text-sm">Apply</button>
    </div>
</div>
