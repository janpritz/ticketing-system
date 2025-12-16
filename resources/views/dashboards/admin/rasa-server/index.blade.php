@extends('layouts.admin')

@section('title', 'Rasa Server Manager')

@section('admin-content')
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
            <div>
                <h1 class="text-xl sm:text-2xl font-semibold text-slate-900">Rasa Server Manager</h1>
                <p class="text-sm text-gray-600 mt-1">Monitor and manage Rasa chatbot server system status, training, and backups.</p>
            </div>
        </div>

        <!-- System Status Card -->
        <div class="mt-6 bg-white rounded-xl border border-gray-200 p-4">
            <div class="flex items-center justify-between mb-4">
                <h3 class="text-lg font-semibold text-gray-900 flex items-center gap-2">
                    <svg class="w-5 h-5 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.75 17L9 20l-1 1h8l-1-1-.75-3M3 13h18M5 17h14a2 2 0 002-2V5a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/>
                    </svg>
                    System Status
                </h3>
                <div class="flex items-center gap-3">
                    <button id="startServerBtn" class="inline-flex items-center gap-2 px-3 py-2 text-sm font-medium text-green-600 bg-green-50 hover:bg-green-100 rounded-lg transition-colors disabled:opacity-50 disabled:cursor-not-allowed">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14.828 14.828a4 4 0 01-5.656 0M9 10h1.586a1 1 0 01.707.293l.707.707A1 1 0 0012.414 11H15m-3-3h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                        Start Server
                    </button>
                    <button id="startActionServerBtn" class="inline-flex items-center gap-2 px-3 py-2 text-sm font-medium text-blue-600 bg-blue-50 hover:bg-blue-100 rounded-lg transition-colors disabled:opacity-50 disabled:cursor-not-allowed">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/>
                        </svg>
                        Start Actions
                    </button>
                    <button id="refreshStatus" class="inline-flex items-center gap-2 px-3 py-2 text-sm font-medium text-gray-700 bg-gray-100 hover:bg-gray-200 rounded-lg transition-colors">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/>
                        </svg>
                        Refresh
                    </button>
                </div>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-5 gap-4 mb-4">
                <!-- Rasa Endpoint Card -->
                <div class="bg-white border border-gray-200 rounded-lg p-4">
                    <div class="text-sm font-medium text-gray-600 mb-1">Rasa Endpoint</div>
                    <div class="text-xs text-gray-500 mb-2">Port 5001</div>
                    <div id="endpointStatus" class="flex items-center gap-2">
                        <div class="w-3 h-3 rounded-full bg-gray-400 animate-pulse"></div>
                        <span class="text-sm text-gray-600">Checking...</span>
                    </div>
                </div>

                <!-- Rasa Server Card -->
                <div class="bg-white border border-gray-200 rounded-lg p-4">
                    <div class="text-sm font-medium text-gray-600 mb-1">Rasa Server</div>
                    <div class="text-xs text-gray-500 mb-2">Port 5005</div>
                    <div id="serverStatus" class="flex items-center gap-2">
                        <div class="w-3 h-3 rounded-full bg-gray-400 animate-pulse"></div>
                        <span class="text-sm text-gray-600">Checking...</span>
                    </div>
                </div>

                <!-- Action Server Card -->
                <div class="bg-white border border-gray-200 rounded-lg p-4">
                    <div class="text-sm font-medium text-gray-600 mb-1">Action Server</div>
                    <div class="text-xs text-gray-500 mb-2">Port 5055</div>
                    <div id="actionServerStatus" class="flex items-center gap-2">
                        <div class="w-3 h-3 rounded-full bg-gray-400 animate-pulse"></div>
                        <span class="text-sm text-gray-600">Checking...</span>
                    </div>
                </div>

                <!-- Last Training Card -->
                <div class="bg-white border border-gray-200 rounded-lg p-4">
                    <div class="text-sm font-medium text-gray-600 mb-1">Last Training</div>
                    <div id="lastTraining" class="text-sm text-gray-900 mb-1">Loading...</div>
                    <div class="text-xs text-gray-500">1 day ago</div>
                </div>

                <!-- Last Backup Card -->
                <div class="bg-white border border-gray-200 rounded-lg p-4">
                    <div class="text-sm font-medium text-gray-600 mb-1">Last Backup</div>
                    <div id="lastBackup" class="text-sm text-gray-900 mb-1">None</div>
                    <div class="text-xs text-gray-500">&nbsp;</div>
                </div>

                <!-- Current Model Card -->
                <div class="bg-white border border-gray-200 rounded-lg p-4">
                    <div class="text-sm font-medium text-gray-600 mb-1">Current Model</div>
                    <div id="currentModel" class="text-sm text-gray-900 mb-1">Loading...</div>
                    <div class="text-xs text-gray-500" id="currentModelVersion">-</div>
                </div>
            </div>
        </div>

        <!-- Training History -->
        <div class="mt-6 bg-white rounded-xl border border-gray-200 overflow-hidden">
            <div class="px-4 sm:px-6 py-4 border-b border-gray-200">
                <div class="flex items-center justify-between">
                    <h3 class="text-lg font-semibold text-gray-900">Training History</h3>
                    <div class="flex items-center gap-3">
                        <button id="trainModelBtn" class="inline-flex items-center gap-2 px-3 py-2 text-sm font-medium text-blue-600 bg-blue-50 hover:bg-blue-100 rounded-lg transition-colors disabled:opacity-50 disabled:cursor-not-allowed">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/>
                            </svg>
                            Train Model
                        </button>
                        <button id="refreshTrainingHistory" class="text-sm text-blue-600 hover:text-blue-800">Refresh</button>
                    </div>
                </div>
            </div>
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-4 sm:px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Date & Time</th>
                            <th class="px-4 sm:px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                            <th class="hidden sm:table-cell px-4 sm:px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">User</th>
                            <th class="hidden md:table-cell px-4 sm:px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">File Changed</th>
                        </tr>
                    </thead>
                    <tbody id="trainingHistoryTable" class="bg-white divide-y divide-gray-200">
                        <tr>
                            <td colspan="4" class="px-4 sm:px-6 py-4 text-center text-sm text-gray-500">Loading training history...</td>
                        </tr>
                    </tbody>
                </table>
            </div>
            <!-- Pagination Controls -->
            <div class="px-4 sm:px-6 py-3 border-t border-gray-200">
                <div class="flex items-center justify-between">
                    <div class="flex items-center gap-2">
                        <span class="text-sm text-gray-700">Show:</span>
                        <select id="trainingHistoryPerPage" class="text-sm border-gray-300 rounded-md focus:ring-blue-500 focus:border-blue-500">
                            <option value="5" selected>5</option>
                            <option value="10">10</option>
                            <option value="20">20</option>
                        </select>
                        <span class="text-sm text-gray-700">entries</span>
                    </div>
                    <div class="flex items-center gap-2">
                        <button id="trainingHistoryPrev" class="px-3 py-1 text-sm border border-gray-300 rounded-md hover:bg-gray-50 disabled:opacity-50 disabled:cursor-not-allowed">Previous</button>
                        <span id="trainingHistoryPageInfo" class="text-sm text-gray-700">Page 1 of 1</span>
                        <button id="trainingHistoryNext" class="px-3 py-1 text-sm border border-gray-300 rounded-md hover:bg-gray-50 disabled:opacity-50 disabled:cursor-not-allowed">Next</button>
                    </div>
                </div>
            </div>
        </div>

        <!-- Backup History -->
        <div class="mt-6 bg-white rounded-xl border border-gray-200 overflow-hidden">
            <div class="px-4 sm:px-6 py-4 border-b border-gray-200">
                <div class="flex items-center justify-between">
                    <h3 class="text-lg font-semibold text-gray-900">Backup History</h3>
                    <div class="flex items-center gap-3">
                        <button id="createBackupBtn" class="inline-flex items-center gap-2 px-3 py-2 text-sm font-medium text-purple-600 bg-purple-50 hover:bg-purple-100 rounded-lg transition-colors disabled:opacity-50 disabled:cursor-not-allowed">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 8h14M5 8a2 2 0 110-4h14a2 2 0 110 4M5 8v10a2 2 0 002 2h10a2 2 0 002-2V8m-9 4h4"/>
                            </svg>
                            Create Backup
                        </button>
                        <button id="refreshBackupHistory" class="text-sm text-blue-600 hover:text-blue-800">Refresh</button>
                    </div>
                </div>
            </div>
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-4 sm:px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Date & Time</th>
                            <th class="px-4 sm:px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Type</th>
                            <th class="hidden sm:table-cell px-4 sm:px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Filename</th>
                            <th class="hidden md:table-cell px-4 sm:px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Size</th>
                        </tr>
                    </thead>
                    <tbody id="backupHistoryTable" class="bg-white divide-y divide-gray-200">
                        <tr>
                            <td colspan="4" class="px-4 sm:px-6 py-4 text-center text-sm text-gray-500">Loading backup history...</td>
                        </tr>
                    </tbody>
                </table>
            </div>
            <!-- Pagination Controls -->
            <div class="px-4 sm:px-6 py-3 border-t border-gray-200">
                <div class="flex items-center justify-between">
                    <div class="flex items-center gap-2">
                        <span class="text-sm text-gray-700">Show:</span>
                        <select id="backupHistoryPerPage" class="text-sm border-gray-300 rounded-md focus:ring-blue-500 focus:border-blue-500">
                            <option value="5" selected>5</option>
                            <option value="10">10</option>
                            <option value="20">20</option>
                        </select>
                        <span class="text-sm text-gray-700">entries</span>
                    </div>
                    <div class="flex items-center gap-2">
                        <button id="backupHistoryPrev" class="px-3 py-1 text-sm border border-gray-300 rounded-md hover:bg-gray-50 disabled:opacity-50 disabled:cursor-not-allowed">Previous</button>
                        <span id="backupHistoryPageInfo" class="text-sm text-gray-700">Page 1 of 1</span>
                        <button id="backupHistoryNext" class="px-3 py-1 text-sm border border-gray-300 rounded-md hover:bg-gray-50 disabled:opacity-50 disabled:cursor-not-allowed">Next</button>
                    </div>
                </div>
            </div>
        </div>

        <!-- Models List -->
        <div class="mt-6 bg-white rounded-xl border border-gray-200 overflow-hidden">
            <div class="px-4 sm:px-6 py-4 border-b border-gray-200">
                <div class="flex items-center justify-between">
                    <h3 class="text-lg font-semibold text-gray-900">Available Models</h3>
                    <div class="flex items-center gap-3">
                        <button id="cleanupModelsBtnTop" class="inline-flex items-center gap-2 px-3 py-2 text-sm font-medium text-red-600 bg-red-50 hover:bg-red-100 rounded-lg transition-colors disabled:opacity-50 disabled:cursor-not-allowed">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                            </svg>
                            Cleanup Models
                        </button>
                        <button id="refreshModelsList" class="text-sm text-blue-600 hover:text-blue-800">Refresh</button>
                    </div>
                </div>
            </div>
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-4 sm:px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Model Name</th>
                            <th class="hidden sm:table-cell px-4 sm:px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Version</th>
                            <th class="px-4 sm:px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                            <th class="hidden md:table-cell px-4 sm:px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Size</th>
                        </tr>
                    </thead>
                    <tbody id="modelsListTable" class="bg-white divide-y divide-gray-200">
                        <tr>
                            <td colspan="4" class="px-4 sm:px-6 py-4 text-center text-sm text-gray-500">Loading models list...</td>
                        </tr>
                    </tbody>
                </table>
            </div>
            <!-- Pagination Controls -->
            <div class="px-4 sm:px-6 py-3 border-t border-gray-200">
                <div class="flex items-center justify-between">
                    <div class="flex items-center gap-2">
                        <span class="text-sm text-gray-700">Show:</span>
                        <select id="modelsListPerPage" class="text-sm border-gray-300 rounded-md focus:ring-blue-500 focus:border-blue-500">
                            <option value="5" selected>5</option>
                            <option value="10">10</option>
                            <option value="20">20</option>
                        </select>
                        <span class="text-sm text-gray-700">entries</span>
                    </div>
                    <div class="flex items-center gap-2">
                        <button id="modelsListPrev" class="px-3 py-1 text-sm border border-gray-300 rounded-md hover:bg-gray-50 disabled:opacity-50 disabled:cursor-not-allowed">Previous</button>
                        <span id="modelsListPageInfo" class="text-sm text-gray-700">Page 1 of 1</span>
                        <button id="modelsListNext" class="px-3 py-1 text-sm border border-gray-300 rounded-md hover:bg-gray-50 disabled:opacity-50 disabled:cursor-not-allowed">Next</button>
                    </div>
                </div>
            </div>
        </div>

    <!-- Status Data (hidden) -->
    <div id="statusData" class="hidden" data-csrf="{{ csrf_token() }}"></div>
@endsection

@section('admin-scripts')
<script>
(function() {
    // CSRF token
    const csrfToken = document.getElementById('statusData').getAttribute('data-csrf');

    // Status elements
    const endpointStatus = document.getElementById('endpointStatus');
    const serverStatus = document.getElementById('serverStatus');
    const actionServerStatus = document.getElementById('actionServerStatus');
    const lastTraining = document.getElementById('lastTraining');
    const lastBackup = document.getElementById('lastBackup');
    const currentModel = document.getElementById('currentModel');
    const currentModelVersion = document.getElementById('currentModelVersion');

    // Button elements
    const refreshStatusBtn = document.getElementById('refreshStatus');
    const startServerBtn = document.getElementById('startServerBtn');
    const startActionServerBtn = document.getElementById('startActionServerBtn');
    const trainModelBtn = document.getElementById('trainModelBtn');
    const createBackupBtn = document.getElementById('createBackupBtn');
    const cleanupModelsBtn = document.getElementById('cleanupModelsBtn');
    const cleanupModelsBtnTop = document.getElementById('cleanupModelsBtnTop');

    console.log('cleanupModelsBtnTop:', cleanupModelsBtnTop);
    console.log('cleanupModelsBtn:', cleanupModelsBtn);

    // Table elements
    const trainingHistoryTable = document.getElementById('trainingHistoryTable');
    const backupHistoryTable = document.getElementById('backupHistoryTable');
    const modelsListTable = document.getElementById('modelsListTable');
    const refreshTrainingHistoryBtn = document.getElementById('refreshTrainingHistory');
    const refreshBackupHistoryBtn = document.getElementById('refreshBackupHistory');
    const refreshModelsListBtn = document.getElementById('refreshModelsList');

    // Pagination elements
    const trainingHistoryPerPage = document.getElementById('trainingHistoryPerPage');
    const backupHistoryPerPage = document.getElementById('backupHistoryPerPage');
    const modelsListPerPage = document.getElementById('modelsListPerPage');
    const trainingHistoryPrev = document.getElementById('trainingHistoryPrev');
    const backupHistoryPrev = document.getElementById('backupHistoryPrev');
    const modelsListPrev = document.getElementById('modelsListPrev');
    const trainingHistoryNext = document.getElementById('trainingHistoryNext');
    const backupHistoryNext = document.getElementById('backupHistoryNext');
    const modelsListNext = document.getElementById('modelsListNext');
    const trainingHistoryPageInfo = document.getElementById('trainingHistoryPageInfo');
    const backupHistoryPageInfo = document.getElementById('backupHistoryPageInfo');
    const modelsListPageInfo = document.getElementById('modelsListPageInfo');

    // Pagination state
    let trainingHistoryData = [];
    let backupHistoryData = [];
    let modelsListData = [];
    let trainingHistoryCurrentPage = 1;
    let backupHistoryCurrentPage = 1;
    let modelsListCurrentPage = 1;
    let trainingHistoryTotalPages = 1;
    let backupHistoryTotalPages = 1;
    let modelsListTotalPages = 1;

    // Status update functions
    function updateEndpointStatus(isRunning) {
        const statusDiv = endpointStatus;
        const dot = statusDiv.querySelector('div');
        const text = statusDiv.querySelector('span');

        if (isRunning) {
            dot.className = 'w-3 h-3 rounded-full bg-green-500';
            text.textContent = 'Online';
            text.className = 'text-sm text-green-700';
        } else {
            dot.className = 'w-3 h-3 rounded-full bg-red-500';
            text.textContent = 'Offline';
            text.className = 'text-sm text-red-700';
        }
    }

    function updateServerStatus(isRunning) {
        const statusDiv = serverStatus;
        const dot = statusDiv.querySelector('div');
        const text = statusDiv.querySelector('span');

        if (isRunning) {
            dot.className = 'w-3 h-3 rounded-full bg-green-500';
            text.textContent = 'Online';
            text.className = 'text-sm text-green-700';
        } else {
            dot.className = 'w-3 h-3 rounded-full bg-red-500';
            text.textContent = 'Offline';
            text.className = 'text-sm text-red-700';
        }
    }

    function updateActionServerStatus(isRunning) {
        const statusDiv = actionServerStatus;
        const dot = statusDiv.querySelector('div');
        const text = statusDiv.querySelector('span');

        if (isRunning) {
            dot.className = 'w-3 h-3 rounded-full bg-green-500';
            text.textContent = 'Online';
            text.className = 'text-sm text-green-700';
        } else {
            dot.className = 'w-3 h-3 rounded-full bg-red-500';
            text.textContent = 'Offline';
            text.className = 'text-sm text-red-700';
        }
    }

    function updateLastTraining(data) {
        const card = lastTraining.closest('.bg-white');
        const relativeDiv = card.querySelector('.text-xs.text-gray-500');

        if (data) {
            lastTraining.textContent = data.formatted;
            relativeDiv.textContent = data.relative;
        } else {
            lastTraining.textContent = 'Never';
            relativeDiv.textContent = '';
        }
    }

    function updateLastBackup(data) {
        const card = lastBackup.closest('.bg-white');
        const relativeDiv = card.querySelector('.text-xs.text-gray-500');

        if (data) {
            // Convert timestamp to human-readable format
            const date = new Date(data.timestamp);
            const formattedDate = date.toLocaleDateString('en-US', {
                year: 'numeric',
                month: 'long',
                day: 'numeric'
            });
            const formattedTime = date.toLocaleTimeString('en-US', {
                hour: 'numeric',
                minute: '2-digit',
                hour12: true
            });
            
            lastBackup.textContent = `${formattedDate} at ${formattedTime}`;
            const size = (data.size / 1024).toFixed(1) + ' KB, ' + data.file_count + ' files';
            relativeDiv.textContent = size;
        } else {
            lastBackup.textContent = 'None';
            relativeDiv.textContent = '';
        }
    }

    function updateCurrentModel(data) {
        if (data) {
            currentModel.textContent = data.name || 'Unknown';
            currentModelVersion.textContent = data.version ? `v${data.version}` : '-';
        } else {
            currentModel.textContent = 'None';
            currentModelVersion.textContent = '-';
        }
    }

    // Fetch status
    async function fetchStatus() {
        try {
            const response = await fetch('{{ route("admin.rasa-server.status") }}', {
                headers: {
                    'X-Requested-With': 'XMLHttpRequest'
                }
            });

            if (response.ok) {
                const data = await response.json();
                updateEndpointStatus(data.endpoint_5001);
                updateServerStatus(data.server_5005);
                updateActionServerStatus(data.action_server_5055);
                updateLastTraining(data.last_training);
                updateLastBackup(data.last_backup);
                updateCurrentModel(data.current_model);
            }
        } catch (error) {
            console.error('Failed to fetch status:', error);
        }
    }

    // Fetch training history
    async function fetchTrainingHistory() {
        try {
            const response = await fetch('{{ route("admin.rasa-server.training-history") }}', {
                headers: {
                    'X-Requested-With': 'XMLHttpRequest'
                }
            });

            if (response.ok) {
                const data = await response.json();
                updateTrainingHistoryTable(data.trainings || []);
            }
        } catch (error) {
            console.error('Failed to fetch training history:', error);
        }
    }

    // Fetch backup history
    async function fetchBackupHistory() {
        try {
            const response = await fetch('{{ route("admin.rasa-server.backup-history") }}', {
                headers: {
                    'X-Requested-With': 'XMLHttpRequest'
                }
            });

            if (response.ok) {
                const data = await response.json();
                updateBackupHistoryTable(data.backups || []);
            }
        } catch (error) {
            console.error('Failed to fetch backup history:', error);
        }
    }

    // Fetch models list
    async function fetchModelsList() {
        try {
            const response = await fetch('{{ route("admin.rasa-server.models-list") }}', {
                headers: {
                    'X-Requested-With': 'XMLHttpRequest'
                }
            });

            if (response.ok) {
                const data = await response.json();
                updateModelsListTable(data.models || []);
            }
        } catch (error) {
            console.error('Failed to fetch models list:', error);
        }
    }

    // Pagination functions
    function updateTrainingHistoryTable(data) {
        trainingHistoryData = data || [];
        trainingHistoryCurrentPage = 1;
        renderTrainingHistoryPage();
    }

    function renderTrainingHistoryPage() {
        const perPage = parseInt(trainingHistoryPerPage.value);
        const start = (trainingHistoryCurrentPage - 1) * perPage;
        const end = start + perPage;
        const pageData = trainingHistoryData.slice(start, end);
        
        trainingHistoryTotalPages = Math.ceil(trainingHistoryData.length / perPage);

        if (pageData.length === 0) {
            trainingHistoryTable.innerHTML = `
                <tr>
                    <td colspan="4" class="px-4 sm:px-6 py-4 text-center text-sm text-gray-500">No training history found.</td>
                </tr>
            `;
        } else {
            const rows = pageData.map(training => {
                const statusClass = {
                    'success': 'text-green-700 bg-green-50',
                    'failed': 'text-red-700 bg-red-50',
                    'pending': 'text-yellow-700 bg-yellow-50',
                    'superseded': 'text-gray-700 bg-gray-50'
                }[training.status] || 'text-gray-700 bg-gray-50';

                return `
                    <tr>
                        <td class="px-4 sm:px-6 py-4 whitespace-nowrap text-sm text-gray-900">${training.date}</td>
                        <td class="px-4 sm:px-6 py-4 whitespace-nowrap">
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium ${statusClass}">
                                ${training.status.charAt(0).toUpperCase() + training.status.slice(1)}
                            </span>
                        </td>
                        <td class="hidden sm:table-cell px-4 sm:px-6 py-4 whitespace-nowrap text-sm text-gray-900">${training.user}</td>
                        <td class="hidden md:table-cell px-4 sm:px-6 py-4 whitespace-nowrap text-sm text-gray-900">${training.file_name}</td>
                    </tr>
                `;
            }).join('');
            
            trainingHistoryTable.innerHTML = rows;
        }
        
        updatePaginationControls('trainingHistory');
    }

    function updateBackupHistoryTable(data) {
        backupHistoryData = data || [];
        backupHistoryCurrentPage = 1;
        renderBackupHistoryPage();
    }

    function renderBackupHistoryPage() {
        const perPage = parseInt(backupHistoryPerPage.value);
        const start = (backupHistoryCurrentPage - 1) * perPage;
        const end = start + perPage;
        const pageData = backupHistoryData.slice(start, end);
        
        backupHistoryTotalPages = Math.ceil(backupHistoryData.length / perPage);

        if (pageData.length === 0) {
            backupHistoryTable.innerHTML = `
                <tr>
                    <td colspan="4" class="px-4 sm:px-6 py-4 text-center text-sm text-gray-500">No backup history found.</td>
                </tr>
            `;
        } else {
            const rows = pageData.map(backup => {
                const size = (backup.size / 1024).toFixed(1) + ' KB';
                return `
                    <tr>
                        <td class="px-4 sm:px-6 py-4 whitespace-nowrap text-sm text-gray-900">${backup.date}</td>
                        <td class="px-4 sm:px-6 py-4 whitespace-nowrap text-sm text-gray-900">${backup.type}</td>
                        <td class="hidden sm:table-cell px-4 sm:px-6 py-4 whitespace-nowrap text-sm text-gray-900">${backup.folder} (${backup.file_count} files)</td>
                        <td class="hidden md:table-cell px-4 sm:px-6 py-4 whitespace-nowrap text-sm text-gray-900">${size}</td>
                    </tr>
                `;
            }).join('');

            backupHistoryTable.innerHTML = rows;
        }
        
        updatePaginationControls('backupHistory');
    }

    function updateModelsListTable(data) {
        modelsListData = data || [];
        modelsListCurrentPage = 1;
        renderModelsListPage();
    }

    function renderModelsListPage() {
        const perPage = parseInt(modelsListPerPage.value);
        const start = (modelsListCurrentPage - 1) * perPage;
        const end = start + perPage;
        const pageData = modelsListData.slice(start, end);
        
        modelsListTotalPages = Math.ceil(modelsListData.length / perPage);

        if (pageData.length === 0) {
            modelsListTable.innerHTML = `
                <tr>
                    <td colspan="4" class="px-4 sm:px-6 py-4 text-center text-sm text-gray-500">No models found.</td>
                </tr>
            `;
        } else {
            const rows = pageData.map(model => {
                const statusClass = {
                    'loaded': 'text-green-700 bg-green-50',
                    'available': 'text-blue-700 bg-blue-50',
                    'training': 'text-yellow-700 bg-yellow-50',
                    'failed': 'text-red-700 bg-red-50'
                }[model.status] || 'text-gray-700 bg-gray-50';

                // Special styling for current model
                const rowClass = model.is_current ? 'bg-blue-50 border-l-4 border-blue-500' : '';
                const nameClass = model.is_current ? 'text-blue-900 font-bold' : 'text-gray-900 font-medium';
                const currentBadge = model.is_current ? '<span class="ml-2 inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-blue-100 text-blue-800">Current</span>' : '';

                return `
                    <tr class="${rowClass}">
                        <td class="px-4 sm:px-6 py-4 whitespace-nowrap text-sm ${nameClass}">${model.name}${currentBadge}</td>
                        <td class="hidden sm:table-cell px-4 sm:px-6 py-4 whitespace-nowrap text-sm text-gray-900">${model.version}</td>
                        <td class="px-4 sm:px-6 py-4 whitespace-nowrap">
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium ${statusClass}">
                                ${model.status.charAt(0).toUpperCase() + model.status.slice(1)}
                            </span>
                        </td>
                        <td class="hidden md:table-cell px-4 sm:px-6 py-4 whitespace-nowrap text-sm text-gray-900">${model.size_formatted}</td>
                    </tr>
                `;
            }).join('');
            
            modelsListTable.innerHTML = rows;
        }
        
        updatePaginationControls('modelsList');
    }

    function updatePaginationControls(tableType) {
        switch(tableType) {
            case 'trainingHistory':
                trainingHistoryPageInfo.textContent = `Page ${trainingHistoryCurrentPage} of ${trainingHistoryTotalPages}`;
                trainingHistoryPrev.disabled = trainingHistoryCurrentPage <= 1;
                trainingHistoryNext.disabled = trainingHistoryCurrentPage >= trainingHistoryTotalPages;
                break;
            case 'backupHistory':
                backupHistoryPageInfo.textContent = `Page ${backupHistoryCurrentPage} of ${backupHistoryTotalPages}`;
                backupHistoryPrev.disabled = backupHistoryCurrentPage <= 1;
                backupHistoryNext.disabled = backupHistoryCurrentPage >= backupHistoryTotalPages;
                break;
            case 'modelsList':
                modelsListPageInfo.textContent = `Page ${modelsListCurrentPage} of ${modelsListTotalPages}`;
                modelsListPrev.disabled = modelsListCurrentPage <= 1;
                modelsListNext.disabled = modelsListCurrentPage >= modelsListTotalPages;
                break;
        }
    }

    // Action handlers
    async function startServer() {
        startServerBtn.disabled = true;
        startServerBtn.innerHTML = '<svg class="animate-spin h-4 w-4 mr-2" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg> Starting...';

        try {
            const response = await fetch('{{ route("admin.document-changes.start-rasa-api") }}', {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': csrfToken,
                    'X-Requested-With': 'XMLHttpRequest',
                    'Accept': 'application/json'
                }
            });

            const data = await response.json();

            if (data.success) {
                Swal.fire({
                    icon: 'success',
                    title: 'Server Started',
                    text: data.message,
                    timer: 3000,
                    showConfirmButton: false
                });
                fetchStatus(); // Refresh status
            } else {
                throw new Error(data.message || 'Failed to start server');
            }
        } catch (error) {
            Swal.fire({
                icon: 'error',
                title: 'Start Failed',
                text: error.message
            });
        } finally {
            startServerBtn.disabled = false;
            startServerBtn.innerHTML = '<svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14.828 14.828a4 4 0 01-5.656 0M9 10h1.586a1 1 0 01.707.293l.707.707A1 1 0 0012.414 11H15m-3-3h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg><span class="text-center text-xs">Start Server</span>';
        }
    }

    async function startActionServer() {
        startActionServerBtn.disabled = true;
        startActionServerBtn.innerHTML = '<svg class="animate-spin h-4 w-4 mr-2" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg> Starting...';

        try {
            const response = await fetch('{{ route("admin.rasa-server.start-action-server") }}', {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': csrfToken,
                    'X-Requested-With': 'XMLHttpRequest',
                    'Accept': 'application/json'
                }
            });

            const data = await response.json();

            if (data.success) {
                Swal.fire({
                    icon: 'success',
                    title: 'Action Server Started',
                    text: data.message,
                    timer: 3000,
                    showConfirmButton: false
                });
                fetchStatus(); // Refresh status
            } else {
                throw new Error(data.message || 'Failed to start action server');
            }
        } catch (error) {
            Swal.fire({
                icon: 'error',
                title: 'Start Failed',
                text: error.message
            });
        } finally {
            startActionServerBtn.disabled = false;
            startActionServerBtn.innerHTML = '<svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/></svg><span class="text-center text-xs">Start Actions</span>';
        }
    }

    async function trainModel() {
        trainModelBtn.disabled = true;
        trainModelBtn.innerHTML = '<svg class="animate-spin h-4 w-4 mr-2" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg> Training...';

        try {
            const response = await fetch('{{ route("admin.document-changes.train-rasa") }}', {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': csrfToken,
                    'X-Requested-With': 'XMLHttpRequest',
                    'Accept': 'application/json'
                }
            });

            const data = await response.json();

            if (data.success) {
                Swal.fire({
                    icon: 'success',
                    title: 'Training Started',
                    text: 'Rasa model training has been initiated. This may take several minutes.',
                    timer: 5000,
                    showConfirmButton: false
                });
                // Refresh status and history after a delay
                setTimeout(() => {
                    fetchStatus();
                    fetchTrainingHistory();
                }, 2000);
            } else {
                throw new Error(data.message || 'Training failed');
            }
        } catch (error) {
            Swal.fire({
                icon: 'error',
                title: 'Training Failed',
                text: error.message
            });
        } finally {
            trainModelBtn.disabled = false;
            trainModelBtn.innerHTML = '<svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/></svg><span class="text-center text-xs">Train Model</span>';
        }
    }

    async function createBackup() {
        createBackupBtn.disabled = true;
        createBackupBtn.innerHTML = '<svg class="animate-spin h-4 w-4 mr-2" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg> Creating...';

        try {
            const response = await fetch('{{ route("admin.rasa-server.create-backup") }}', {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': csrfToken,
                    'X-Requested-With': 'XMLHttpRequest',
                    'Accept': 'application/json'
                }
            });

            const data = await response.json();

            if (data.success) {
                Swal.fire({
                    icon: 'success',
                    title: 'Backup Created',
                    text: data.message,
                    timer: 3000,
                    showConfirmButton: false
                });
                fetchStatus(); // Refresh status
                fetchBackupHistory(); // Refresh backup history
            } else {
                throw new Error(data.message || 'Backup failed');
            }
        } catch (error) {
            Swal.fire({
                icon: 'error',
                title: 'Backup Failed',
                text: error.message
            });
        } finally {
            createBackupBtn.disabled = false;
            createBackupBtn.innerHTML = '<svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 8h14M5 8a2 2 0 110-4h14a2 2 0 110 4M5 8v10a2 2 0 002 2h10a2 2 0 002-2V8m-9 4h4"/></svg><span class="text-center text-xs">Create Backup</span>';
        }
    }

    async function cleanupModels() {
        console.log('cleanupModels called');
        const result = await Swal.fire({
            title: 'Cleanup Old Models',
            text: 'This will delete old Rasa models, keeping only the 5 most recent ones. Continue?',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#f97316',
            cancelButtonColor: '#6b7280',
            confirmButtonText: 'Yes, cleanup'
        });

        if (!result.isConfirmed) return;

        cleanupModelsBtn.disabled = true;
        cleanupModelsBtnTop.disabled = true;
        cleanupModelsBtn.innerHTML = '<svg class="animate-spin h-4 w-4 mr-2" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg> Cleaning...';
        cleanupModelsBtnTop.innerHTML = '<svg class="animate-spin h-4 w-4 mr-2" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg> Cleaning...';

        try {
            const response = await fetch('{{ route("admin.rasa-server.cleanup-models") }}', {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': csrfToken,
                    'X-Requested-With': 'XMLHttpRequest',
                    'Accept': 'application/json'
                }
            });

            const data = await response.json();

            if (data.success) {
                Swal.fire({
                    icon: 'success',
                    title: 'Cleanup Complete',
                    text: data.message,
                    timer: 3000,
                    showConfirmButton: false
                });
                fetchStatus(); // Refresh status
            } else {
                throw new Error(data.message || 'Cleanup failed');
            }
        } catch (error) {
            Swal.fire({
                icon: 'error',
                title: 'Cleanup Failed',
                text: error.message
            });
        } finally {
            cleanupModelsBtn.disabled = false;
            cleanupModelsBtnTop.disabled = false;
            cleanupModelsBtn.innerHTML = '<svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg><span class="text-center text-xs">Cleanup Models</span>';
            cleanupModelsBtnTop.innerHTML = '<svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>Cleanup Models';
        }
    }

    // Event listeners
    refreshStatusBtn.addEventListener('click', fetchStatus);
    startServerBtn.addEventListener('click', startServer);
    startActionServerBtn.addEventListener('click', startActionServer);
    trainModelBtn.addEventListener('click', trainModel);
    createBackupBtn.addEventListener('click', createBackup);
    cleanupModelsBtn.addEventListener('click', cleanupModels);
    cleanupModelsBtnTop.addEventListener('click', cleanupModels);
    cleanupModelsBtnTop.addEventListener('click', cleanupModels);

    refreshTrainingHistoryBtn.addEventListener('click', fetchTrainingHistory);
    refreshBackupHistoryBtn.addEventListener('click', fetchBackupHistory);
    refreshModelsListBtn.addEventListener('click', fetchModelsList);

    // Pagination event listeners
    trainingHistoryPerPage.addEventListener('change', (e) => {
        trainingHistoryCurrentPage = 1;
        renderTrainingHistoryPage();
    });

    backupHistoryPerPage.addEventListener('change', (e) => {
        backupHistoryCurrentPage = 1;
        renderBackupHistoryPage();
    });

    modelsListPerPage.addEventListener('change', (e) => {
        modelsListCurrentPage = 1;
        renderModelsListPage();
    });

    trainingHistoryPrev.addEventListener('click', (e) => {
        if (trainingHistoryCurrentPage > 1) {
            trainingHistoryCurrentPage--;
            renderTrainingHistoryPage();
        }
    });

    backupHistoryPrev.addEventListener('click', (e) => {
        if (backupHistoryCurrentPage > 1) {
            backupHistoryCurrentPage--;
            renderBackupHistoryPage();
        }
    });

    modelsListPrev.addEventListener('click', (e) => {
        if (modelsListCurrentPage > 1) {
            modelsListCurrentPage--;
            renderModelsListPage();
        }
    });

    trainingHistoryNext.addEventListener('click', (e) => {
        if (trainingHistoryCurrentPage < trainingHistoryTotalPages) {
            trainingHistoryCurrentPage++;
            renderTrainingHistoryPage();
        }
    });

    backupHistoryNext.addEventListener('click', (e) => {
        if (backupHistoryCurrentPage < backupHistoryTotalPages) {
            backupHistoryCurrentPage++;
            renderBackupHistoryPage();
        }
    });

    modelsListNext.addEventListener('click', (e) => {
        if (modelsListCurrentPage < modelsListTotalPages) {
            modelsListCurrentPage++;
            renderModelsListPage();
        }
    });

    // Initial load
    fetchStatus();
    fetchTrainingHistory();
    fetchBackupHistory();
    fetchModelsList();

    // Auto-refresh status every 30 seconds
    setInterval(fetchStatus, 30000);
})();
</script>
@endsection