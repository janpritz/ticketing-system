const Utils = {
    escapeHtml(s) {
        if (s == null) return '';
        return String(s).replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;').replace(/"/g, '&quot;').replace(/'/g, "&#039;");
    },
    getStatusClass(status) {
        const classes = {
            'publish': 'text-green-700 bg-green-50 ring-green-600/20',
            'pending': 'text-yellow-700 bg-yellow-50 ring-yellow-600/20',
            'unpublish': 'text-red-700 bg-red-50 ring-red-600/20'
        };
        return classes[status] || 'text-slate-700 bg-slate-50 ring-slate-600/20';
    }
};