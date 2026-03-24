<div class="mt-4 bg-white rounded-xl border border-gray-200 overflow-hidden">
    <div class="overflow-x-auto">
        <table id="ticketsTable" class="min-w-full text-sm">
            <thead class="bg-gray-50 text-gray-600">
                <tr>
                    <th class="py-3 pl-5 pr-3 text-left font-medium">Ticket</th>
                    <th class="px-3 py-3 text-left font-medium">Category</th>
                    <th class="px-3 py-3 text-left font-medium">Message</th>
                    <th class="px-3 py-3 text-left font-medium">Status</th>
                    <th class="px-3 py-3 text-left font-medium">Assignee</th>
                    <th class="px-3 py-3 text-left font-medium">Created</th>
                </tr>
            </thead>
            <tbody id="ticketsTbody" class="divide-y divide-gray-100">
                <tr>
                    <td colspan="6" class="px-5 py-6 text-center text-sm text-gray-500">Loading...</td>
                </tr>
            </tbody>
        </table>
    </div>
    <div id="ticketsFooter" class="px-5 py-3 border-t border-gray-200">
        <div id="ticketsPagination" class="flex items-center justify-between"></div>
    </div>
</div>
