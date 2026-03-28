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
            this.tbody.innerHTML = '<tr><td colspan="5" class="px-5 py-6 text-center text-sm text-red-600">Error loading FAQs</td></tr>';
        }
    },

    render(items) {
        if (!items.length) {
            this.tbody.innerHTML = '<tr><td colspan="5" class="px-5 py-10 text-center text-sm text-gray-500">No FAQs found.</td></tr>';
            return;
        }

        this.tbody.innerHTML = items.map(f => `
            <tr class="hover:bg-gray-50">
                <td class="py-4 pl-5 pr-3">${Utils.escapeHtml((f.general_topic || '').slice(0, 50))}</td>
                <td class="px-3 py-4">${Utils.escapeHtml((f.suggested_q || '').slice(0, 80))}</td>
                <td class="px-3 py-4">${Utils.escapeHtml((f.suggested_a || '').slice(0, 100))}</td>
                <td class="px-3 py-4">
                    <span class="inline-flex items-center rounded-full px-3 py-1 text-xs font-medium ring-1 ${Utils.getStatusClass(f.status)}">
                        ${Utils.escapeHtml(f.status || '')}
                    </span>
                </td>
                <td class="px-3 py-4 space-x-2">${this.getActionButtons(f)}</td>
            </tr>
        `).join('');
    },

    getActionButtons(f) {
        if (f.status === 'pending') {
            return `
                <button onclick="changeStatus(${f.id}, 'approved')" class="px-3 py-1 bg-green-600 text-white text-xs rounded hover:bg-green-700">Approve</button>
                <button onclick="changeStatus(${f.id}, 'rejected')" class="px-3 py-1 bg-red-600 text-white text-xs rounded hover:bg-red-700">Reject</button>
            `;
        }
        const target = f.status === 'approved' ? 'rejected' : 'approved';
        const color = f.status === 'approved' ? 'red' : 'green';
        return `<button onclick="changeStatus(${f.id}, '${target}')" class="px-3 py-1 bg-${color}-600 text-white text-xs rounded hover:bg-${color}-700">${target.charAt(0).toUpperCase() + target.slice(1)}</button>`;
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

window.changeStatus = async (id, status) => {
    const result = await Swal.fire({
        title: 'Confirm Action',
        text: `Are you sure you want to ${status} this FAQ?`,
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: status === 'approved' ? '#10b981' : '#ef4444'
    });

    if (!result.isConfirmed) return;

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
    }
};