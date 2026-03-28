document.addEventListener('DOMContentLoaded', () => {
    const countBadge = document.getElementById('unprocessed-count');
    if (countBadge) countBadge.textContent = Config.unprocessedCount;

    // Call your fetcher from faq-manager.js
    if (typeof fetchAndRenderFaqs === 'function') {
        fetchAndRenderFaqs(1);
    }

    document.getElementById('searchBtn')?.addEventListener('click', () => fetchAndRenderFaqs(1));
    document.getElementById('analyze-btn')?.addEventListener('click', startAnalysis);
});