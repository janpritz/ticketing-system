<div id="avgResolutionTimeModal" class="fixed inset-0 z-50 overflow-y-auto hidden">
    <div class="absolute inset-0 bg-black/60 backdrop-blur-md transition-opacity"
        onclick="closeModal('avgResolutionTimeModal')"></div>
    <div class="relative mx-auto my-0 sm:my-8 w-full h-full sm:h-auto sm:w-[95%] max-w-2xl flex items-center">
        <div
            class="bg-white shadow-2xl w-full h-full sm:h-auto sm:max-w-2xl overflow-hidden sm:rounded-2xl flex flex-col">

            <div class="px-4 sm:px-6 py-4 border-b border-gray-100 flex items-center justify-between shrink-0">
                <div class="flex-1 min-w-0">
                    <h3 class="text-lg font-semibold text-gray-900">Average Resolution Time Details</h3>
                </div>
                <div class="flex items-center gap-2 ml-4">
                    <button onclick="closeModal('avgResolutionTimeModal')"
                        class="text-gray-400 hover:text-gray-600 transition-colors p-1 rounded-lg hover:bg-gray-100">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24"
                            stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </button>
                </div>
            </div>

            <div class="flex-1 overflow-y-auto px-6 py-5">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <h4 class="text-md font-semibold mb-2">Average Resolution Time</h4>
                        <div class="text-3xl font-bold text-blue-600">
                            {{ number_format($performanceMetrics['avg_resolution_time'], 1) }} hours</div>
                        <p class="text-sm text-gray-500 mt-2">Based on resolved tickets</p>
                    </div>
                    <div>
                        <h4 class="text-md font-semibold mb-2">Resolution Time Distribution</h4>
                        <div class="h-48">
                            <canvas id="resolutionTimeChart"></canvas>
                        </div>
                    </div>
                </div>
            </div>

            <div class="px-6 py-4 border-t border-gray-100 bg-gray-50 shrink-0">
                <div class="flex flex-col sm:flex-row items-stretch sm:items-center justify-between gap-3">
                    <div class="flex items-center gap-2"></div>
                    <div class="flex items-center gap-2">
                        <button type="button" onclick="closeModal('avgResolutionTimeModal')"
                            class="flex-1 sm:flex-none inline-flex items-center justify-center rounded-lg bg-white px-4 py-2 text-sm font-medium text-gray-700 ring-1 ring-gray-300 hover:bg-gray-50 transition-colors">
                            Close
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
