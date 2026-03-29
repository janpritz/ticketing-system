<div id="viewAnnouncementModal" class="fixed inset-0 z-50 hidden">
    <div class="absolute inset-0 bg-black/40" data-close="view-announcement"></div>
    <div class="absolute inset-0 flex items-center justify-center p-4">
        <div
            class="relative w-full max-w-full sm:max-w-4xl bg-white rounded-lg shadow border border-gray-200 overflow-auto h-[80vh] mx-4 sm:mx-0">
            <div class="h-12 flex items-center justify-between px-4 border-b">
                <div class="text-sm font-semibold text-slate-800" id="viewAnnouncementTitle">Announcement</div>
                <div class="flex items-center gap-2">
                    <button id="announcementMenuBtn" class="text-slate-500 hover:text-slate-700" aria-label="Menu">
                        <svg class="h-5 w-5" fill="currentColor" viewBox="0 0 20 20">
                            <path
                                d="M10 6a2 2 0 110-4 2 2 0 010 4zM10 12a2 2 0 110-4 2 2 0 010 4zM10 18a2 2 0 110-4 2 2 0 010 4z" />
                        </svg>
                    </button>
                    <button type="button" class="text-gray-700 hover:text-gray-900 p-1 rounded hover:bg-gray-100"
                        data-close="view-announcement" aria-label="Close">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 24 24" fill="none"
                            stroke="currentColor" stroke-width="2">
                            <path d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </button>
                </div>
            </div>
            <div id="announcementMenu"
                class="hidden absolute right-4 top-12 bg-white border border-gray-200 rounded shadow-lg z-10 w-32">
                <button id="editAnnouncementMenu"
                    class="block w-full text-left px-4 py-2 text-sm hover:bg-gray-100">Edit</button>
                <button id="deleteAnnouncementMenu"
                    class="block w-full text-left px-4 py-2 text-sm hover:bg-gray-100">Delete</button>
                <button id="pinAnnouncementMenu"
                    class="block w-full text-left px-4 py-2 text-sm hover:bg-gray-100">Pin</button>
            </div>
            <div class="p-6">
                <h2 class="text-lg font-semibold text-gray-900 mb-4" id="viewAnnouncementTitleDisplay"></h2>
                <div class="text-sm text-gray-700 whitespace-pre-line break-words" id="viewAnnouncementContentDisplay">
                </div>
            </div>
        </div>
    </div>
</div>
