<script>
    // Helper function to get cookie value
    function getCookie(name) {
        const nameEQ = name + "=";
        const cookies = document.cookie.split(';');
        for (let i = 0; i < cookies.length; i++) {
            let cookie = cookies[i].trim();
            if (cookie.indexOf(nameEQ) === 0) {
                return decodeURIComponent(cookie.substring(nameEQ.length));
            }
        }
        return null;
    }

    
    // Check if OTP session is active before loading the page
    document.addEventListener('DOMContentLoaded', function() {
        const verifiedEmail = getCookie('verified_email');
        if (!verifiedEmail) {
            // No OTP session, redirect to OTP verification
            window.location.href = '{{ route('tickets.verify-otp') }}';
        }
    });
</script>
<script src="https://www.google.com/recaptcha/api.js" async defer></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Add photo button
        document.getElementById('add-photo-btn-submit').addEventListener('click', function() {
            document.getElementById('attachments').click();
        });

        // Validate attachments count and display thumbnails
        document.getElementById('attachments').addEventListener('change', function(e) {
            const files = e.target.files;
            const container = document.getElementById('selected-thumbnails-submit');
            container.innerHTML = ''; // Clear previous

            if (files.length > 5) {
                Swal.fire({
                    icon: 'error',
                    title: 'Too many files',
                    text: 'You can upload a maximum of 5 images.'
                });
                e.target.value = ''; // Clear selection
                return;
            }

            Array.from(files).forEach((file, index) => {
                if (file.type.startsWith('image/')) {
                    const reader = new FileReader();
                    reader.onload = function(e) {
                        const div = document.createElement('div');
                        div.className = 'relative';
                        div.innerHTML = `
                                                <img src="${e.target.result}" alt="Selected ${index + 1}" class="w-full h-20 object-cover rounded border">
                                                <button type="button" class="absolute top-0 right-0 bg-red-500 text-white rounded-full w-4 h-4 text-xs remove-selected-submit" data-index="${index}">&times;</button>
                                            `;
                        container.appendChild(div);
                    };
                    reader.readAsDataURL(file);
                }
            });
        });

        // Remove selected thumbnail
        document.addEventListener('click', function(e) {
            if (e.target.classList.contains('remove-selected-submit')) {
                const index = e.target.getAttribute('data-index');
                const input = document.getElementById('attachments');
                const dt = new DataTransfer();
                const files = Array.from(input.files);
                files.splice(index, 1);
                files.forEach(file => dt.items.add(file));
                input.files = dt.files;
                // Re-trigger change to update thumbnails
                input.dispatchEvent(new Event('change'));
            }
        });

        // Form submission loading state
        document.getElementById('submitTicketBtn').addEventListener('click', function(e) {
            const submitBtn = this;
            const form = this.closest('form');

            // Basic validation
            const email = document.getElementById('email').value.trim();
            const category = document.getElementById('category').value.trim();
            const question = document.getElementById('question').value.trim();

            if (!email || !category || !question) {
                e.preventDefault();
                Swal.fire({
                    icon: 'error',
                    title: 'Validation Error',
                    text: 'Please fill in email, category and question fields.'
                });
                return;
            }

            // Show loading state
            const originalText = submitBtn.innerHTML;
            submitBtn.innerHTML =
                '<svg class="animate-spin -ml-1 mr-3 h-5 w-5 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg>Creating...';
            submitBtn.disabled = true;

            // Allow form submission to continue
            form.submit();
        });

        // Privacy consent checkbox enables submit button
        const consentCheckbox = document.getElementById('privacy-consent');
        const submitBtn = document.getElementById('submitTicketBtn');
        if (consentCheckbox && submitBtn) {
            consentCheckbox.addEventListener('change', function() {
                submitBtn.disabled = !this.checked;
            });
        }

        // Map category name -> id and keep hidden role_id in sync
        const categoryMapCreate = {};
        @if (isset($roles) && count($roles))
            @foreach ($roles as $id => $name)
                categoryMapCreate["{{ addslashes($name) }}"] = "{{ $id }}";
            @endforeach
        @endif

        const cnameInput = document.getElementById('category');
        const cidInput = document.getElementById('role_id_input');
        if (cnameInput) {
            cnameInput.addEventListener('input', function() {
                const v = this.value.trim();
                if (v && categoryMapCreate[v]) {
                    cidInput.value = categoryMapCreate[v];
                } else {
                    cidInput.value = '';
                }
            });
        }
    });
</script>
