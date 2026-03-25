<div id="uploadFileModal" class="fixed inset-0 z-50 hidden">
    <div class="absolute inset-0 bg-black/40" data-close="upload"></div>
    <div class="absolute inset-0 flex items-center justify-center p-4">
        <div
            class="w-full max-w-full sm:max-w-lg bg-white rounded-none sm:rounded-lg shadow border border-gray-200 overflow-auto max-h-[90vh]">
            <div class="h-12 flex items-center justify-between px-4 border-b">
                <div class="text-sm font-semibold text-slate-800">Upload File</div>
                <button type="button" class="text-slate-500 hover:text-slate-700" data-close="upload" aria-label="Close">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 24 24" fill="currentColor">
                        <path d="M6 18 18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>
            <form id="uploadFileForm" class="p-4 space-y-4">
                <div>
                    <label class="block text-sm font-medium text-slate-700">Select Text File</label>
                    <input type="file" id="faqFile" accept=".txt" required
                        class="mt-1 w-full rounded-md border border-gray-300 px-3 py-2 text-sm focus:ring-2 focus:ring-purple-500 focus:border-purple-500" />
                    <p class="mt-1 text-xs text-slate-500">Only .txt files are allowed</p>
                    <p id="upload_file_error" class="mt-1 text-xs text-red-600 hidden"></p>
                </div>
                <div class="pt-2 flex items-center justify-end gap-3">
                    <button type="button"
                        class="rounded-md border border-gray-300 bg-white hover:bg-gray-50 text-sm px-4 py-2"
                        data-close="upload">Cancel</button>
                    <button id="uploadFileSubmit" type="button"
                        class="rounded-md bg-purple-600 hover:bg-purple-700 text-white text-sm font-medium px-4 py-2">Upload
                        & Sync</button>
                </div>
            </form>
        </div>
    </div>
</div>
