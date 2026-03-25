<div id="viewFaqModal" class="fixed inset-0 z-50 hidden"
    data-update-template="{{ route('admin.knowledgebase.update', ['faq' => '__ID__']) }}"
    data-show-url-template="{{ route('admin.knowledgebase.show', ['faq' => '__ID__']) }}"
    data-destroy-template="{{ route('admin.knowledgebase.destroy', ['faq' => '__ID__']) }}">
    <div class="absolute inset-0 bg-black/40" data-close="view"></div>
    <div class="absolute inset-0 flex items-center justify-center p-4">
        <div
            class="relative w-full max-w-full sm:max-w-2xl bg-white rounded-none sm:rounded-lg shadow border border-gray-200 overflow-auto max-h-[90vh]">

            <!-- Header -->
            <div class="h-12 flex items-center px-4 border-b">
                <div class="text-sm font-semibold text-slate-800">Document Details</div>
            </div>

            <!-- Close button top-right -->
            <button type="button" class="absolute top-3 right-3 text-slate-500 hover:text-slate-700" data-close="view"
                aria-label="Close">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24"
                    stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                </svg>
            </button>

            <button id="moreActionsBtn" type="button"
                class="absolute top-3 right-12 text-slate-500 hover:text-slate-700 hidden" aria-label="More actions">
                <span class="text-xl font-bold">⋯</span>
            </button>

            <!-- More actions menu (hidden by default) -->
            <div id="moreActionsMenu"
                class="absolute top-10 right-3 hidden bg-white border border-gray-200 rounded shadow-md z-50 w-44">
                <div class="py-1">
                    <!-- Removed per-document history button -->
                    <button id="more_restore_btn" type="button"
                        class="w-full text-left px-3 py-2 text-sm text-gray-700 hover:bg-gray-50 hidden">Restore
                        Document</button>
                </div>
            </div>

            <!-- Body -->
            <form id="viewFaqForm" class="p-4 space-y-4">
                @csrf
                @method('PUT')
                <input type="hidden" id="view_faq_id" name="faq_id" value="">

                <!-- Topic -->
                <div>
                    <label class="block text-sm font-medium text-slate-700">Intent</label>
                    <input type="text" name="intent" id="view_intent" required
                        class="mt-1 w-full rounded-md border border-gray-300 px-3 py-2 text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500" />
                    <p id="view_intent_error" class="mt-1 text-xs text-red-600 hidden"></p>
                </div>

                <div>
                    <label class="block text-sm font-medium text-slate-700">Description</label>
                    <div class="flex flex-col sm:flex-row gap-2">
                        <textarea name="description" id="view_description" rows="3" required
                            class="mt-1 w-full rounded-md border border-gray-300 px-3 py-2 text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500"></textarea>
                        <button id="viewTemplateBtn" type="button"
                            class="hidden mt-2 sm:mt-1 w-full sm:w-auto rounded-md border border-gray-300 bg-gray-50 hover:bg-gray-100 text-sm px-3 py-2 text-slate-700 sm:self-start">
                            Use template
                        </button>
                    </div>
                    <p id="view_description_error" class="mt-1 text-xs text-red-600 hidden"></p>
                </div>

                <!-- Previous revision collapsible (populated dynamically) -->
                <div id="previousRevisionWrapper" class="mt-3 hidden">
                    <button type="button" id="togglePrevRevisionBtn" class="text-sm text-blue-600 hover:underline">Show
                        previous response</button>
                    <div id="prevRevisionBlock"
                        class="mt-2 hidden bg-gray-50 border border-gray-200 rounded p-3 text-sm whitespace-pre-line max-h-64 overflow-auto sm:max-h-[50vh]">
                        <div id="prevRevisionMeta" class="text-xs text-slate-500 mb-2"></div>
                        <div id="prevRevisionContent" class="text-slate-800"></div>
                        <div class="mt-3 flex justify-end">
                            <button id="restorePrevBtn" type="button"
                                class="rounded-md bg-yellow-500 hover:bg-yellow-600 text-white text-sm px-3 py-1">Restore
                                Previous</button>
                        </div>
                    </div>
                </div>

                <!-- Response -->
                <div>
                    <label class="block text-sm font-medium text-slate-700">Response</label>
                    <textarea name="response" id="view_response" rows="6" required
                        class="mt-1 w-full rounded-md border border-gray-300 px-3 py-2 text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500"></textarea>
                    <p id="view_response_error" class="mt-1 text-xs text-red-600 hidden"></p>
                </div>

                <!-- Footer -->
                <div class="pt-2 flex items-center justify-between">
                    <div class="text-xs text-slate-500" id="view_timestamps"></div>
                    <div class="flex items-center gap-3">
                        <button id="deleteFaqBtn" type="button"
                            class="rounded-md border border-red-200 bg-white text-sm px-3 py-2 text-red-700 hover:bg-red-50">Delete</button>
                        <button id="updateFaqSubmit" type="button"
                            class="rounded-md bg-blue-600 hover:bg-blue-700 text-white text-sm font-medium px-4 py-2">Save
                            Changes</button>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>
