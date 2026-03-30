<div class="bg-white rounded-lg shadow-sm ring-1 ring-slate-900/5">
    <div class="px-4 py-3 border-b border-gray-200 sm:px-6">
        <h3 class="text-lg font-medium text-gray-900" id="tableTitle">All Tickets</h3>
    </div>
    <div class="overflow-x-auto">
        <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50">
                <tr>
                    <th scope="col"
                        class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                        <button class="group inline-flex items-center gap-1" data-sort="id">
                            ID

                        </button>
                    </th>
                    <th scope="col"
                        class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                        <button class="group inline-flex items-center gap-1" data-sort="question">
                            Concern
                        </button>
                    </th>
                    <th scope="col"
                        class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                        Status
                    </th>
                    <th scope="col"
                        class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                        <button class="group inline-flex items-center gap-1" data-sort="date_created">
                            Created Date
                        </button>
                    </th>
                </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-200" id="ticketsTableBody">
                <tr>
                    <td colspan="4" class="px-4 py-12 text-center">
                        <div class="flex flex-col items-center">
                            <svg class="animate-spin h-8 w-8 text-gray-400 mb-4" xmlns="http://www.w3.org/2000/svg"
                                fill="none" viewBox="0 0 24 24">
                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor"
                                    stroke-width="4"></circle>
                                <path class="opacity-75" fill="currentColor"
                                    d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z">
                                </path>
                            </svg>
                            <p class="text-gray-500">Loading tickets...</p>
                        </div>
                    </td>
                </tr>
            </tbody>
        </table>
    </div>

    <!-- Pagination -->
    <div class="bg-white px-4 py-3 flex items-center justify-between border-t border-gray-200 sm:px-6">
        <div class="flex-1 flex justify-between sm:hidden">
            <button id="mobilePrevBtn"
                class="relative inline-flex items-center px-4 py-2 border border-gray-300 text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50">
                Previous
            </button>
            <button id="mobileNextBtn"
                class="ml-3 relative inline-flex items-center px-4 py-2 border border-gray-300 text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50">
                Next
            </button>
        </div>
        <div class="hidden sm:flex-1 sm:flex sm:items-center sm:justify-between">
            <div>
                <p class="text-sm text-gray-700">
                    Showing <span class="font-medium" id="showingFrom">1</span> to <span class="font-medium"
                        id="showingTo">10</span> of
                    <span class="font-medium" id="totalResults">0</span> results
                </p>
            </div>
            <div>
                <nav class="relative z-0 inline-flex rounded-md shadow-sm -space-x-px" aria-label="Pagination">
                    <button id="prevPageBtn"
                        class="relative inline-flex items-center px-2 py-2 rounded-l-md border border-gray-300 bg-white text-sm font-medium text-gray-500 hover:bg-gray-50">
                        <span class="sr-only">Previous</span>
                        <svg class="h-5 w-5" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                            <path fill-rule="evenodd"
                                d="M12.707 5.293a1 1 0 010 1.414L9.414 10l3.293 3.293a1 1 0 01-1.414 1.414l-4-4a1 1 0 010-1.414l4-4a1 1 0 011.414 0z"
                                clip-rule="evenodd" />
                        </svg>
                    </button>
                    <div id="pageNumbers" class="relative inline-flex items-center">
                    </div>
                    <button id="nextPageBtn"
                        class="relative inline-flex items-center px-2 py-2 rounded-r-md border border-gray-300 bg-white text-sm font-medium text-gray-500 hover:bg-gray-50">
                        <span class="sr-only">Next</span>
                        <svg class="h-5 w-5" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                            <path fill-rule="evenodd"
                                d="M7.293 14.707a1 1 0 010-1.414L10.586 10 7.293 6.707a1 1 0 011.414-1.414l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0z"
                                clip-rule="evenodd" />
                        </svg>
                    </button>
                </nav>
            </div>
        </div>
    </div>
</div>
