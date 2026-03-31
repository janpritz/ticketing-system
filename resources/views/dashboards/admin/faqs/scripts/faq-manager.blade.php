// 📊 Table UI & Render Logic
const faqManager = {
    tbody: document.getElementById('faqsTbody'),
    pagination: document.getElementById('faqsPagination'),

    async fetchList(page = 1) {
        Config.currentPage = page;

        try {
            const q = document.getElementById('q')?.value.trim() || document.getElementById('q_mobile')?.value.trim() || '';
            const status = document.getElementById('filterStatus')?.value || 'pending';
            const per = document.getElementById('filterPerPage')?.value || '25';

            const sep = Config.listUrl.includes('?') ? '&' : '?';
            let url = `${Config.listUrl}${sep}page=${page}&per_page=${per}&status=${status}`;
            if (q) url += `&search=${encodeURIComponent(q)}`;

            const data = await apiFetch(url); // 📡 Uses shared http.js
            this.render(data.items || []);
            this.renderPagination(data.meta || {});
        } catch (err) {
            console.error('[FaqManager Error]:', err);
            this.tbody.innerHTML = '<tr><td colspan="4" class="px-5 py-6 text-center text-sm text-red-600">Error loading FAQs</td></tr>';
        }
    },

    render(items) {
        if (!items.length) {
            this.tbody.innerHTML = '<tr><td colspan="4" class="px-5 py-10 text-center text-sm text-gray-500">No FAQs found.</td></tr>';
            return;
        }

        this.tbody.innerHTML = items.map(f => `
            <tr class="hover:bg-gray-50">
                <td class="py-4 pl-5 pr-3">${Utils.escapeHtml((f.general_topic || '').slice(0, 50))}</td>
                <td class="px-3 py-4">${Utils.escapeHtml((f.suggested_q || '').slice(0, 80))}</td>
                <td class="px-3 py-4">${Utils.escapeHtml((f.suggested_a || '').slice(0, 100))}</td>
                <td class="px-3 py-4 space-x-2">${this.getActionButtons(f)}</td>
            </tr>
        `).join('');
    },

    getActionButtons(f) {
        const pending = 'pending', publish = 'publish', unpublish = 'unpublish';
        
        // Pending: can publish or reject
        if (f.status === pending) {
            return `
                <button onclick="changeStatus(${f.id}, '${publish}', this)" class="px-3 py-1 bg-blue-600 text-white text-xs rounded hover:bg-blue-700 transition-colors disabled:opacity-50" data-faq-id="${f.id}">Publish</button>
                <button onclick="changeStatus(${f.id}, '${unpublish}', this)" class="px-3 py-1 bg-gray-600 text-white text-xs rounded hover:bg-gray-700 transition-colors disabled:opacity-50" data-faq-id="${f.id}">Reject</button>
            `;
        }
        
        // Published: can unpublish (reject)
        if (f.status === publish) {
            return `<button onclick="changeStatus(${f.id}, '${unpublish}', this)" class="px-3 py-1 bg-gray-600 text-white text-xs rounded hover:bg-gray-700 transition-colors disabled:opacity-50" data-faq-id="${f.id}">Unpublish</button>`;
        }
        
        // Unpublished (rejected): can republish
        if (f.status === unpublish) {
            return `<button onclick="changeStatus(${f.id}, '${publish}', this)" class="px-3 py-1 bg-blue-600 text-white text-xs rounded hover:bg-blue-700 transition-colors disabled:opacity-50" data-faq-id="${f.id}">Publish</button>`;
        }
        
        // Fallback for any other status
        return `<button onclick="changeStatus(${f.id}, '${publish}', this)" class="px-3 py-1 bg-blue-600 text-white text-xs rounded hover:bg-blue-700 transition-colors disabled:opacity-50" data-faq-id="${f.id}">Publish</button>`;
    },

    renderPagination(meta) {
        if (!meta || !meta.total) { this.pagination.innerHTML = ''; return; }

        const pages = [];
        for (let i = Math.max(1, meta.current_page - 2); i <= Math.min(meta.last_page, meta.current_page + 2); i++) pages.push(i);

        this.pagination.innerHTML = `
            <div class="flex items-center gap-3"><div class="text-sm text-slate-600">Showing ${meta.per_page} per page — ${meta.total} total</div></div>
            <div class="flex items-center gap-2">
                <button ${meta.current_page <= 1 ? 'disabled' : ''} onclick="fetchAndRenderFaqs(${meta.current_page - 1})" class="rounded-md border bg-white px-3 py-1 text-sm ${meta.current_page <= 1 ? 'opacity-50' : 'hover:bg-gray-50'}">Prev</button>
                ${pages.map(p => `<button onclick="fetchAndRenderFaqs(${p})" class="rounded-md ${p === meta.current_page ? 'bg-blue-600 text-white' : 'border bg-white text-sm hover:bg-gray-50'} px-3 py-1">${p}</button>`).join('')}
                <button ${meta.current_page >= meta.last_page ? 'disabled' : ''} onclick="fetchAndRenderFaqs(${meta.current_page + 1})" class="rounded-md border bg-white px-3 py-1 text-sm ${meta.current_page >= meta.last_page ? 'opacity-50' : 'hover:bg-gray-50'}">Next</button>
            </div>
        `;
    }
};

// Global hooks for standard HTML onclick bindings
window.fetchAndRenderFaqs = (page) => faqManager.fetchList(page);

// Spinner SVG for loading state
const SPINNER_SVG = `<svg class="animate-spin h-4 w-4" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg>`;

function setButtonLoading(btn, isLoading, originalText = null) {
    if (isLoading) {
        btn.disabled = true;
        btn.dataset.originalText = btn.innerHTML;
        btn.innerHTML = SPINNER_SVG;
        btn.classList.add('opacity-75');
    } else {
        btn.disabled = false;
        btn.innerHTML = btn.dataset.originalText || originalText;
        btn.classList.remove('opacity-75');
    }
}

function getAllActionButtons() {
    return document.querySelectorAll('#faqsTbody button[data-faq-id]');
}

function disableAllActionButtons(disable = true) {
    getAllActionButtons().forEach(btn => {
        btn.disabled = disable;
        if (disable) btn.classList.add('opacity-50', 'cursor-not-allowed');
        else btn.classList.remove('opacity-50', 'cursor-not-allowed');
    });
}

window.changeStatus = async (id, status, btn) => {
    const result = await Swal.fire({
        title: 'Confirm Action',
        text: `Are you sure you want to ${status} this FAQ?`,
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: status === 'publish' ? '#3b82f6' : '#6b7280',
        cancelButtonColor: '#6b7280',
        confirmButtonText: 'Yes, continue',
        cancelButtonText: 'Cancel'
    });

    if (!result.isConfirmed) return;

    // Show loading state on the clicked button
    setButtonLoading(btn, true);
    // Disable all action buttons to prevent multiple clicks
    disableAllActionButtons(true);

    try {
        const data = await apiFetch(Config.updateStatusUrl, 'POST', { id, status });
        if (data.success) {
            Swal.fire('Success!', `FAQ ${status} successfully!`, 'success');
            faqManager.fetchList(Config.currentPage);
        } else {
            throw new Error(data.message || 'Update failed');
        }
    } catch (err) {
        Swal.fire('Error!', err.message, 'error');
    } finally {
        // Reset button state (will be restored when table re-renders)
        disableAllActionButtons(false);
    }
};