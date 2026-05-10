<div class="mt-6 bg-yellow-50 rounded-xl border border-gray-200 p-4">
    <div class="flex items-center justify-between mb-4">
        <h3 class="text-lg font-semibold text-gray-900 flex items-center gap-2">
            <svg class="w-5 h-5 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                    d="M9.75 17L9 20l-1 1h8l-1-1-.75-3M3 13h18M5 17h14a2 2 0 002-2V5a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" />
            </svg>
            System Status
        </h3>
        <div class="flex items-center gap-3">
            <button id="startServerBtn"
                class="inline-flex items-center gap-2 px-3 py-2 text-sm font-medium text-green-600 bg-green-50 hover:bg-green-100 rounded-lg transition-colors disabled:opacity-50 disabled:cursor-not-allowed">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M14.828 14.828a4 4 0 01-5.656 0M9 10h1.586a1 1 0 01.707.293l.707.707A1 1 0 0012.414 11H15m-3-3h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
                Start Server
            </button>
            {{-- <button id="startActionServerBtn"
                class="inline-flex items-center gap-2 px-3 py-2 text-sm font-medium text-blue-600 bg-blue-50 hover:bg-blue-100 rounded-lg transition-colors disabled:opacity-50 disabled:cursor-not-allowed">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M13 10V3L4 14h7v7l9-11h-7z" />
                </svg>
                Start Actions
            </button> --}}
            <button id="refreshStatus"
                class="inline-flex items-center gap-2 px-3 py-2 text-sm font-medium text-gray-700 bg-gray-100 hover:bg-gray-200 rounded-lg transition-colors">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
                </svg>
                Refresh
            </button>
        </div>
    </div>

    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 xl:grid-cols-4 gap-4 mb-4">
        <!-- Rasa Endpoint Card -->
        {{-- <div class="bg-white border border-gray-200 rounded-lg p-4">
            <div class="text-sm font-medium text-gray-600 mb-1">Rasa Endpoint</div>
            <div class="text-xs text-gray-500 mb-2">Port 5001</div>
            <div id="endpointStatus" class="flex items-center gap-2">
                <div class="w-4 h-4 rounded-full bg-gray-400 animate-pulse"></div>
                <span class="text-sm text-gray-600">Checking...</span>
            </div>
        </div> --}}

        <!-- Rasa Server Card -->
        <div class="bg-white border border-gray-200 rounded-lg p-4">
            <div class="text-sm font-medium text-gray-600 mb-1">Rasa Server</div>
            <div class="text-xs text-gray-500 mb-2">Port 5005</div>
            <div id="serverStatus" class="flex items-center gap-2">
                <div class="w-4 h-4 rounded-full bg-gray-400 animate-pulse"></div>
                <span class="text-sm text-gray-600">Checking...</span>
            </div>
        </div>

        <!-- Last Training Card -->
        <div class="bg-white border border-gray-200 rounded-lg p-4">
            <div class="text-sm font-medium text-gray-600 mb-1">Last Training</div>
            <div id="lastTraining" class="text-sm text-gray-900 mb-1">Loading...</div>
            <div class="text-xs text-gray-500">1 day ago</div>
        </div>

        <!-- Last Backup card removed (backups stored in DB; unnecessary UI) -->

        <!-- Current Model Card -->
        <div class="bg-white border border-gray-200 rounded-lg p-4">
            <div class="text-sm font-medium text-gray-600 mb-1">Current Model</div>
            <div id="currentModel" class="text-sm text-gray-900 mb-1">Loading...</div>
            <div class="text-xs text-gray-500" id="currentModelVersion">-</div>
        </div>

        <div class="bg-white border border-gray-200 rounded-lg p-4">
            <div class="text-sm font-medium text-gray-600 mb-1">Chatbot Status</div>
            <div id="training-status-container" class="flex flex-col items-center justify-center py-2">
                <i id="status-icon" class="fas fa-robot fa-2x mb-2 text-blue-600"></i>
                <h4 id="status-text" class="text-sm font-medium text-gray-800">Checking system...</h4>
                <p id="status-subtext" class="text-xs text-gray-500 mt-1"></p>
            </div>
            <div id="progress-wrapper" class="hidden mt-3">
                <div class="w-full h-2 bg-gray-200 rounded-full overflow-hidden">
                    <div class="h-full bg-blue-600 animate-pulse rounded-full" style="width: 100%"></div>
                </div>
                <p class="text-xs text-gray-500 mt-1 text-center">Updating chatbot knowledge base...</p>
            </div>
        </div>
    </div>
</div>
