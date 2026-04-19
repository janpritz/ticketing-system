<script src="https://cdn.jsdelivr.net/npm/flowbite@2.5.2/dist/flowbite.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.1/dist/chart.umd.min.js"></script>
<script>
    (function() {
        // Data from backend (read from hidden element to avoid Blade-in-JS parsing issues)
        const analyticsEl = document.getElementById('analytics-data');
        const weekLabels = analyticsEl ? JSON.parse(analyticsEl.getAttribute('data-week-labels') || '[]') : [];
        const weekData = analyticsEl ? JSON.parse(analyticsEl.getAttribute('data-week-data') || '[]') : [];
        const categoryLabels = analyticsEl ? JSON.parse(analyticsEl.getAttribute('data-category-labels') || '[]') :
            [];
        const categoryData = analyticsEl ? JSON.parse(analyticsEl.getAttribute('data-category-data') || '[]') : [];
        const activeStaffSeed = analyticsEl ? JSON.parse(analyticsEl.getAttribute('data-active-staff') || '[]') :
    [];
        let activeStaffList = Array.isArray(activeStaffSeed) ? activeStaffSeed : [];
        const staffContactsSeed = analyticsEl ? JSON.parse(analyticsEl.getAttribute('data-staff-contacts') ||
            '[]') : [];
        let staffContactsList = Array.isArray(staffContactsSeed) ? staffContactsSeed : [];
        // Chart instances (assigned after init so refresh can update them)
        let weeklyChart, catChart;

        // Training status state
        let trainingHistoryData = [];
        let isTrainingInProgress = false;

        // Weekly Tickets Chart
        const weeklyEl = document.getElementById('weeklyTicketsChart');
        if (weeklyEl) {
            weeklyChart = new Chart(weeklyEl, {
                type: 'bar',
                data: {
                    labels: weekLabels,
                    datasets: [{
                        label: 'Tickets',
                        data: weekData,
                        backgroundColor: '#3B82F6',
                        borderRadius: 6,
                        maxBarThickness: 28
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    scales: {
                        x: {
                            grid: {
                                display: false
                            }
                        },
                        y: {
                            beginAtZero: true,
                            ticks: {
                                precision: 0
                            },
                            grid: {
                                color: '#f1f5f9'
                            }
                        }
                    },
                    plugins: {
                        legend: {
                            display: false
                        }
                    }
                }
            });
        }

        // Tickets by Category Chart
        const catEl = document.getElementById('ticketCategoryChart');
        if (catEl) {
            const palette = ['#6366F1', '#10B981', '#F59E0B', '#EF4444', '#06B6D4', '#84CC16', '#F472B6',
                '#FB7185'
            ];
            const colors = categoryLabels.map((_, i) => palette[i % palette.length]);

            catChart = new Chart(catEl, {
                type: 'doughnut',
                data: {
                    labels: categoryLabels,
                    datasets: [{
                        data: categoryData,
                        backgroundColor: colors,
                        borderWidth: 1
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: {
                            position: 'bottom'
                        }
                    },
                    cutout: '60%'
                }
            });
        }

        // Auto-refresh admin dashboard data
        const fmt = new Intl.NumberFormat('en-US');

        // Background fetch data every 5 minutes (reduced since we have real-time updates)
        setInterval(() => {
            refreshAdminData();
        }, 300000);

        function updateCounts(payload) {
            const elOpen = document.getElementById('openTicketsCount');
            const elOpenDelta = document.getElementById('openTicketsDelta');
            const wrapOpen = document.getElementById('openTicketsDeltaWrap');

            const elFaq = document.getElementById('faqCountValue');
            const elFaqNew = document.getElementById('faqNewCount');
            const wrapFaq = document.getElementById('faqNewWrap');

            const elUser = document.getElementById('userCountValue');
            const elUserNew = document.getElementById('userNewCount');
            const wrapUser = document.getElementById('userNewWrap');

            const elLastTraining = document.getElementById('lastTrainingValue');

            if (elOpen) elOpen.textContent = fmt.format(payload.openTickets ?? 0);

            const d = Number(payload.openTicketsDelta ?? 0);
            if (wrapOpen) wrapOpen.style.display = d > 0 ? 'flex' : 'none';
            if (elOpenDelta) {
                const sign = d > 0 ? '+' : '';
                elOpenDelta.textContent = `${sign}${fmt.format(d)} from yesterday`;
            }

            if (elFaq) elFaq.textContent = fmt.format(payload.faqCount ?? 0);
            const fn = Number(payload.faqNewCount ?? 0);
            if (wrapFaq) wrapFaq.style.display = fn > 0 ? 'flex' : 'none';
            if (elFaqNew) elFaqNew.textContent = fmt.format(fn);

            if (elUser) elUser.textContent = fmt.format(payload.userCount ?? 0);
            const nu = Number(payload.newUsers ?? 0);
            if (wrapUser) wrapUser.style.display = nu > 0 ? 'flex' : 'none';
            if (elUserNew) {
                const signU = nu > 0 ? '+' : '';
                elUserNew.textContent = `${signU}${fmt.format(nu)} new users`;
            }

            if (elLastTraining) elLastTraining.textContent = payload.lastTraining ?? 'Never';
        }


        function updateTopSenders(payload) {
            const tbody = document.getElementById('topSendersBody');
            if (!tbody || !Array.isArray(payload.topSenders)) return;
            tbody.innerHTML = '';
            payload.topSenders.forEach((row, idx) => {
                const tr = document.createElement('tr');
                tr.className = 'hover:bg-gray-50';
                tr.innerHTML = `
                    <td class="py-3 pl-5 pr-3 align-top">${idx + 1}</td>
                    <td class="px-3 py-3 align-top"><div class="text-gray-900">${row.email || '—'}</div></td>
                    <td class="px-3 py-3 align-top"><span class="font-medium text-slate-900">${fmt.format(row.count || 0)}</span></td>
                `;
                tbody.appendChild(tr);
            });
            if (payload.topSenders.length === 0) {
                const tr = document.createElement('tr');
                tr.innerHTML = '<td colspan="3" class="px-5 py-10 text-center text-sm text-gray-500">No data.</td>';
                tbody.appendChild(tr);
            }
        }

        // Contacts aside rendering
        const contactsListEl = document.getElementById('contactsList');

        function initialsOf(name) {
            if (!name) return '?';
            const parts = String(name).trim().split(/\s+/).slice(0, 2);
            return parts.map(p => (p && p[0] ? p[0].toUpperCase() : '')).join('') || '?';
        }

        function escapeHtml(s) {
            return String(s || '')
                .replace(/&/g, '&')
                .replace(/</g, '<')
                .replace(/>/g, '>')
                .replace(/"/g, '"')
                .replace(/'/g, '&#039;');
        }

        function renderContacts(list) {
            // Use the initial staffContactsList for displaying active status
            // The list parameter from broadcast events may have incorrect active status
            const displayList = staffContactsList;
            if (!contactsListEl) return;
            const arr = Array.isArray(displayList) ? displayList.slice() : [];
            if (!arr.length) {
                contactsListEl.innerHTML = '<div class="p-3 text-xs text-slate-500">No staff found.</div>';
                return;
            }
            // Sort by active first, then name
            arr.sort((a, b) => {
                const aa = a.is_active ? 0 : 1;
                const bb = b.is_active ? 0 : 1;
                if (aa !== bb) return aa - bb;
                return String(a.name || '').localeCompare(String(b.name || ''));
            });
            const html = arr.map(u => {
                // Debug: log the is_active value
                console.log('Rendering contact:', u.name, 'is_active:', u.is_active, 'typeof:', typeof u
                    .is_active);

                // More robust check for is_active - handle different data types
                const isActive = Boolean(u.is_active) ||
                    u.is_active === true ||
                    u.is_active === 'true' ||
                    u.is_active === 1 ||
                    u.is_active === '1' ||
                    u.is_active === '1.0' ||
                    u.is_active === 'yes' ||
                    u.is_active === 'YES' ||
                    u.is_active === 'Yes' ||
                    (typeof u.is_active === 'string' && u.is_active.toLowerCase() === 'true') ||
                    (typeof u.is_active === 'number' && u.is_active > 0);
                const dot = isActive ? 'bg-emerald-500' : 'bg-slate-300';

                // Debug logging for active status
                console.log('Processing user:', u.name, 'is_active:', u.is_active, 'type:', typeof u
                    .is_active, 'isActive:', isActive, 'dot class:', dot);

                console.log('Final isActive check:', isActive, 'dot class:', dot);

                const initials = initialsOf(u.name);
                const name = escapeHtml(u.name);
                const email = escapeHtml(u.email);
                return `
                <div class="flex items-center gap-3 px-2 py-2 rounded-md hover:bg-gray-50">
                  <div class="relative">
                    <div class="w-8 h-8 rounded-full bg-slate-100 text-slate-700 flex items-center justify-center text-xs font-semibold">${initials}</div>
                    <span class="absolute -bottom-0 -right-0 w-2.5 h-2.5 rounded-full ring-2 ring-white ${dot}"></span>
                  </div>
                  <div class="min-w-0 flex-1">
                    <div class="text-sm text-slate-900 truncate">${name}</div>
                    <div class="text-xs text-slate-500 truncate">${email}</div>
                  </div>
                </div>`;
            }).join('');
            contactsListEl.innerHTML = html;
        }
        // Initial render
        renderContacts(staffContactsList);

        // Helpers for lists rendering
        const adminPad = (num, size = 4) => {
            num = String(num ?? '');
            while (num.length < size) num = '0' + num;
            return num;
        };

        function adminFmtDate(d) {
            try {
                const dt = new Date(d);
                if (isNaN(dt.getTime())) return '';
                // yyyy-mm-dd hh:mm am/pm
                const yyyy = dt.getFullYear();
                const mm = String(dt.getMonth() + 1).padStart(2, '0');
                const dd = String(dt.getDate()).padStart(2, '0');
                let hours = dt.getHours();
                const minutes = String(dt.getMinutes()).padStart(2, '0');
                const ampm = hours >= 12 ? 'pm' : 'am';
                hours = hours % 12;
                if (hours === 0) hours = 12;
                const hh = String(hours).padStart(2, '0');
                return `${yyyy}-${mm}-${dd} ${hh}:${minutes} ${ampm}`;
            } catch (_) {
                return '';
            }
        }

        function adminBadgeClass(status) {
            switch (status) {
                case 'Open':
                    return 'text-blue-700 bg-blue-50 ring-blue-600/20';
                case 'Forwarded':
                    return 'text-amber-700 bg-amber-50 ring-amber-600/20';
                case 'Closed':
                    return 'text-emerald-700 bg-emerald-50 ring-emerald-600/20';
                default:
                    return 'text-slate-700 bg-slate-50 ring-slate-600/20';
            }
        }

        function updateOpenList(list) {
            const tbody = document.getElementById('openListBody');
            if (!tbody) return;
            const rows = Array.isArray(list) ? list.map(t => {
                const ticketNo = String(t.id);
                const createdAt = adminFmtDate(t.date_created || t.created_at);
                const email = t.email || '—';
                const category = t.category || '';
                const badge = adminBadgeClass(t.status);
                return `
                <tr class="hover:bg-gray-50">
                    <td class="py-3 pl-5 pr-3 align-top">
                        <div class="text-indigo-700 font-medium">${ticketNo}</div>
                        <div class="mt-1 text-xs text-gray-500">${createdAt}</div>
                    </td>
                    <td class="px-3 py-3 align-top">
                        <div class="text-gray-900">${email}</div>
                        <div class="text-xs text-gray-500">${category}</div>
                    </td>
                    <td class="px-3 py-3 align-top">
                        <span class="inline-flex items-center gap-1 rounded-full px-2.5 py-1 text-xs font-medium ring-1 ${badge}">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-3.5 w-3.5" viewBox="0 0 24 24" fill="currentColor"><circle cx="12" cy="12" r="5"></circle></svg>
                            ${t.status ?? ''}
                        </span>
                    </td>
                    <td class="py-3 pl-3 pr-5 align-top">
                        <button type="button" data-id="${t.id}" class="btn-view inline-flex items-center gap-2 rounded-lg border border-gray-200 bg-white px-3 py-1.5 text-xs font-medium text-gray-700 hover:bg-gray-50">
                            View
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-3.5 w-3.5" viewBox="0 0 24 24" fill="currentColor"><path d="M9 5l7 7-7 7" /></svg>
                        </button>
                    </td>
                </tr>`;
            }) : [];
            tbody.innerHTML = rows.length ? rows.join('') :
                `<tr><td colspan="4" class="px-5 py-10 text-center text-sm text-gray-500">No open tickets.</td></tr>`;
        }

        function updateUnassignedTicketsList(list) {
            const tbody = document.getElementById('unassignedTicketsListBody');
            if (!tbody) return;
            const rows = Array.isArray(list) ? list.map(t => {
                const ticketNo = String(t.id);
                const updatedAt = adminFmtDate(t.updated_at || t.date_created || t.created_at);
                const email = t.email || '—';
                const staffName = t.staff && t.staff.name ? `Staff: ${t.staff.name}` : '';
                const badge = adminBadgeClass(t.status);
                return `
                <tr class="hover:bg-gray-50 cursor-pointer btn-view" data-id="${t.id}">
                    <td class="py-3 pl-5 pr-3 align-top">
                        <div class="text-indigo-700 font-medium">${ticketNo}</div>
                        <div class="mt-1 text-xs text-gray-500">Updated ${updatedAt}</div>
                    </td>
                    <td class="px-3 py-3 align-top">
                        <div class="text-gray-900">${email}</div>
                        <div class="text-xs text-gray-500">${staffName}</div>
                    </td>
                    <td class="px-3 py-3 align-top">
                        <span class="inline-flex items-center gap-1 rounded-full px-2.5 py-1 text-xs font-medium ring-1 ${badge}">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-3.5 w-3.5" viewBox="0 0 24 24" fill="currentColor"><circle cx="12" cy="12" r="5"></circle></svg>
                            ${t.status ?? ''}
                        </span>
                    </td>
                </tr>`;
            }) : [];
            tbody.innerHTML = rows.length ? rows.join('') :
                `<tr><td colspan="3" class="px-5 py-10 text-center text-sm text-gray-500">No unassigned tickets.</td></tr>`;
        }
        async function refreshAdminData() {
            const url = analyticsEl ? analyticsEl.getAttribute('data-admin-url') : null;
            if (!url) return;
            try {
                const res = await fetch(url, {
                    method: 'GET',
                    headers: {
                        'Accept': 'application/json'
                    },
                    credentials: 'same-origin',
                    cache: 'no-store'
                });
                if (!res.ok) return;
                const data = await res.json();

                // Update metrics
                updateCounts(data);

                // Update weekly chart
                if (weeklyChart) {
                    weeklyChart.data.labels = data.weekLabels || [];
                    weeklyChart.data.datasets[0].data = data.weekData || [];
                    weeklyChart.update();
                }

                // Update category chart
                if (catChart) {
                    catChart.data.labels = data.categoryLabels || [];
                    const palette = ['#6366F1', '#10B981', '#F59E0B', '#EF4444', '#06B6D4', '#84CC16',
                        '#F472B6', '#FB7185'
                    ];
                    catChart.data.datasets[0].data = data.categoryData || [];
                    catChart.data.datasets[0].backgroundColor = (data.categoryLabels || []).map((_, i) =>
                        palette[i % palette.length]);
                    catChart.update();
                }

                // Update top senders table and lists
                updateTopSenders(data);
                updateOpenList(data.openList || []);
                updateUnassignedTicketsList(data.unassignedTickets || []);

                // Update right-side contacts
                // Note: We do NOT update the contact drawer with API data as it may have incorrect active status
                // The contact drawer should only use the initial staffContactsList with correct active status
                if (Array.isArray(data.staffContacts)) {
                    staffContactsList = data.staffContacts;
                    // DO NOT call renderContacts here - this was causing the second incorrect render
                    // The contact drawer uses the initial staffContactsList which has correct active status
                }

                // Update Rasa status
                if (typeof window.refreshRasaStatus === 'function') {
                    window.refreshRasaStatus();
                }
            } catch (e) {
                // swallow errors to avoid UI disruption
                console.debug('Admin auto-refresh failed', e);
            }
        }

        // Document Training Alert Management
        async function trainRasa() {
            const btn = document.getElementById('trainRasaBtn');
            const spinner = document.getElementById('trainSpinner');
            const btnText = document.getElementById('trainBtnText');

            // Show loading state
            btn.disabled = true;
            spinner.classList.remove('hidden');
            btnText.textContent = 'Training...';

            try {
                const csrf = '{{ csrf_token() }}';
                const res = await fetch('{{ route('admin.rasa-server.train-rasa') }}', {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': csrf,
                        'X-Requested-With': 'XMLHttpRequest',
                        'Accept': 'application/json'
                    }
                });

                if (!res.ok) {
                    // Handle non-JSON responses (like HTML error pages)
                    const text = await res.text();
                    console.error('Training request failed with status:', res.status, 'Body:', text);
                    throw new Error('Server error: HTTP ' + res.status);
                }

                const data = await res.json();

                if (!res.ok || !data.success) {
                    throw new Error(data.message || 'Training failed');
                }

                // Show success message
                Swal.fire({
                    toast: true,
                    position: 'top-end',
                    icon: 'success',
                    title: 'Rasa training completed successfully!',
                    showConfirmButton: false,
                    timer: 3000,
                    timerProgressBar: true
                });

                // Hide the training alert
                const trainingAlert = document.getElementById('trainingAlert');
                if (trainingAlert) {
                    trainingAlert.classList.add('hidden');
                }

            } catch (err) {
                console.error('[DEBUG] Training error:', err);
                Swal.fire({
                    icon: 'error',
                    title: 'Training Failed',
                    text: `Training failed: ${err.message}`,
                    confirmButtonText: 'OK'
                });
            } finally {
                // Reset button state
                btn.disabled = false;
                spinner.classList.add('hidden');
                btnText.textContent = 'Train Rasa';
            }
        }

        async function checkTrainingStatus() {
            try {
                const res = await fetch('{{ route('admin.document-changes.training-status') }}', {
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest'
                    }
                });

                if (res.ok) {
                    const data = await res.json();
                    const alertEl = document.getElementById('trainingAlert');
                    if (alertEl) {
                        if (data.requires_training) {
                            // Show training alert
                            alertEl.classList.remove('hidden');
                        } else {
                            // Hide training alert
                            alertEl.classList.add('hidden');
                        }
                    }
                }
            } catch (err) {
                console.error('[DEBUG] Error checking training status:', err);
            }
        }

        // Add event listener for train button
        const trainBtn = document.getElementById('trainRasaBtn');
        if (trainBtn) {
            trainBtn.addEventListener('click', trainRasa);
        }

        // Check training status on page load
        checkTrainingStatus();

        // Listen for real-time active staff updates
        if (typeof Echo !== 'undefined') {
            Echo.channel('active-staff').listen('.active-staff.updated', (e) => {
                console.log('Active staff count received from broadcast event:', e.count);
                console.log('Staff contacts data:', e.staffContacts);

                const dot = document.getElementById('activeStaffDot');
                const countText = document.getElementById('activeStaffCountText');
                if (dot) {
                    if (e.count > 0) {
                        dot.classList.remove('hidden');
                        // Ensure it's green when there are active staff
                        dot.className = 'w-4 h-4 rounded-full bg-green-500';
                        console.log('Green dot shown');
                    } else {
                        dot.classList.add('hidden');
                        console.log('Green dot hidden');
                    }
                }
                if (countText) {
                    countText.textContent = e.count;
                }

                // DO NOT update the contact drawer with broadcast data
                // The broadcast event data may have incorrect active status
                // Keep using the initial staffContactsList for contact drawer rendering
                // to maintain correct active status display
            });
        }

        // Rasa status update functions
        function updateServerStatus(isRunning) {
            const serverStatus = document.getElementById('serverStatus');
            if (!serverStatus) return;
            const dot = serverStatus.querySelector('div');
            const text = serverStatus.querySelector('span');

            if (isRunning) {
                dot.className = 'w-4 h-4 rounded-full bg-green-600';
                text.textContent = 'Online';
                text.className = 'text-sm text-green-800';
            } else {
                dot.className = 'w-4 h-4 rounded-full bg-red-600';
                text.textContent = 'Offline';
                text.className = 'text-sm text-red-800';
            }
        }

        function updateLastTraining(data) {
            const lastTraining = document.getElementById('lastTraining');
            if (!lastTraining) return;
            if (data && data.formatted) {
                lastTraining.textContent = data.formatted;
                const relativeDiv = lastTraining.closest('.bg-white').querySelector('.text-xs.text-gray-500');
                if (relativeDiv) relativeDiv.textContent = data.relative || '';
            } else {
                lastTraining.textContent = 'Never';
                const relativeDiv = lastTraining.closest('.bg-white').querySelector('.text-xs.text-gray-500');
                if (relativeDiv) relativeDiv.textContent = '';
            }
        }

        function updateCurrentModel(data) {
            const currentModel = document.getElementById('currentModel');
            if (!currentModel) return;
            currentModel.textContent = data || 'None';

            const currentModelVersion = document.getElementById('currentModelVersion');
            if (currentModelVersion) {
                currentModelVersion.textContent = data ? '-' : '';
            }
        }

        // Fetch Rasa status
        async function fetchRasaStatus() {
            try {
                const response = await fetch('{{ route("admin.rasa-server.status") }}', {
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest'
                    }
                });

                if (response.ok) {
                    const data = await response.json();
                    updateServerStatus(data.server_status === 'online');
                    updateLastTraining(data.last_training);
                    updateCurrentModel(data.current_model);
                }
            } catch (error) {
                console.error('Failed to fetch Rasa status:', error);
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
                    trainingHistoryData = data.trainings || [];
                    checkTrainingStatus();
                } else {
                    console.error('fetchTrainingHistory response not ok:', response.status);
                }
            } catch (error) {
                console.error('Failed to fetch training history:', error);
            }
        }

        // Check for ongoing trainings and update UI
        function checkTrainingStatus() {
            const icon = document.getElementById('status-icon');
            const text = document.getElementById('status-text');
            const subtext = document.getElementById('status-subtext');
            const progress = document.getElementById('progress-wrapper');

            // Check if there's any training with status 'training' and no completed_at
            const ongoingTraining = trainingHistoryData.find(training =>
                training.status === 'training' && !training.completed_at
            );

            isTrainingInProgress = !!ongoingTraining;

            if (ongoingTraining) {
                // Training is in progress
                if (icon) icon.className = "fas fa-sync fa-spin fa-2x mb-2 text-blue-600";
                if (text) {
                    text.innerText = "Training Processing";
                    text.className = "text-sm font-medium text-blue-700";
                }
                //if (subtext) subtext.innerText = "Training in progress...";
                if (progress) progress.classList.remove('hidden');
            } else {
                // No training in progress
                if (icon) icon.className = "fas fa-check-circle fa-2x mb-2 text-green-600";
                if (text) {
                    text.innerText = "System Active";
                    text.className = "text-sm font-medium text-green-700";
                }
                if (subtext) subtext.innerText = "Ready for training.";
                if (progress) progress.classList.add('hidden');
            }
        }

        // Legacy function for backward compatibility
        async function fetchTrainingStatus() {
            return fetchTrainingHistory();
        }

        // Global function for refreshing Rasa status
        window.refreshRasaStatus = () => {
            fetchRasaStatus();
            fetchTrainingHistory();
        };

        // Attach event listener for refresh status button
        const refreshStatusBtn = document.getElementById('refreshStatus');
        if (refreshStatusBtn) {
            refreshStatusBtn.addEventListener('click', () => {
                fetchRasaStatus();
                fetchTrainingHistory();
            });
        }

        // Initial fetch (once) - polling disabled to avoid overloading the database.
        // The dashboard will only refresh when a CRUD operation signals a change
        // via localStorage (key: 'ts_tickets_changed') or when the user focuses the tab
        // and a change has been recorded.
        setTimeout(() => {
            refreshAdminData();
            fetchRasaStatus();
            fetchTrainingHistory();

            // Ensure green dot is shown if there are active staff initially
            const initialActiveCount = parseInt(document.getElementById('activeStaffCountText')
                ?.textContent || '0');
            const initialDot = document.getElementById('activeStaffDot');
            if (initialDot && initialActiveCount > 0) {
                initialDot.classList.remove('hidden');
                initialDot.className = 'w-4 h-4 rounded-full bg-green-500';
            }
        }, 250);

        // Refresh on tab focus only if a change was recorded by another tab/window
        /**window.addEventListener('focus', () => {
            try {
                if (localStorage.getItem('ts_tickets_changed')) refreshAdminData();
            } catch (_) {}
        });

        // Refresh on visibility change only if a change was recorded
        document.addEventListener('visibilitychange', () => {
            try {
                if (!document.hidden && localStorage.getItem('ts_tickets_changed')) refreshAdminData();
            } catch (_) {}
        });
        **/

        // Cross-tab notification: when other tabs perform CRUD they should set
        // localStorage.ts_tickets_changed to notify this tab to refresh.
        window.addEventListener('storage', (e) => {
            if (e && e.key === 'ts_tickets_changed') {
                refreshAdminData();
            }
        });
    })();
</script>

<script>
    // Unassigned tickets refresh handler: use DOMContentLoaded to ensure elements exist
    document.addEventListener('DOMContentLoaded', function() {
        const btn = document.getElementById('refreshUnassignedBtn');
        if (!btn) return;

        btn.addEventListener('click', async function() {
            const orig = btn.innerHTML;
            try {
                console.log('Unassigned tickets refresh: start');
                btn.disabled = true;
                btn.setAttribute('aria-busy', 'true');
                btn.classList.add('opacity-70', 'pointer-events-none');
                btn.innerHTML =
                    '<svg xmlns="http://www.w3.org/2000/svg" class="animate-spin h-4 w-4 mr-2" viewBox="0 0 24 24" fill="none" role="img" aria-hidden="true"><circle cx="12" cy="12" r="10" stroke="currentColor" stroke-width="3" stroke-linecap="round"/></svg>Refreshing';

                const analyticsEl = document.getElementById('analytics-data');
                const url = analyticsEl ? analyticsEl.getAttribute('data-admin-url') : null;
                if (!url) return;

                const res = await fetch(url, {
                    headers: {
                        'Accept': 'application/json'
                    },
                    credentials: 'same-origin',
                    cache: 'no-store'
                });
                if (!res.ok) {
                    console.warn('Unassigned tickets refresh: network response not ok', res.status);
                    return;
                }
                const data = await res.json();

                const tbody = document.getElementById('unassignedTicketsListBody');
                if (!tbody) return;
                const list = Array.isArray(data.unassignedTickets) ? data.unassignedTickets : [];
                tbody.innerHTML = '';
                if (list.length === 0) {
                    tbody.innerHTML =
                        '<tr><td colspan="3" class="px-5 py-10 text-center text-sm text-gray-500">No unassigned tickets.</td></tr>';
                    return;
                }

                const rows = list.map(t => {
                    const updatedAt = (t.updated_at || t.date_created || t.created_at) ||
                        '';
                    const staffName = t.staff && t.staff.name ? `Staff: ${t.staff.name}` :
                        '';
                    const status = t.status || '';
                    return `
            <tr class="hover:bg-gray-50 cursor-pointer btn-view" data-id="${t.id}">
              <td class="py-3 pl-5 pr-3 align-top">
                <div class="text-indigo-700 font-medium">${t.id}</div>
                <div class="mt-1 text-xs text-gray-500">Updated ${updatedAt}</div>
              </td>
              <td class="px-3 py-3 align-top">
                <div class="text-gray-900">${t.email || '—'}</div>
                <div class="text-xs text-gray-500">${staffName}</div>
              </td>
              <td class="px-3 py-3 align-top">
                <span class="inline-flex items-center gap-1 rounded-full px-2.5 py-1 text-xs font-medium ring-1 text-slate-700 bg-slate-50 ring-slate-600/20">
                  <svg xmlns="http://www.w3.org/2000/svg" class="h-3.5 w-3.5" viewBox="0 0 24 24" fill="currentColor"><circle cx="12" cy="12" r="5"></circle></svg>
                  ${status}
                </span>
              </td>
            </tr>`;
                }).join('');

                tbody.innerHTML = rows;
                console.log('Unassigned tickets refresh: complete, rows rendered:', list.length);
            } catch (err) {
                console.error('Failed to refresh unassigned tickets', err);
            } finally {
                btn.disabled = false;
                btn.removeAttribute('aria-busy');
                btn.classList.remove('opacity-70', 'pointer-events-none');
                btn.innerHTML = orig;
                console.log('Unassigned tickets refresh: UI restored');
            }
        });
    });
</script>

<!-- Toggle Contacts Aside via Active Staff card -->
<script>
    (function() {
        const activeCard = document.getElementById('activeStaffCard');
        const contactsAside = document.getElementById('contacts-aside');
        const content = document.getElementById('content-wrapper');

        if (!activeCard || !contactsAside || !content) return;

        function isShown() {
            return !contactsAside.classList.contains('hidden');
        }

        function showAside() {
            contactsAside.classList.remove('hidden');
            contactsAside.classList.add('flex'); // ensure flex layout when visible
            content.classList.add('sm:mr-72'); // reserve space on right for >= sm
        }

        function hideAside() {
            contactsAside.classList.add('hidden');
            contactsAside.classList.remove('flex');
            content.classList.remove('sm:mr-72');
        }

        function toggleAside() {
            if (isShown()) {
                console.log('Hiding contacts aside');
                hideAside();
            } else {
                console.log('Showing contacts aside');
                showAside();
            }
        }

        // Click to toggle
        activeCard.addEventListener('click', () => {
            console.log('Active Staff card clicked, toggling aside');
            toggleAside();
        });
        // Keyboard support (Enter/Space)
        activeCard.addEventListener('keydown', (e) => {
            if (e.key === 'Enter' || e.key === ' ') {
                e.preventDefault();
                console.log('Active Staff card keyboard activated, toggling aside');
                toggleAside();
            }
        });
    })();
</script>

<!-- Sidebar collapse/expand for mobile + desktop -->
<script>
    (function() {
        const toggleBtn = document.getElementById('sidebar-toggle');
        const sidebar = document.getElementById('default-sidebar');
        const content = document.getElementById('content-wrapper');
        const backdrop = document.getElementById('sidebar-backdrop');

        if (!toggleBtn || !sidebar || !content) return;

        const mq = window.matchMedia('(max-width: 639.98px)'); // Tailwind sm breakpoint

        function isMobile() {
            return mq.matches;
        }

        function openDesktop() {
            sidebar.classList.remove('sm:-translate-x-full');
            sidebar.classList.add('sm:translate-x-0');
            content.classList.add('sm:ml-64');
            content.classList.remove('ml-0');
            if (backdrop) backdrop.classList.add('hidden');
            document.body.classList.remove('overflow-hidden');
        }

        function closeDesktop() {
            sidebar.classList.add('sm:-translate-x-full');
            sidebar.classList.remove('sm:translate-x-0');
            content.classList.remove('sm:ml-64');
            content.classList.add('ml-0');
            if (backdrop) backdrop.classList.add('hidden');
            document.body.classList.remove('overflow-hidden');
        }

        function openMobile() {
            sidebar.classList.remove('-translate-x-full');
            sidebar.classList.add('translate-x-0');
            if (backdrop) backdrop.classList.remove('hidden');
            document.body.classList.add('overflow-hidden');
        }

        function closeMobile() {
            sidebar.classList.add('-translate-x-full');
            sidebar.classList.remove('translate-x-0');
            if (backdrop) backdrop.classList.add('hidden');
            document.body.classList.remove('overflow-hidden');
        }

        function toggleSidebar() {
            if (isMobile()) {
                const isHidden = sidebar.classList.contains('-translate-x-full');
                if (isHidden) openMobile();
                else closeMobile();
            } else {
                const isCollapsed = sidebar.classList.contains('sm:-translate-x-full');
                if (isCollapsed) openDesktop();
                else closeDesktop();
            }
        }

        toggleBtn.addEventListener('click', function(e) {
            e.preventDefault();
            toggleSidebar();
        });

        if (backdrop) {
            backdrop.addEventListener('click', closeMobile);
        }

        document.addEventListener('keydown', (e) => {
            if (e.key === 'Escape' && isMobile()) {
                closeMobile();
            }
        });

        // Ensure correct state on resize
        mq.addEventListener('change', () => {
            if (!isMobile()) {
                // Leaving mobile: hide backdrop and ensure desktop-open by default
                sidebar.classList.remove('translate-x-0');
                sidebar.classList.add('-translate-x-full'); // keep base mobile hidden
                openDesktop();
            } else {
                // Entering mobile: keep content unshifted and sidebar hidden
                content.classList.remove('sm:ml-64');
                if (backdrop) backdrop.classList.add('hidden');
                sidebar.classList.add('-translate-x-full');
                sidebar.classList.remove('translate-x-0');
            }
        });
    })();
</script>
<script>
    (function() {
        const ticketModal = document.getElementById('ticketModal');
        const modalBackdrop = ticketModal ? ticketModal.querySelector('[data-modal-backdrop]') : null;
        const modalCloseBtns = ticketModal ? ticketModal.querySelectorAll('[data-modal-close]') : [];
        const tmTicketNo = document.getElementById('tmTicketNo');
        const tmStatus = document.getElementById('tmStatus');
        const tmQuestion = document.getElementById('tmQuestion');
        const tmResponse = document.getElementById('tmResponse');
        const tmCategory = document.getElementById('tmCategory');
        const tmDates = document.getElementById('tmDates');
        const tmEmail = document.getElementById('tmEmail');
        const tmRecepient = document.getElementById('tmRecepient');
        const tmStoredResponseBlock = document.getElementById('tmStoredResponseBlock');
        const tmStoredResponse = document.getElementById('tmStoredResponse');
        const tmSendResponse = document.getElementById('tmSendResponse');
        const tmOptionsBtn = document.getElementById('tmOptionsBtn');
        const tmOptionsMenu = document.getElementById('tmOptionsMenu');
        const tmOptionAssign = document.getElementById('tmOptionAssign');
        const tmOptionHideForward = document.getElementById('tmOptionHideForward');
        const tmForwardControls = document.getElementById('tmForwardControls');
        const tmForwardSelect = document.getElementById('tmForwardSelect');
        const tmForwardApply = document.getElementById('tmForwardApply');

        const csrfToken = '{{ csrf_token() }}';

        // Attachment URL helper (works even when /public/storage symlink is missing on hosted setups)
        const ATTACHMENT_BASE = "{{ url('/attachments') }}";

        function attachmentUrl(p) {
            if (!p) return '';
            return ATTACHMENT_BASE + '/' + String(p).split('/').map(encodeURIComponent).join('/');
        }

        const forwardBase = "{{ url('/admin/tickets') }}";
        let currentTicketId = null;
        let currentIsAssigning = false;

        const statusStyles = {
            'Open': 'text-blue-700 bg-blue-50 ring-blue-600/20',
            'Forwarded': 'text-amber-700 bg-amber-50 ring-amber-600/20',
            'Closed': 'text-emerald-700 bg-emerald-50 ring-emerald-600/20',
        };

        function statusClassFor(s) {
            return statusStyles[s] || 'text-slate-700 bg-slate-50 ring-slate-600/20';
        }

        function fmtDate(d) {
            try {
                const dt = new Date(d);
                if (isNaN(dt.getTime())) return '';
                return dt.toLocaleString();
            } catch (_) {
                return '';
            }
        }

        function escapeHtml(s) {
            if (s == null) return '';
            return String(s).replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;').replace(/"/g,
                '&quot;').replace(/'/g, "&#039;");
        }

        function ensureHistorySection() {
            let section = document.getElementById('tmHistorySection');
            const list = document.getElementById('tmHistoryList');
            return {
                section,
                list
            };
        }

        function renderHistory(histArr) {
            const {
                section,
                list
            } = ensureHistorySection();
            if (!section || !list) return;
            if (!Array.isArray(histArr) || histArr.length === 0) {
                list.innerHTML = '<li class="text-xs text-gray-500">No routing history.</li>';
                return;
            }
            const items = histArr.map(h => {
                const when = fmtDate(h.routed_at || h.created_at);
                const who = (h.staff && h.staff.name) ? h.staff.name : '-';
                const status = h.status || '';
                const notes = h.notes || '';
                return `
        <li class="text-xs text-gray-700">
          <div class="flex items-start justify-between">
            <div>
              <span class="font-medium">${status}</span>
              <span class="text-gray-500"> • ${who}</span>
            </div>
            <div class="text-gray-500">${when}</div>
          </div>
          ${notes ? `<div class="text-gray-600 mt-0.5">${notes}</div>` : ''}
        </li>
      `;
            });
            list.innerHTML = items.join('');
        }

        /**
         * Disables the Send Response button if Forward Controls are visible
         */
        function syncResponseButtonState() {
            if (!tmForwardControls || !tmSendResponse) return;

            // Check if the forward controls are NOT hidden
            const isForwarding = !tmForwardControls.classList.contains('hidden');

            if (isForwarding) {
                tmSendResponse.disabled = true;
                tmSendResponse.classList.add('opacity-50', 'cursor-not-allowed', 'pointer-events-none');
            } else {
                // Re-enable only if the ticket isn't closed (checking current ticket status)
                const isClosed = (tmStatus && tmStatus.textContent.includes('Closed'));

                tmSendResponse.disabled = isClosed;
                tmSendResponse.classList.toggle('opacity-50', isClosed);
                tmSendResponse.classList.toggle('cursor-not-allowed', isClosed);
                tmSendResponse.classList.toggle('pointer-events-none', isClosed);
            }
        }

        /**
         * Synchronizes the Send Response button state with the Forward controls
         */
        function updateResponseButtonState() {
            if (!tmSendResponse || !tmForwardControls) return;

            // Check if Forward Controls are NOT hidden
            const isForwardingActive = !tmForwardControls.classList.contains('hidden');

            // Also consider if the ticket is already closed
            const isClosed = tmStatus ? tmStatus.textContent.toLowerCase().includes('closed') : false;

            if (isForwardingActive || isClosed) {
                tmSendResponse.disabled = true;
                tmSendResponse.classList.add('opacity-50', 'pointer-events-none', 'cursor-not-allowed');
            } else {
                tmSendResponse.disabled = false;
                tmSendResponse.classList.remove('opacity-50', 'pointer-events-none', 'cursor-not-allowed');
            }
        }

        async function loadAndShowTicket(id) {
            currentTicketId = id;
            if (!id) return;
            const url = "{{ url('/admin/tickets') }}/" + encodeURIComponent(id);
            try {
                const res = await fetch(url, {
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest'
                    },
                    credentials: 'same-origin'
                });
                if (!res.ok) {
                    console.error('Dashboard: failed to load ticket', res.status);
                    return;
                }
                const t = await res.json();
                if (!t) return;

                const ticketNo = String(t.id);
                const createdAt = fmtDate(t.date_created || t.created_at);
                const updatedAt = fmtDate(t.updated_at);
                const category = t.category ?? '';
                const question = t.question ?? '';
                const email = t.email ?? '';
                const recepient = t.recepient_id ?? '';

                // Fill fields
                if (tmTicketNo) tmTicketNo.textContent = 'Ticket #' + ticketNo;
                if (tmDates) tmDates.textContent = createdAt ?
                    `Created ${createdAt}${updatedAt ? ' • Updated ' + updatedAt : ''}` : '';
                if (tmStatus) {
                    tmStatus.className =
                        'inline-flex items-center gap-1.5 rounded-full px-3 py-1 text-xs font-medium ring-1 ' +
                        statusClassFor(t.status);
                    tmStatus.innerHTML =
                        `<svg xmlns="http://www.w3.org/2000/svg" class="h-3.5 w-3.5" viewBox="0 0 24 24" fill="currentColor"><circle cx="12" cy="12" r="5"></circle></svg> ${t.status ?? ''}`;
                }
                if (tmCategory) tmCategory.textContent = category;
                if (tmQuestion) tmQuestion.textContent = question;
                if (tmEmail) tmEmail.textContent = email;
                if (tmRecepient) tmRecepient.textContent = recepient;
                if (tmResponse) tmResponse.value = '';

                // Reset details section to collapsed state
                const detailsContent = document.getElementById('tmDetailsContent');
                const detailsChevron = document.getElementById('tmDetailsChevron');
                const toggleDetailsBtn = document.getElementById('tmToggleDetails');
                if (detailsContent) detailsContent.classList.add('hidden');
                if (detailsChevron) detailsChevron.style.transform = 'rotate(0deg)';
                if (toggleDetailsBtn) toggleDetailsBtn.querySelector('span').textContent = 'Show Details';

                // Handle attachments
                const attachmentsBlock = document.getElementById('tmAttachmentsBlock');
                const attachmentsList = document.getElementById('tmAttachmentsList');
                if (attachmentsBlock && attachmentsList) {
                    attachmentsList.innerHTML = '';
                    if (t.attachments) {
                        let attachments = [];
                        try {
                            attachments = JSON.parse(t.attachments);
                        } catch (e) {
                            attachments = [];
                        }
                        if (attachments.length > 0) {
                            attachments.forEach((path, index) => {
                                const img = document.createElement('img');
                                img.src = attachmentUrl(path);
                                img.alt = 'Attachment ' + (index + 1);
                                img.className =
                                    'max-w-16 max-h-16 object-cover rounded cursor-pointer border border-gray-300 hover:border-indigo-400';
                                img.onclick = () => openLightbox(attachments, index);
                                attachmentsList.appendChild(img);
                            });
                            attachmentsBlock.classList.remove('hidden');
                        } else {
                            attachmentsBlock.classList.add('hidden');
                        }
                    } else {
                        attachmentsBlock.classList.add('hidden');
                    }
                }

                // Hide forward controls initially
                if (tmForwardControls) tmForwardControls.classList.add('hidden');

                // Prepare and render history; keep hidden by default until toggled in Options
                const hsObj = ensureHistorySection();
                if (hsObj.section) hsObj.section.classList.add('hidden');
                const histories = t.routing_histories || t.routingHistories || [];
                renderHistory(Array.isArray(histories) ? histories : []);

                // Toggle forward option and response display based on status
                const isClosed = (t.status === 'Closed');
                const hasStaff = t.staff && t.staff.name;
                currentIsAssigning = !hasStaff;
                
                // Update labels based on assignment status
                const forwardLabel = document.getElementById('tmForwardLabel');
                const forwardButtonText = document.getElementById('tmForwardButtonText');
                if (forwardLabel) forwardLabel.textContent = hasStaff ? 'Forward to:' : 'Assign to:';
                if (forwardButtonText) forwardButtonText.textContent = hasStaff ? 'Forward Ticket' : 'Assign';
                
                // Reset options menu to show "Assign to a Staff" and hide "Hide Forward Controls"
                if (tmOptionAssign) tmOptionAssign.classList.remove('hidden');
                if (tmOptionHideForward) tmOptionHideForward.classList.add('hidden');
                
                // Hide forward controls initially when loading new ticket
                if (tmForwardControls) tmForwardControls.classList.add('hidden');

                // Ensure the response button is enabled for the new ticket (unless it's closed)
                if (tmSendResponse) {
                    const isClosed = (t.status === 'Closed');
                    tmSendResponse.disabled = isClosed;
                    tmSendResponse.classList.toggle('opacity-50', isClosed);
                    tmSendResponse.classList.toggle('pointer-events-none', isClosed);
                }

                if (tmStoredResponseBlock) {
                    if (isClosed) {
                        tmStoredResponseBlock.classList.remove('hidden');
                        if (tmStoredResponse) tmStoredResponse.textContent = t.response ? String(t.response) :
                            'No response on record.';
                    } else {
                        tmStoredResponseBlock.classList.add('hidden');
                        if (tmStoredResponse) tmStoredResponse.textContent = '';
                    }
                }
                if (tmResponse) {
                    tmResponse.disabled = isClosed;
                    tmResponse.placeholder = isClosed ? 'Ticket is closed. Response cannot be edited.' :
                        'Type your response message here...';
                }
                if (tmSendResponse) {
                    tmSendResponse.disabled = isClosed;
                    tmSendResponse.classList.toggle('opacity-50', isClosed);
                    tmSendResponse.classList.toggle('pointer-events-none', isClosed);
                }

                // Populate forward select with users
                if (tmForwardSelect && t.users) {
                    tmForwardSelect.innerHTML = '<option value="" selected disabled>Select user</option>';
                    t.users.forEach(user => {
                        const option = document.createElement('option');
                        option.value = user.id;
                        option.textContent = user.name;
                        tmForwardSelect.appendChild(option);
                    });
                }

                if (ticketModal) {
                    ticketModal.classList.remove('hidden');
                    document.body.classList.add('overflow-hidden');
                }

                // ADD THIS: Sync Response Button with Forward Controls
                syncResponseButtonState();

            } catch (err) {
                console.error('Dashboard: error loading ticket', err);
            }
        }

        function closeModal() {
            if (!ticketModal) return;
            ticketModal.classList.add('hidden');
            document.body.classList.remove('overflow-hidden');
            currentTicketId = null;
        }

        // Lightbox functions
        let currentLightboxImages = [];
        let currentLightboxIndex = 0;

        function openLightbox(images, index) {
            currentLightboxImages = images;
            currentLightboxIndex = index;
            const lightbox = document.getElementById('imageLightbox');
            const img = document.getElementById('lightboxImage');
            if (lightbox && img) {
                img.src = attachmentUrl(images[index]);
                lightbox.classList.remove('hidden');
                document.body.classList.add('overflow-hidden');
                updateLightboxButtons();
            }
        }

        function closeLightbox() {
            const lightbox = document.getElementById('imageLightbox');
            if (lightbox) {
                lightbox.classList.add('hidden');
                document.body.classList.remove('overflow-hidden');
            }
        }

        function updateLightboxButtons() {
            const prevBtn = document.getElementById('lightboxPrev');
            const nextBtn = document.getElementById('lightboxNext');
            if (prevBtn) prevBtn.style.display = currentLightboxIndex > 0 ? 'flex' : 'none';
            if (nextBtn) nextBtn.style.display = currentLightboxIndex < currentLightboxImages.length - 1 ? 'flex' :
                'none';
        }

        function prevImage() {
            if (currentLightboxIndex > 0) {
                currentLightboxIndex--;
                const img = document.getElementById('lightboxImage');
                if (img) img.src = attachmentUrl(currentLightboxImages[currentLightboxIndex]);
                updateLightboxButtons();
            }
        }

        function nextImage() {
            if (currentLightboxIndex < currentLightboxImages.length - 1) {
                currentLightboxIndex++;
                const img = document.getElementById('lightboxImage');
                if (img) img.src = attachmentUrl(currentLightboxImages[currentLightboxIndex]);
                updateLightboxButtons();
            }
        }

        // Open modal for "View" buttons in Open Tickets and My Tickets tables
        document.addEventListener('click', function(e) {
            const btn = e.target && e.target.closest ? e.target.closest('.btn-view') : null;
            if (!btn) return;
            const id = btn.getAttribute('data-id') || btn.dataset.id;
            if (!id) return;
            loadAndShowTicket(id);
        });

        // Close modal interactions
        if (modalBackdrop) modalBackdrop.addEventListener('click', closeModal);
        if (modalCloseBtns && modalCloseBtns.length) {
            modalCloseBtns.forEach(btn => btn.addEventListener('click', closeModal));
        }
        document.addEventListener('keydown', (e) => {
            if (e.key === 'Escape' && ticketModal && !ticketModal.classList.contains('hidden')) {
                closeModal();
            }
        });

        // Toggle Details Section
        const tmToggleDetails = document.getElementById('tmToggleDetails');
        const tmDetailsContent = document.getElementById('tmDetailsContent');
        const tmDetailsChevron = document.getElementById('tmDetailsChevron');

        if (tmToggleDetails && tmDetailsContent && tmDetailsChevron) {
            tmToggleDetails.addEventListener('click', () => {
                const isHidden = tmDetailsContent.classList.contains('hidden');
                tmDetailsContent.classList.toggle('hidden');
                tmDetailsChevron.style.transform = isHidden ? 'rotate(180deg)' : 'rotate(0deg)';
                tmToggleDetails.querySelector('span').textContent = isHidden ? 'Hide Details' :
                    'Show Details';
            });
        }

        // Options dropdown
        if (tmOptionsBtn && tmOptionsMenu) {
            tmOptionsBtn.addEventListener('click', (e) => {
                e.stopPropagation();
                const isOpen = !tmOptionsMenu.classList.contains('hidden');
                tmOptionsMenu.classList.toggle('hidden', isOpen);
                tmOptionsBtn.setAttribute('aria-expanded', String(!isOpen));
            });

            document.addEventListener('click', (e) => {
                if (!tmOptionsMenu.contains(e.target) && !tmOptionsBtn.contains(e.target)) {
                    tmOptionsMenu.classList.add('hidden');
                    tmOptionsBtn.setAttribute('aria-expanded', 'false');
                }
            });

            tmOptionsMenu.addEventListener('click', (e) => {
                const btn = e.target.closest('[data-option]');
                if (!btn) return;
                const action = btn.getAttribute('data-option');

                // Hide menu after action
                tmOptionsMenu.classList.add('hidden');
                tmOptionsBtn.setAttribute('aria-expanded', 'false');

                if (action === 'toggle-history') {
                    const hs = ensureHistorySection().section;
                    if (hs) {
                        const willShow = hs.classList.contains('hidden');
                        hs.classList.toggle('hidden');
                        btn.textContent = willShow ? 'Hide History' : 'Show History';
                    }
                } else if (action === 'show-forward') {
                    if (tmForwardControls) {
                        tmForwardControls.classList.remove('hidden');
                        // Disable the Send Response button when Forward Controls are shown
                        if (tmSendResponse) {
                            tmSendResponse.disabled = true;
                            tmSendResponse.classList.add('opacity-50', 'pointer-events-none', 'cursor-not-allowed');
                        }
                        // Toggle menu options: hide "Assign to a Staff", show "Hide Forward Controls"
                        if (tmOptionAssign) tmOptionAssign.classList.add('hidden');
                        if (tmOptionHideForward) tmOptionHideForward.classList.remove('hidden');
                    }
                } else if (action === 'hide-forward') {
                    if (tmForwardControls) {
                        tmForwardControls.classList.add('hidden');
                        // Enable the Send Response button when Forward Controls are hidden
                        if (tmSendResponse) {
                            // Check if ticket is closed before enabling
                            const isClosed = tmStatus ? tmStatus.textContent.includes('Closed') : false;
                            tmSendResponse.disabled = isClosed;
                            tmSendResponse.classList.toggle('opacity-50', isClosed);
                            tmSendResponse.classList.toggle('pointer-events-none', isClosed);
                            tmSendResponse.classList.toggle('cursor-not-allowed', isClosed);
                        }
                        // Toggle menu options: show "Assign to a Staff", hide "Hide Forward Controls"
                        if (tmOptionAssign) tmOptionAssign.classList.remove('hidden');
                        if (tmOptionHideForward) tmOptionHideForward.classList.add('hidden');
                    }
                }
            });
        }

        // Forward via select + apply
        if (tmForwardApply && tmForwardSelect) {
            tmForwardApply.addEventListener('click', async () => {
                if (!currentTicketId) return;
                if (!tmForwardSelect.value) {
                    Swal.fire({
                        icon: 'warning',
                        title: 'Selection Required',
                        text: 'Please choose a user to forward to.',
                        confirmButtonText: 'OK'
                    });
                    return;
                }
                const userId = tmForwardSelect.value;
                const originalButtonText = tmForwardApply.textContent;
                try {
                    tmForwardApply.disabled = true;
                    tmForwardApply.innerHTML =
                        '<svg class="animate-spin h-4 w-4 mr-2" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg> Processing...';
                    const res = await fetch(`${forwardBase}/${currentTicketId}/forward`, {
                        method: 'POST',
                        headers: {
                            'Accept': 'application/json',
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': csrfToken
                        },
                        credentials: 'same-origin',
                        body: JSON.stringify({
                            user_id: userId
                        })
                    });
                    console.log('Forward request sent to:',
                        `${forwardBase}/${currentTicketId}/forward`);
                    console.log('Response status:', res.status, res.statusText);
                    let forwardResp = null;
                    if (res.ok) {
                        forwardResp = await res.json();
                        console.log('Forward successful:', forwardResp);
                        Swal.fire({
                            icon: 'success',
                            title: currentIsAssigning ? 'Ticket Assigned' : 'Ticket Forwarded',
                            text: currentIsAssigning ?
                                'Ticket has been assigned successfully!' :
                                'Ticket has been forwarded successfully!',
                            timer: 3000,
                            timerProgressBar: true,
                            showConfirmButton: false,
                            position: 'top-end',
                            toast: true
                        });
                        closeModal();
                        // Refresh dashboard data
                        if (typeof refreshAdminData === 'function') refreshAdminData();
                    } else {
                        const errorText = await res.text();
                        console.error('Forward failed', res.status, errorText);
                        Swal.fire({
                            icon: 'error',
                            title: currentIsAssigning ? 'Assign Failed' : 'Forward Failed',
                            text: (currentIsAssigning ? 'Failed to assign ticket. ' :
                                    'Failed to forward ticket. ') +
                                'Please try again. Error: ' + res.status + ' ' + res.statusText,
                            confirmButtonText: 'OK'
                        });
                    }
                    // Refresh dashboard data if the response indicates it
                    if (forwardResp && forwardResp.refresh_dashboard) {
                        if (typeof refreshAdminData === 'function') refreshAdminData();
                    }
                } catch (err) {
                    console.error('Forward error', err);
                    alert('Network error during forward.');
                } finally {
                    tmForwardApply.disabled = false;
                    tmForwardApply.innerHTML = originalButtonText;
                }
            });
        }

        // Lightbox event listeners
        const lightboxCloseBtn = document.getElementById('lightboxClose');
        const lightboxPrevBtn = document.getElementById('lightboxPrev');
        const lightboxNextBtn = document.getElementById('lightboxNext');
        const lightboxEl = document.getElementById('imageLightbox');

        if (lightboxCloseBtn) lightboxCloseBtn.addEventListener('click', closeLightbox);
        if (lightboxPrevBtn) lightboxPrevBtn.addEventListener('click', prevImage);
        if (lightboxNextBtn) lightboxNextBtn.addEventListener('click', nextImage);

        // Close lightbox on background click
        if (lightboxEl) {
            lightboxEl.addEventListener('click', (e) => {
                if (e.target === lightboxEl) closeLightbox();
            });
        }

        // Keyboard navigation for lightbox
        document.addEventListener('keydown', (e) => {
            if (lightboxEl && !lightboxEl.classList.contains('hidden')) {
                if (e.key === 'Escape') closeLightbox();
                else if (e.key === 'ArrowLeft') prevImage();
                else if (e.key === 'ArrowRight') nextImage();
            }
        });

        // Send response (email via backend)
        if (tmSendResponse && tmResponse) {
            tmSendResponse.addEventListener('click', async () => {
                const msg = tmResponse.value.trim();
                if (!msg) {
                    Swal.fire({
                        icon: 'warning',
                        title: 'Message Required',
                        text: 'Please enter a response message.',
                        confirmButtonText: 'OK'
                    });
                    return;
                }
                if (!currentTicketId) {
                    Swal.fire({
                        icon: 'error',
                        title: 'No Ticket Selected',
                        text: 'No ticket selected.',
                        confirmButtonText: 'OK'
                    });
                    return;
                }
                try {
                    tmSendResponse.disabled = true;
                    tmSendResponse.classList.add('opacity-50', 'pointer-events-none');
                    const res = await fetch(`${forwardBase}/${currentTicketId}/respond`, {
                        method: 'POST',
                        headers: {
                            'Accept': 'application/json',
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': csrfToken
                        },
                        credentials: 'same-origin',
                        body: JSON.stringify({
                            message: msg
                        })
                    });
                    if (res.ok) {
                        Swal.fire({
                            icon: 'success',
                            title: 'Response Sent',
                            text: 'Response email sent successfully!',
                            timer: 3000,
                            timerProgressBar: true,
                            showConfirmButton: false,
                            position: 'top-end',
                            toast: true
                        });
                        tmResponse.value = '';
                        closeModal();
                        // Refresh dashboard data
                        if (typeof refreshAdminData === 'function') refreshAdminData();
                    } else {
                        const txt = await res.text();
                        console.error('Send response failed', txt);
                        Swal.fire({
                            icon: 'error',
                            title: 'Failed to Send Response',
                            text: 'Failed to send response. Please check mail configuration.',
                            confirmButtonText: 'OK'
                        });
                    }
                } catch (err) {
                    console.error('Send response error', err);
                    alert('Network error while sending response.');
                } finally {
                    tmSendResponse.disabled = false;
                    tmSendResponse.classList.remove('opacity-50', 'pointer-events-none');
                }
            });
        }
    })();
</script>

