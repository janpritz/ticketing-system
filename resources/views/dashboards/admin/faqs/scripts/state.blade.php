const stateEl = document.getElementById('admin-faqs-state');

export const Config = {
    listUrl: stateEl?.getAttribute('data-list-url'),
    updateStatusUrl: stateEl?.getAttribute('data-update-status-url'),
    processAnalysisUrl: stateEl?.getAttribute('data-process-analysis-url'),
    unprocessedCount: parseInt(stateEl?.getAttribute('data-unprocessed-tickets') || '0', 10),
    csrf: document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '',
    currentPage: 1
};