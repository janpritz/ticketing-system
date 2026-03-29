<div id="emailNotificationCard" class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden mt-4">
    <div
        class="px-6 py-4 border-b border-gray-100 bg-gradient-to-r from-indigo-50 to-white flex flex-col sm:flex-row sm:items-center justify-between gap-3">
        <div>
            <h2 class="text-lg font-semibold text-gray-900 flex items-center gap-2">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-indigo-600" fill="none" viewBox="0 0 24 24"
                    stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" />
                </svg>
                Email Notifications
            </h2>
            <p class="text-sm text-gray-500 mt-1">Receive email notifications for ticket updates</p>
        </div>
        <div>
            <button id="emailNotifToggle" role="switch" aria-checked="false" type="button"
                class="inline-flex items-center px-3 py-1.5 rounded-full text-sm font-medium bg-gray-200 text-gray-700 focus:outline-none">
                <span id="emailNotifDot" class="inline-block w-3 h-3 rounded-full bg-white shadow-sm mr-2"></span>
                <span id="emailNotifLabel">Off</span>
            </button>
        </div>
    </div>
</div>
