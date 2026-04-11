<script>
    // Mobile menu toggle
    const mobileMenuButton = document.getElementById('mobile-menu-button');
    const mobileMenu = document.getElementById('mobile-menu');
    const mobileMenuOpen = document.getElementById('mobile-menu-open');
    const mobileMenuClose = document.getElementById('mobile-menu-close');

    if (mobileMenuButton && mobileMenu) {
        mobileMenuButton.addEventListener('click', function() {
            const isExpanded = mobileMenuButton.getAttribute('aria-expanded') === 'true';
            mobileMenuButton.setAttribute('aria-expanded', !isExpanded);
            mobileMenu.classList.toggle('hidden');

            // Toggle icons
            if (mobileMenu.classList.contains('hidden')) {
                mobileMenuOpen.classList.remove('hidden');
                mobileMenuClose.classList.add('hidden');
            } else {
                mobileMenuOpen.classList.add('hidden');
                mobileMenuClose.classList.remove('hidden');
            }
        });
    }

    // User menu dropdown toggle
    const userMenuButton = document.getElementById('user-menu-button');
    const userMenu = document.getElementById('user-menu');

    if (userMenuButton && userMenu) {
        userMenuButton.addEventListener('click', function() {
            const isExpanded = userMenuButton.getAttribute('aria-expanded') === 'true';
            userMenuButton.setAttribute('aria-expanded', !isExpanded);
            userMenu.classList.toggle('hidden');
        });

        // Close dropdown when clicking outside
        document.addEventListener('click', function(event) {
            if (!userMenuButton.contains(event.target) && !userMenu.contains(event.target)) {
                userMenu.classList.add('hidden');
                userMenuButton.setAttribute('aria-expanded', 'false');
            }
        });
    }

    // Toast helper
    let toastContainer = document.getElementById('toastContainer');
    // Ensure toast container is attached directly to <body> to avoid stacking/transform issues
    try {
        if (toastContainer && toastContainer.parentElement !== document.body) {
            document.body.appendChild(toastContainer);
        }
    } catch (_) {}

    function showToast(type, message) {
        if (!toastContainer) {
            try {
                alert(message);
            } catch (_) {}
            return;
        }
        const isSuccess = type === 'success';
        const icon = isSuccess ?
            '<svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-emerald-500" viewBox="0 0 24 24" fill="currentColor"><path d="M9 12.75l-2.25-2.25-1.5 1.5L9 15.75l9-9-1.5-1.5z"/></svg>' :
            '<svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-red-500" viewBox="0 0 24 24" fill="currentColor"><path d="M12 2a10 10 0 100 20 10 10 0 000-20zm.75 5.5h-1.5v7h1.5v-7zm0 8.5h-1.5v1.5h1.5V16z"/></svg>';
        const outer = document.createElement('div');
        outer.className = 'w-80 rounded-lg border bg-white px-4 py-3 shadow ring-1 ring-black/5';
        outer.setAttribute('role', 'status');
        outer.style.pointerEvents = 'auto';
        outer.innerHTML = '<div class="flex items-start gap-2">' +
            icon +
            '<div class="flex-1 text-sm ' + (isSuccess ? 'text-emerald-800' : 'text-red-800') + '">' + String(message ||
                '') + '</div>' +
            '<button type="button" aria-label="Close" class="text-gray-400 hover:text-gray-600" data-close>&times;</button>' +
            '</div>';
        toastContainer.appendChild(outer);
        const closer = outer.querySelector('[data-close]');
        if (closer) closer.addEventListener('click', () => {
            try {
                outer.remove();
            } catch (_) {}
        });
        setTimeout(() => {
            try {
                outer.remove();
            } catch (_) {}
        }, 5000);
    }

    // ===== Overdue Tickets Toggle =====
    const overdueDropdownBtn = document.getElementById('overdueDropdownBtn');
    const overdueTicketsList = document.getElementById('overdueTicketsList');
    if (overdueDropdownBtn && overdueTicketsList) {
        overdueDropdownBtn.addEventListener('click', () => {
            overdueTicketsList.classList.toggle('hidden');
            overdueDropdownBtn.textContent = overdueTicketsList.classList.contains('hidden') ? 'View Tickets' :
                'Hide Tickets';
        });
    }

    // ===== Live auto-sync for tickets & KPIs (polling) =====
    (function() {
        const dataUrl = "{{ route('staff.dashboard.data') }}";
        const openCountEl = document.getElementById('openCount');
        const inProgressCountEl = document.getElementById('inProgressCount');
        const closedCountEl = document.getElementById('closedCount');
        const totalCountEl = document.getElementById('totalCount');
        const ticketsBodyEl = document.getElementById('ticketsBody');
        const toggleEl = document.getElementById('toggleViewAll');
        const ticketsHeadingEl = document.getElementById('ticketsHeading');
        // Pagination controls and per-page
        const perPageSelect = document.getElementById('perPageSelect');
        const pagerPrev = document.getElementById('pagerPrev');
        const pagerNext = document.getElementById('pagerNext');
        const pagerInfo = document.getElementById('pagerInfo');
        const searchInput = document.getElementById('searchInput');
        let currentPage = 1,
            lastPage = 1;
        let searchTerm = '';

        function normalizePerPage(v) {
            const n = Number(v || 10);
            return [10, 25, 50].includes(n) ? n : 10;
        }
        let perPage = normalizePerPage(localStorage.getItem('ts_staff_perPage'));
        if (perPageSelect) {
            perPageSelect.value = String(perPage);
        }
        let ticketsMap = new Map();

        const statusStyles = {
            'Open': 'text-blue-700 bg-blue-50 ring-blue-600/20',
            'Forwarded': 'text-amber-700 bg-amber-50 ring-amber-600/20',
            'Closed': 'text-emerald-700 bg-emerald-50 ring-emerald-600/20',
        };

        let lastSnapshot = '';

        function fmtDate(d) {
            try {
                const dt = new Date(d);
                if (isNaN(dt.getTime())) return '';
                return dt.toLocaleString();
            } catch (_) {
                return '';
            }
        }

        function renderTickets(tickets) {
            if (!ticketsBodyEl) return;
            if (!Array.isArray(tickets)) tickets = [];

            // Build HTML rows
            const rows = tickets.map(t => {
                const ticketNo = String(t.id);
                const style = statusStyles[t.status] || 'text-slate-700 bg-slate-50 ring-slate-600/20';
                const createdAt = fmtDate(t.date_created || t.created_at);
                const category = (t.category && typeof t.category === 'object') ? (t.category.name ?? '') :
                    (t.category ?? '');

                const subject = (t.question ?? '').length > 80 ? (t.question ?? '').slice(0, 77) + '...' : (
                    t.question ?? '');

                return `
                    <tr class="hover:bg-gray-50" data-id="${t.id}" style="cursor: pointer;">
                        <td class="py-4 pl-5 pr-3 align-top">
                            <div class="text-indigo-700 font-medium">${ticketNo}</div>
                            <div class="mt-1 text-xs text-gray-500">
                                ${createdAt}
                            </div>
                        </td>

                        <td class="px-3 py-4 align-top">
                            <div class="text-gray-900">${subject}</div>
                            <div class="mt-1 text-xs text-gray-500 flex items-center gap-2">
                                <span class="inline-flex items-center rounded-full bg-gray-100 px-2 py-0.5 text-[11px] font-medium text-gray-700">${category}</span>
                            </div>
                        </td>

                        <td class="px-3 py-4 align-top">
                            <span class="inline-flex items-center gap-1 rounded-full px-2.5 py-1 text-xs font-medium ring-1 ${style}">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-3.5 w-3.5" viewBox="0 0 24 24" fill="currentColor">
                                    <circle cx="12" cy="12" r="5"></circle>
                                </svg>
                                ${t.status ?? ''}
                            </span>
                        </td>
                    </tr>
                `;
            });

            if (rows.length === 0) {
                ticketsBodyEl.innerHTML = `
                    <tr>
                        <td colspan="3" class="px-5 py-10 text-center text-sm text-gray-500">
                            No tickets assigned yet.
                        </td>
                    </tr>
                `;
            } else {
                ticketsBodyEl.innerHTML = rows.join('');
            }
        }


        async function fetchData() {
            // Avoid background polling to save resources
            if (document.hidden) return;

            // Determine current filter (view all vs open-only) BEFORE the request
            const viewAll = toggleEl ? toggleEl.checked : false;
            const url = dataUrl + '?viewAll=' + (viewAll ? 'true' : 'false') + '&page=' + currentPage +
                '&perPage=' + perPage + '&search=' + encodeURIComponent(searchTerm);

            try {
                const res = await fetch(url, {
                    method: 'GET',
                    headers: {
                        'Accept': 'application/json'
                    },
                    credentials: 'same-origin'
                });

                if (res.status === 401) {
                    // Session expired or not authenticated
                    window.location.href = "{{ route('login') }}";
                    return;
                }

                if (!res.ok) return;

                const data = await res.json();

                // Update KPIs
                if (openCountEl) openCountEl.textContent = data.openCount ?? 0;
                if (inProgressCountEl) inProgressCountEl.textContent = data.inProgressCount ?? 0;
                if (closedCountEl) closedCountEl.textContent = data.closedCount ?? 0;
                if (totalCountEl) totalCountEl.textContent = data.totalCount ?? 0;

                // Update heading based on filter
                if (ticketsHeadingEl) {
                    ticketsHeadingEl.textContent = viewAll ? 'My Tickets' : 'Open Tickets';
                }

                // Server already applies the filter. Keep a safety client-side filter when viewAll=false.
                const list = Array.isArray(data.recentTickets) ? data.recentTickets : [];
                console.log('Recent tickets from API:', list);
                // Keep a fast lookup for "View" modal
                ticketsMap = new Map(list.map(t => [String(t.id), t]));
                // Show both Open and Forwarded when not viewing all
                const filtered = viewAll ? list : list.filter(t => (t.status === 'Open' || t.status ===
                    'Forwarded'));

                // Update pagination UI
                var pg = data.pagination || {};
                currentPage = Number(pg.currentPage || 1);
                lastPage = Number(pg.lastPage || 1);
                if (pagerInfo) {
                    const totalTxt = (typeof pg.total !== 'undefined') ? (' • ' + pg.total + ' total') : '';
                    pagerInfo.textContent = 'Page ' + currentPage + ' of ' + (lastPage || 1) + totalTxt;
                }
                if (pagerPrev) pagerPrev.disabled = currentPage <= 1;
                if (pagerNext) pagerNext.disabled = currentPage >= lastPage;

                // Update pagination UI
                var pg = data.pagination || {};
                currentPage = Number(pg.currentPage || 1);
                lastPage = Number(pg.lastPage || 1);
                if (pagerInfo) {
                    const totalTxt = (typeof pg.total !== 'undefined') ? (' • ' + pg.total + ' total') : '';
                    pagerInfo.textContent = 'Page ' + currentPage + ' of ' + (lastPage || 1) + totalTxt;
                }
                if (pagerPrev) pagerPrev.disabled = currentPage <= 1;
                if (pagerNext) pagerNext.disabled = currentPage >= lastPage;

                // Render tickets only if changed (cheap diff by filter+IDs+counts JSON)
                const snapshot = JSON.stringify({
                    mode: viewAll ? 'all' : 'open',
                    total: data.totalCount,
                    ids: filtered.map(t => t.id)
                });
                if (snapshot !== lastSnapshot) {
                    renderTickets(filtered);
                    lastSnapshot = snapshot;
                }
            } catch (e) {
                // Silently ignore transient errors; next tick will retry
            }
        }

        // Initial load only — polling disabled to avoid overloading the database.
        // The dashboard will refresh when:
        //  - a CRUD operation in any tab sets localStorage.ts_tickets_changed
        //  - the current tab becomes visible AND a change was recorded
        fetchData();

        // If the page was opened with a ticket_id query param (from a push), attempt to open the ticket modal.
        (function openTicketFromQuery() {
            try {
                const params = new URLSearchParams(window.location.search);
                const ticketId = params.get('ticket_id');
                if (!ticketId) return;

                // Try to open the modal as soon as the ticket is present in ticketsMap.
                // ticketsMap is populated by fetchData(); poll briefly until available.
                let attempts = 0;
                const maxAttempts = 20;
                const interval = setInterval(() => {
                    attempts++;
                    if (ticketsMap && ticketsMap.has(String(ticketId))) {
                        const t = ticketsMap.get(String(ticketId));
                        try {
                            openModalFrom(t);
                        } catch (e) {
                            /* ignore */
                        }
                        // Remove ticket_id from URL so reloading doesn't reopen modal
                        try {
                            const newSearch = window.location.search.replace(
                                /([?&])ticket_id=[^&]*(&|$)/, (m, p1, p2) => p2 ? p1 : '');
                            history.replaceState(null, '', window.location.pathname + newSearch);
                        } catch (_) {}
                        clearInterval(interval);
                    } else if (attempts >= maxAttempts) {
                        clearInterval(interval);
                    }
                }, 300);
            } catch (e) {
                // ignore
            }
        })();

        // Cross-tab notification: refresh when other tabs set the flag
        window.addEventListener('storage', (e) => {
            if (e && e.key === 'ts_tickets_changed') {
                fetchData();
            }
        });
        // When tab becomes visible, refresh only if an external change was recorded
        document.addEventListener('visibilitychange', () => {
            try {
                if (!document.hidden && localStorage.getItem('ts_tickets_changed')) {
                    fetchData();
                }
            } catch (_) {}
        });

        // React to toggle changes immediately
        if (toggleEl) {
            toggleEl.addEventListener('change', () => {
                // reset pagination to first page
                currentPage = 1;
                lastSnapshot = '';
                fetchData();
            });
        }
        if (perPageSelect) {
            perPageSelect.addEventListener('change', () => {
                perPage = normalizePerPage(perPageSelect.value);
                localStorage.setItem('ts_staff_perPage', String(perPage));
                currentPage = 1;
                lastSnapshot = '';
                fetchData();
            });
        }
        if (pagerPrev) {
            pagerPrev.addEventListener('click', () => {
                if (currentPage > 1) {
                    currentPage -= 1;
                    lastSnapshot = '';
                    fetchData();
                }
            });
        }
        if (pagerNext) {
            pagerNext.addEventListener('click', () => {
                if (currentPage < lastPage) {
                    currentPage += 1;
                    lastSnapshot = '';
                    fetchData();
                }
            });
        }
        // Search input with debounce
        let searchTimeout;
        if (searchInput) {
            searchInput.addEventListener('input', () => {
                clearTimeout(searchTimeout);
                searchTimeout = setTimeout(() => {
                    searchTerm = searchInput.value.trim();
                    currentPage = 1;
                    lastSnapshot = '';
                    fetchData();
                }, 300);
            });
        }

        // ===== Modal & View logic =====
        const modalEl = document.getElementById('ticketModal');
        console.log('modalEl:', modalEl);
        const modalBackdrop = modalEl ? modalEl.querySelector('[data-modal-backdrop]') : null;
        const modalCloseBtns = modalEl ? modalEl.querySelectorAll('[data-modal-close]') : [];
        const tmTicketNo = document.getElementById('tmTicketNo');
        const tmDates = document.getElementById('tmDates');
        const tmStatus = document.getElementById('tmStatus');
        const tmCategory = document.getElementById('tmCategory');
        const tmSubject = document.getElementById('tmSubject');
        const tmQuestion = document.getElementById('tmQuestion');
        const tmEmail = document.getElementById('tmEmail');
        const tmRecepient = document.getElementById('tmRecepient');
        const tmResponse = document.getElementById('tmResponse');
        const tmSendResponse = document.getElementById('tmSendResponse');
        const tmOptionsBtn = document.getElementById('tmOptionsBtn');
        const tmOptionsMenu = document.getElementById('tmOptionsMenu');
        const tmOptionForward = document.getElementById('tmOptionForward');
        const tmStoredResponseBlock = document.getElementById('tmStoredResponseBlock');
        const tmStoredResponse = document.getElementById('tmStoredResponse');

        const csrfToken = '{{ csrf_token() }}';
        const forwardBase = "{{ url('/staff/tickets') }}";
        // Attachment URL helper (works even when /public/storage symlink is missing on hosted setups)
        const ATTACHMENT_BASE = "{{ url('/attachments') }}";

        function attachmentUrl(p) {
            if (!p) return '';
            return ATTACHMENT_BASE + '/' + String(p).split('/').map(encodeURIComponent).join('/');
        }

        let currentTicketId = null;

        function statusClassFor(s) {
            const base = statusStyles[s] || 'text-slate-700 bg-slate-50 ring-slate-600/20';
            return base;
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

        async function openModalFrom(ticket) {
            console.log('openModalFrom called with ticket:', ticket);
            // If the ticket object doesn't include detailed fields (users, attachments, histories),
            // fetch the full ticket from the server (mirrors admin modal behavior).
            try {
                if (!ticket || !ticket.users) {
                    const res = await fetch(`${forwardBase}/${ticket.id}`, {
                        headers: {
                            'X-Requested-With': 'XMLHttpRequest',
                            'Accept': 'application/json'
                        },
                        credentials: 'same-origin'
                    });
                    if (res && res.ok) {
                        const full = await res.json();
                        // use detailed payload when available
                        ticket = full || ticket;
                    }
                }
            } catch (err) {
                console.warn('Could not fetch detailed ticket data, falling back to provided ticket', err);
            }
            if (!modalEl) {
                console.error('modalEl not found');
                return;
            }
            if (!ticket) {
                console.error('No ticket provided');
                return;
            }
            const ticketNo = String(ticket.id);
            const createdAt = fmtDate(ticket.date_created || ticket.created_at);
            const updatedAt = fmtDate(ticket.updated_at);
            const category = (ticket.category && typeof ticket.category === 'object') ? (ticket.category.name ??
                '') : (ticket.category ?? '');
            const subject = category ? `${category} - ${(ticket.question ?? '').slice(0, 80)}` : ((ticket
                .question ?? '').slice(0, 80));
            const question = ticket.question ?? '';
            const email = ticket.email ?? '';
            const recepient = ticket.recepient_id ?? '';

            // Fill fields
            if (tmTicketNo) tmTicketNo.textContent = 'Ticket #' + ticketNo;
            if (tmDates) tmDates.textContent = createdAt ?
                `Created ${createdAt}${updatedAt ? ' • Updated ' + updatedAt : ''}` : '';
            if (tmStatus) {
                tmStatus.className =
                    'inline-flex items-center gap-1.5 rounded-full px-3 py-1 text-xs font-medium ring-1 ' +
                    statusClassFor(ticket.status);
                tmStatus.innerHTML =
                    `<svg xmlns="http://www.w3.org/2000/svg" class="h-3.5 w-3.5" viewBox="0 0 24 24" fill="currentColor"><circle cx="12" cy="12" r="5"></circle></svg> ${ticket.status ?? ''}`;
            }
            if (tmCategory) tmCategory.textContent = category;
            if (tmSubject) tmSubject.textContent = subject;
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
            console.log('Ticket attachments:', ticket.attachments);
            if (attachmentsBlock && attachmentsList) {
                attachmentsList.innerHTML = '';
                if (ticket.attachments) {
                    let attachments = [];
                    try {
                        attachments = JSON.parse(ticket.attachments);
                        console.log('Parsed attachments:', attachments);
                    } catch (e) {
                        console.error('Error parsing attachments:', e);
                        attachments = [];
                    }
                    if (attachments.length > 0) {
                        console.log('Showing attachments block');
                        attachments.forEach((path, index) => {
                            const img = document.createElement('img');
                            img.src = attachmentUrl(path);
                            console.log('Image src:', img.src);
                            img.alt = 'Attachment ' + (index + 1);
                            img.className =
                                'max-w-16 max-h-16 object-cover rounded cursor-pointer border border-gray-300 hover:border-indigo-400';
                            img.onclick = () => openLightbox(attachments, index);
                            attachmentsList.appendChild(img);
                        });
                        attachmentsBlock.classList.remove('hidden');
                    } else {
                        console.log('No attachments to show');
                        attachmentsBlock.classList.add('hidden');
                    }
                } else {
                    console.log('No attachments field');
                    attachmentsBlock.classList.add('hidden');
                }
            }

            // Hide forward controls initially
            const tmForwardControls = document.getElementById('tmForwardControls');
            if (tmForwardControls) tmForwardControls.classList.add('hidden');

            // Populate forward select with users (mirror admin behavior)
            const tmForwardSelect = document.getElementById('tmForwardSelect');
            if (tmForwardSelect) {
                // clear existing options and add placeholder
                tmForwardSelect.innerHTML = '';
                if (ticket.users && Array.isArray(ticket.users) && ticket.users.length > 0) {
                    const placeholder = document.createElement('option');
                    placeholder.value = '';
                    placeholder.disabled = true;
                    placeholder.selected = true;
                    placeholder.textContent = 'Select user';
                    tmForwardSelect.appendChild(placeholder);
                    ticket.users.forEach(user => {
                        const option = document.createElement('option');
                        option.value = user.id;
                        option.textContent = user.name + (user.email ? (' <' + user.email + '>') : '');
                        tmForwardSelect.appendChild(option);
                    });
                } else {
                    // fallback: if server provided a static list at render time, keep it; otherwise show disabled
                    if (tmForwardSelect.options.length === 0) {
                        const opt = document.createElement('option');
                        opt.disabled = true;
                        opt.textContent = 'No users available';
                        tmForwardSelect.appendChild(opt);
                    }
                }
            }

            // Prepare and render history; keep hidden by default until toggled in Options
            const hsObj = ensureHistorySection();
            if (hsObj.section) hsObj.section.classList.add('hidden');
            const histories = ticket.routing_histories || ticket.routingHistories || [];
            renderHistory(Array.isArray(histories) ? histories : []);

            // Toggle forward option and response display based on status
            const isClosed = (ticket.status === 'Closed');
            if (tmOptionForward) tmOptionForward.classList.toggle('hidden', isClosed);
            if (tmForwardControls) tmForwardControls.classList.add('hidden');
            if (tmStoredResponseBlock) {
                if (isClosed) {
                    tmStoredResponseBlock.classList.remove('hidden');
                    if (tmStoredResponse) tmStoredResponse.textContent = ticket.response ? String(ticket
                            .response) :
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

            currentTicketId = ticket.id;

            // Show modal
            console.log('Showing modal');
            modalEl.classList.remove('hidden');
            document.body.classList.add('overflow-hidden');
            console.log('Modal should be visible now');
        }

        function closeModal() {
            if (!modalEl) return;
            modalEl.classList.add('hidden');
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
                updateLightboxButtons();
            }
        }

        function closeLightbox() {
            const lightbox = document.getElementById('imageLightbox');
            if (lightbox) {
                lightbox.classList.add('hidden');
                // Only remove overflow-hidden if the ticket modal is also closed
                if (!modalEl || modalEl.classList.contains('hidden')) {
                    document.body.classList.remove('overflow-hidden');
                }
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

        // Delegate click on "View" or row
        if (ticketsBodyEl) {
            console.log('Attaching click listener to ticketsBodyEl');
            ticketsBodyEl.addEventListener('click', (e) => {
                console.log('Click detected on ticketsBodyEl', e.target);
                const target = e.target.closest('[data-action="view"], tr[data-id]');
                console.log('Closest target:', target);
                if (!target) return;
                e.preventDefault();
                let id;
                if (target.tagName === 'TR') {
                    id = target.getAttribute('data-id');
                } else {
                    id = target.getAttribute('data-id');
                }
                console.log('Ticket ID:', id);
                let ticket = ticketsMap.get(String(id));
                console.log('Ticket from map:', ticket);
                // Fallback to server-rendered data attributes if available (for static rows)
                if (!ticket) {
                    console.log('Using fallback ticket data');
                    ticket = {
                        id: Number(id),
                        category: target.getAttribute('data-category') || '',
                        question: target.getAttribute('data-question') || '',
                        status: target.getAttribute('data-status') || '',
                        staff: {
                            name: target.getAttribute('data-staff') || ''
                        },
                        date_created: target.getAttribute('data-date-created') || '',
                        updated_at: target.getAttribute('data-updated-at') || '',
                        email: target.getAttribute('data-email') || '',
                        recepient_id: target.getAttribute('data-recepient') || '',
                        response: target.getAttribute('data-response') || ''
                    };
                }
                console.log('Calling openModalFrom with ticket:', ticket);
                openModalFrom(ticket);
            });
        } else {
            console.error('ticketsBodyEl not found');
        }

        // Close modal interactions
        if (modalBackdrop) modalBackdrop.addEventListener('click', closeModal);
        if (modalCloseBtns && modalCloseBtns.length) {
            modalCloseBtns.forEach(btn => btn.addEventListener('click', closeModal));
        }
        document.addEventListener('keydown', (e) => {
            if (e.key === 'Escape') {
                // Don't close modal if lightbox is open (lightbox handles its own Escape)
                const lightbox = document.getElementById('imageLightbox');
                if (lightbox && !lightbox.classList.contains('hidden')) return;
                closeModal();
            }
        });

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
                    const tmForwardControls = document.getElementById('tmForwardControls');
                    if (tmForwardControls) tmForwardControls.classList.remove('hidden');
                }
            });
        }

        // Forward via select + apply (use same behavior as admin: select user and POST user_id)
        const tmForwardSelect = document.getElementById('tmForwardSelect');
        const tmForwardApply = document.getElementById('tmForwardApply');
        if (tmForwardApply && tmForwardSelect) {
            tmForwardApply.addEventListener('click', async () => {
                if (!currentTicketId) return;
                if (!tmForwardSelect || !tmForwardSelect.value) {
                    if (window.Swal) {
                        Swal.fire({
                            icon: 'warning',
                            title: 'Selection Required',
                            text: 'Please choose a user to forward to.',
                            confirmButtonText: 'OK'
                        });
                    } else {
                        alert('Please choose a user to forward to.');
                    }
                    return;
                }
                const userId = tmForwardSelect.value;

                // Disable button and show loading
                tmForwardApply.disabled = true;
                tmForwardApply.classList.add('opacity-50', 'pointer-events-none');
                const originalText = tmForwardApply.textContent;
                tmForwardApply.innerHTML =
                    '<svg class="animate-spin -ml-1 mr-2 h-4 w-4 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg>Forwarding...';

                try {
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

                    if (res.ok) {
                        let forwardResp = null;
                        try {
                            forwardResp = await res.json();
                        } catch (_) {}
                        // If backend returned the new staff info, update UI immediately (job may be async)
                        if (forwardResp && forwardResp.new_staff) {
                            try {
                                const newStaff = forwardResp.new_staff;
                                // Update ticketsMap entry if present
                                const key = String(currentTicketId);
                                if (ticketsMap && ticketsMap.has(key)) {
                                    const t = ticketsMap.get(key);
                                    if (t) {
                                        t.staff = t.staff || {};
                                        t.staff.name = newStaff.name || (newStaff.name ?? '');
                                        ticketsMap.set(key, t);
                                    }
                                }
                            } catch (e) {
                                console.warn('Failed to apply immediate update', e);
                            }
                        }
                        lastSnapshot = '';
                        try {
                            localStorage.setItem('ts_tickets_changed', String(Date.now()));
                        } catch (e) {}
                        fetchData();
                        closeModal();
                        setTimeout(() => {
                            if (window.Swal) {
                                Swal.fire({
                                    icon: 'success',
                                    title: 'Ticket Forwarded',
                                    text: 'Ticket has been forwarded successfully!',
                                    timer: 3000,
                                    timerProgressBar: true,
                                    showConfirmButton: false,
                                    position: 'top-end',
                                    toast: true
                                });
                            } else {
                                alert('Ticket forwarded successfully.');
                            }
                        }, 500);
                    } else {
                        const errorText = await res.text();
                        console.error('Forward failed', res.status, errorText);
                        if (window.Swal) {
                            Swal.fire({
                                icon: 'error',
                                title: 'Forward Failed',
                                text: 'Failed to forward ticket. Please try again. Error: ' +
                                    res.status + ' ' + res.statusText,
                                confirmButtonText: 'OK'
                            });
                        } else {
                            alert('Forward failed. Please try again. Error: ' + res.status + ' ' + res
                                .statusText);
                        }
                    }
                } catch (err) {
                    console.error('Forward error', err);
                    if (window.Swal) {
                        Swal.fire({
                            icon: 'error',
                            title: 'Network Error',
                            text: 'Network error during forward.',
                            confirmButtonText: 'OK'
                        });
                    } else {
                        alert('Network error during forward.');
                    }
                } finally {
                    // Re-enable button
                    tmForwardApply.disabled = false;
                    tmForwardApply.classList.remove('opacity-50', 'pointer-events-none');
                    tmForwardApply.textContent = originalText;
                    // Refresh dashboard if server requested it (mirrors admin logic)
                }
            });
        }

        // Send response (email via backend)
        if (tmSendResponse && tmResponse) {
            tmSendResponse.addEventListener('click', async () => {
                const msg = tmResponse.value.trim();
                if (!msg) {
                    alert('Please enter a response message.');
                    return;
                }
                if (!currentTicketId) {
                    alert('No ticket selected.');
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
                        if (window.Swal) {
                            Swal.fire({
                                position: 'top-end',
                                icon: 'success',
                                title: 'Response email sent',
                                showConfirmButton: false,
                                timer: 1500
                            });
                        } else {
                            (window.showToast || showToast)('success', 'Response email sent.');
                        }
                        tmResponse.value = '';
                        // Refresh KPIs and table to reflect ticket Closed status
                        lastSnapshot = '';
                        try {
                            localStorage.setItem('ts_tickets_changed', String(Date.now()));
                        } catch (e) {}
                        fetchData();
                        closeModal();
                    } else {
                        const txt = await res.text();
                        let errorMsg = 'Failed to send response.';
                        try {
                            const errData = JSON.parse(txt);
                            if (errData && errData.message) {
                                errorMsg = errData.message;
                            } else if (errData && errData.errors && errData.errors.message) {
                                errorMsg = errData.errors.message[0] || errorMsg;
                            }
                        } catch (_) {}
                        console.error('Send response failed', txt);
                        if (window.Swal) {
                            Swal.fire({
                                position: 'top-end',
                                icon: 'error',
                                title: 'Failed to send response',
                                text: errorMsg,
                                showConfirmButton: false,
                                timer: 3000
                            });
                        } else {
                            showToast('error', errorMsg);
                        }
                    }
                } catch (err) {
                    console.error('Send response error', err);
                    if (window.Swal) {
                        Swal.fire({
                            position: 'top-end',
                            icon: 'error',
                            title: 'Network error while sending response',
                            showConfirmButton: false,
                            timer: 2000
                        });
                    } else {
                        (window.showToast || showToast)('error',
                            'Network error while sending response.');
                    }
                } finally {
                    tmSendResponse.disabled = false;
                    tmSendResponse.classList.remove('opacity-50', 'pointer-events-none');
                }
            });
        }
    })();
</script>
<script>
    // Handle service-worker notification clicks forwarded to the page.
    // When the service worker focuses an existing client it posts a message:
    // { type: 'notification-click', url: 'https://.../staff/tickets/123?ticket_id=123' }
    // We navigate the focused tab to that URL so the staff sees the ticket detail.
    window.addEventListener('message', function(e) {
        try {
            if (e.data && e.data.type === 'notification-click' && e.data.url) {
                // Safely navigate to the provided URL in the current tab
                window.location.href = e.data.url;
            }
        } catch (_) {
            /* ignore */
        }
    });
</script>
