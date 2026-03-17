<script>
(function() {
    // Debug: Log when Rasa status script loads
    console.log('[RasaStatus] Script loaded for Rasa Server Manager System Status Card');

    // CSRF token from hidden element
    const statusDataEl = document.getElementById('statusData');
    const csrfToken = statusDataEl ? statusDataEl.getAttribute('data-csrf') : '';

    // Status elements (from Rasa Server Manager's System Status Card)
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

    /**
     * Updates the Rasa Endpoint status display
     * @param {boolean} isRunning - Whether the endpoint is running
     */
    function updateEndpointStatus(isRunning) {
        if (!endpointStatus) return;
        const dot = endpointStatus.querySelector('div');
        const text = endpointStatus.querySelector('span');

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

    /**
     * Updates the Rasa Server status display
     * @param {boolean} isRunning - Whether the server is running
     */
    function updateServerStatus(isRunning) {
        if (!serverStatus) return;
        const dot = serverStatus.querySelector('div');
        const text = serverStatus.querySelector('span');

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

    /**
     * Updates the Action Server status display
     * @param {boolean} isRunning - Whether the action server is running
     */
    function updateActionServerStatus(isRunning) {
        if (!actionServerStatus) return;
        const dot = actionServerStatus.querySelector('div');
        const text = actionServerStatus.querySelector('span');

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

    /**
     * Updates the Last Training display
     * @param {Object|null} data - Training data object
     */
    function updateLastTraining(data) {
        if (!lastTraining) return;
        const card = lastTraining.closest('.bg-white');
        const relativeDiv = card ? card.querySelector('.text-xs.text-gray-500') : null;

        if (data) {
            lastTraining.textContent = data.formatted || 'Unknown';
            if (relativeDiv) relativeDiv.textContent = data.relative || '';
        } else {
            lastTraining.textContent = 'Never';
            if (relativeDiv) relativeDiv.textContent = '';
        }
    }

    /**
     * Updates the Current Model display
     * @param {Object|null} data - Model data object
     */
    function updateCurrentModel(data) {
        if (!currentModel || !currentModelVersion) return;

        if (data) {
            currentModel.textContent = data.name || 'Unknown';
            currentModelVersion.textContent = data.version ? `v${data.version}` : '-';
        } else {
            currentModel.textContent = 'None';
            currentModelVersion.textContent = '-';
        }
    }

    /**
     * Fetches Rasa server status from the API
     */
    async function fetchStatus() {
        console.log('[RasaStatus] Fetching status...');
        
        try {
            const response = await fetch('{{ route("admin.rasa-server.status") }}', {
                headers: {
                    'X-Requested-With': 'XMLHttpRequest'
                }
            });

            if (response.ok) {
                const data = await response.json();
                console.log('[RasaStatus] Status data:', data);
                
                updateEndpointStatus(data.endpoint_5001);
                updateServerStatus(data.server_5005);
                updateActionServerStatus(data.action_server_5055);
                updateLastTraining(data.last_training);
                updateCurrentModel(data.current_model);
                
                return { success: true };
            } else {
                console.error('[RasaStatus] Status fetch failed:', response.status);
                return { success: false, error: `HTTP ${response.status}` };
            }
        } catch (error) {
            console.error('[RasaStatus] Failed to fetch status:', error);
            return { success: false, error: error.message };
        }
    }

    /**
     * Starts the Rasa server via API call
     */
    async function startServer() {
        if (!startServerBtn) return;

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
                // Show success notification
                if (typeof Swal !== 'undefined') {
                    Swal.fire({
                        icon: 'success',
                        title: 'Server Started',
                        text: data.message,
                        timer: 3000,
                        showConfirmButton: false
                    });
                }
                fetchStatus(); // Refresh status
            } else {
                throw new Error(data.message || 'Failed to start server');
            }
        } catch (error) {
            console.error('[RasaStatus] Start server failed:', error);
            if (typeof Swal !== 'undefined') {
                Swal.fire({
                    icon: 'error',
                    title: 'Start Failed',
                    text: error.message
                });
            }
        } finally {
            startServerBtn.disabled = false;
            startServerBtn.innerHTML = '<svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14.828 14.828a4 4 0 01-5.656 0M9 10h1.586a1 1 0 01.707.293l.707.707A1 1 0 0012.414 11H15m-3-3h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" /></svg><span class="text-center text-xs">Start Server</span>';
        }
    }

    /**
     * Starts the Action server via API call
     */
    async function startActionServer() {
        if (!startActionServerBtn) return;

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
                if (typeof Swal !== 'undefined') {
                    Swal.fire({
                        icon: 'success',
                        title: 'Action Server Started',
                        text: data.message,
                        timer: 3000,
                        showConfirmButton: false
                    });
                }
                fetchStatus(); // Refresh status
            } else {
                throw new Error(data.message || 'Failed to start action server');
            }
        } catch (error) {
            console.error('[RasaStatus] Start action server failed:', error);
            if (typeof Swal !== 'undefined') {
                Swal.fire({
                    icon: 'error',
                    title: 'Start Failed',
                    text: error.message
                });
            }
        } finally {
            startActionServerBtn.disabled = false;
            startActionServerBtn.innerHTML = '<svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/></svg><span class="text-center text-xs">Start Actions</span>';
        }
    }

    // Set up button event listeners
    if (refreshStatusBtn) {
        refreshStatusBtn.addEventListener('click', fetchStatus);
    }
    
    if (startServerBtn) {
        startServerBtn.addEventListener('click', startServer);
    }
    
    if (startActionServerBtn) {
        startActionServerBtn.addEventListener('click', startActionServer);
    }

    // Initial status fetch on page load
    setTimeout(() => {
        fetchStatus();
    }, 250);

    // Auto-refresh status every 30 seconds
    setInterval(fetchStatus, 30000);

    // Expose function for external calls
    window.refreshRasaStatus = fetchStatus;
    window.updateRasaStatus = fetchStatus;
})();
</script>

<!-- Hidden data element for CSRF token -->
<div id="statusData" class="hidden" data-csrf="{{ csrf_token() }}"></div>
