<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
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
    //console.log('All cookies:', document.cookie);
    console.log('verified_email cookie:', getCookie('verified_email'));
    console.log(window.AppConfig);

    document.addEventListener('DOMContentLoaded', function() {
        // 1. Grab the button
        const createTicketBtn = document.getElementById('createTicketBtn');

        // 2. ONLY run the logic when the button is actually clicked
        if (createTicketBtn) {
            createTicketBtn.addEventListener('click', function(e) {
                e.preventDefault();

                // Read the plain-text cookie
                const verifiedEmail = getCookie('verified_email');

                console.log('Plain Cookie Found:', verifiedEmail);

                if (verifiedEmail && verifiedEmail !== 'deleted' && verifiedEmail !== "") {
                    // Decode it (e.g., %40 back to @) and strip any outer quotes
                    const cleanEmail = decodeURIComponent(verifiedEmail).replace(/^"|"$/g, '');

                    // Redirect to the email-based path, removing the recepient_id
                    const targetUrl = `/tickets/create/${encodeURIComponent(cleanEmail)}`;

                    window.location.href = targetUrl;
                } else {
                    // No cookie? Send them to verify
                    window.location.href = "{{ route('tickets.verify-otp') }}";
                }
            });
        }

        // Add photo button
        document.getElementById('add-photo-btn').addEventListener('click', function() {
            document.getElementById('edit-attachments').click();
        });

        // Validate attachments count for edit and display thumbnails
        document.getElementById('edit-attachments').addEventListener('change', function(e) {
            const files = e.target.files;
            const container = document.getElementById('selected-thumbnails');
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
                            <button type="button" class="absolute top-0 right-0 bg-red-500 text-white rounded-full w-4 h-4 text-xs remove-selected" data-index="${index}">&times;</button>
                        `;
                        container.appendChild(div);
                    };
                    reader.readAsDataURL(file);
                }
            });
        });

        // Remove selected thumbnail
        document.addEventListener('click', function(e) {
            if (e.target.classList.contains('remove-selected')) {
                const index = e.target.getAttribute('data-index');
                const input = document.getElementById('edit-attachments');
                const dt = new DataTransfer();
                const files = Array.from(input.files);
                files.splice(index, 1);
                files.forEach(file => dt.items.add(file));
                input.files = dt.files;
                // Re-trigger change to update thumbnails
                input.dispatchEvent(new Event('change'));
            }
        });

        // Handle delete attachments
        document.addEventListener('click', function(e) {
            if (e.target.classList.contains('delete-attachment')) {
                const path = e.target.getAttribute('data-path');
                let deleteList = document.getElementById('delete-attachments').value;
                deleteList = deleteList ? JSON.parse(deleteList) : [];
                deleteList.push(path);
                document.getElementById('delete-attachments').value = JSON.stringify(deleteList);
                e.target.parentElement.remove();
            }
        });

        // Handle viewing attachments
        document.addEventListener('click', function(e) {
            if (e.target.classList.contains('view-attachment')) {
                const path = e.target.getAttribute('data-path');
                const attachmentList = e.target.closest('#edit-attachments-container').querySelectorAll(
                    '.view-attachment');
                const images = Array.from(attachmentList).map(img => img.getAttribute('data-path'));
                const currentIndex = images.indexOf(path);

                if (window.openLightbox) {
                    openLightbox(images, currentIndex);
                } else {
                    // Fallback for lightbox if not available
                    window.open(`/storage/${path}`, '_blank');
                }
            }
        });

        // Add event listeners to all edit buttons
        document.querySelectorAll('.edit-ticket-btn').forEach(button => {
            button.addEventListener('click', function() {
                const ticketId = this.getAttribute('data-id');
                const role = this.getAttribute('data-role');
                const question = this.getAttribute('data-question');
                const attachments = this.getAttribute('data-attachments');

                // Set form values
                document.getElementById('edit-ticket-id').value = ticketId;
                document.getElementById('edit-category').value = role;
                document.getElementById('edit-question').value = question;
                document.getElementById('edit-attachments').value = '';
                document.getElementById('delete-attachments').value = '';

                // Handle attachments
                const attachmentsContainer = document.getElementById(
                    'edit-attachments-container');
                attachmentsContainer.innerHTML = '';
                if (attachments) {
                    try {
                        const attachmentList = JSON.parse(attachments);
                        attachmentList.forEach((path, index) => {
                            const div = document.createElement('div');
                            div.className = 'relative';
                            div.innerHTML = `
                                <img src="/storage/${path}" alt="Attachment ${index + 1}" class="w-full h-20 object-cover rounded border cursor-pointer hover:opacity-90 view-attachment" data-path="${path}">
                                <button type="button" class="absolute top-1 right-1 bg-red-500 text-white rounded-full w-5 h-5 text-xs sm:w-6 sm:h-6 sm:text-sm hover:bg-red-600 transition-colors delete-attachment" data-path="${path}">×</button>
                            `;
                            attachmentsContainer.appendChild(div);
                        });
                    } catch (e) {
                        console.error('Error parsing attachments:', e);
                    }
                }

                // Update form action URL
                const form = document.getElementById('editTicketForm');
                form.action = "/tickets/" + ticketId;

                // Show modal
                document.getElementById('editModal').classList.remove('hidden');
            });

            // Submit handler for the admin-style edit modal (wire the Update button)
            const editSubmitBtn = document.getElementById('editTicketSubmitBtn');
            if (editSubmitBtn) {
                editSubmitBtn.addEventListener('click', function() {
                    const form = document.getElementById('editTicketForm');
                    if (form) form.submit();
                });
            }
        });

        // Add event listeners to all delete buttons (SweetAlert2 confirmation)
        document.querySelectorAll('.delete-ticket-btn').forEach(button => {
            button.addEventListener('click', function() {
                const ticketId = this.getAttribute('data-id');
                const role = this.getAttribute('data-role');

                if (window.Swal) {
                    Swal.fire({
                        title: 'Delete ticket?',
                        text: `Are you sure you want to delete the ticket "${role}"? This action cannot be undone.`,
                        icon: 'warning',
                        showCancelButton: true,
                        confirmButtonColor: '#d33',
                        cancelButtonColor: '#6b7280',
                        confirmButtonText: 'Yes, delete',
                        cancelButtonText: 'Cancel',
                        reverseButtons: true,
                    }).then((result) => {
                        if (result.isConfirmed) {
                            // Create a form dynamically
                            const form = document.createElement('form');
                            form.method = 'POST';
                            form.action = `/tickets/${ticketId}`;

                            // Add CSRF token
                            const csrfToken = document.createElement('input');
                            csrfToken.type = 'hidden';
                            csrfToken.name = '_token';
                            csrfToken.value = document.querySelector(
                                'meta[name="csrf-token"]').getAttribute('content');
                            form.appendChild(csrfToken);

                            // Add method field for DELETE
                            const methodField = document.createElement('input');
                            methodField.type = 'hidden';
                            methodField.name = '_method';
                            methodField.value = 'DELETE';
                            form.appendChild(methodField);

                            // Submit the form
                            try {
                                localStorage.setItem('ts_tickets_changed', String(Date
                                    .now()));
                            } catch (e) {}
                            document.body.appendChild(form);
                            form.submit();
                        }
                    });
                } else {
                    // Fallback if SweetAlert2 is not available
                    if (confirm(`Are you sure you want to delete the ticket "${role}"?`)) {
                        const form = document.createElement('form');
                        form.method = 'POST';
                        form.action = `/tickets/${ticketId}`;

                        const csrfToken = document.createElement('input');
                        csrfToken.type = 'hidden';
                        csrfToken.name = '_token';
                        csrfToken.value = document.querySelector('meta[name="csrf-token"]')
                            .getAttribute('content');
                        form.appendChild(csrfToken);

                        const methodField = document.createElement('input');
                        methodField.type = 'hidden';
                        methodField.name = '_method';
                        methodField.value = 'DELETE';
                        form.appendChild(methodField);

                        try {
                            localStorage.setItem('ts_tickets_changed', String(Date.now()));
                        } catch (e) {}
                        document.body.appendChild(form);
                        form.submit();
                    }
                }
            });
        });
    });

    // Show SweetAlert for success message
    @if (session('success'))
        Swal.fire({
            icon: 'success',
            title: 'Success',
            text: '{{ session('success') }}',
            position: 'top-end',
            timer: 3000,
            timerProgressBar: true,
            showConfirmButton: false,
            toast: true
        });
    @endif
</script>
