@extends('layouts.app')

@section('title', 'Tickets')

@section('content')
<div class="max-w-7xl mx-auto py-6 px-4 sm:px-6 lg:px-8">
    <div class="md:flex md:items-center md:justify-between">
        <div class="flex-1 min-w-0">
            <h2 class="text-2xl text-center font-bold leading-7 text-gray-900 sm:text-3xl sm:truncate">
                My Tickets
            </h2>
            @if($isEmail && $identifier)
                <p class="mt-1 text-sm text-gray-500 text-center">
                    Viewing tickets for: <span class="font-medium">{{ $identifier }}</span>
                </p>
            @endif
        </div>
    </div>

    <div class="mt-8">
        <!-- Success Message will be shown via SweetAlert -->

        <!-- Error Message -->
        @if(session('error'))
        <div class="rounded-md bg-red-50 p-4 mb-6">
            <div class="flex">
                <div class="flex-shrink-0">
                    <svg class="h-5 w-5 text-red-400" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd" />
                    </svg>
                </div>
                <div class="ml-3">
                    <h3 class="text-sm font-medium text-red-800">
                        {{ session('error') }}
                    </h3>
                </div>
            </div>
        </div>
        @endif

        <div class="bg-white shadow overflow-hidden sm:rounded-md">
            <ul class="divide-y divide-gray-200">
                @forelse ($tickets as $ticket)
                <li>
                    <div class="px-4 py-4 flex items-center justify-between hover:bg-gray-50 sm:px-6">
                        <div class="flex-1 min-w-0">
                            <div class="flex items-center">
                                <p class="text-sm font-medium text-indigo-600 truncate">
                                    {{ is_object($ticket->category) ? ($ticket->category->name ?? ($ticket->getAttribute('category') ?? '')) : ($ticket->getAttribute('category') ?? '') }}
                                </p>
                                <span class="ml-2 inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium {{ $ticket->status === 'Open' ? 'bg-green-100 text-green-800' : ($ticket->status === 'Re-routed' ? 'bg-yellow-100 text-yellow-800' : 'bg-gray-100 text-gray-800') }}">
                                    {{ $ticket->status }}
                                </span>
                            </div>
                            <div class="mt-1">
                                <p class="text-sm text-gray-500 truncate">
                                    {{ $ticket->question }}
                                </p>
                            </div>
                        </div>
                        <div class="ml-4 flex-shrink-0 flex items-center">
                            <p class="text-sm text-gray-500 mr-4">
                                {{ $ticket->created_at->format('Y-m-d h:i a') }}
                            </p>
                            <button type="button" class="inline-flex items-center px-3 py-1 border border-transparent text-sm leading-4 font-medium rounded-md text-indigo-700 bg-indigo-100 hover:bg-indigo-200 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 edit-ticket-btn mr-2"
                                data-id="{{ $ticket->id }}"
                                data-category-id="{{ $ticket->category_id }}"
                                data-category="{{ is_object($ticket->category) ? ($ticket->category->name ?? ($ticket->getAttribute('category') ?? '')) : ($ticket->getAttribute('category') ?? '') }}"
                                data-question="{{ $ticket->question }}"
                                data-attachments="{{ $ticket->attachments }}">
                                Edit
                            </button>
                            <button type="button" class="inline-flex items-center px-3 py-1 border border-transparent text-sm leading-4 font-medium rounded-md text-red-700 bg-red-100 hover:bg-red-200 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500 delete-ticket-btn"
                                data-id="{{ $ticket->id }}"
                                data-category-id="{{ $ticket->category_id }}"
                                data-category="{{ is_object($ticket->category) ? ($ticket->category->name ?? ($ticket->getAttribute('category') ?? '')) : ($ticket->getAttribute('category') ?? '') }}">
                                Delete
                            </button>
                        </div>
                    </div>
                </li>
                @empty
                <li>
                    <div class="px-4 py-4 text-center sm:px-6">
                        <p class="text-sm text-gray-500">
                            No tickets found. <a href="{{ route('tickets.create') }}" class="text-indigo-600 hover:text-indigo-900">Create your first ticket</a>.
                        </p>
                    </div>
                </li>
                @endforelse
            </ul>
        </div>
    </div>
</div>

<!-- Edit Ticket Modal (admin-style for consistent look) -->
<div id="editModal" class="fixed inset-0 z-50 hidden overflow-y-auto" aria-hidden="true">
    <div class="absolute inset-0 bg-black/60 backdrop-blur-md transition-opacity" data-modal-backdrop></div>
    <div class="relative mx-auto my-0 sm:my-8 w-full h-full sm:h-auto sm:w-[95%] max-w-2xl flex items-center">
        <div class="bg-white shadow-2xl w-full h-full sm:h-auto sm:max-h-[95vh] sm:max-w-2xl overflow-hidden sm:rounded-2xl flex flex-col">
            <div class="px-4 sm:px-6 py-4 border-b border-gray-100 flex items-center justify-between shrink-0">
                <div class="flex-1 min-w-0">
                    <h3 class="modal-title text-lg font-semibold text-gray-900">Edit Ticket</h3>
                    <div class="text-xs text-gray-500">Modify your ticket details</div>
                </div>
                <div class="flex items-center gap-2 ml-4">
                    <button type="button" class="text-gray-400 hover:text-gray-600 transition-colors p-1 rounded-lg" aria-label="Close" data-modal-close onclick="document.getElementById('editModal').classList.add('hidden')">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/></svg>
                    </button>
                </div>
            </div>

            <div class="flex-1 overflow-y-auto px-6 py-5 modal-body text-sm text-gray-800">
                <form id="editTicketForm" action="" method="POST" enctype="multipart/form-data">
                    @csrf
                    @method('PUT')
                    <input type="hidden" id="edit-ticket-id" name="id">
                    <div class="space-y-4">
                        <div>
                            <label for="edit-category" class="block text-sm font-medium text-gray-700">Category</label>
                            <input type="text" id="edit-category" name="category" readonly aria-readonly="true" class="mt-1 block w-full border border-gray-200 bg-gray-50 text-gray-500 rounded-md shadow-sm py-2 px-3 cursor-not-allowed focus:outline-none focus:ring-0 focus:border-gray-200 sm:text-sm">
                        </div>
                        <div>
                            <label for="edit-question" class="block text-sm font-medium text-gray-700">Question</label>
                            <textarea id="edit-question" name="question" rows="3" class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm py-2 px-3 focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm"></textarea>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700">Current Attachments</label>
                            <div id="edit-attachments-container" class="mt-1 grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 gap-2"></div>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700">Add Attachments (Screenshots - Max 5MB per image)</label>
                            <div class="mt-1 flex items-center gap-3">
                                <button type="button" id="add-photo-btn" class="inline-flex items-center px-3 py-2 border border-gray-300 rounded-md shadow-sm text-sm font-medium text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                                    <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
                                    </svg>
                                    Add Photo
                                </button>
                                <input type="file" name="attachments[]" id="edit-attachments" multiple accept="image/*" class="hidden">
                            </div>
                            <div id="selected-thumbnails" class="mt-2 grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 gap-2"></div>
                            <p class="mt-1 text-sm text-gray-500">You can upload up to 5 files. (Only jpeg,jpg,png files are allowed.)</p>
                            <input type="hidden" name="delete_attachments" id="delete-attachments">
                        </div>
                    </div>
                </form>
            </div>

            <div class="px-4 sm:px-6 py-4 border-t border-gray-100 flex items-center justify-end shrink-0 gap-3">
                <button type="button" class="px-4 py-2 bg-gray-500 text-white text-sm font-medium rounded-md shadow-sm hover:bg-gray-600" onclick="document.getElementById('editModal').classList.add('hidden')">Cancel</button>
                <button type="button" id="editTicketSubmitBtn" class="px-4 py-2 bg-indigo-600 text-white text-sm font-medium rounded-md shadow-sm hover:bg-indigo-700">Update</button>
            </div>
        </div>
    </div>
</div>
{{-- <rasa-chatbot-widget error-message="Server is not running. Please come again in a few minutes." widget-title="Sangkay Chatbot" server-url="{{ env('RASA_SERVER_URL') }}" bot-icon="{{ asset('logo-white.png') }}"
    initial-payload="As my sangkay, I would love to know your name. What is your name?" stream-messages="true" > 
    <style>:root { --color-primary: #184c1c;}</style>
</rasa-chatbot-widget> --}}
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Add photo button
        document.getElementById('add-photo-btn').addEventListener('click', function() {
            document.getElementById('edit-attachments').click();
        });

        // Validate attachments count for edit and display thumbnails
        document.getElementById('edit-attachments').addEventListener('change', function (e) {
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
                const attachmentList = e.target.closest('#edit-attachments-container').querySelectorAll('.view-attachment');
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
                const category = this.getAttribute('data-category');
                const question = this.getAttribute('data-question');
                const attachments = this.getAttribute('data-attachments');

                // Set form values
                document.getElementById('edit-ticket-id').value = ticketId;
                document.getElementById('edit-category').value = category;
                document.getElementById('edit-question').value = question;
                document.getElementById('edit-attachments').value = '';
                document.getElementById('delete-attachments').value = '';

                // Handle attachments
                const attachmentsContainer = document.getElementById('edit-attachments-container');
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
            editSubmitBtn.addEventListener('click', function () {
                const form = document.getElementById('editTicketForm');
                if (form) form.submit();
            });
        }
        });

        // Add event listeners to all delete buttons (SweetAlert2 confirmation)
        document.querySelectorAll('.delete-ticket-btn').forEach(button => {
            button.addEventListener('click', function() {
                const ticketId = this.getAttribute('data-id');
                const category = this.getAttribute('data-category');

                if (window.Swal) {
                    Swal.fire({
                        title: 'Delete ticket?',
                        text: `Are you sure you want to delete the ticket "${category}"? This action cannot be undone.`,
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
                            csrfToken.value = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
                            form.appendChild(csrfToken);

                            // Add method field for DELETE
                            const methodField = document.createElement('input');
                            methodField.type = 'hidden';
                            methodField.name = '_method';
                            methodField.value = 'DELETE';
                            form.appendChild(methodField);

                            // Submit the form
                            try { localStorage.setItem('ts_tickets_changed', String(Date.now())); } catch (e) {}
                            document.body.appendChild(form);
                            form.submit();
                        }
                    });
                } else {
                    // Fallback if SweetAlert2 is not available
                    if (confirm(`Are you sure you want to delete the ticket "${category}"?`)) {
                        const form = document.createElement('form');
                        form.method = 'POST';
                        form.action = `/tickets/${ticketId}`;

                        const csrfToken = document.createElement('input');
                        csrfToken.type = 'hidden';
                        csrfToken.name = '_token';
                        csrfToken.value = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
                        form.appendChild(csrfToken);

                        const methodField = document.createElement('input');
                        methodField.type = 'hidden';
                        methodField.name = '_method';
                        methodField.value = 'DELETE';
                        form.appendChild(methodField);

                        try { localStorage.setItem('ts_tickets_changed', String(Date.now())); } catch (e) {}
                        document.body.appendChild(form);
                        form.submit();
                    }
                }
            });
        });
    });
    
    // Show SweetAlert for success message
    @if(session('success'))
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

    @endsection
