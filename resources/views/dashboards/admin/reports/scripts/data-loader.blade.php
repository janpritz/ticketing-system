<script>
    (function() {
        window.loadClosedTicketsTrendData = function(days) {
            fetch(`{{ route('admin.reports.closed-tickets-trend-data') }}?days=${days}`)
                .then(r => r.json()).then(data => initClosedTicketsTrendChart(data));
        };

        window.loadDynamicData = function(days) {
            fetch(`{{ route('admin.reports.dynamic-data') }}?days=${days}`)
                .then(r => r.json())
                .then(data => {
                    updateTicketsSolvedTable(data.ticketsSolved);
                    document.getElementById('avgResolutionTimeValue').textContent = data.avgResolutionTime;
                    document.getElementById('totalTicketsValue').textContent = data.totalTickets;
                    initTicketsByOrgTable(data.ticketsByOrg);
                    initTopTicketDriversChart(data.topTicketDrivers);
                });
        };

        window.initTicketsByOrgTable = function(data) {
            const tbody = document.getElementById('ticketsByOrgBody');
            if (!tbody) return;
            tbody.innerHTML = data.map(item => `
            <tr class="hover:bg-gray-50 cursor-pointer" onclick="fetchForwards(${item.id})">
                <td class="py-2 pl-3 pr-2 align-top text-gray-900">${item.name || 'Unknown'}</td>
                <td class="px-2 py-2 align-top font-medium text-slate-900">${number_format(item.count || 0)}</td>
            </tr>`).join('');
        };

        window.fetchForwards = function(staffId) {
            const days = document.getElementById('timeRangeSelect').value || 30;
            fetch(`{{ url('/admin/reports/forwards') }}/${staffId}?days=${days}`)
                .then(r => r.json()).then(payload => showForwardsModal(payload));
        };

        // Initialize Page
        document.addEventListener('DOMContentLoaded', () => {
            initClosedTicketsTrendChart(@json($closedTicketsTrendData));
            initWorkloadDistributionChart(@json($workloadDistribution ?? []));
            initTopTicketDriversChart(@json($topTicketDrivers ?? []));
            initTicketsByOrgTable(@json($ticketsByOrg ?? []));
            updateTicketsSolvedTitle(document.getElementById('timeRangeSelect').value || 30);
        });

        document.getElementById('timeRangeSelect').addEventListener('change', (e) => {
            const days = e.target.value;
            loadClosedTicketsTrendData(days);
            loadDynamicData(days);
            updateTicketsSolvedTitle(days);
        });
    })();
</script>
