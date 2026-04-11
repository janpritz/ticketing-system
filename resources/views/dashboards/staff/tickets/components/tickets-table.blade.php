<div class="bg-white rounded-xl shadow-sm ring-1 ring-slate-900/5 pb-6 md:pb-5">
    <div class="px-5 py-4 border-b border-gray-100">
        <h3 class="text-base font-semibold text-gray-800" id="tableTitle">All Tickets</h3>
    </div>
    <div class="overflow-x-auto">
        <table class="min-w-full text-sm">
            <thead class="bg-gray-50 text-gray-600">
                <tr>
                    <th class="py-3 pl-5 pr-3 text-left font-medium">
                        <button class="group inline-flex items-center gap-1" data-sort="id">Ticket</button>
                    </th>
                    <th class="px-3 py-3 text-left font-medium">
                        <button class="group inline-flex items-center gap-1" data-sort="question">Concern</button>
                    </th>
                    <th class="px-3 py-3 text-left font-medium">Status</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100" id="ticketsTableBody">
                <tr>
                    <td colspan="3" class="px-5 py-12 text-center">
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
    <div class="px-5 py-3 flex items-center justify-between border-t border-gray-100 text-sm">
        <div class="flex-1 flex justify-between sm:hidden">
            <button id="mobilePrevBtn"
                class="rounded-md border border-gray-300 px-3 py-1.5 text-gray-700 disabled:opacity-50">Prev</button>
            <button id="mobileNextBtn"
                class="rounded-md border border-gray-300 px-3 py-1.5 text-gray-700 disabled:opacity-50">Next</button>
        </div>
        <div class="hidden sm:flex-1 sm:flex sm:items-center sm:justify-between">
            <div>
                <p class="text-sm text-gray-500">
                    Showing <span class="font-medium" id="showingFrom">1</span> to <span class="font-medium"
                        id="showingTo">10</span> of
                    <span class="font-medium" id="totalResults">0</span> results
                </p>
            </div>
            <div class="flex items-center gap-2">
                <button id="prevPageBtn" type="button"
                    class="rounded-md border border-gray-300 px-3 py-1.5 text-gray-700 disabled:opacity-50">Prev</button>
                <div id="pageNumbers" class="flex items-center gap-1"></div>
                <button id="nextPageBtn" type="button"
                    class="rounded-md border border-gray-300 px-3 py-1.5 text-gray-700 disabled:opacity-50">Next</button>
            </div>
        </div>
    </div>
</div>
