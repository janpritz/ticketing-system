<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
    document.addEventListener('DOMContentLoaded', function() {
        @if (session('error'))
            Swal.fire({
                toast: true,
                icon: 'error',
                title: 'Session Expired',
                text: "{{ session('error') }}",
                showConfirmButton: false,
                timer: 4000,
                timerProgressBar: true,
                position: 'top-end'
            });
        @endif

        const faqCards = document.querySelectorAll('.faq-card');
        faqCards.forEach((card, idx) => {
            card.addEventListener('click', () => selectQuestion(idx));
        });

        const messageInput = document.getElementById('messageInput');
        if (messageInput) {
            messageInput.addEventListener('keypress', function(e) {
                if (e.key === 'Enter') sendMessage();
            });
        }
    });

    function selectQuestion(index) {
        const cards = document.querySelectorAll('.faq-card');
        if (!cards[index]) return;
        const questionText = cards[index].querySelector('p').textContent.trim();
        showFaqModal(questionText, "Thank you for your question! Our team will get back to you shortly or check the chatbot for instant answers.");
    }

    function sendMessage() {
        const input = document.getElementById('messageInput');
        if (!input || !input.value.trim()) return;
        const message = input.value.trim();
        showFaqModal("Your question", message + "<br><br>Thank you! We'll respond via the chatbot or email shortly.");
        input.value = '';
    }

    function showFaqModal(question, answer) {
        const modal = document.getElementById('faqModal');
        document.getElementById('modalQuestion').textContent = question;
        document.getElementById('modalAnswer').innerHTML = answer;
        modal.classList.remove('hidden');
        modal.classList.add('flex');
    }

    function closeModal() {
        const modal = document.getElementById('faqModal');
        modal.classList.remove('flex');
        modal.classList.add('hidden');
    }
</script>
