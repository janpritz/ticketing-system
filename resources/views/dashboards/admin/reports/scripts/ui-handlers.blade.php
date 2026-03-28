<script>
    (function() {
        window.number_format = (num) => num.toLocaleString();

        window.updateTicketsSolvedTitle = function(days) {
            const el = document.getElementById('ticketsSolvedTitle');
            if (!el) return;
            el.textContent = `Tickets Solved (${parseInt(days, 10) || 30} Days)`;
        };

        window.updateTicketsSolvedTable = function(items) {
            const tbody = document.getElementById('ticketsSolvedBody');
            if (!tbody) return;
            tbody.innerHTML = (items.length === 0) ?
                '<tr><td colspan="2" class="px-3 py-6 text-center text-sm text-gray-500">No solved tickets.</td></tr>' :
                items.map(item => `
                <tr class="hover:bg-gray-50">
                    <td class="py-2 pl-3 pr-2 align-top text-gray-900">${item.name || 'Unknown'}</td>
                    <td class="px-2 py-2 align-top font-medium text-slate-900">${number_format(item.count || 0)}</td>
                </tr>`).join('');
        };

        window.showForwardsModal = function(payload) {
            const modal = document.getElementById('forwardsModal');
            if (!modal) return;
            modal.querySelector('.modal-title').textContent = `Forwards by: ${payload.forwarder || 'Unknown'}`;

            const body = modal.querySelector('.modal-body');
            if (!payload.recipients?.length) {
                body.innerHTML = '<p class="text-sm text-gray-500">No forwards found.</p>';
            } else {
                body.innerHTML = `<div class="space-y-4">${payload.recipients.map(r => `
                <div class="p-4 bg-white shadow-sm rounded-lg">
                    <div class="flex justify-between">
                        <span class="text-sm font-medium text-gray-900">${r.name}</span>
                        <span class="px-2 py-1 rounded-full text-xs font-medium bg-gray-100 text-gray-700">${r.count}</span>
                    </div>
                    <details class="mt-3 bg-gray-50 rounded-lg p-2">
                        <summary class="text-sm cursor-pointer">View questions</summary>
                        <ul class="mt-2 space-y-2">
                            ${(r.tickets || []).map(t => `<li class="text-sm text-gray-700 bg-white px-3 py-2 rounded">${t.question || 'No question'}</li>`).join('')}
                        </ul>
                    </details>
                </div>`).join('')}</div>`;
            }
            modal.classList.remove('hidden');
        };

        document.addEventListener('click', (e) => {
            if (e.target.closest('[data-modal-backdrop]') || e.target.closest('[data-modal-close]')) {
                document.getElementById('forwardsModal')?.classList.add('hidden');
            }
        });
    })();
</script>
