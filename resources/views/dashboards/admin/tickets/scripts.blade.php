<script>
    (function() {
        const state = document.createElement('div');
        state.id = 'admin-tickets-state';
        state.className = 'hidden';
        state.setAttribute('data-list-url', "{{ route('admin.tickets.list') }}");
        // Use a raw URL template here (avoid route() encoding the placeholder)
        state.setAttribute('data-show-url-template', "{{ url('/admin/tickets') }}/__ID__");
        state.setAttribute('data-respond-url-template', "{{ url('/admin/tickets') }}/__ID__/respond");
        state.setAttribute('data-forward-url-template', "{{ url('/admin/tickets') }}/__ID__/forward");
        state.setAttribute('data-destroy-url-template', "{{ url('/admin/tickets') }}/__ID__");
        document.body.appendChild(state);

        const LIST_URL = state.getAttribute('data-list-url');
        const SHOW_TEMPLATE = state.getAttribute('data-show-url-template');
        const RESPOND_TEMPLATE = state.getAttribute('data-respond-url-template');
        const FORWARD_TEMPLATE = state.getAttribute('data-forward-url-template');
        const DESTROY_TEMPLATE = state.getAttribute('data-destroy-url-template');
        const csrf = document.querySelector('meta[name="csrf-token"]').getAttribute('content');

        // Attachment URL helper (works even when /public/storage symlink is missing on hosted setups)
        const ATTACHMENT_BASE = "{{ url('/attachments') }}";

        function attachmentUrl(p) {
            if (!p) return '';
            // Keep slashes but encode each segment (handles spaces, unicode, etc.)
            return ATTACHMENT_BASE + '/' + String(p).split('/').map(encodeURIComponent).join('/');
        }

        const ticketsTbody = document.getElementById('ticketsTbody');
        const ticketsPagination = document.getElementById('ticketsPagination');
        const ticketModal = document.getElementById('ticketModal');

        let currentPage = 1;
        let ticketsMap = new Map();

        function fmtDate(d) {
            try {
                const dt = new Date(d);
                return isNaN(dt) ? '' : dt.toLocaleString();
            } catch (_) {
                return '';
            }
        }

        async function fetchList(page = 1, minimal = false) {
            currentPage = page;
            try {
                // read UI filters
                const qEl = document.getElementById('q');
                const qMobileEl = document.getElementById('q_mobile');
                const qVal = (qEl && qEl.value.trim()) ? qEl.value.trim() : (qMobileEl && qMobileEl.value
                    .trim() ? qMobileEl.value.trim() : '');
                const perEl = document.getElementById('filterPerPage') || document.getElementById(
                    'perPageSelect');
                let per = perEl ? perEl.value : '25';

                const statusEl = document.getElementById('filterStatus');
                const sortEl = document.getElementById('filterSort');
                const roleEl = document.getElementById('filterRole');
                const assigneeIdEl = document.getElementById('filterAssigneeId');
                const assigneeEl = document.getElementById('filterAssignee'); // fallback (text input)

                const statusVal = statusEl ? statusEl.value : '';
                const sortVal = sortEl ? sortEl.value : '';
                const roleVal = roleEl ? roleEl.value : '';
                const assigneeIdVal = assigneeIdEl ? assigneeIdEl.value : '';
                const assigneeVal = assigneeEl ? assigneeEl.value.trim() : '';

                const sep = LIST_URL.includes('?') ? '&' : '?';
                let url = `${LIST_URL}${sep}page=${page}&per_page=${encodeURIComponent(per)}`;

                // Only send full filters when not doing a minimal poll
                if (!minimal) {
                    if (qVal) url += '&q=' + encodeURIComponent(qVal);
                    if (statusVal) url += '&status=' + encodeURIComponent(statusVal);
                    if (sortVal) url += '&sort=' + encodeURIComponent(sortVal);

                    // Role param only from dropdown (pills removed)
                    if (roleVal) {
                        url += '&role=' + encodeURIComponent(roleVal);
                    }

                    if (assigneeIdVal) url += '&assignee_id=' + encodeURIComponent(assigneeIdVal);
                    else if (assigneeVal) url += '&assignee=' + encodeURIComponent(assigneeVal);
                }

                const res = await fetch(url, {
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest'
                    }
                });
                if (!res.ok) throw new Error('Failed to load tickets');
                const json = await res.json();
                renderTable(json.items || []);
                renderPagination(json.meta || {});
                // do not cache server data in localStorage or elsewhere — always render fresh data
            } catch (err) {
                ticketsTbody.innerHTML =
                    '<tr><td colspan="6" class="px-5 py-6 text-center text-sm text-red-600">Error loading tickets</td></tr>';
            }
        }

        function renderTable(items) {
            ticketsMap = new Map(items.map(t => [String(t.id), t]));
            if (!items.length) {
                ticketsTbody.innerHTML =
                    '<tr><td colspan="6" class="px-5 py-10 text-center text-sm text-gray-500">No tickets found.</td></tr>';
                return;
            }
            ticketsTbody.innerHTML = items.map(t => {
                const ticketNo = String(t.id);
                return `
        <tr class="hover:bg-gray-50 cursor-pointer" data-id="${t.id}">
          <td class="py-4 pl-5 pr-3">${ticketNo}</td>
          <td class="px-3 py-4">${escapeHtml(t.role_name || (t.role && t.role.name) || '')}</td>
          <td class="px-3 py-4">${escapeHtml((t.question||'').slice(0,80))}</td>
          <td class="px-3 py-4">${escapeHtml(t.status||'')}</td>
          <td class="px-3 py-4">${escapeHtml((t.staff && t.staff.name) || '-')}</td>
          <td class="px-3 py-4">${escapeHtml(fmtDate(t.date_created||t.created_at))}</td>
        </tr>
      `;
            }).join('');
            // Rendering only — do not attach event listeners here (attach once globally below)
        }

        // Attach a single delegated click listener for ticket rows (attach once)
        if (ticketsTbody) {
            ticketsTbody.addEventListener('click', (e) => {
                const tr = e.target.closest('tr[data-id]');
                if (!tr) return;
                const id = tr.getAttribute('data-id');
                if (!id) return;
                openModalFor(id);
            });
        }

        function renderPagination(meta) {
            if (!meta || !meta.total) {
                ticketsPagination.innerHTML = '';
                return;
            }
            const total = meta.total || 0;
            const per = meta.per_page || (document.getElementById('filterPerPage') ? document.getElementById(
                'filterPerPage').value : 25);
            const current = meta.current_page || 1;
            const last = meta.last_page || 1;

            // windowed pages
            const delta = 2;
            const left = Math.max(1, current - delta);
            const right = Math.min(last, current + delta);
            const pages = [];
            for (let i = left; i <= right; i++) pages.push(i);

            const prevDisabled = current <= 1;
            const nextDisabled = current >= last;

            ticketsPagination.innerHTML = `
      <div class="flex items-center gap-3">
        <div class="text-sm text-slate-600">Showing ${per} per page — ${total} total</div>
      </div>
      <div class="flex items-center gap-2">
        <button ${prevDisabled ? 'disabled' : ''} data-page="${current-1}" class="pagerBtn rounded-md border border-gray-200 bg-white px-3 py-1 text-sm ${prevDisabled ? 'opacity-50' : 'hover:bg-gray-50'}">Prev</button>
        ${pages.map(p => `<button data-page="${p}" class="pagerBtn rounded-md ${p===current ? 'bg-blue-600 text-white' : 'border border-gray-200 bg-white text-sm hover:bg-gray-50'} px-3 py-1">${p}</button>`).join('')}
        <button ${nextDisabled ? 'disabled' : ''} data-page="${current+1}" class="pagerBtn rounded-md border border-gray-200 bg-white px-3 py-1 text-sm ${nextDisabled ? 'opacity-50' : 'hover:bg-gray-50'}">Next</button>
      </div>
    `;

            ticketsPagination.querySelectorAll('.pagerBtn').forEach(b => b.addEventListener('click', (e) => {
                const p = parseInt(b.getAttribute('data-page') || '1', 10);
                if (!isNaN(p)) fetchList(p);
            }));
        }

        // Hook search and per-page controls
        const searchBtn = document.getElementById('searchBtn');
        const searchBtnMobile = document.getElementById('searchBtnMobile');
        const qInput = document.getElementById('q');
        const qMobileInput = document.getElementById('q_mobile');
        const perPageSelect = document.getElementById('filterPerPage');

        if (searchBtn) {
            searchBtn.addEventListener('click', () => fetchList(1));
        }
        if (qInput) {
            qInput.addEventListener('keyup', (e) => {
                if (e.key === 'Enter') fetchList(1);
            });
        }
        if (searchBtnMobile) {
            searchBtnMobile.addEventListener('click', () => {
                // copy mobile query to desktop input so UI stays consistent
                if (qMobileInput && qInput) qInput.value = qMobileInput.value;
                fetchList(1);
            });
        }
        if (perPageSelect) {
            perPageSelect.addEventListener('change', () => fetchList(1));
        }

        // Role filter dropdown change handler
        const roleSelect = document.getElementById('filterRole');
        if (roleSelect) {
            roleSelect.addEventListener('change', () => {
                fetchList(1);
            });
        }

        // Filters & drawer controls (apply / reset / close)
        const applyFiltersBtn = document.getElementById('applyFiltersBtn');
        const resetFiltersBtn = document.getElementById('resetFiltersBtn');
        const closeFiltersBtn = document.getElementById('closeFiltersBtn');
        const openFiltersBtn = document.getElementById('openFiltersBtn');

        if (applyFiltersBtn) {
            applyFiltersBtn.addEventListener('click', () => {
                const drawer = document.getElementById('ticketsBottomDrawer');
                const overlay = document.getElementById('ticketsDrawerOverlay');
                if (drawer) {
                    drawer.classList.add('translate-y-full');
                }
                if (overlay) {
                    overlay.classList.add('hidden');
                }
                fetchList(1);
            });
        }

        if (resetFiltersBtn) {
            resetFiltersBtn.addEventListener('click', () => {
                const statusEl = document.getElementById('filterStatus');
                const sortEl = document.getElementById('filterSort');
                const roleEl = document.getElementById('filterRole');
                const assigneeIdEl = document.getElementById('filterAssigneeId');
                const assigneeEl = document.getElementById('filterAssignee');
                if (statusEl) statusEl.value = 'all';
                if (sortEl) sortEl.value = 'created_desc';
                if (roleEl) roleEl.value = '';
                if (assigneeIdEl) assigneeIdEl.value = '';
                if (assigneeEl) assigneeEl.value = '';
                // also clear search inputs
                if (qInput) qInput.value = '';
                if (qMobileInput) qMobileInput.value = '';
                fetchList(1);
            });
        }

        if (closeFiltersBtn) {
            closeFiltersBtn.addEventListener('click', () => {
                const drawer = document.getElementById('ticketsBottomDrawer');
                const overlay = document.getElementById('ticketsDrawerOverlay');
                if (drawer) {
                    drawer.classList.add('translate-y-full');
                }
                if (overlay) {
                    overlay.classList.add('hidden');
                }
            });
        }

        // Overlay click closes the drawer (FAQ-style)
        const ticketsDrawerOverlayEl = document.getElementById('ticketsDrawerOverlay');
        if (ticketsDrawerOverlayEl) {
            ticketsDrawerOverlayEl.addEventListener('click', () => {
                const drawer = document.getElementById('ticketsBottomDrawer');
                if (drawer) drawer.classList.add('translate-y-full');
                ticketsDrawerOverlayEl.classList.add('hidden');
            });
        }

        // Close drawer on Escape
        document.addEventListener('keydown', (e) => {
            if (e.key === 'Escape') {
                const drawer = document.getElementById('ticketsBottomDrawer');
                const overlay = document.getElementById('ticketsDrawerOverlay');
                if (drawer && !drawer.classList.contains('translate-y-full')) {
                    drawer.classList.add('translate-y-full');
                    if (overlay) overlay.classList.add('hidden');
                }
            }
        });

        if (openFiltersBtn) {
            openFiltersBtn.addEventListener('click', (e) => {
                e.stopPropagation();
                const drawer = document.getElementById('ticketsBottomDrawer');
                const overlay = document.getElementById('ticketsDrawerOverlay');
                if (!drawer || !overlay) return;
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

        function escapeHtml(s) {
            if (s == null) return '';
            return String(s).replace(/&/g, '&').replace(/</g, '<').replace(/>/g, '>').replace(/"/g, '"').replace(
                /'/g, "&#039;");
        }

        // Modern modal functions from admin dashboard
        let currentTicketId = null;

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

        async function openModalFor(id) {
            currentTicketId = id;
            const url = SHOW_TEMPLATE.replace('__ID__', id);
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

                const tmTicketNo = document.getElementById('tmTicketNo');
                const tmStatus = document.getElementById('tmStatus');
                const tmQuestion = document.getElementById('tmQuestion');
                const tmCategory = document.getElementById('tmCategory');
                const tmDates = document.getElementById('tmDates');
                const tmEmail = document.getElementById('tmEmail');
                const tmRecepient = document.getElementById('tmRecepient');
                const tmResponse = document.getElementById('tmResponse');

                const ticketNo = String(t.id);
                const createdAt = fmtDate(t.date_created || t.created_at);
                const updatedAt = fmtDate(t.updated_at);
                const category = t.role_name ?? (t.role && t.role.name) ?? '';
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
                const tmForwardControls = document.getElementById('tmForwardControls');
                if (tmForwardControls) tmForwardControls.classList.add('hidden');

                // Prepare and render history; keep hidden by default until toggled in Options
                const hsObj = ensureHistorySection();
                if (hsObj.section) hsObj.section.classList.add('hidden');
                const histories = t.routing_histories || t.routingHistories || [];
                renderHistory(Array.isArray(histories) ? histories : []);

                // Toggle forward option and response display based on status
                const isClosed = (t.status === 'Closed');
                const tmOptionForward = document.getElementById('tmOptionForward');
                const tmOptionForwardFooter = document.getElementById('tmOptionForwardFooter');
                const tmStoredResponseBlock = document.getElementById('tmStoredResponseBlock');
                const tmStoredResponse = document.getElementById('tmStoredResponse');
                const tmSendResponse = document.getElementById('tmSendResponse');

                if (tmOptionForward) tmOptionForward.classList.toggle('hidden', isClosed);
                if (tmOptionForwardFooter) tmOptionForwardFooter.classList.toggle('hidden', isClosed);
                if (tmForwardControls) tmForwardControls.classList.add('hidden');
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
                const tmForwardSelect = document.getElementById('tmForwardSelect');
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
            } catch (err) {
                console.error('Dashboard: error loading ticket', err, 'url=', url);
                // Try a relative URL fallback (some dev setups have origin mismatches)
                try {
                    const altUrl = '/admin/tickets/' + id;
                    const altRes = await fetch(altUrl, {
                        headers: {
                            'X-Requested-With': 'XMLHttpRequest'
                        },
                        credentials: 'same-origin'
                    });
                    if (altRes && altRes.ok) {
                        const t = await altRes.json();
                        // reuse rendering code path (keep simple: fill modal minimally)
                        const tmTicketNo = document.getElementById('tmTicketNo');
                        const tmQuestion = document.getElementById('tmQuestion');
                        if (tmTicketNo) tmTicketNo.textContent = 'Ticket #' + String(t.id);
                        if (tmQuestion) tmQuestion.textContent = t.question || '';
                        if (ticketModal) {
                            ticketModal.classList.remove('hidden');
                            document.body.classList.add('overflow-hidden');
                        }
                        return;
                    }
                } catch (err2) {
                    console.error('Dashboard: fallback fetch also failed', err2);
                }
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

        // inline save edit
        async function saveEdit(id, payload) {
            try {
                const upUrl = "{{ url('/admin/tickets') }}/" + id;
                const res = await fetch(upUrl, {
                    method: 'PUT',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': csrf
                    },
                    body: JSON.stringify(payload)
                });
                if (!res.ok) throw new Error('Failed to update');
                // immediately refresh list after update
                fetchList(currentPage);
            } catch (err) {
                console.error(err);
                alert('Update failed');
            }
        }

        async function deleteTicket(id) {
            try {
                const dUrl = DESTROY_TEMPLATE.replace('__ID__', id);
                const res = await fetch(dUrl, {
                    method: 'DELETE',
                    headers: {
                        'X-CSRF-TOKEN': csrf
                    }
                });
                if (!res.ok) throw new Error('Failed to delete');
                // immediately refresh list after delete
                fetchList(currentPage);
            } catch (err) {
                console.error(err);
                alert('Delete failed');
            }
        }

        // Modern Modal Event Handlers from Admin Dashboard
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

        // Options dropdown handlers
        function setupOptionsMenu(optionsBtnId, optionsMenuId) {
            const tmOptionsBtn = document.getElementById(optionsBtnId);
            const tmOptionsMenu = document.getElementById(optionsMenuId);

            if (!tmOptionsBtn || !tmOptionsMenu) return false;

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

            return true;
        }

        // Setup both options menus
        setupOptionsMenu('tmOptionsBtn', 'tmOptionsMenu');
        setupOptionsMenu('tmOptionsBtnFooter', 'tmOptionsMenuFooter');

        // Forward via select + apply with SweetAlert
        const tmForwardApply = document.getElementById('tmForwardApply');
        const tmForwardSelect = document.getElementById('tmForwardSelect');

        if (tmForwardApply && tmForwardSelect) {
            tmForwardApply.addEventListener('click', async () => {
                if (!currentTicketId) return;
                if (!tmForwardSelect.value) {
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
                const fUrl = FORWARD_TEMPLATE.replace('__ID__', currentTicketId);
                try {
                    const res = await fetch(fUrl, {
                        method: 'POST',
                        headers: {
                            'Accept': 'application/json',
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': csrf
                        },
                        credentials: 'same-origin',
                        body: JSON.stringify({
                            user_id: userId
                        })
                    });
                    console.log('Forward request sent to:', fUrl);
                    console.log('Response status:', res.status, res.statusText);
                    if (res.ok) {
                        const data = await res.json();
                        console.log('Forward successful:', data);
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
                        closeModal();
                        // refresh list after forward
                        fetchList(currentPage);
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
                }
            });
        }

        // Send response with SweetAlert
        const tmSendResponse = document.getElementById('tmSendResponse');
        const tmResponse = document.getElementById('tmResponse');

        if (tmSendResponse && tmResponse) {
            const tmSendIcon = document.getElementById('tmSendIcon');
            const tmSendSpinner = document.getElementById('tmSendSpinner');
            const tmSendText = document.getElementById('tmSendText');

            tmSendResponse.addEventListener('click', async () => {
                const msg = tmResponse.value.trim();
                if (!msg) {
                    if (window.Swal) {
                        Swal.fire({
                            icon: 'warning',
                            title: 'Message Required',
                            text: 'Please enter a response message.',
                            confirmButtonText: 'OK'
                        });
                    } else {
                        alert('Please enter a response message.');
                    }
                    return;
                }
                if (!currentTicketId) {
                    if (window.Swal) {
                        Swal.fire({
                            icon: 'error',
                            title: 'No Ticket Selected',
                            text: 'No ticket selected.',
                            confirmButtonText: 'OK'
                        });
                    } else {
                        alert('No ticket selected.');
                    }
                    return;
                }

                // Show spinner and disable button to prevent multiple clicks
                try {
                    tmSendResponse.disabled = true;
                    tmSendResponse.classList.add('opacity-50', 'pointer-events-none');
                    if (tmSendIcon) tmSendIcon.classList.add('hidden');
                    if (tmSendText) tmSendText.classList.add('hidden');
                    if (tmSendSpinner) tmSendSpinner.classList.remove('hidden');

                    const rUrl = RESPOND_TEMPLATE.replace('__ID__', currentTicketId);
                    const res = await fetch(rUrl, {
                        method: 'POST',
                        headers: {
                            'Accept': 'application/json',
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': csrf
                        },
                        credentials: 'same-origin',
                        body: JSON.stringify({
                            message: msg
                        })
                    });

                    if (res.ok) {
                        if (window.Swal) {
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
                        } else {
                            alert('Response email sent successfully.');
                        }
                        tmResponse.value = '';
                        closeModal();
                        // refresh list after sending response
                        fetchList(currentPage);
                    } else {
                        const txt = await res.text();
                        console.error('Send response failed', txt);
                        if (window.Swal) {
                            Swal.fire({
                                icon: 'error',
                                title: 'Failed to Send Response',
                                text: 'Failed to send response. Please check mail configuration.',
                                confirmButtonText: 'OK'
                            });
                        } else {
                            alert('Failed to send response. Please check mail configuration.');
                        }
                    }
                } catch (err) {
                    console.error('Send response error', err);
                    if (window.Swal) {
                        Swal.fire({
                            icon: 'error',
                            title: 'Network Error',
                            text: 'Network error while sending response.',
                            confirmButtonText: 'OK'
                        });
                    } else {
                        alert('Network error while sending response.');
                    }
                } finally {
                    // Restore button state
                    if (tmSendSpinner) tmSendSpinner.classList.add('hidden');
                    if (tmSendIcon) tmSendIcon.classList.remove('hidden');
                    if (tmSendText) tmSendText.classList.remove('hidden');
                    tmSendResponse.disabled = false;
                    tmSendResponse.classList.remove('opacity-50', 'pointer-events-none');
                }
            });
        }

        // Close modal handlers
        document.addEventListener('click', (e) => {
            if (e.target && e.target.closest('[data-modal-backdrop]')) {
                closeModal();
            }
            if (e.target && e.target.getAttribute && e.target.getAttribute('data-modal-close') != null) {
                closeModal();
            }
            if (e.target && e.target.closest && e.target.closest('[data-modal-close]')) {
                closeModal();
            }
        });

        // Escape key to close modal
        document.addEventListener('keydown', (e) => {
            if (e.key === 'Escape' && ticketModal && !ticketModal.classList.contains('hidden')) {
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

        // When the user focuses or visibility changes, refresh data to ensure it's up-to-date.
        window.addEventListener('focus', () => fetchList(currentPage));
        document.addEventListener('visibilitychange', () => {
            if (!document.hidden) fetchList(currentPage);
        });

        // Background auto-polling has been disabled per request to avoid unexpected auto-open behavior.
        // If you want to re-enable a poller in the future, implement a user-toggle and attach listeners
        // carefully so they don't interfere with modal interactions.

        // Refresh button logic (near filters)
        const refreshBtn = document.getElementById('refreshBtn');
        const refreshSpinner = document.getElementById('refreshSpinner');
        const refreshIcon = document.getElementById('refreshIcon');

        if (refreshBtn) {
            refreshBtn.addEventListener('click', async (e) => {
                try {
                    // disable to prevent multiple clicks
                    refreshBtn.disabled = true;
                    refreshBtn.classList.add('opacity-50', 'pointer-events-none');
                    // show spinner while fetching and for a brief success period
                    refreshSpinner.classList.remove('hidden');
                    refreshIcon.classList.add('hidden');

                    await fetchList(1);

                    // keep spinner visible for a short success delay (2s) then restore
                    setTimeout(() => {
                        refreshSpinner.classList.add('hidden');
                        refreshIcon.classList.remove('hidden');
                        refreshBtn.disabled = false;
                        refreshBtn.classList.remove('opacity-50', 'pointer-events-none');
                    }, 2000);
                } catch (err) {
                    console.error('Refresh failed', err);
                    // re-enable
                    refreshSpinner.classList.add('hidden');
                    refreshIcon.classList.remove('hidden');
                    refreshBtn.disabled = false;
                    refreshBtn.classList.remove('opacity-50', 'pointer-events-none');
                }
            });
        }

        // initial load
        fetchList(1);
    })();
</script>
