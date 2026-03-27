<div id="viewAnnouncementModal" class="fixed inset-0 z-50 hidden">
    <div class="absolute inset-0 bg-black/40" data-close="view-announcement"></div>
    <div class="absolute inset-0 flex items-center justify-center p-4">
        <div class="w-full max-w-full sm:max-w-4xl bg-white rounded-lg shadow border border-gray-200 overflow-hidden flex flex-col h-[80vh] mx-4 sm:mx-0">

            <div class="h-12 flex-shrink-0 flex items-center justify-between px-4 border-b bg-gray-50 relative">
                <div class="text-sm font-semibold text-slate-800" id="viewAnnouncementTitle">Announcement</div>
                <div class="flex items-center gap-2">
                    <div class="relative">
                        <button id="announcementMenuBtn" class="text-slate-500 hover:text-slate-700" aria-label="Menu">
                            <svg class="h-5 w-5" fill="currentColor" viewBox="0 0 20 20">
                                <path
                                    d="M10 6a2 2 0 110-4 2 2 0 010 4zM10 12a2 2 0 110-4 2 2 0 010 4zM10 18a2 2 0 110-4 2 2 0 010 4z" />
                            </svg>
                        </button>
                        <div id="announcementMenu"
                            class="hidden absolute right-0 top-6 bg-white border border-gray-200 rounded shadow-lg z-50 w-32">
                            <button id="editAnnouncementMenu"
                                class="block w-full text-left px-4 py-2 text-sm hover:bg-gray-100">Edit</button>
                            <button id="deleteAnnouncementMenu"
                                class="block w-full text-left px-4 py-2 text-sm hover:bg-gray-100">Delete</button>
                            <button id="pinAnnouncementMenu"
                                class="block w-full text-left px-4 py-2 text-sm hover:bg-gray-100">Pin</button>
                        </div>
                    </div>
                    <button type="button" class="text-gray-700 hover:text-gray-900 p-1 rounded hover:bg-gray-100"
                        data-close="view-announcement" aria-label="Close">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 24 24" fill="none"
                            stroke="currentColor" stroke-width="2">
                            <path d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </button>
                </div>
            </div>

            <div class="p-6 space-y-5 flex-1 overflow-y-auto">

                <div>
                    <label class="block text-sm font-medium text-slate-700">Announcement Title</label>
                    <div class="mt-1 w-full rounded-md border border-gray-300 px-3 py-2.5 text-sm bg-gray-50 text-gray-900" id="viewAnnouncementTitleDisplay"></div>
                </div>

                <div>
                    <label class="block text-sm font-medium text-slate-700">Announcement Content</label>
                    <div class="mt-1 w-full rounded-md border border-gray-300 px-3 py-2 text-sm bg-gray-50 text-gray-700 whitespace-pre-line break-words min-h-[120px]" id="viewAnnouncementContentDisplay"></div>
                    <p class="mt-1 text-xs text-slate-500">Announcements are for short-term, constantly changing information like enrollment schedules and document releases.</p>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-3 gap-4 bg-slate-50 p-4 rounded-lg border border-slate-200">
                    <div>
                        <label class="block text-sm font-medium text-slate-700">Visible Starting From</label>
                        <div class="mt-1 w-full rounded-md border border-gray-300 px-3 py-2 text-sm bg-white text-gray-700" id="viewAnnouncementStartsAt"></div>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-slate-700">Expire</label>
                        <div class="mt-1 w-full rounded-md border border-gray-300 px-3 py-2 text-sm bg-white text-gray-700" id="viewAnnouncementExpiresAt"></div>
                    </div>
                    <div>
                    <label class="block text-sm font-medium text-slate-700">Created At</label>
                    <div class="mt-1 w-full rounded-md border border-gray-300 px-3 py-2 text-sm bg-white text-gray-700" id="viewAnnouncementCreatedAt"></div>
                </div>
                </div>
                

            </div>

            <div id="restoreButtonContainer" class="p-4 flex-shrink-0 flex flex-col sm:flex-row sm:items-center justify-end gap-3 border-t bg-gray-50 hidden">
                <button type="button"
                    class="rounded-md border border-gray-300 bg-white hover:bg-gray-50 text-sm px-6 py-3 order-2 sm:order-1"
                    data-close="view-announcement">Close</button>
                <button id="restoreAnnouncementBtn" type="button"
                    class="rounded-md bg-green-600 hover:bg-green-700 text-white text-sm font-medium px-6 py-3 order-1 sm:order-2 w-full sm:w-auto">
                    <svg class="w-4 h-4 mr-2 inline" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
                    </svg>
                    Restore Announcement
                </button>
            </div>
        </div>
    </div>
</div>
