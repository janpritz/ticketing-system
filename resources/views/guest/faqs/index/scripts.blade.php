<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
    function showFaqModal(question, answer) {
        const modalEl = document.getElementById('faq-modal');
        if (!modalEl || !window.Alpine) return;

        const data = Alpine.$data(modalEl);
        data.question = question;
        data.answer = answer;
        data.show = true;
    }

    function copyAnswer() {
        const modalEl = document.getElementById('faq-modal');
        if (!modalEl || !window.Alpine) return;

        const data = Alpine.$data(modalEl);
        if (data.answer) {
            navigator.clipboard.writeText(data.answer).then(() => {
                const toast = document.createElement('div');
                toast.className = 'fixed bottom-6 left-1/2 -translate-x-1/2 bg-gray-800 text-white text-sm px-5 py-2.5 rounded-2xl shadow-lg';
                toast.textContent = '✓ Copied to clipboard';
                document.body.appendChild(toast);
                setTimeout(() => toast.remove(), 2000);
            });
        }
    }
</script>
<script>
function showFaqModal(q, a) {
    const modal = document.getElementById('faq-modal').__x;
    if (modal) {
        modal.question = q;
        modal.answer = a;
        modal.show = true;
    }
}

function copyAnswer() {
    const modal = document.getElementById('faq-modal').__x;
    if (modal && modal.answer) {
        navigator.clipboard.writeText(modal.answer).then(() => {
            // Optional: Show toast
            const toast = document.createElement('div');
            toast.className = 'fixed bottom-6 left-1/2 -translate-x-1/2 bg-gray-800 text-white text-sm px-5 py-2.5 rounded-2xl shadow-lg';
            toast.textContent = '✓ Copied to clipboard';
            document.body.appendChild(toast);
            setTimeout(() => toast.remove(), 2000);
        });
    }
}
</script>