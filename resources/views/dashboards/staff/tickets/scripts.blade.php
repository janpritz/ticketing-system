<script>
let currentFilter = 'all';
let currentPage = 1;
let perPage = 10;
let searchTerm = '';
let sortField = 'date_created';
let sortDirection = 'desc';

document.addEventListener('DOMContentLoaded', function() {
    initializeEventListeners();
    setActiveFilter(currentFilter, false);
    loadTickets();
});

function initializeEventListeners() {
    // Search functionality
    document.getElementById('searchInput').addEventListener('input', function() {
        searchTerm = this.value.trim();
        currentPage = 1;
        loadTickets();
    });

    // Per page selector
    document.getElementById('perPageSelect').addEventListener('change', function() {
        perPage = parseInt(this.value);
        currentPage = 1;
        loadTickets();
    });

    // Filter tabs
    document.querySelectorAll('.filter-tab').forEach(tab => {
        tab.addEventListener('click', function() {
            setActiveFilter(this.dataset.filter);
        });
    });

    // Sort buttons
    document.querySelectorAll('[data-sort]').forEach(button => {
        button.addEventListener('click', function() {
            const field = this.dataset.sort;
            if (sortField === field) {
                sortDirection = sortDirection === 'asc' ? 'desc' : 'asc';
            } else {
                sortField = field;
                sortDirection = 'asc';
            }
            updateSortIcons();
            loadTickets();
        });
    });

    // Pagination
    document.getElementById('prevPageBtn').addEventListener('click', () => changePage(currentPage - 1));
    document.getElementById('nextPageBtn').addEventListener('click', () => changePage(currentPage + 1));
    document.getElementById('mobilePrevBtn').addEventListener('click', () => changePage(currentPage - 1));
    document.getElementById('mobileNextBtn').addEventListener('click', () => changePage(currentPage + 1));

    // Row click to open modal
    document.getElementById('ticketsTableBody').addEventListener('click', (e) => {
        const tr = e.target.closest('tr');
        if (!tr) return;
        const id = tr.getAttribute('data-id');
        if (!id) return;
        window.openModalFor(id);
    });
}

async function loadTickets() {
    try {
        const params = new URLSearchParams({
            status: currentFilter,
            search: searchTerm,
            page: currentPage,
            per_page: perPage,
            sort_by: sortField,
            sort_direction: sortDirection
        });

        const response = await fetch(`/staff/tickets/data?${params}`, {
            method: 'GET',
            headers: {
                'Accept': 'application/json',
                'X-Requested-With': 'XMLHttpRequest'
            },
            credentials: 'include'
        });

        if (!response.ok) {
            throw new Error(`HTTP ${response.status}: ${response.statusText}`);
        }

        const data = await response.json();

        updateCounts(data.counts);
        renderTickets(data.tickets);
        renderPagination(data.pagination);

    } catch (error) {
        console.error('Error loading tickets:', error);
        showErrorState('Failed to load tickets. Please try again.');
    }
}

function updateCounts(counts) {
    Object.keys(counts).forEach(key => {
        const element = document.getElementById(`count-${key}`);
        if (element) {
            element.textContent = counts[key];
        }
    });
}

function renderTickets(tickets) {
    const tbody = document.getElementById('ticketsTableBody');
    if (!tbody) return;

    if (!tickets || tickets.length === 0) {
        tbody.innerHTML = `
            <tr>
                <td colspan="4" class="px-4 py-12 text-center">
                    <div class="flex flex-col items-center">
                        <svg class="h-12 w-12 text-gray-400 mb-4" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                        </svg>
                        <h3 class="text-sm font-medium text-gray-900 mb-1">No tickets found</h3>
                        <p class="text-sm text-gray-500">No tickets match the current filter.</p>
                    </div>
                </td>
            </tr>
        `;
        return;
    }

    const rows = tickets.map(ticket => {
        const statusClass = getStatusClass(ticket.status);
        const createdDate = formatDate(ticket.date_created);

        return `
            <tr class="hover:bg-gray-50 cursor-pointer" data-id="${ticket.id}">
                <td class="px-4 py-4 whitespace-nowrap">
                    <div class="text-sm font-medium text-indigo-600">#${ticket.id}</div>
                </td>
                <td class="px-4 py-4">
                    <div class="text-sm text-gray-900 max-w-xs truncate" title="${ticket.question || ''}">${ticket.question || 'No Concern'}</div>
                    <div class="flex items-center gap-2 mt-1">
                        <span class="inline-flex items-center rounded-full bg-gray-100 px-2 py-0.5 text-xs font-medium text-gray-700">${(ticket.category && typeof ticket.category === 'object') ? (ticket.category.name ?? 'Uncategorized') : (ticket.category || 'Uncategorized')}</span>
                    </div>
                </td>
                <td class="px-4 py-4 whitespace-nowrap">
                    <span class="inline-flex items-center gap-1 rounded-full px-2.5 py-1 text-xs font-medium ${statusClass}">
                        ${ticket.status}
                    </span>
                </td>
                <td class="px-4 py-4 whitespace-nowrap text-sm text-gray-500">
                    ${createdDate}
                </td>
            </tr>
        `;
    }).join('');

    tbody.innerHTML = rows;
}

function renderPagination(pagination) {
    // Update showing info
    document.getElementById('showingFrom').textContent = pagination.from || 0;
    document.getElementById('showingTo').textContent = pagination.to || 0;
    document.getElementById('totalResults').textContent = pagination.total || 0;

    // Update navigation buttons
    document.getElementById('prevPageBtn').disabled = pagination.current_page <= 1;
    document.getElementById('nextPageBtn').disabled = pagination.current_page >= pagination.last_page;
    document.getElementById('mobilePrevBtn').disabled = pagination.current_page <= 1;
    document.getElementById('mobileNextBtn').disabled = pagination.current_page >= pagination.last_page;

    // Render page numbers (simplified)
    const pageNumbers = document.getElementById('pageNumbers');
    pageNumbers.innerHTML = '';

    for (let i = Math.max(1, pagination.current_page - 2); i <= Math.min(pagination.last_page, pagination.current_page + 2); i++) {
        const button = document.createElement('button');
        button.className = `relative inline-flex items-center px-4 py-2 border text-sm font-medium ${
            i === pagination.current_page
                ? 'z-10 bg-indigo-50 border-indigo-500 text-indigo-600'
                : 'bg-white border-gray-300 text-gray-500 hover:bg-gray-50'
        }`;
        button.textContent = i;
        button.onclick = () => changePage(i);
        pageNumbers.appendChild(button);
    }
}

function setActiveFilter(filter, shouldReload = true) {
    currentFilter = filter;

    // Update tab appearance
    document.querySelectorAll('.filter-tab').forEach(tab => {
        tab.classList.remove('active', 'border-indigo-500', 'text-indigo-600');
        tab.classList.add('border-transparent', 'text-gray-500');

        if (tab.dataset.filter === filter) {
            tab.classList.add('active', 'border-indigo-500', 'text-indigo-600');
            tab.classList.remove('border-transparent', 'text-gray-500');
        }
    });

    // Update table title
    const titles = {
        all: 'All Tickets',
        open: 'Open Tickets',
        forwarded: 'Forwarded Tickets',
        closed: 'Closed Tickets'
    };

    document.getElementById('tableTitle').textContent = titles[filter] || 'Tickets';

    currentPage = 1;

    if (shouldReload) {
        loadTickets();
    }
}

function changePage(page) {
    if (page < 1) return;
    currentPage = page;
    loadTickets();
}

function updateSortIcons() {
    document.querySelectorAll('.sort-icon').forEach(icon => {
        icon.className = 'w-4 h-4 text-gray-400 group-hover:text-gray-600 sort-icon';
    });

    const activeButton = document.querySelector(`[data-sort="${sortField}"]`);
    if (activeButton) {
        const icon = activeButton.querySelector('.sort-icon');
        if (icon) {
            icon.className = `w-4 h-4 ${sortDirection === 'asc' ? 'text-indigo-600' : 'text-indigo-600 rotate-180'} sort-icon`;
        }
    }
}

function getStatusClass(status) {
    const classes = {
        'Open': 'text-blue-700 bg-blue-50 ring-blue-600/20',
        'Forwarded': 'text-amber-700 bg-amber-50 ring-amber-600/20',
        'Closed': 'text-emerald-700 bg-emerald-50 ring-emerald-600/20'
    };
    return classes[status] || 'text-slate-700 bg-slate-50 ring-slate-600/20';
}

function formatDate(dateString) {
    if (!dateString) return '-';
    try {
        const date = new Date(dateString);
        return date.toLocaleDateString();
    } catch (error) {
        return '-';
    }
}

function showErrorState(message = 'Failed to load tickets. Please try again.') {
    const tbody = document.getElementById('ticketsTableBody');
    if (tbody) {
        tbody.innerHTML = `
            <tr>
                <td colspan="4" class="px-4 py-12 text-center">
                    <div class="flex flex-col items-center">
                        <svg class="h-8 w-8 text-red-400 mb-4" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                        <p class="text-gray-500">${message}</p>
                        <button onclick="loadTickets()" class="mt-2 text-indigo-600 hover:text-indigo-500 text-sm font-medium">
                            Retry
                        </button>
                    </div>
                </td>
            </tr>
        `;
    }
}

function openTicket(id) {
    window.openModalFor(id);
}

// Modal functionality copied from admin dashboard
(function() {
    const state = document.createElement('div');
    state.id = 'staff-tickets-state';
    state.setAttribute('data-show-url-template', "{{ url('/staff/tickets') }}/__ID__");
    state.setAttribute('data-respond-url-template', "{{ url('/staff/tickets') }}/__ID__/respond");
    state.setAttribute('data-forward-url-template', "{{ url('/staff/tickets') }}/__ID__/forward");
    document.body.appendChild(state);

    const SHOW_TEMPLATE = state.getAttribute('data-show-url-template');
    const RESPOND_TEMPLATE = state.getAttribute('data-respond-url-template');
    const FORWARD_TEMPLATE = state.getAttribute('data-forward-url-template');
    const csrf = document.querySelector('meta[name="csrf-token"]').getAttribute('content');

    // Attachment URL helper (works even when /public/storage symlink is missing on hosted setups)
    const ATTACHMENT_BASE = "{{ url('/attachments') }}";
    function attachmentUrl(p) {
        if (!p) return '';
        return ATTACHMENT_BASE + '/' + String(p).split('/').map(encodeURIComponent).join('/');
    }

    const ticketModal = document.getElementById('ticketModal');

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
        return String(s).replace(/&/g, '&').replace(/</g, '<').replace(/>/g, '>').replace(/"/g,
            '"').replace(/'/g, "&#039;");
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
                console.error('Staff: failed to load ticket', res.status);
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
            const category = (t.category && typeof t.category === 'object') ? (t.category.name ?? '') : (t.category ?? '');
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
            const tmStoredResponseBlock = document.getElementById('tmStoredResponseBlock');
            const tmStoredResponse = document.getElementById('tmStoredResponse');
            const tmSendResponse = document.getElementById('tmSendResponse');

            if (tmOptionForward) tmOptionForward.classList.toggle('hidden', isClosed);
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
            console.error('Staff: error loading ticket', err);
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
            // Show spinner and disable button
            const originalButtonHtml = tmForwardApply.innerHTML;
            try {
                tmForwardApply.disabled = true;
                tmForwardApply.classList.add('opacity-50', 'pointer-events-none');
                tmForwardApply.innerHTML = '<svg class="animate-spin -ml-1 mr-2 h-4 w-4 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg>Forwarding...';

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
                    // Refresh the tickets list
                    loadTickets();
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
                // Re-enable button and restore original text
                tmForwardApply.disabled = false;
                tmForwardApply.classList.remove('opacity-50', 'pointer-events-none');
                tmForwardApply.innerHTML = originalButtonHtml;
            }
        });
    }

    // Send response with SweetAlert
    const tmSendResponse = document.getElementById('tmSendResponse');
    const tmResponse = document.getElementById('tmResponse');

    if (tmSendResponse && tmResponse) {
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
            try {
                tmSendResponse.disabled = true;
                tmSendResponse.classList.add('opacity-50', 'pointer-events-none');
                // Show spinner
                tmSendResponse.innerHTML = `<svg class="animate-spin h-4 w-4" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg> Sending...`;
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
                    // Refresh the tickets list
                    loadTickets();
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
                tmSendResponse.disabled = false;
                tmSendResponse.classList.remove('opacity-50', 'pointer-events-none');
                // Restore button text
                tmSendResponse.innerHTML = `<svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" viewBox="0 0 24 24" fill="currentColor"><path d="M3 12l18-9-9 18-2-7-7-2z" /></svg> Send Response`;
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

    // Make openModalFor available globally
    window.openModalFor = openModalFor;
})();
</script>
