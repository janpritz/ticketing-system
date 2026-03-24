<script>
(function() {
    //console.log('Rasa Server Manager script loaded');
    // CSRF token
    const csrfToken = document.getElementById('statusData').getAttribute('data-csrf');

    // Status elements
    const endpointStatus = document.getElementById('endpointStatus');
    const serverStatus = document.getElementById('serverStatus');
    const actionServerStatus = document.getElementById('actionServerStatus');
    const lastTraining = document.getElementById('lastTraining');
    const currentModel = document.getElementById('currentModel');
    const currentModelVersion = document.getElementById('currentModelVersion');

    // Button elements
    const refreshStatusBtn = document.getElementById('refreshStatus');
    const startServerBtn = document.getElementById('startServerBtn');
    const startActionServerBtn = document.getElementById('startActionServerBtn');
    const trainModelBtn = document.getElementById('trainModelBtn');
    const cleanupModelsBtnTop = document.getElementById('cleanupModelsBtnTop');

    // Table elements
    const trainingHistoryTable = document.getElementById('trainingHistoryTable');
    const modelsListTable = document.getElementById('modelsListTable');
    const refreshTrainingHistoryBtn = document.getElementById('refreshTrainingHistory');
    const refreshModelsListBtn = document.getElementById('refreshModelsList');

    // Pagination elements
    const trainingHistoryPrev = document.getElementById('trainingHistoryPrev');
    const modelsListPrev = document.getElementById('modelsListPrev');
    const trainingHistoryNext = document.getElementById('trainingHistoryNext');
    const modelsListNext = document.getElementById('modelsListNext');
    const trainingHistoryPageInfo = document.getElementById('trainingHistoryPageInfo');
    const modelsListPageInfo = document.getElementById('modelsListPageInfo');

    // Pagination state
    let trainingHistoryData = [];
    let modelsListData = [];
    let trainingHistoryCurrentPage = 1;
    let modelsListCurrentPage = 1;
    let trainingHistoryTotalPages = 1;
    let modelsListTotalPages = 1;

    // Status update functions
    function updateEndpointStatus(isRunning) {
        const statusDiv = endpointStatus;
        const dot = statusDiv.querySelector('div');
        const text = statusDiv.querySelector('span');

        if (isRunning) {
            dot.className = 'w-4 h-4 rounded-full bg-green-600';
            text.textContent = 'Online';
            text.className = 'text-sm font-semibold text-green-800';
        } else {
            dot.className = 'w-4 h-4 rounded-full bg-red-600';
            text.textContent = 'Offline';
            text.className = 'text-sm font-semibold text-red-800';
        }
    }

    function updateServerStatus(isRunning) {
        const statusDiv = serverStatus;
        const dot = statusDiv.querySelector('div');
        const text = statusDiv.querySelector('span');

        if (isRunning) {
            dot.className = 'w-4 h-4 rounded-full bg-green-600';
            text.textContent = 'Online';
            text.className = 'text-sm font-semibold text-green-800';
        } else {
            dot.className = 'w-4 h-4 rounded-full bg-red-600';
            text.textContent = 'Offline';
            text.className = 'text-sm font-semibold text-red-800';
        }
    }

    function updateActionServerStatus(isRunning) {
        const statusDiv = actionServerStatus;
        const dot = statusDiv.querySelector('div');
        const text = statusDiv.querySelector('span');

        if (isRunning) {
            dot.className = 'w-4 h-4 rounded-full bg-green-600';
            text.textContent = 'Online';
            text.className = 'text-sm font-semibold text-green-800';
        } else {
            dot.className = 'w-4 h-4 rounded-full bg-red-600';
            text.textContent = 'Offline';
            text.className = 'text-sm font-semibold text-red-800';
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
        //console.log('Starting fetchStatus');
        try {
            const response = await fetch('{{ route("admin.rasa-server.status") }}', {
                headers: {
                    'X-Requested-With': 'XMLHttpRequest'
                }
            });

            if (response.ok) {
                const data = await response.json();
                console.log('fetchStatus received data:', data);
                updateEndpointStatus(data.endpoint_5001);
                updateServerStatus(data.server_5005);
                updateActionServerStatus(data.action_server_5055);
                updateLastTraining(data.last_training);
                updateCurrentModel(data.current_model);
                return { success: true };
            } else {
                console.error('fetchStatus response not ok:', response.status);
                return { success: false, error: `HTTP ${response.status}` };
            }
        } catch (error) {
            console.error('Failed to fetch status:', error);
            return { success: false, error: error.message };
        }
        //console.log('Completed fetchStatus');
    }

    // Fetch training history
    async function fetchTrainingHistory() {
        //console.log('Starting fetchTrainingHistory');
        try {
            const response = await fetch('{{ route("admin.rasa-server.training-history") }}', {
                headers: {
                    'X-Requested-With': 'XMLHttpRequest'
                }
            });

            if (response.ok) {
                const data = await response.json();
                //console.log('fetchTrainingHistory received data:', data);
                updateTrainingHistoryTable(data.trainings || []);
            } else {
                console.error('fetchTrainingHistory response not ok:', response.status);
            }
        } catch (error) {
            console.error('Failed to fetch training history:', error);
        }
        //console.log('Completed fetchTrainingHistory');
    }


    // Fetch models list
    async function fetchModelsList() {
        //console.log('Starting fetchModelsList');
        try {
            const response = await fetch('{{ route("admin.rasa-server.models-list") }}', {
                headers: {
                    'X-Requested-With': 'XMLHttpRequest'
                }
            });

            if (response.ok) {
                const data = await response.json();
                //console.log('fetchModelsList received data:', data);
                updateModelsListTable(data.models || []);
            } else {
                console.error('fetchModelsList response not ok:', response.status);
            }
        } catch (error) {
            console.error('Failed to fetch models list:', error);
        }
        //console.log('Completed fetchModelsList');
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
                    <td colspan="3" class="px-4 sm:px-6 py-4 text-center text-sm text-gray-500">No training history found.</td>
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
                        <td class="hidden md:table-cell px-4 sm:px-6 py-4 whitespace-nowrap text-sm text-gray-900">${training.file_name}</td>
                    </tr>
                `;
            }).join('');
            
            trainingHistoryTable.innerHTML = rows;
        }
        
        updatePaginationControls('trainingHistory');
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
            const response = await fetch('{{ route("admin.rasa-server.start-rasa-api") }}', {
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
            const response = await fetch('{{ route("admin.rasa-server.train-rasa") }}', {
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
                    title: 'Training Completed',
                    text: 'Rasa model training has been completed. Rasa Server will restart and may take several minutes to apply update.',
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


    async function deleteBackup(backupId, button) {
        const result = await Swal.fire({
            title: 'Delete Backup',
            text: 'Are you sure you want to delete this backup? This action cannot be undone.',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#ef4444',
            cancelButtonColor: '#6b7280',
            confirmButtonText: 'Yes, delete',
            cancelButtonText: 'Cancel'
        });

        if (!result.isConfirmed) return;

        // Disable button and show spinner
        const originalText = button.innerHTML;
        button.disabled = true;
        button.innerHTML = '<svg class="animate-spin h-4 w-4 mr-2" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg> Deleting...';

        try {
            const response = await fetch(`{{ route("admin.rasa-server.delete-backup", ":backupId") }}`.replace(':backupId', backupId), {
                method: 'DELETE',
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
                    title: 'Backup Deleted',
                    text: data.message,
                    timer: 3000,
                    showConfirmButton: false
                });
                fetchBackupHistory(); // Refresh backup history
            } else {
                throw new Error(data.message || 'Delete failed');
            }
        } catch (error) {
            Swal.fire({
                icon: 'error',
                title: 'Delete Failed',
                text: error.message
            });
        } finally {
            // Re-enable button and restore original text
            button.disabled = false;
            button.innerHTML = originalText;
        }
    }

    async function cleanupModels() {
        //console.log('cleanupModels called');
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

        cleanupModelsBtnTop.disabled = true;
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
            cleanupModelsBtnTop.disabled = false;
            cleanupModelsBtnTop.innerHTML = '<svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>Cleanup Models';
        }
    }

    // Event listeners
    refreshStatusBtn.addEventListener('click', fetchStatus);
    startServerBtn.addEventListener('click', startServer);
    startActionServerBtn.addEventListener('click', startActionServer);
    trainModelBtn.addEventListener('click', trainModel);
    cleanupModelsBtnTop.addEventListener('click', cleanupModels);
    cleanupModelsBtnTop.addEventListener('click', cleanupModels);

    refreshTrainingHistoryBtn.addEventListener('click', fetchTrainingHistory);
    refreshModelsListBtn.addEventListener('click', fetchModelsList);

    cleanupModelsBtnTop.addEventListener('click', cleanupModels);

    // Pagination event listeners

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


    modelsListNext.addEventListener('click', (e) => {
        if (modelsListCurrentPage < modelsListTotalPages) {
            modelsListCurrentPage++;
            renderModelsListPage();
        }
    });

    // Fetch backup files

    // Fetch backup file content

    // Backup modal event listeners


    // Close modal handlers
    document.addEventListener('click', (e) => {
        if (e.target && e.target.closest('[data-modal-backdrop]')) {
            backupFilesModal.classList.add('hidden');
        }
        if (e.target && e.target.getAttribute && e.target.getAttribute('data-modal-close') != null) {
            backupFilesModal.classList.add('hidden');
        }
        if (e.target && e.target.closest && e.target.closest('[data-modal-close]')) {
            backupFilesModal.classList.add('hidden');
        }
    });

    // Escape key to close modal
    document.addEventListener('keydown', (e) => {
        if (e.key === 'Escape' && backupFilesModal && !backupFilesModal.classList.contains('hidden')) {
            backupFilesModal.classList.add('hidden');
        }
    });

    // Initial load
    //console.log('Starting initial load fetches');
    fetchStatus();
    fetchTrainingHistory();
    fetchModelsList();
    //console.log('Initial load fetches initiated');

    // Auto-refresh status every 30 seconds
    setInterval(fetchStatus, 30000);
})();
</script>