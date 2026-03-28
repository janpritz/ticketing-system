<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

<script>
    (function() {

        let closedTicketsTrendChart, workloadDistributionChart, topTicketDriversChart;



        function updateTicketsSolvedTitle(days) {

            const el = document.getElementById('ticketsSolvedTitle');

            if (!el) return;

            const n = parseInt(days, 10);

            el.textContent = `Tickets Solved (${Number.isFinite(n) ? n : 30} Days)`;

        }



        function number_format(num) {

            return num.toLocaleString();

        }



        function updateTicketsSolvedTable(items) {

            const tbody = document.getElementById('ticketsSolvedBody');

            if (!tbody) return;



            tbody.innerHTML = '';

            if (!Array.isArray(items) || items.length === 0) {

                tbody.innerHTML =

                    '<tr><td colspan="2" class="px-3 py-6 text-center text-sm text-gray-500">No solved tickets.</td></tr>';

                return;

            }



            items.forEach(item => {

                const tr = document.createElement('tr');

                tr.className = 'hover:bg-gray-50';

                tr.innerHTML = `

        <td class="py-2 pl-3 pr-2 align-top text-gray-900">${item.name || 'Unknown'}</td>

        <td class="px-2 py-2 align-top font-medium text-slate-900">${number_format(item.count || 0)}</td>

      `;

                tbody.appendChild(tr);

            });

        }



        function initClosedTicketsTrendChart(data) {

            const ctx = document.getElementById('closedTicketsTrendChart');

            if (!ctx) return;



            if (closedTicketsTrendChart) {

                closedTicketsTrendChart.destroy();

            }



            closedTicketsTrendChart = new Chart(ctx, {

                type: 'line',

                data: {

                    labels: data.labels,

                    datasets: [{

                        label: 'Closed Tickets',

                        data: data.data,

                        borderColor: 'rgb(16, 185, 129)',

                        backgroundColor: 'rgba(16, 185, 129, 0.12)',

                        tension: 0.4,

                        fill: true

                    }]

                },

                options: {

                    responsive: true,

                    maintainAspectRatio: false,

                    plugins: {

                        legend: {

                            display: false

                        }

                    },

                    scales: {

                        y: {

                            beginAtZero: true,

                            ticks: {

                                stepSize: 1

                            }

                        }

                    }

                }

            });

        }





        function initWorkloadDistributionChart(data) {

            const ctx = document.getElementById('workloadDistributionChart');

            if (!ctx) return;



            if (workloadDistributionChart) {

                workloadDistributionChart.destroy();

            }



            const labels = data.map(item => item.name);

            const values = data.map(item => item.percentage);



            const palette = ['#6366F1', '#10B981', '#F59E0B', '#EF4444', '#06B6D4', '#84CC16', '#F472B6',

                '#FB7185'
            ];

            const colors = labels.map((_, i) => palette[i % palette.length]);



            workloadDistributionChart = new Chart(ctx, {

                type: 'doughnut',

                data: {

                    labels: labels,

                    datasets: [{

                        data: values,

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



        function initTopTicketDriversChart(data) {

            const ctx = document.getElementById('topTicketDriversChart');

            if (!ctx) return;



            if (topTicketDriversChart) {

                topTicketDriversChart.destroy();

            }



            const labels = data.map(item => item.label || 'Unknown');

            const values = data.map(item => item.count || 0);



            const palette = ['#6366F1', '#10B981', '#F59E0B', '#EF4444', '#06B6D4', '#84CC16', '#F472B6',

                '#FB7185'
            ];

            const colors = labels.map((_, i) => palette[i % palette.length]);



            topTicketDriversChart = new Chart(ctx, {

                type: 'doughnut',

                data: {

                    labels: labels,

                    datasets: [{

                        data: values,

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



        function initTicketsByOrgTable(data) {

            const tbody = document.getElementById('ticketsByOrgBody');

            if (!tbody) return;

            tbody.innerHTML = '';

            if (!data || data.length === 0) {

                tbody.innerHTML =

                    '<tr><td colspan="2" class="px-3 py-6 text-center text-sm text-gray-500">No data available.</td></tr>';

                return;

            }

            data.forEach(item => {

                const tr = document.createElement('tr');

                tr.className = 'hover:bg-gray-50';

                tr.dataset.staffId = item.id || '';

                tr.style.cursor = 'pointer';

                tr.innerHTML = `

        <td class="py-2 pl-3 pr-2 align-top text-gray-900">${item.name || 'Unknown'}</td>

        <td class="px-2 py-2 align-top font-medium text-slate-900">${number_format(item.count || 0)}</td>

      `;

                tr.addEventListener('click', function() {

                    const staffId = this.dataset.staffId;

                    if (!staffId) return;

                    const days = document.getElementById('timeRangeSelect').value || 30;

                    fetch(`{{ url('/admin/reports/forwards') }}/${staffId}?days=${days}`)

                        .then(r => r.json())

                        .then(payload => {

                            showForwardsModal(payload);

                        }).catch(err => console.error(err));

                });

                tbody.appendChild(tr);

            });

        }



        // Modal for showing forwards breakdown

        function showForwardsModal(payload) {

            let modal = document.getElementById('forwardsModal');

            if (!modal) return;

            const title = modal.querySelector('.modal-title');

            const body = modal.querySelector('.modal-body');

            title.textContent = `Forwards by: ${payload.forwarder || 'Unknown'}`;

            if (!payload.recipients || payload.recipients.length === 0) {

                body.innerHTML =

                    '<p class="text-sm text-gray-500">No forwards found for this staff in the selected period.</p>';

            } else {

                let html = '<div class="space-y-4">';

                payload.recipients.forEach(r => {

                    // Build questions dropdown (don't display raw ticket IDs)

                    const esc = (s) => s == null ? '' : String(s).replace(/&/g, '&amp;').replace(/</g,

                        '&lt;').replace(/>/g, '&gt;');

                    let questionsHtml = '';

                    if (Array.isArray(r.tickets) && r.tickets.length) {

                        questionsHtml = `<details class="mt-3 bg-gray-50 rounded-lg p-2">

            <summary class="flex items-center justify-between cursor-pointer text-sm text-gray-700">View questions <svg class="h-4 w-4 text-gray-400" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg></summary>

            <ul class="mt-2 space-y-2">`;

                        r.tickets.forEach(t => {

                            const q = esc(t.question || 'No question available');

                            questionsHtml +=

                                `<li class="text-sm text-gray-700 bg-white px-3 py-2 rounded">${q}</li>`;

                        });

                        questionsHtml += '</ul></details>';

                    } else {

                        questionsHtml =

                            '<div class="text-sm text-gray-500 mt-2">No questions available</div>';

                    }



                    html += `

          <div class="p-4 bg-white shadow-sm rounded-lg">

            <div class="flex items-start justify-between gap-4">

              <div class="min-w-0">

                <div class="text-sm font-medium text-gray-900 truncate">${r.name}</div>

              </div>

              <div class="flex-shrink-0">

                <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-gray-100 text-gray-700">${r.count}</span>

              </div>

            </div>

            ${questionsHtml}

          </div>

        `;

                });

                html += '</div>';

                body.innerHTML = html;

            }

            modal.classList.remove('hidden');

        }



        // Close modal handler

        function closeForwardsModal() {

            const modal = document.getElementById('forwardsModal');

            if (modal) modal.classList.add('hidden');

        }



        // Local modal backdrop / close handling (ensures consistent behavior on this page)

        document.addEventListener('click', function(e) {

            if (!e.target) return;

            if (e.target.closest && (e.target.closest('[data-modal-backdrop]') || e.target.closest(

                    '[data-modal-close]'))) {

                const modal = document.getElementById('forwardsModal');

                if (modal) modal.classList.add('hidden');

            }

        });



        function loadClosedTicketsTrendData(days) {

            fetch(`{{ route('admin.reports.closed-tickets-trend-data') }}?days=${days}`)

                .then(response => response.json())

                .then(data => {

                    initClosedTicketsTrendChart(data);

                })

                .catch(error => {

                    console.error('Error loading chart data:', error);

                });

        }



        function loadDynamicData(days) {

            fetch(`{{ route('admin.reports.dynamic-data') }}?days=${days}`)

                .then(response => response.json())

                .then(data => {

                    // Update the Tickets Solved table first so it refreshes even if other widgets error.

                    updateTicketsSolvedTable(data.ticketsSolved);



                    const avgEl = document.getElementById('avgResolutionTimeValue');

                    if (avgEl) avgEl.textContent = data.avgResolutionTime;



                    const totalEl = document.getElementById('totalTicketsValue');

                    if (totalEl) totalEl.textContent = data.totalTickets;



                    // update other widgets

                    initTicketsByOrgTable(data.ticketsByOrg);

                    initTopTicketDriversChart(data.topTicketDrivers);

                })

                .catch(error => {

                    console.error('Error loading dynamic data:', error);

                });

        }



        // Initialize with default data

        const initialClosedTicketsTrendData = @json($closedTicketsTrendData);

        initClosedTicketsTrendChart(initialClosedTicketsTrendData);





        const initialWorkloadData = @json($workloadDistribution ?? []);

        initWorkloadDistributionChart(initialWorkloadData);



        const initialTopTicketDriversData = @json($topTicketDrivers ?? []);

        initTopTicketDriversChart(initialTopTicketDriversData);



        const initialTicketsByOrgData = @json($ticketsByOrg ?? []);

        initTicketsByOrgTable(initialTicketsByOrgData);



        // Sync the Tickets Solved title with the current range (default is 30)

        updateTicketsSolvedTitle(document.getElementById('timeRangeSelect').value || 30);



        // Handle time range changes

        document.getElementById('timeRangeSelect').addEventListener('change', function(e) {

            const days = e.target.value;

            loadClosedTicketsTrendData(days);

            loadDynamicData(days);

            updateTicketsSolvedTitle(days);

        });

    })();
</script>
