<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.1/dist/chart.umd.min.js"></script>
<script>
    (function() {
        // Ticket Status Chart
        const statusEl = document.getElementById('ticketStatusChart');
        if (statusEl) {
            const resolved = {{ $performanceMetrics['resolved_tickets'] }};
            const total = {{ $performanceMetrics['total_tickets'] }};
            const open = total - resolved;

            new Chart(statusEl, {
                type: 'doughnut',
                data: {
                    labels: ['Resolved', 'Open'],
                    datasets: [{
                        data: [resolved, open],
                        backgroundColor: ['#10B981', '#EF4444'],
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

        // FAQ Chart
        // const faqEl = document.getElementById('faqChart');
        // if (faqEl) {
        //     const processed = {{ $faqAnalysis['processed_faqs'] }};
        //     const total = {{ $faqAnalysis['total_faqs'] }};
        //     const unprocessed = total - processed;

        //     new Chart(faqEl, {
        //         type: 'bar',
        //         data: {
        //             labels: ['Processed', 'Total Available'],
        //             datasets: [{
        //                 label: 'FAQs',
        //                 data: [processed, total],
        //                 backgroundColor: ['#3B82F6', '#E5E7EB'],
        //                 borderRadius: 6,
        //                 maxBarThickness: 40
        //             }]
        //         },
        //         options: {
        //             responsive: true,
        //             maintainAspectRatio: false,
        //             scales: {
        //                 x: { grid: { display: false } },
        //                 y: { beginAtZero: true, ticks: { precision: 0 }, grid: { color: '#f1f5f9' } }
        //             },
        //             plugins: { legend: { display: false } }
        //         }
        //     });
        // }

        // Weekly Chart Animation
        const weeklyChartEl = document.getElementById('weeklyChart');
        if (weeklyChartEl) {
            // Initialize weekly bar heights from server-rendered data-height (and ensure minimum visible bar)
            function applyInitialWeeklyHeights() {
                weeklyChartEl.querySelectorAll('.weekly-bar').forEach(el => {
                    const v = Number(el.getAttribute('data-height') || 0);
                    const countVal = Number(el.getAttribute('data-count') || 0);
                    const pct = v > 0 ? v : (countVal > 0 ? 4 : 0); // show a tiny bar when count > 0
                    el.style.height = (Number.isFinite(pct) ? pct : 0) + '%';
                });
            }
            applyInitialWeeklyHeights();
            // Re-apply after first paint in case styles load late
            if (typeof requestAnimationFrame === 'function') {
                requestAnimationFrame(applyInitialWeeklyHeights);
            }
        }

        // Modal functions
        window.openModal = function(modalId) {
            document.getElementById(modalId).classList.remove('hidden');
        };

        window.closeModal = function(modalId) {
            document.getElementById(modalId).classList.add('hidden');
        };

        // Close modal when clicking outside
        document.addEventListener('click', function(event) {
            if (event.target.classList.contains('bg-black/60')) {
                event.target.parentElement.classList.add('hidden');
            }
        });

        // Time range selector (reload page with days param)
        const timeRangeSelect = document.getElementById('timeRangeSelect');
        if (timeRangeSelect) {
            timeRangeSelect.addEventListener('change', function(e) {
                const days = e.target.value;
                const url = new URL(window.location.href);
                url.searchParams.set('days', days);
                window.location.href = url.toString();
            });
        }

        // Resolution Time Chart for modal
        const resolutionTimeEl = document.getElementById('resolutionTimeChart');
        if (resolutionTimeEl) {
            @php
                $resolvedTickets = \App\Models\Ticket::where('staff_id', auth()->id())
                    ->where('status', 'Closed')
                    ->get();
                $timeRanges = ['0-1h' => 0, '1-2h' => 0, '2-4h' => 0, '4-8h' => 0, '8h+' => 0];
                foreach ($resolvedTickets as $ticket) {
                    $hours = $ticket->updated_at->diffInHours($ticket->created_at);
                    if ($hours <= 1) {
                        $timeRanges['0-1h']++;
                    } elseif ($hours <= 2) {
                        $timeRanges['1-2h']++;
                    } elseif ($hours <= 4) {
                        $timeRanges['2-4h']++;
                    } elseif ($hours <= 8) {
                        $timeRanges['4-8h']++;
                    } else {
                        $timeRanges['8h+']++;
                    }
                }
            @endphp

            new Chart(resolutionTimeEl, {
                type: 'bar',
                data: {
                    labels: Object.keys({!! json_encode($timeRanges) !!}),
                    datasets: [{
                        label: 'Tickets',
                        data: Object.values({!! json_encode($timeRanges) !!}),
                        backgroundColor: '#3B82F6',
                        borderRadius: 4
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    scales: {
                        y: {
                            beginAtZero: true,
                            ticks: {
                                precision: 0
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

        // Resolution Rate Chart for modal
        const resolutionRateEl = document.getElementById('resolutionRateChart');
        if (resolutionRateEl) {
            const resolved = {{ $performanceMetrics['resolved_tickets'] }};
            const total = {{ $performanceMetrics['total_tickets'] }};
            const open = total - resolved;

            new Chart(resolutionRateEl, {
                type: 'pie',
                data: {
                    labels: ['Resolved', 'Open'],
                    datasets: [{
                        data: [resolved, open],
                        backgroundColor: ['#10B981', '#EF4444']
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: {
                            position: 'bottom'
                        }
                    }
                }
            });
        }
    })();
</script>
