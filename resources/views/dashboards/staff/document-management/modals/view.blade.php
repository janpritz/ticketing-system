<div id="viewDocumentModal" class="fixed inset-0 z-50 hidden">
    <div class="absolute inset-0 bg-black/40" data-close="view-doc"></div>
    <div class="absolute inset-0 flex items-center justify-center p-4">
        <div
            class="relative w-full max-w-full sm:max-w-4xl bg-white rounded-none sm:rounded-lg shadow border border-gray-200 overflow-auto max-h-[90vh]">
            <div class="h-12 flex items-center justify-between px-4 border-b">
                <div class="text-sm font-semibold text-slate-800">View Document</div>
                <button type="button" class="text-slate-500 hover:text-slate-700" data-close="view-doc"
                    aria-label="Close">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24"
                        stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>

            <div class="p-4 space-y-4">
                <div>
                    <label class="block text-sm font-medium text-slate-700">File Name</label>
                    <input type="text" id="view_doc_filename" readonly
                        class="mt-1 w-full rounded-md border border-gray-300 px-3 py-2 text-sm bg-gray-50 text-gray-600" />
                </div>
                <div>
                    <label class="block text-sm font-medium text-slate-700">Content</label>
                    <pre id="view_doc_content"
                        class="mt-1 w-full rounded-md border border-gray-300 px-3 py-2 text-sm font-mono bg-gray-50 text-gray-800 whitespace-pre-wrap overflow-auto max-h-[60vh]"></pre>
                </div>
            </div>

            <div class="px-4 py-3 border-t flex items-center justify-end gap-3">
                <button type="button" id="viewDocEditBtn"
                    class="rounded-md bg-amber-600 hover:bg-amber-700 text-white text-sm font-medium px-4 py-2">
                    Edit Document
                </button>
                <button type="button"
                    class="rounded-md border border-gray-300 bg-white hover:bg-gray-50 text-sm px-4 py-2"
                    data-close="view-doc">Close</button>
            </div>
        </div>
    </div>
</div>
