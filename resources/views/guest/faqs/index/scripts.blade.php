<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
    document.addEventListener('DOMContentLoaded', function() {

        //Load Any errors
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
        // FAQ Accordion Toggle
        const faqHeaders = document.querySelectorAll('.faq-header');

        faqHeaders.forEach(header => {
            header.addEventListener('click', function() {
                const item = this.closest('.faq-item');
                const body = item.querySelector('.faq-body');
                const chevron = item.querySelector('.faq-chevron');

                // Toggle the hidden class
                body.classList.toggle('hidden');

                // Rotate the chevron
                if (chevron) {
                    chevron.classList.toggle('rotate-180');
                }
            });
        });

        // Search Functionality
        const searchInput = document.getElementById('searchInput');
        if (searchInput) {
            searchInput.addEventListener('input', function() {
                const searchTerm = this.value.toLowerCase();
                const faqItems = document.querySelectorAll('.faq-item');

                faqItems.forEach(item => {
                    const question = item.querySelector('h3').textContent.toLowerCase();
                    const topic = item.querySelector('.text-gray-500')?.textContent
                    .toLowerCase() || '';

                    if (question.includes(searchTerm) || topic.includes(searchTerm)) {
                        item.style.display = '';
                    } else {
                        item.style.display = 'none';
                    }
                });
            });
        }
    });
</script>
