<div id="forwardsModal" class="fixed inset-0 z-50 hidden overflow-y-auto">
    <div class="absolute inset-0 bg-black/60 backdrop-blur-md transition-opacity" data-modal-backdrop></div>
    <!-- Centered panel with modern minimal design (copied from ticket modal for consistent sizing) -->
    <div class="relative mx-auto my-0 sm:my-8 w-full h-full sm:h-auto sm:w-[95%] max-w-2xl flex items-center">
        <div
            class="bg-white shadow-2xl w-full h-full sm:h-auto sm:max-h-[95vh] sm:max-w-2xl overflow-hidden sm:rounded-2xl flex flex-col">
            <div class="px-4 sm:px-6 py-4 border-b border-gray-100 flex items-center justify-between shrink-0">
                <div class="flex-1 min-w-0">
                    <h3 class="modal-title text-lg font-semibold text-gray-900">Forwards</h3>
                    <div class="text-xs text-gray-500">Breakdown of recipients for forwards by staff</div>
                </div>
                <div class="flex items-center gap-2 ml-4">
                    <button type="button" class="text-gray-400 hover:text-gray-600 transition-colors p-1 rounded-lg"
                        aria-label="Close" data-modal-close>
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24"
                            stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </button>
                </div>
            </div>
            <div class="flex-1 overflow-y-auto px-6 py-5 modal-body text-sm text-gray-800">
                <!-- populated dynamically -->
            </div>
        </div>
    </div>
</div>
