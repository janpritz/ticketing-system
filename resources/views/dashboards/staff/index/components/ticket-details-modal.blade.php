<div id="ticketModal" class="fixed inset-0 z-50 hidden overflow-y-auto">
    <div class="absolute inset-0 bg-black/60 backdrop-blur-md transition-opacity" data-modal-backdrop>
    </div>
    <!-- Centered panel with modern minimal design -->
    <div class="relative mx-auto my-0 sm:my-8 w-full h-full sm:h-auto sm:w-[95%] max-w-2xl flex items-center">
        <div
            class="bg-white shadow-2xl w-full h-full sm:h-auto sm:max-h-[95vh] sm:max-w-2xl overflow-hidden sm:rounded-2xl flex flex-col">

            <!-- Header - Minimal & Clean -->
            <div class="px-4 sm:px-6 py-4 border-b border-gray-100 flex items-center justify-between shrink-0">
                <div class="flex-1 min-w-0">
                    <div class="flex items-center gap-3 mb-2">
                        <h3 id="tmTicketNo" class="text-lg font-semibold text-gray-900">Ticket #</h3>
                        <span id="tmStatus"
                            class="inline-flex items-center gap-1.5 rounded-full px-3 py-1 text-xs font-medium ring-1"></span>
                    </div>
                </div>
                <div class="flex items-center gap-2 ml-4">
                    <!-- Options Menu in Header -->
                    <div class="relative">
                        <button type="button" id="tmOptionsBtn"
                            class="inline-flex items-center gap-2 rounded-lg px-2 py-2 text-sm font-medium text-gray-700 hover:bg-gray-100 transition-colors"
                            aria-haspopup="true" aria-expanded="false">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" viewBox="0 0 24 24"
                                fill="currentColor">
                                <path
                                    d="M5 12a2 2 0 114 0 2 2 0 01-4 0zm5 0a2 2 0 114 0 2 2 0 01-4 0zm5 0a2 2 0 114 0 2 2 0 01-4 0z" />
                            </svg>
                            <span class="hidden sm:inline">Options</span>
                        </button>
                        <div id="tmOptionsMenu"
                            class="absolute right-0 top-full mt-2 w-48 bg-white rounded-lg shadow-lg ring-1 ring-black/5 hidden z-10 overflow-hidden">
                            <button type="button"
                                class="w-full text-left px-4 py-2.5 text-sm text-gray-700 hover:bg-gray-50 transition-colors"
                                data-option="toggle-history">Show History</button>
                            <button type="button" id="tmOptionForward"
                                class="w-full text-left px-4 py-2.5 text-sm text-gray-700 hover:bg-gray-50 transition-colors border-t border-gray-100"
                                data-option="show-forward">Forward Ticket</button>
                        </div>
                    </div>
                    <button type="button"
                        class="text-gray-400 hover:text-gray-600 transition-colors p-1 rounded-lg hover:bg-gray-100"
                        aria-label="Close" data-modal-close>
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24"
                            stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </button>
                </div>
            </div>

            <!-- Content - Scrollable -->
            <div class="flex-1 overflow-y-auto px-6 py-5 space-y-5">

                <!-- Question/Issue - Primary Focus -->
                <div class="bg-gray-50 rounded-xl p-4 border border-gray-100">
                    <div class="flex items-center gap-2 mb-2">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 text-gray-500" fill="none"
                            viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M8.228 9c.549-1.165 2.03-2 3.772-2 2.21 0 4 1.343 4 3 0 1.4-1.278 2.575-3.006 2.907-.542.104-.994.54-.994 1.093m0 3h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                        <span class="text-xs font-medium text-gray-500 uppercase tracking-wide">Concern</span>
                    </div>
                    <div id="tmQuestion" class="text-sm text-gray-900 whitespace-pre-wrap leading-relaxed"></div>
                </div>

                <!-- Attachments - Visible when present -->
                <div id="tmAttachmentsBlock" class="hidden">
                    <div class="flex items-center gap-2 mb-3">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 text-gray-500" fill="none"
                            viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M15.172 7l-6.586 6.586a2 2 0 102.828 2.828l6.414-6.586a4 4 0 00-5.656-5.656l-6.415 6.585a6 6 0 108.486 8.486L20.5 13" />
                        </svg>
                        <span class="text-xs font-medium text-gray-700">Attachments</span>
                    </div>
                    <div id="tmAttachmentsList" class="flex flex-wrap gap-2"></div>
                </div>

                <!-- Sent Response - For closed tickets -->
                <div id="tmStoredResponseBlock" class="hidden bg-emerald-50 rounded-xl p-4 border border-emerald-200">
                    <div class="flex items-center gap-2 mb-2">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 text-emerald-600" fill="none"
                            viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                        <span class="text-xs font-semibold text-emerald-700 uppercase tracking-wide">Sent
                            Response</span>
                    </div>
                    <div id="tmStoredResponse" class="text-sm text-gray-800 whitespace-pre-wrap leading-relaxed">
                    </div>
                </div>

                <!-- Collapsible Details Section -->
                <div id="tmDetailsSection" class="border-t border-gray-100 pt-4">
                    <button type="button" id="tmToggleDetails"
                        class="flex items-center justify-between w-full text-left group">
                        <span class="text-sm font-medium text-gray-700 group-hover:text-gray-900">Show
                            Details</span>
                        <svg id="tmDetailsChevron" xmlns="http://www.w3.org/2000/svg"
                            class="h-4 w-4 text-gray-500 transition-transform" fill="none" viewBox="0 0 24 24"
                            stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                        </svg>
                    </button>

                    <div id="tmDetailsContent" class="hidden mt-4 space-y-4 pb-2">
                        <!-- Category & Dates -->
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                            <div>
                                <label
                                    class="text-xs font-medium text-gray-500 uppercase tracking-wide">Category</label>
                                <div id="tmCategory"
                                    class="mt-1 inline-flex items-center rounded-full bg-gray-100 px-2.5 py-1 text-xs font-medium text-gray-700">
                                </div>
                            </div>
                            <div>
                                <label
                                    class="text-xs font-medium text-gray-500 uppercase tracking-wide">Timeline</label>
                                <div id="tmDates" class="mt-1 text-xs text-gray-700"></div>
                            </div>
                        </div>

                        <!-- Contact Info -->
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                            <div>
                                <label class="text-xs font-medium text-gray-500 uppercase tracking-wide">Email</label>
                                <div id="tmEmail" class="mt-1 text-sm text-gray-800"></div>
                            </div>
                            <div>
                                <label class="text-xs font-medium text-gray-500 uppercase tracking-wide">Recipient
                                    ID</label>
                                <div id="tmRecepient" class="mt-1 text-sm text-gray-800"></div>
                            </div>
                        </div>

                        <!-- Routing History -->
                        <div id="tmHistorySection" class="hidden">
                            <label class="text-xs font-medium text-gray-500 uppercase tracking-wide">Routing
                                History</label>
                            <ul id="tmHistoryList" class="mt-2 space-y-2"></ul>
                        </div>
                    </div>
                </div>

                <!-- Response Input -->
                <div class="bg-gradient-to-br from-indigo-50 to-blue-50 rounded-xl p-4 border border-indigo-100">
                    <label for="tmResponse" class="flex items-center gap-2 text-sm font-medium text-indigo-900 mb-2">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24"
                            stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" />
                        </svg>
                        Your Response
                    </label>
                    <textarea id="tmResponse"
                        class="w-full rounded-lg border-indigo-200 text-sm focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 bg-white shadow-sm resize-none"
                        rows="4" placeholder="Type your response message here..."></textarea>
                </div>

                <!-- Forward Controls -->
                <div id="tmForwardControls" class="hidden">
                    <div class="flex flex-col sm:flex-row sm:items-center gap-2">
                        <label for="tmForwardSelect" class="text-xs font-medium text-gray-700">Forward
                            to:</label>
                        <select id="tmForwardSelect"
                            class="flex-1 sm:flex-none rounded-lg border-gray-300 text-sm focus:ring-2 focus:ring-indigo-500">
                            <!-- options will be populated dynamically when opening a ticket (mirrors admin dashboard) -->
                        </select>
                        <button type="button" id="tmForwardApply"
                            class="inline-flex items-center justify-center rounded-lg bg-amber-600 px-4 py-2 text-sm font-medium text-white hover:bg-amber-700 transition-colors">
                            Forward Ticket
                        </button>
                    </div>
                </div>
            </div>

            <!-- Footer - Actions -->
            <div class="px-6 py-4 border-t border-gray-100 bg-gray-50 shrink-0">
                <!-- Main Actions -->
                <div class="flex flex-col sm:flex-row items-stretch sm:items-center justify-between gap-3">
                    <div class="flex items-center gap-2">

                    </div>
                    <div class="flex items-center gap-2">
                        <button type="button"
                            class="flex-1 sm:flex-none inline-flex items-center justify-center rounded-lg bg-white px-4 py-2 text-sm font-medium text-gray-700 ring-1 ring-gray-300 hover:bg-gray-50 transition-colors"
                            data-modal-close>Cancel</button>
                        <button type="button" title="Send response" aria-label="Send response"
                            class="flex-1 sm:flex-none inline-flex items-center justify-center gap-2 rounded-lg bg-indigo-600 px-5 py-2 text-sm font-medium text-white hover:bg-indigo-700 transition-colors shadow-sm"
                            id="tmSendResponse">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" viewBox="0 0 24 24"
                                fill="currentColor">
                                <path d="M3 12l18-9-9 18-2-7-7-2z" />
                            </svg>
                            Send Response
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
