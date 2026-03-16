<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<script>
    /**
     * Helper: Get Cookie Value
     */
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

    document.addEventListener('DOMContentLoaded', function() {

        // --- 1. SUCCESS NOTIFICATION ---
        @if (session('success'))
            Swal.fire({
                icon: 'success',
                title: 'Success!',
                text: "{{ session('success') }}",
                toast: true,
                position: 'top-end',
                showConfirmButton: false,
                timer: 4000,
                timerProgressBar: true,
                background: '#ffffff',
                iconColor: '#22c55e'
            });
        @endif

        // --- 2. ERROR NOTIFICATION ---
        @if ($errors->any())
            Swal.fire({
                icon: 'error',
                title: 'Validation Error',
                text: "{{ $errors->first() }}",
                confirmButtonColor: '#ef4444'
            });
        @endif

        // --- 3. CREATE TICKET LOGIC ---
        const createTicketBtn = document.getElementById('createTicketBtn');
        if (createTicketBtn) {
            createTicketBtn.addEventListener('click', function(e) {
                e.preventDefault();
                const verifiedEmail = getCookie('verified_email');

                if (verifiedEmail && verifiedEmail !== 'deleted' && verifiedEmail !== "") {
                    const cleanEmail = decodeURIComponent(verifiedEmail).replace(/^"|"$/g, '');
                    window.location.href = `/tickets/create/${encodeURIComponent(cleanEmail)}`;
                } else {
                    window.location.href = "{{ route('tickets.verify-otp') }}";
                }
            });
        }

        // --- 4. NEW ATTACHMENT HANDLING (PREVIEW) ---
        const addPhotoBtn = document.getElementById('add-photo-btn');
        if (addPhotoBtn) {
            addPhotoBtn.addEventListener('click', () => {
                document.getElementById('edit-attachments').click();
            });
        }

        const editAttachmentsInput = document.getElementById('edit-attachments');
        if (editAttachmentsInput) {
            editAttachmentsInput.addEventListener('change', function(e) {
                const files = e.target.files;
                const container = document.getElementById('selected-thumbnails');
                container.innerHTML = '';

                if (files.length > 5) {
                    Swal.fire({
                        icon: 'error',
                        title: 'Too many files',
                        text: 'You can upload a maximum of 5 images.'
                    });
                    e.target.value = '';
                    return;
                }

                Array.from(files).forEach((file, index) => {
                    if (file.type.startsWith('image/')) {
                        const reader = new FileReader();
                        reader.onload = (e) => {
                            const div = document.createElement('div');
                            // Added inline-block to match the other container
                            div.className = 'relative inline-block'; 
                            div.innerHTML = `
                                <img src="${e.target.result}" class="w-full h-20 object-cover rounded border border-gray-200">
                                <button type="button" 
                                    style="top: -8px; right: -8px;"
                                    class="absolute bg-red-500 text-white rounded-full w-5 h-5 flex items-center justify-center text-xs shadow-sm remove-selected" 
                                    data-index="${index}">&times;</button>
                            `;
                            container.appendChild(div);
                        };
                        reader.readAsDataURL(file);
                    }
                });
            });
        }

        // --- 5. MODAL EDIT TRIGGER (EXISTING ATTACHMENTS) ---
        document.querySelectorAll('.edit-ticket-btn').forEach(button => {
            button.addEventListener('click', function() {
                const ticketId = this.getAttribute('data-id');
                const question = this.getAttribute('data-question');
                const attachments = this.getAttribute('data-attachments');

                document.getElementById('edit-ticket-id').value = ticketId;
                document.getElementById('edit-question').value = question;
                document.getElementById('delete-attachments').value = '[]';

                const container = document.getElementById('edit-attachments-container');
                container.innerHTML = '';
                if (attachments) {
                    try {
                        const attachmentList = JSON.parse(attachments);
                        attachmentList.forEach((path, index) => {
                            const div = document.createElement('div');
                            div.className = 'relative inline-block';
                            div.innerHTML = `
                                <img src="/storage/${path}" alt="Attachment ${index + 1}" 
                                    class="w-full h-20 object-cover rounded border cursor-pointer hover:opacity-90 view-attachment" 
                                    data-path="${path}">
                                <button type="button" 
                                    style="top: -8px; right: -8px;"
                                    class="absolute bg-red-500 text-white rounded-full w-5 h-5 flex items-center justify-center text-xs hover:bg-red-600 transition-colors delete-attachment" 
                                    data-path="${path}">×</button>
                            `;
                            container.appendChild(div);
                        });
                    } catch (e) {
                        console.error('Parsing error:', e);
                    }
                }

                const form = document.getElementById('editTicketForm');
                form.action = "/tickets/" + ticketId;
                document.getElementById('editModal').classList.remove('hidden');
            });
        });

        // --- 6. ATTACHMENT DELETION & REMOVAL ---
        document.addEventListener('click', function(e) {
            // Handle existing files deletion
            if (e.target.classList.contains('delete-attachment')) {
                const path = e.target.getAttribute('data-path');
                let deleteListInput = document.getElementById('delete-attachments');
                let deleteList = JSON.parse(deleteListInput.value || '[]');

                deleteList.push(path);
                deleteListInput.value = JSON.stringify(deleteList);
                e.target.closest('div').remove();
            }

            // Handle newly selected files removal
            if (e.target.classList.contains('remove-selected')) {
                const index = parseInt(e.target.getAttribute('data-index'));
                const input = document.getElementById('edit-attachments');
                const dt = new DataTransfer();
                const { files } = input;

                for (let i = 0; i < files.length; i++) {
                    if (i !== index) dt.items.add(files[i]);
                }

                input.files = dt.files;
                input.dispatchEvent(new Event('change'));
            }
        });

        // --- 7. FINAL SUBMISSION ---
        const editSubmitBtn = document.getElementById('editTicketSubmitBtn');
        if (editSubmitBtn) {
            editSubmitBtn.addEventListener('click', function() {
                const form = document.getElementById('editTicketForm');
                if (form.reportValidity()) {
                    this.disabled = true;
                    this.innerHTML = '<svg class="animate-spin h-4 w-4 mr-2 inline" viewBox="0 0 24 24">...</svg> Saving...';
                    form.submit();
                }
            });
        }

        // --- 8. DELETE TICKET ---
        document.querySelectorAll('.delete-ticket-btn').forEach(button => {
            button.addEventListener('click', function() {
                const id = this.getAttribute('data-id');
                const role = this.getAttribute('data-role');

                Swal.fire({
                    title: 'Are you sure?',
                    text: `Delete ticket for "${role}"? This cannot be undone.`,
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#dc2626',
                    cancelButtonColor: '#6b7280',
                    confirmButtonText: 'Yes, delete it'
                }).then((result) => {
                    if (result.isConfirmed) {
                        const form = document.createElement('form');
                        form.method = 'POST';
                        form.action = `/tickets/${id}`;
                        form.innerHTML = `
                            <input type="hidden" name="_token" value="${document.querySelector('meta[name="csrf-token"]').content}">
                            <input type="hidden" name="_method" value="DELETE">
                        `;
                        document.body.appendChild(form);
                        form.submit();
                    }
                });
            });
        });
    });
</script>