<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script>
        (function() {
            const state = document.createElement('div');
            state.id = 'admin-faqs-state';
            state.className = 'hidden';
            state.setAttribute('data-list-url', "{{ route('admin.faqs.list') ?? route('admin.faqs.index') }}");
            state.setAttribute('data-update-status-url', "{{ route('admin.faqs.update-status') }}");
            document.body.appendChild(state);

            const LIST_URL = state.getAttribute('data-list-url');
            const UPDATE_STATUS_URL = state.getAttribute('data-update-status-url');
            const csrf = document.querySelector('meta[name="csrf-token"]').getAttribute('content');

            const faqsTbody = document.getElementById('faqsTbody');
            const faqsPagination = document.getElementById('faqsPagination');

            let currentPage = 1;
            let faqsMap = new Map();

            function escapeHtml(s) {
                if (s == null) return '';
                return String(s).replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;').replace(/"/g,
                    '&quot;').replace(/'/g, "&#039;");
            }

            function getStatusClass(status) {
                const classes = {
                    'publish': 'text-green-700 bg-green-50 ring-green-600/20',
                    'pending': 'text-yellow-700 bg-yellow-50 ring-yellow-600/20',
                    'unpublish': 'text-red-700 bg-red-50 ring-red-600/20'
                };
                return classes[status] || 'text-slate-700 bg-slate-50 ring-slate-600/20';
            }

            async function fetchList(page = 1) {
                currentPage = page;
                try {
                    const qEl = document.getElementById('q');
                    const qMobileEl = document.getElementById('q_mobile');
                    const qVal = (qEl && qEl.value.trim()) ? qEl.value.trim() : (qMobileEl && qMobileEl.value
                    .trim() ? qMobileEl.value.trim() : '');

                    const statusEl = document.getElementById('filterStatus');
                    const perEl = document.getElementById('filterPerPage');

                    const statusVal = statusEl ? statusEl.value : 'pending';
                    const per = perEl ? perEl.value : '25';

                    const sep = LIST_URL.includes('?') ? '&' : '?';
                    let url =
                        `${LIST_URL}${sep}page=${page}&per_page=${encodeURIComponent(per)}&status=${encodeURIComponent(statusVal)}`;

                    if (qVal) url += '&search=' + encodeURIComponent(qVal);

                    const res = await fetch(url, {
                        headers: {
                            'X-Requested-With': 'XMLHttpRequest'
                        }
                    });
                    if (!res.ok) throw new Error('Failed to load FAQs');
                    const json = await res.json();
                    renderTable(json.items || []);
                    renderPagination(json.meta || {});
                } catch (err) {
                    console.error('Error loading FAQs:', err);
                    faqsTbody.innerHTML =
                        '<tr><td colspan="5" class="px-5 py-6 text-center text-sm text-red-600">Error loading FAQs</td></tr>';
                }
            }

            function renderTable(items) {
                faqsMap = new Map(items.map(f => [String(f.id), f]));
                if (!items.length) {
                    faqsTbody.innerHTML =
                        '<tr><td colspan="4" class="px-5 py-10 text-center text-sm text-gray-500">No RAQs found.</td></tr>';
                    return;
                }
                faqsTbody.innerHTML = items.map(f => {
                    const actionButtons = getActionButtons(f);
                    return `
                <tr class="hover:bg-gray-50">
                    <td class="py-4 pl-5 pr-3">${escapeHtml((f.general_topic || '').slice(0, 50))}</td>
                    <td class="px-3 py-4">${escapeHtml((f.suggested_q || '').slice(0, 80))}</td>
                    <td class="px-3 py-4">${escapeHtml((f.suggested_a || '').slice(0, 100))}</td>
                    <td class="px-3 py-4 space-x-2">${actionButtons}</td>
                </tr>
            `;
                }).join('');
            }

            function getActionButtons(faq) {
                if (faq.status === 'pending') {
                    return `
                <div class="flex gap-2">
                    <button onclick="updateFAQStatus(${faq.id}, 'publish')" class="px-3 py-1 bg-blue-600 text-white text-xs rounded hover:bg-blue-700">Publish</button>
                    <button onclick="updateFAQStatus(${faq.id}, 'unpublish')" class="px-3 py-1 bg-gray-600 text-white text-xs rounded hover:bg-gray-700">Reject</button>
                </div>
            `;
                } else if (faq.status === 'publish') {
                    return `<button onclick="updateFAQStatus(${faq.id}, 'unpublish')" class="px-3 py-1 bg-gray-600 text-white text-xs rounded hover:bg-gray-700">Unpublish</button>`;
                } else if (faq.status === 'unpublish') {
                    return `<button onclick="updateFAQStatus(${faq.id}, 'publish')" class="px-3 py-1 bg-blue-600 text-white text-xs rounded hover:bg-blue-700">Publish</button>`;
                }
                return '';
            }

            function renderPagination(meta) {
                if (!meta || !meta.total) {
                    faqsPagination.innerHTML = '';
                    return;
                }
                const total = meta.total || 0;
                const per = meta.per_page || 25;
                const current = meta.current_page || 1;
                const last = meta.last_page || 1;

                const delta = 2;
                const left = Math.max(1, current - delta);
                const right = Math.min(last, current + delta);
                const pages = [];
                for (let i = left; i <= right; i++) pages.push(i);

                const prevDisabled = current <= 1;
                const nextDisabled = current >= last;

                faqsPagination.innerHTML = `
            <div class="flex items-center gap-3">
                <div class="text-sm text-slate-600">Showing ${per} per page — ${total} total</div>
            </div>
            <div class="flex items-center gap-2">
                <button ${prevDisabled ? 'disabled' : ''} data-page="${current-1}" class="pagerBtn rounded-md border border-gray-200 bg-white px-3 py-1 text-sm ${prevDisabled ? 'opacity-50' : 'hover:bg-gray-50'}">Prev</button>
                ${pages.map(p => `<button data-page="${p}" class="pagerBtn rounded-md ${p===current ? 'bg-blue-600 text-white' : 'border border-gray-200 bg-white text-sm hover:bg-gray-50'} px-3 py-1">${p}</button>`).join('')}
                <button ${nextDisabled ? 'disabled' : ''} data-page="${current+1}" class="pagerBtn rounded-md border border-gray-200 bg-white px-3 py-1 text-sm ${nextDisabled ? 'opacity-50' : 'hover:bg-gray-50'}">Next</button>
            </div>
        `;

                faqsPagination.querySelectorAll('.pagerBtn').forEach(b => b.addEventListener('click', (e) => {
                    const p = parseInt(b.getAttribute('data-page') || '1', 10);
                    if (!isNaN(p)) fetchList(p);
                }));
            }

            // Search handlers
            const searchBtn = document.getElementById('searchBtn');
            const searchBtnMobile = document.getElementById('searchBtnMobile');
            const qInput = document.getElementById('q');
            const qMobileInput = document.getElementById('q_mobile');

            if (searchBtn) searchBtn.addEventListener('click', () => fetchList(1));
            if (qInput) qInput.addEventListener('keyup', (e) => {
                if (e.key === 'Enter') fetchList(1);
            });
            if (searchBtnMobile) searchBtnMobile.addEventListener('click', () => {
                if (qMobileInput && qInput) qInput.value = qMobileInput.value;
                fetchList(1);
            });

            // Filter drawer handlers
            const openFiltersBtn = document.getElementById('openFiltersBtn');
            const closeFiltersBtn = document.getElementById('closeFiltersBtn');
            const applyFiltersBtn = document.getElementById('applyFiltersBtn');
            const resetFiltersBtn = document.getElementById('resetFiltersBtn');
            const drawer = document.getElementById('faqsBottomDrawer');
            const overlay = document.getElementById('faqsDrawerOverlay');

            if (openFiltersBtn) {
                openFiltersBtn.addEventListener('click', (e) => {
                    e.stopPropagation();
                    const isOpen = !drawer.classList.contains('translate-y-full');
                    if (isOpen) {
                        drawer.classList.add('translate-y-full');
                        overlay.classList.add('hidden');
                    } else {
                        drawer.classList.remove('translate-y-full');
                        overlay.classList.remove('hidden');
                    }
                });
            }

            if (closeFiltersBtn) {
                closeFiltersBtn.addEventListener('click', () => {
                    drawer.classList.add('translate-y-full');
                    overlay.classList.add('hidden');
                });
            }

            if (applyFiltersBtn) {
                applyFiltersBtn.addEventListener('click', () => {
                    drawer.classList.add('translate-y-full');
                    overlay.classList.add('hidden');
                    fetchList(1);
                });
            }

            if (resetFiltersBtn) {
                resetFiltersBtn.addEventListener('click', () => {
                    const statusEl = document.getElementById('filterStatus');
                    const perEl = document.getElementById('filterPerPage');
                    if (statusEl) statusEl.value = 'pending';
                    if (perEl) perEl.value = '25';
                    if (qInput) qInput.value = '';
                    if (qMobileInput) qMobileInput.value = '';
                    fetchList(1);
                });
            }

            if (overlay) {
                overlay.addEventListener('click', () => {
                    drawer.classList.add('translate-y-full');
                    overlay.classList.add('hidden');
                });
            }

            document.addEventListener('keydown', (e) => {
                if (e.key === 'Escape' && !drawer.classList.contains('translate-y-full')) {
                    drawer.classList.add('translate-y-full');
                    overlay.classList.add('hidden');
                }
            });

            // Global update function
            window.updateFAQStatus = function(faqId, status) {
                const statusText = status.charAt(0).toUpperCase() + status.slice(1);
                Swal.fire({
                    title: 'Confirm Action',
                    text: `Are you sure you want to ${status} this FAQ?`,
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: status === 'publish' ? '#10b981' : '#ef4444',
                    cancelButtonColor: '#6b7280',
                    confirmButtonText: statusText,
                    cancelButtonText: 'Cancel'
                }).then((result) => {
                    if (result.isConfirmed) {
                        fetch(UPDATE_STATUS_URL, {
                                method: 'POST',
                                headers: {
                                    'Content-Type': 'application/json',
                                    'X-CSRF-TOKEN': csrf
                                },
                                body: JSON.stringify({
                                    id: faqId,
                                    status: status
                                })
                            })
                            .then(response => response.json())
                            .then(data => {
                                if (data.success) {
                                    Swal.fire({
                                        title: 'Success!',
                                        text: `FAQ ${status} successfully!`,
                                        icon: 'success',
                                        confirmButtonColor: '#3b82f6'
                                    }).then(() => {
                                        fetchList(currentPage);
                                    });
                                } else {
                                    Swal.fire({
                                        title: 'Error!',
                                        text: data.message || 'Failed to update FAQ.',
                                        icon: 'error',
                                        confirmButtonColor: '#ef4444'
                                    });
                                }
                            })
                            .catch(error => {
                                Swal.fire({
                                    title: 'Error!',
                                    text: 'Error: ' + error.message,
                                    icon: 'error',
                                    confirmButtonColor: '#ef4444'
                                });
                            });
                    }
                });
            };

            // Modal functions
            window.openAnalyzeModal = function() {
                document.getElementById('analyze-modal').classList.remove('hidden');
                document.body.classList.add('overflow-hidden');

                // Reset modal state
                document.getElementById('progress-section').classList.add('hidden');
                document.getElementById('results-section').classList.add('hidden');
                document.getElementById('analyzeIcon').classList.remove('hidden');
                document.getElementById('analyzeSpinner').classList.add('hidden');
                document.getElementById('analyzeText').textContent = 'Start Analysis';

                // Check if there are unprocessed tickets
                const unprocessedCount = {{ $unprocessedTickets ?? 0 }};
                const analyzeBtn = document.getElementById('analyze-btn');
                if (unprocessedCount === 0) {
                    analyzeBtn.disabled = true;
                    document.getElementById('analyzeText').textContent = 'No Tickets to Analyze';
                } else {
                    analyzeBtn.disabled = false;
                }
            };

            window.closeAnalyzeModal = function() {
                document.getElementById('analyze-modal').classList.add('hidden');
                document.body.classList.remove('overflow-hidden');
            };

            window.startAnalysis = function() {
                const btn = document.getElementById('analyze-btn');
                const icon = document.getElementById('analyzeIcon');
                const spinner = document.getElementById('analyzeSpinner');
                const text = document.getElementById('analyzeText');

                btn.disabled = true;
                icon.classList.add('hidden');
                spinner.classList.remove('hidden');
                text.textContent = 'Analyzing...';

                document.getElementById('progress-section').classList.remove('hidden');
                document.getElementById('results-section').classList.add('hidden');

                // Call the backend API to process analysis
                fetch('{{ route('admin.faqs.process-analysis') }}', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content || ''
                        },
                        body: JSON.stringify({})
                    })
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            window.completeAnalysis(data);
                        } else {
                            window.showError(data.message || 'Analysis failed');
                            btn.disabled = false;
                            spinner.classList.add('hidden');
                            icon.classList.remove('hidden');
                            text.textContent = 'Start Analysis';
                            document.getElementById('progress-section').classList.add('hidden');
                        }
                    })
                    .catch(error => {
                        window.showError('Error: ' + error.message);
                        btn.disabled = false;
                        spinner.classList.add('hidden');
                        icon.classList.remove('hidden');
                        text.textContent = 'Start Analysis';
                        document.getElementById('progress-section').classList.add('hidden');
                    });

                // Simulate progress updates
                let progress = 0;
                const interval = setInterval(() => {
                    progress += Math.random() * 25;
                    if (progress > 90) progress = 90;

                    document.getElementById('progress-bar').style.width = progress + '%';
                    document.getElementById('progress-text').textContent = 'Processing tickets... ' + Math
                        .round(progress) + '%';

                    if (progress >= 90) {
                        clearInterval(interval);
                    }
                }, 800);
            };

            window.completeAnalysis = function(data) {
                document.getElementById('progress-bar').style.width = '100%';
                document.getElementById('progress-text').textContent = 'Analysis complete!';

                setTimeout(() => {
                    document.getElementById('progress-section').classList.add('hidden');
                    document.getElementById('results-section').classList.remove('hidden');

                    document.getElementById('tickets-processed').textContent = data.tickets_processed || 0;
                    document.getElementById('faqs-generated').textContent = data.faqs_generated || 0;

                    window.showSuccess('Analysis completed successfully!');

                    // Reload page after 3 seconds to show new FAQs
                    setTimeout(() => {
                        location.reload();
                    }, 3000);
                }, 1000);
            };

            window.showSuccess = function(message) {
                const notification = document.getElementById('success-notification');
                notification.textContent = message;
                notification.classList.remove('hidden');
                setTimeout(() => {
                    notification.classList.add('hidden');
                }, 4000);
            };

            window.showError = function(message) {
                const notification = document.getElementById('error-notification');
                notification.textContent = message;
                notification.classList.remove('hidden');
                setTimeout(() => {
                    notification.classList.add('hidden');
                }, 4000);
            };

            // Initialize
            document.addEventListener('DOMContentLoaded', function() {
                const unprocessedCount = {{ $unprocessedTickets ?? 0 }};
                document.getElementById('unprocessed-count').textContent = unprocessedCount;

                const analyzeBtn = document.getElementById('analyze-btn');
                if (unprocessedCount === 0) {
                    analyzeBtn.disabled = true;
                }

                fetchList(1);
            });
        })();
    </script>