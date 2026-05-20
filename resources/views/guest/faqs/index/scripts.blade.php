<script>
    document.addEventListener('DOMContentLoaded', function () {

        // Laravel session error
        const sessionError = <?= json_encode(session('error')) ?>;

        if (sessionError) {
            Swal.fire({
                toast: true,
                icon: 'error',
                title: 'Session Expired',
                text: sessionError,
                showConfirmButton: false,
                timer: 4000,
                timerProgressBar: true,
                position: 'top-end'
            });
        }

        // Enter key support
        const messageInput = document.getElementById('messageInput');

        if (messageInput) {
            messageInput.addEventListener('keypress', function (e) {
                if (e.key === 'Enter' && !e.shiftKey) {
                    e.preventDefault();
                    sendMessage();
                }
            });
        }

        // Suggestion buttons
        document.addEventListener('click', function (e) {
            const btn = e.target.closest('.faq-suggest-btn');

            if (btn) {
                showFaqModal(
                    btn.dataset.question,
                    btn.dataset.answer
                );
            }
        });
    });

    function sendMessage() {
        const input = document.getElementById('messageInput');

        if (!input || !input.value.trim()) return;

        const message = input.value.trim();

        showFaqModal(
            message,
            "Thank you! We'll respond via the chatbot or email shortly."
        );

        input.value = '';
    }

    function showFaqModal(question, answer) {
        const modalEl = document.getElementById('faq-modal');

        if (!modalEl) return;

        const data = Alpine.$data(modalEl);

        if (data) {
            data.question = question;
            data.answer = answer;
            data.show = true;
        }
    }

    function copyAnswer() {
        const modalEl = document.getElementById('faq-modal');

        if (!modalEl) return;

        const data = Alpine.$data(modalEl);

        if (data?.answer) {
            navigator.clipboard.writeText(data.answer).then(() => {

                const toast = document.createElement('div');

                toast.className =
                    'fixed bottom-6 left-1/2 -translate-x-1/2 bg-gray-800 text-white text-sm px-5 py-2.5 rounded-2xl shadow-lg z-[60]';

                toast.textContent = '✓ Copied to clipboard';

                document.body.appendChild(toast);

                setTimeout(() => toast.remove(), 2000);
            });
        }
    }
</script>