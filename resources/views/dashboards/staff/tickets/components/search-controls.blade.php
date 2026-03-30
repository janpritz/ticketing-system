<div class="bg-white rounded-lg shadow-sm ring-1 ring-slate-900/5 mb-6">
    <div class="p-4 flex flex-col sm:flex-row gap-4 items-start sm:items-center justify-between">
        <div class="flex-1 max-w-md">
            <div class="relative">
                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                    <svg class="h-5 w-5 text-gray-400" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20"
                        fill="currentColor">
                        <path fill-rule="evenodd"
                            d="M8 4a4 4 0 100 8 4 4 0 000-8zM2 8a6 6 0 1110.89 3.476l4.817 4.817a1 1 0 01-1.414 1.414l-4.816-4.816A6 6 0 012 8z"
                            clip-rule="evenodd" />
                    </svg>
                </div>
                <input type="text" id="searchInput" placeholder="Search tickets..."
                    class="block w-full pl-10 pr-3 py-2 border border-gray-300 rounded-md leading-5 bg-white placeholder-gray-500 focus:outline-none focus:placeholder-gray-400 focus:ring-1 focus:ring-indigo-500 focus:border-indigo-500 text-sm">
            </div>
        </div>
        <div class="flex items-center gap-3">
            <div class="flex items-center gap-2">
                <span class="text-sm text-gray-700 hidden sm:inline">Show</span>
                <select id="perPageSelect"
                    class="rounded-md border-gray-300 text-xs focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500">
                    <option value="10">10</option>
                    <option value="25">25</option>
                    <option value="50">50</option>
                    <option value="100">100</option>
                </select>
                <span class="text-sm text-gray-700 hidden sm:inline">per page</span>
            </div>
        </div>
    </div>
</div>
