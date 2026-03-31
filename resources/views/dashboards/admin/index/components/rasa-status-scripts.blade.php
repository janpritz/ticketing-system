<div class="mt-6 bg-yellow-50 rounded-xl border border-gray-200 p-4">
    <div class="flex items-center justify-between mb-4">
        <h3 class="text-lg font-semibold text-gray-900 flex items-center gap-2">
            <svg class="w-5 h-5 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                    d="M9.75 17L9 20l-1 1h8l-1-1-.75-3M3 13h18M5 17h14a2 2 0 002-2V5a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" />
            </svg>
            System & Training Status
        </h3>
        <div class="flex items-center gap-3">
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

    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
        <div class="bg-white border border-gray-200 rounded-lg p-4 shadow-sm">
            <div class="text-sm font-medium text-gray-600 mb-1">Rasa Server</div>
            <div class="text-xs text-gray-500 mb-2">Port 5005</div>
            <div id="serverStatus" class="flex items-center gap-2">
                <div class="w-3 h-3 rounded-full bg-gray-400 animate-pulse"></div>
                <span class="text-sm text-gray-600 font-medium">Checking...</span>
            </div>
        </div>

        <div class="bg-white border border-gray-200 rounded-lg p-4 shadow-sm">
            <div class="text-sm font-medium text-gray-600 mb-1">AI Logic Status</div>
            <div id="trainingStatusContent" class="flex items-center gap-2">
                <i id="status-icon-small" class="fas fa-circle-notch fa-spin text-gray-400 text-xs"></i>
                <span id="status-text-small" class="text-sm font-medium text-gray-900">Loading...</span>
            </div>
            <div id="status-subtext-small" class="text-xs text-gray-500 mt-1">Pending check</div>
        </div>

        <div class="bg-white border border-gray-200 rounded-lg p-4 shadow-sm">
            <div class="text-sm font-medium text-gray-600 mb-1">Active Model</div>
            <div id="currentModel" class="text-sm font-bold text-blue-700 mb-1 truncate">Loading...</div>
            <div class="text-xs text-gray-500" id="currentModelVersion">Version: -</div>
        </div>

        <div class="bg-white border border-gray-200 rounded-lg p-4 shadow-sm flex flex-col justify-center">
            <form action="{{ route('admin.rasa-server.train-rasa') }}" method="POST" id="sync-form">
                @csrf
                <button type="submit" id="sync-btn"
                    class="w-full inline-flex items-center justify-center gap-2 px-4 py-2 bg-blue-600 text-white text-sm font-semibold rounded-lg hover:bg-blue-700 transition-colors disabled:opacity-50">
                    <i class="fas fa-sync-alt" id="btn-icon"></i>
                    <span>Sync & Retrain</span>
                </button>
            </form>
        </div>
    </div>

    <div id="progress-container" class="hidden">
        <div class="bg-blue-50 border border-blue-100 rounded-lg p-4">
            <div class="flex justify-between items-center mb-2">
                <span class="text-sm font-medium text-blue-800 flex items-center gap-2">
                    <i class="fas fa-cog fa-spin"></i> Render.com is rebuilding Sangkay AI...
                </span>
                <span class="text-xs font-semibold text-blue-600">100% (Build Phase)</span>
            </div>
            <div class="w-full bg-blue-200 rounded-full h-2.5">
                <div class="bg-blue-600 h-2.5 rounded-full animate-pulse" style="width: 100%"></div>
            </div>
        </div>
    </div>
</div>