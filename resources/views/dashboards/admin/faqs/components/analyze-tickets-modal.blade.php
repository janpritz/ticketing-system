<div id="analyze-modal" class="fixed inset-0 z-50 hidden overflow-y-auto">
    <div class="absolute inset-0 bg-black/60 backdrop-blur-md transition-opacity" data-modal-backdrop></div>
    <!-- Centered panel with modern minimal design -->
    <div class="relative mx-auto my-0 sm:my-8 w-full h-full sm:h-auto sm:w-[95%] max-w-2xl flex items-center">
        <div
            class="bg-white shadow-2xl w-full h-full sm:h-auto sm:max-h-[95vh] sm:max-w-2xl overflow-hidden sm:rounded-2xl flex flex-col">

            <!-- Header - Minimal & Clean -->
            <div class="px-4 sm:px-6 py-4 border-b border-gray-100 flex items-center justify-between shrink-0">
                <div class="flex-1 min-w-0">
                    <h3 class="text-lg font-semibold text-gray-900">Analyze Tickets for FAQ Generation</h3>
                    <p class="text-sm text-gray-500 mt-1">Process closed tickets using OpenAI to generate FAQ clusters
                    </p>
                </div>
                <button type="button"
                    class="text-gray-400 hover:text-gray-600 transition-colors p-1 rounded-lg hover:bg-gray-100"
                    aria-label="Close" onclick="closeAnalyzeModal()">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24"
                        stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>

            <!-- Content - Scrollable -->
            <div class="flex-1 overflow-y-auto px-6 py-5 space-y-5">

                <!-- Unprocessed Tickets Info -->
                <div class="bg-blue-50 rounded-xl p-4 border border-blue-100">
                    <div class="flex items-center gap-2 mb-2">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 text-blue-600" fill="none"
                            viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                        <span class="text-xs font-medium text-blue-700 uppercase tracking-wide">Unprocessed
                            Tickets</span>
                    </div>
                    <div class="text-2xl font-bold text-blue-900" id="unprocessed-count">0</div>
                    <p class="text-blue-700 text-xs mt-2">
                        These closed tickets will be analyzed by OpenAI to generate FAQ clusters.
                    </p>
                </div>

                <!-- Progress Section (Hidden by default) -->
                <div id="progress-section" class="hidden space-y-3">
                    <div class="w-full bg-gray-200 rounded-full h-2">
                        <div id="progress-bar" class="bg-blue-600 h-2 rounded-full transition-all duration-300"
                            style="width: 0%"></div>
                    </div>
                    <p id="progress-text" class="text-sm text-gray-600">Initializing analysis...</p>
                </div>

                <!-- Results Section (Hidden by default) -->
                <div id="results-section" class="hidden space-y-3">
                    <div class="bg-emerald-50 rounded-xl p-4 border border-emerald-200">
                        <div class="flex items-center gap-2 mb-3">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-emerald-600" fill="none"
                                viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                            <span class="text-sm font-semibold text-emerald-700">Analysis Completed!</span>
                        </div>
                        <ul class="text-emerald-700 text-sm space-y-2">
                            <li class="flex justify-between">
                                <span>Tickets Processed:</span>
                                <span class="font-semibold" id="tickets-processed">0</span>
                            </li>
                            <li class="flex justify-between">
                                <span>FAQs Generated:</span>
                                <span class="font-semibold" id="faqs-generated">0</span>
                            </li>
                        </ul>
                    </div>
                </div>
            </div>

            <!-- Footer - Actions -->
            <div class="px-6 py-4 border-t border-gray-100 bg-gray-50 shrink-0">
                <div class="flex flex-col sm:flex-row items-stretch sm:items-center justify-between gap-3">
                    <div></div>
                    <div class="flex items-center gap-2">
                        <button type="button"
                            class="flex-1 sm:flex-none inline-flex items-center justify-center rounded-lg bg-white px-4 py-2 text-sm font-medium text-gray-700 ring-1 ring-gray-300 hover:bg-gray-50 transition-colors"
                            onclick="closeAnalyzeModal()">Cancel</button>
                        <button type="button"
                            class="flex-1 sm:flex-none inline-flex items-center justify-center gap-2 rounded-lg bg-green-600 px-5 py-2 text-sm font-medium text-white hover:bg-green-700 transition-colors shadow-sm disabled:opacity-50 disabled:cursor-not-allowed"
                            id="analyze-btn" onclick="startAnalysis()">
                            <!-- icon shown when idle -->
                            <svg id="analyzeIcon" xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" viewBox="0 0 24 24"
                                fill="currentColor">
                                <path
                                    d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm0 18c-4.41 0-8-3.59-8-8s3.59-8 8-8 8 3.59 8 8-3.59 8-8 8zm3.5-9c.83 0 1.5-.67 1.5-1.5S16.33 8 15.5 8 14 8.67 14 9.5s.67 1.5 1.5 1.5zm-7 0c.83 0 1.5-.67 1.5-1.5S9.33 8 8.5 8 7 8.67 7 9.5 7.67 11 8.5 11zm3.5 6.5c2.33 0 4.31-1.46 5.11-3.5H6.89c.8 2.04 2.78 3.5 5.11 3.5z" />
                            </svg>
                            <!-- spinner shown while requesting -->
                            <svg id="analyzeSpinner" xmlns="http://www.w3.org/2000/svg"
                                class="h-4 w-4 animate-spin hidden" viewBox="0 0 24 24" fill="none"
                                stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M12 2v4M12 18v4M4.93 4.93l2.83 2.83M16.24 16.24l2.83 2.83M2 12h4M18 12h4M4.93 19.07l2.83-2.83M16.24 7.76l2.83-2.83" />
                            </svg>
                            <span id="analyzeText">Start Analysis</span>
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
