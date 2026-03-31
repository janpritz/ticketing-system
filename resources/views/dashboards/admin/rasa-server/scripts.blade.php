<script>
(function() {
    // Wait for DOM to be fully loaded
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', initScript);
    } else {
        initScript();
    }
    
    function initScript() {
        // CSRF token
        const statusDataEl = document.getElementById('statusData');
        if (!statusDataEl) {
            console.error('statusData element not found');
            return;
        }
        const csrfToken = statusDataEl.getAttribute('data-csrf');

        // Status elements
        const serverStatus = document.getElementById('serverStatus');
        const lastTraining = document.getElementById('lastTraining');
        const currentModel = document.getElementById('currentModel');

        // Button elements - check for existence before using
        const refreshStatusBtn = document.getElementById('refreshStatus');
        const startServerBtn = document.getElementById('startServerBtn');
        const trainModelBtn = document.getElementById('trainModelBtn');

        // Table elements - check for existence
        const trainingHistoryTable = document.getElementById('trainingHistoryTable');
        const refreshTrainingHistoryBtn = document.getElementById('refreshTrainingHistory');

        // Pagination elements - check for existence
        const trainingHistoryPrev = document.getElementById('trainingHistoryPrev');
        const trainingHistoryNext = document.getElementById('trainingHistoryNext');
        const trainingHistoryPageInfo = document.getElementById('trainingHistoryPageInfo');
        const trainingHistoryPerPage = document.getElementById('trainingHistoryPerPage');

        // Pagination state
        let trainingHistoryData = [];
        let trainingHistoryCurrentPage = 1;
        let trainingHistoryTotalPages = 1;

        // Status polling state
        let statusInterval = null;
        let lastStatusFetchTime = 0;
        let isFetchingStatus = false;
        const STATUS_CACHE_DURATION = 60000; // 1 minute minimum between fetches for 5-min interval

        // Status update functions
        function updateServerStatus(isRunning) {
            if (!serverStatus) return;
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

        function updateLastTraining(data) {
            if (!lastTraining) return;
            const card = lastTraining.closest('.bg-white');
            if (!card) return;
            const relativeDiv = card.querySelector('.text-xs.text-gray-500');

            if (data) {
                lastTraining.textContent = data.formatted;
                relativeDiv.textContent = data.relative;
            } else {
                lastTraining.textContent = 'Initial Training';
                relativeDiv.textContent = '';
            }
        }

        function updateCurrentModel(data) {
            if (!currentModel) return;
            if (data) {
                currentModel.textContent = data || 'Unknown';
            } else {
                currentModel.textContent = 'None';
            }
        }

        // Status polling control
        function startStatusPolling() {
            stopStatusPolling(); // Clear any existing
            statusInterval = setInterval(fetchStatus, 300000); // 5 minutes
        }

        function stopStatusPolling() {
            if (statusInterval) {
                clearInterval(statusInterval);
                statusInterval = null;
            }
        }

        // Fetch status
        async function fetchStatus() {
            const now = Date.now();
            if (isFetchingStatus || now - lastStatusFetchTime < STATUS_CACHE_DURATION) return;
            isFetchingStatus = true;
            lastStatusFetchTime = now;
            try {
                const response = await fetch('{{ route("admin.rasa-server.status") }}', {
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest'
                    }
                });

                if (response.ok) {
                    const data = await response.json();
                    console.log('fetchStatus received data:', data);
                    updateServerStatus(data.server_status);
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
            } finally {
                isFetchingStatus = false;
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
                } else {
                    console.error('fetchTrainingHistory response not ok:', response.status);
                }
            } catch (error) {
                console.error('Failed to fetch training history:', error);
            }
        }

        // Pagination functions
        function updateTrainingHistoryTable(data) {
            trainingHistoryData = data || [];
            trainingHistoryCurrentPage = 1;
            renderTrainingHistoryPage();
        }

        function renderTrainingHistoryPage() {
            if (!trainingHistoryPerPage || !trainingHistoryTable) return;
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
                        'training': 'text-blue-700 bg-blue-50'
                    }[training.status] || 'text-gray-700 bg-gray-50';

                    // Format the status for display
                    const statusDisplay = {
                        'success': 'Success',
                        'failed': 'Failed',
                        'pending': 'Pending',
                        'training': 'Training...'
                    }[training.status] || training.status;

                    // Format the date/time
                    const startedAt = training.started_at || '-';
                    const completedAt = training.completed_at || '-';

                    return `
                        <tr>
                            <td class="px-4 sm:px-6 py-4 whitespace-nowrap text-sm text-gray-900">${startedAt}</td>
                            <td class="px-4 sm:px-6 py-4 whitespace-nowrap text-sm text-gray-900">${completedAt}</td>
                            <td class="px-4 sm:px-6 py-4 whitespace-nowrap">
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium ${statusClass}">
                                    ${statusDisplay}
                                </span>
                            </td>
                            <td class="hidden md:table-cell px-4 sm:px-6 py-4 whitespace-nowrap text-sm text-gray-900">${training.trigger}</td>
                        </tr>
                    `;
                }).join('');
                
                trainingHistoryTable.innerHTML = rows;
            }
            
            updatePaginationControls('trainingHistory');
        }

        function updatePaginationControls(tableType) {
            switch(tableType) {
                case 'trainingHistory':
                    if (trainingHistoryPageInfo) trainingHistoryPageInfo.textContent = `Page ${trainingHistoryCurrentPage} of ${trainingHistoryTotalPages}`;
                    if (trainingHistoryPrev) trainingHistoryPrev.disabled = trainingHistoryCurrentPage <= 1;
                    if (trainingHistoryNext) trainingHistoryNext.disabled = trainingHistoryCurrentPage >= trainingHistoryTotalPages;
                    break;
            }
        }

        // Action handlers
        async function startServer() {
            if (!startServerBtn) return;
            console.log('[startServer] Starting server...');
            startServerBtn.disabled = true;
            startServerBtn.innerHTML = '<svg class="animate-spin h-4 w-4 mr-2" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg> Starting...';

            try {
                console.log('[startServer] Sending POST request to start-rasa-api endpoint');
                const response = await fetch('{{ route("admin.rasa-server.start-rasa-api") }}', {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': csrfToken,
                        'X-Requested-With': 'XMLHttpRequest',
                        'Accept': 'application/json'
                    }
                });

                console.log('[startServer] Response received - status:', response.status, 'ok:', response.ok);

                if (!response.ok) {
                    const text = await response.text();
                    console.error('[startServer] Request failed with status:', response.status, 'body:', text);
                    throw new Error('Server error: HTTP ' + response.status);
                }

                const data = await response.json();
                console.log('[startServer] JSON parsed successfully:', data);

                if (data.success) {
                    console.log('[startServer] Server started successfully');
                    Swal.fire({
                        icon: 'success',
                        title: 'Server Started',
                        text: data.message,
                        timer: 3000,
                        showConfirmButton: false
                    });
                    fetchStatus(); // Refresh status
                } else {
                    console.error('[startServer] Server start returned success=false:', data);
                    throw new Error(data.message || 'Failed to start server');
                }
            } catch (error) {
                console.error('[startServer] Caught error:', error.message);
                Swal.fire({
                    icon: 'error',
                    title: 'Start Failed',
                    text: error.message
                });
            } finally {
                if (startServerBtn) {
                    startServerBtn.disabled = false;
                    startServerBtn.innerHTML = '<svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14.828 14.828a4 4 0 01-5.656 0M9 10h1.586a1 1 0 01.707.293l.707.707A1 1 0 0012.414 11H15m-3-3h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg><span class="text-center text-xs">Start Server</span>';
                }
            }
        }

        async function trainModel() {
            if (!trainModelBtn) return;
            console.log('[trainModel] Starting training...');
            trainModelBtn.disabled = true;
            trainModelBtn.innerHTML = '<svg class="animate-spin h-4 w-4 mr-2" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg> Training...';

            try {
                console.log('[trainModel] Sending POST request to train-rasa endpoint');
                const response = await fetch('{{ route("admin.rasa-server.train-rasa") }}', {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': csrfToken,
                        'X-Requested-With': 'XMLHttpRequest',
                        'Accept': 'application/json'
                    }
                });

                console.log('[trainModel] Response received - status:', response.status, 'ok:', response.ok);

                if (!response.ok) {
                    // Handle non-JSON responses (like HTML error pages)
                    const text = await response.text();
                    console.error('[trainModel] Training request failed with status:', response.status);
                    console.error('[trainModel] Response body:', text);
                    throw new Error('Server error: HTTP ' + response.status);
                }

                const data = await response.json();
                console.log('[trainModel] JSON parsed successfully:', data);

                if (data.success) {
                    console.log('[trainModel] Training successful!');
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
                    console.error('[trainModel] Training returned success=false:', data);
                    throw new Error(data.message || 'Training failed');
                }
            } catch (error) {
                console.error('[trainModel] Caught error:', error.message);
                Swal.fire({
                    icon: 'error',
                    title: 'Training Failed',
                    text: error.message
                });
            } finally {
                if (trainModelBtn) {
                    trainModelBtn.disabled = false;
                    trainModelBtn.innerHTML = '<svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/></svg><span class="text-center text-xs">Train Model</span>';
                }
            }
        }

        // Event listeners - only attach to elements that exist
        if (refreshStatusBtn) refreshStatusBtn.addEventListener('click', fetchStatus);
        if (startServerBtn) startServerBtn.addEventListener('click', startServer);
        if (trainModelBtn) trainModelBtn.addEventListener('click', trainModel);

        if (refreshTrainingHistoryBtn) refreshTrainingHistoryBtn.addEventListener('click', fetchTrainingHistory);

        // Pagination event listeners - only attach to elements that exist
        if (trainingHistoryPerPage) {
            trainingHistoryPerPage.addEventListener('change', (e) => {
                trainingHistoryCurrentPage = 1;
                renderTrainingHistoryPage();
            });
        }

        if (trainingHistoryPrev) {
            trainingHistoryPrev.addEventListener('click', (e) => {
                if (trainingHistoryCurrentPage > 1) {
                    trainingHistoryCurrentPage--;
                    renderTrainingHistoryPage();
                }
            });
        }

        if (trainingHistoryNext) {
            trainingHistoryNext.addEventListener('click', (e) => {
                if (trainingHistoryCurrentPage < trainingHistoryTotalPages) {
                    trainingHistoryCurrentPage++;
                    renderTrainingHistoryPage();
                }
            });
        }

        // Initial load
        fetchStatus();
        fetchTrainingHistory();

        // Auto-refresh status every 5 minutes
        startStatusPolling();

        // Training status check function
        function checkStatus() {
            const icon = document.getElementById('status-icon');
            const text = document.getElementById('status-text');
            const subtext = document.getElementById('status-subtext');
            const progress = document.getElementById('progress-wrapper');
            const btn = document.getElementById('sync-btn');

            if (!icon || !text || !subtext) return;

            fetch('/admin/training-status')
                .then(response => {
                    if (!response.ok) {
                        console.error('Training status check failed:', response.status);
                        return { status: 'idle', time: 'Status check failed' };
                    }
                    return response.json();
                })
                .then(data => {
                    if (data.status === 'training') {
                        icon.className = "fas fa-sync fa-spin fa-2x mb-2 text-yellow-600";
                        text.innerText = "Training in Progress...";
                        text.className = "text-sm font-medium text-yellow-700";
                        subtext.innerText = "Render is currently rebuilding the model.";
                        if (progress) progress.classList.remove('hidden');
                        if (btn) btn.disabled = true;
                    } else if (data.status === 'success') {
                        icon.className = "fas fa-check-circle fa-2x mb-2 text-green-600";
                        text.innerText = "System Active";
                        text.className = "text-sm font-medium text-green-700";
                        subtext.innerText = "Last trained: " + (data.time || "Just now");
                        if (progress) progress.classList.add('hidden');
                        if (btn) btn.disabled = false;
                    } else if (data.status === 'failed') {
                        icon.className = "fas fa-times-circle fa-2x mb-2 text-red-600";
                        text.innerText = "Training Failed";
                        text.className = "text-sm font-medium text-red-700";
                        subtext.innerText = "Failed at: " + (data.time || "Unknown time");
                        if (progress) progress.classList.add('hidden');
                        if (btn) btn.disabled = false;
                    } else if (data.status === 'idle') {
                        icon.className = "fas fa-robot fa-2x mb-2 text-blue-600";
                        text.innerText = "System Ready";
                        text.className = "text-sm font-medium text-blue-700";
                        subtext.innerText = data.time || "Ready for training";
                        if (progress) progress.classList.add('hidden');
                        if (btn) btn.disabled = false;
                    }
                })
                .catch(error => {
                    console.error('checkStatus error:', error);
                });
        }

        // Poll training status every 5 minutes
        setInterval(checkStatus, 300000);
        checkStatus(); // Initial check on load
    } // End of initScript
})();
</script>