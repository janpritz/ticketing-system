<div id="editDocumentModal" class="fixed inset-0 z-50 hidden">
        <div class="absolute inset-0 bg-black/40" data-close="edit-doc"></div>
        <div class="absolute inset-0 flex items-center justify-center p-4">
            <div
                class="w-full max-w-full sm:max-w-4xl bg-white rounded-none sm:rounded-lg shadow border border-gray-200 overflow-auto max-h-[90vh]">
                <div class="h-12 flex items-center justify-between px-4 border-b">
                    <div class="text-sm font-semibold text-slate-800">Edit Document</div>
                    <div class="flex items-center gap-3">
                        <button type="button" class="text-slate-500 hover:text-slate-700" data-close="edit-doc"
                            aria-label="Close">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 24 24"
                                fill="currentColor">
                                <path d="M6 18 18 6M6 6l12 12" />
                            </svg>
                        </button>
                    </div>
                </div>
                <form id="editDocumentForm" class="p-4 space-y-4">
                    <div>
                        <label class="block text-sm font-medium text-slate-700">File Name</label>
                        <input type="text" id="edit_doc_filename" readonly
                            class="mt-1 w-full rounded-md border border-gray-300 px-3 py-2 text-sm bg-gray-50 text-gray-600" />
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-slate-700">Content</label>
                        <textarea id="edit_doc_content" rows="20" required
                            class="mt-1 w-full rounded-md border border-gray-300 px-3 py-2 text-sm font-mono focus:ring-2 focus:ring-amber-500 focus:border-amber-500"
                            placeholder="Enter document content here..."></textarea>
                        <p class="mt-1 text-xs text-slate-500">Supports plain text and markdown formatting</p>
                        <p id="edit_doc_error" class="mt-1 text-xs text-red-600 hidden"></p>
                    </div>
                    <div class="pt-2 flex items-center justify-end gap-3">
                        <button type="button"
                            class="rounded-md border border-gray-300 bg-white hover:bg-gray-50 text-sm px-4 py-2"
                            data-close="edit-doc">Cancel</button>
                        <button id="editDocumentSubmit" type="button"
                            class="rounded-md bg-amber-600 hover:bg-amber-700 text-white text-sm font-medium px-4 py-2">
                            <svg class="animate-spin h-4 w-4 mr-2 hidden" id="editDocSpinner" fill="none"
                                viewBox="0 0 24 24">
                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor"
                                    stroke-width="4"></circle>
                                <path class="opacity-75" fill="currentColor"
                                    d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z">
                                </path>
                            </svg>
                            <span id="editDocBtnText">Save Changes</span>
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
