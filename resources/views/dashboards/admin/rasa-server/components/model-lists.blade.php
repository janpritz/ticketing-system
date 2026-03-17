<div class="mt-6 bg-white rounded-xl border border-gray-200 overflow-hidden">
    <div class="px-4 sm:px-6 py-4 border-b border-gray-200">
        <div class="flex items-center justify-between">
            <h3 class="text-lg font-semibold text-gray-900">Old Models</h3>
            <div class="flex items-center gap-3">
                <button id="cleanupModelsBtnTop"
                    class="inline-flex items-center gap-2 px-3 py-2 text-sm font-medium text-red-600 bg-red-50 hover:bg-red-100 rounded-lg transition-colors disabled:opacity-50 disabled:cursor-not-allowed">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                    </svg>
                    Cleanup Models
                </button>
                <button id="refreshModelsList" class="text-sm text-blue-600 hover:text-blue-800">Refresh</button>
            </div>
        </div>
    </div>
    <div class="overflow-x-auto">
        <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50">
                <tr>
                    <th class="px-4 sm:px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                        Model Name</th>
                    <th
                        class="hidden sm:table-cell px-4 sm:px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                        Version</th>
                    <th class="px-4 sm:px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                        Status</th>
                    <th
                        class="hidden md:table-cell px-4 sm:px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                        Size</th>
                </tr>
            </thead>
            <tbody id="modelsListTable" class="bg-white divide-y divide-gray-200">
                <tr>
                    <td colspan="4" class="px-4 sm:px-6 py-4 text-center text-sm text-gray-500">Loading models
                        list...</td>
                </tr>
            </tbody>
        </table>
    </div>
    <!-- Pagination Controls -->
    <div class="px-4 sm:px-6 py-3 border-t border-gray-200">
        <div class="flex items-center justify-between">
            <div class="flex items-center gap-2">
                <span class="text-sm text-gray-700">Show:</span>
                <select id="modelsListPerPage"
                    class="text-sm border-gray-300 rounded-md focus:ring-blue-500 focus:border-blue-500">
                    <option value="5" selected>5</option>
                    <option value="10">10</option>
                    <option value="20">20</option>
                </select>
                <span class="text-sm text-gray-700">entries</span>
            </div>
            <div class="flex items-center gap-2">
                <button id="modelsListPrev"
                    class="px-3 py-1 text-sm border border-gray-300 rounded-md hover:bg-gray-50 disabled:opacity-50 disabled:cursor-not-allowed">Previous</button>
                <span id="modelsListPageInfo" class="text-sm text-gray-700">Page 1 of 1</span>
                <button id="modelsListNext"
                    class="px-3 py-1 text-sm border border-gray-300 rounded-md hover:bg-gray-50 disabled:opacity-50 disabled:cursor-not-allowed">Next</button>
            </div>
        </div>
    </div>
</div>
