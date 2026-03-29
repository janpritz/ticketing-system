<div id="addAnnouncementModal" class="fixed inset-0 z-50 hidden">
    <div class="absolute inset-0 bg-black/40" data-close="announcement"></div>
    <div class="absolute inset-0 flex items-center justify-center p-4">
        <div
            class="w-full max-w-full sm:max-w-4xl bg-white rounded-lg shadow border border-gray-200 overflow-hidden flex flex-col h-[80vh] mx-4 sm:mx-0">
            <div class="h-12 flex-shrink-0 flex items-center justify-between px-4 border-b bg-gray-50">
                <div class="text-sm font-semibold text-slate-800" id="addModalTitle">Add Announcement</div>
                <button type="button" class="text-slate-500 hover:text-slate-700" data-close="announcement" aria-label="Close">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>

            <form id="addAnnouncementForm" class="p-6 space-y-5 flex-1 overflow-y-auto">

                <div>
                    <label class="block text-sm font-medium text-slate-700">Announcement Title</label>
                    <input type="text" id="announcementTitle" required
                        class="mt-1 w-full rounded-md border border-gray-300 px-3 py-2.5 text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                        placeholder="Enter announcement title...">
                    <p id="title_error" class="mt-1 text-xs text-red-600 hidden"></p>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4 bg-slate-50 p-4 rounded-lg border border-slate-200">
                    <div>
                        <label class="block text-sm font-medium text-slate-700">Visible Starting From</label>
                        <input type="datetime-local" id="announcementStartsAt" required
                            class="mt-1 w-full rounded-md border border-gray-300 px-3 py-2 text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-slate-700">Auto-Expire (Retraining Trigger)</label>
                        <input type="datetime-local" id="announcementExpiresAt" required
                            class="mt-1 w-full rounded-md border border-gray-300 px-3 py-2 text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                        <p class="mt-1 text-xs text-slate-500">Must be at least 3 days after the start date.</p>
                    </div>
                    <p id="date_error" class="md:col-span-2 text-xs text-red-600 hidden"></p>
                </div>

                <div>
                    <label class="block text-sm font-medium text-slate-700">Announcement Content</label>
                    <textarea id="announcementContent" rows="8" required
                        class="mt-1 w-full rounded-md border border-gray-300 px-3 py-2 text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                        placeholder="Enter your announcement here..."></textarea>
                    <p class="mt-1 text-xs text-slate-500">Announcements are for short-term, constantly changing information like enrollment schedules and document releases.</p>
                    <p id="announcement_error" class="mt-1 text-xs text-red-600 hidden"></p>
                </div>

            </form>

            <div class="p-4 flex-shrink-0 flex flex-col sm:flex-row sm:items-center justify-end gap-3 border-t bg-gray-50">
                <button type="button"
                    class="rounded-md border border-gray-300 bg-white hover:bg-gray-50 text-sm px-6 py-3 order-2 sm:order-1"
                    data-close="announcement">Cancel</button>
                <button id="addAnnouncementSubmit" type="button"
                    class="rounded-md bg-blue-600 hover:bg-blue-700 text-white text-sm font-medium px-6 py-3 order-1 sm:order-2 w-full sm:w-auto">Add</button>
            </div>
        </div>
    </div>
</div>
