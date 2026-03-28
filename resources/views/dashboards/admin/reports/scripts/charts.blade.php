<script>
    (function() {
        let closedTicketsTrendChart, workloadDistributionChart, topTicketDriversChart;

        window.initClosedTicketsTrendChart = function(data) {
            const ctx = document.getElementById('closedTicketsTrendChart');
            if (!ctx) return;
            if (closedTicketsTrendChart) closedTicketsTrendChart.destroy();

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
                    }
                }
            });
        };

        window.initWorkloadDistributionChart = function(data) {
            const ctx = document.getElementById('workloadDistributionChart');
            if (!ctx || workloadDistributionChart) {
                if (workloadDistributionChart) workloadDistributionChart.destroy();
            }

            const palette = ['#6366F1', '#10B981', '#F59E0B', '#EF4444', '#06B6D4', '#84CC16'];
            workloadDistributionChart = new Chart(ctx, {
                type: 'doughnut',
                data: {
                    labels: data.map(item => item.name),
                    datasets: [{
                        data: data.map(item => item.percentage),
                        backgroundColor: data.map((_, i) => palette[i % palette.length]),
                        borderWidth: 1
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    cutout: '60%'
                }
            });
        };

        window.initTopTicketDriversChart = function(data) {
            const ctx = document.getElementById('topTicketDriversChart');
            if (!ctx) return;
            if (topTicketDriversChart) topTicketDriversChart.destroy();

            const palette = ['#6366F1', '#10B981', '#F59E0B', '#EF4444'];
            topTicketDriversChart = new Chart(ctx, {
                type: 'doughnut',
                data: {
                    labels: data.map(item => item.label || 'Unknown'),
                    datasets: [{
                        data: data.map(item => item.count || 0),
                        backgroundColor: data.map((_, i) => palette[i % palette.length]),
                        borderWidth: 1
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    cutout: '60%'
                }
            });
        };
    })();
</script>
