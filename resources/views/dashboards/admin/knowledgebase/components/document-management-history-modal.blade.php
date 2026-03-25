<div id="uploadLogsModal" class="fixed inset-0 z-50 hidden overflow-y-auto">
    <div class="absolute inset-0 bg-black/60 backdrop-blur-md transition-opacity" data-modal-backdrop></div>
    <div class="relative mx-auto my-0 sm:my-8 w-full h-full sm:h-auto sm:w-[95%] max-w-2xl flex items-center">
        <button type="button" class="absolute top-3 right-3 text-slate-700 hover:text-slate-900 z-50"
            data-close="upload-logs" aria-label="Close">
            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-x"
                viewBox="0 0 16 16">
                <path
                    d="M4.646 4.646a.5.5 0 0 1 .708 0L8 7.293l2.646-2.647a.5.5 0 0 1 .708.708L8.707 8l2.647 2.646a.5.5 0 0 1-.708.708L8 8.707l-2.646 2.647a.5.5 0 0 1-.708-.708L7.293 8 4.646 5.354a.5.5 0 0 1 0-.708" />
            </svg>
        </button>
        <div
            class="bg-white shadow-2xl w-full h-full sm:h-auto sm:max-h-[95vh] sm:max-w-2xl overflow-hidden sm:rounded-2xl flex flex-col">
            <!-- Header -->
            <div class="px-4 sm:px-6 py-3 border-b flex items-center justify-between">
                <div class="text-sm font-semibold text-slate-800">Document Upload History</div>
                <div class="flex items-center gap-2">
                    <!-- header controls (kept for spacing) -->
                </div>
            </div>
            <!-- Body -->
            <div class="p-4 overflow-auto flex-1">
                <div id="uploadLogsTableWrapper" class="overflow-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th
                                    class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    File Name</th>
                                <th
                                    class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Size</th>
                                <th
                                    class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Upload Date</th>
                                <th
                                    class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Server Received</th>
                            </tr>
                        </thead>
                        <tbody id="uploadLogsTableBody" class="bg-white divide-y divide-gray-200"></tbody>
                    </table>
                </div>
            </div>
            <!-- Footer (pagination) -->
            <div class="px-4 py-3 border-t bg-gray-50 text-sm">
                <div id="uploadLogsPagination" class="flex items-center justify-end"></div>
            </div>
        </div>
    </div>
</div>
