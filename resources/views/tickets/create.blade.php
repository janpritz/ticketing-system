@extends('layouts.app')

@section('title', 'Create Ticket')

@section('content')
    <div class="max-w-7xl mx-auto py-6 px-4 sm:px-6 lg:px-8">
        <div class="md:flex md:items-center md:justify-between">
            <div class="flex-1 text-center min-w-0 pt-5">
                <h2 class="text-2xl font-bold leading-7 text-gray-900 sm:text-3xl sm:truncate">
                    Create a Ticket
                </h2>
                <p class="mt-1 text-sm text-gray-500">
                    Please fill out the form below to create your support ticket.
                </p>
            </div>
        </div>

        <div class="mt-8">
            <div class="bg-white shadow overflow-hidden sm:rounded-lg">
                <div class="px-4 py-5 sm:p-6">

                    <!-- Success Message -->
                    @if (session('success'))
                        <div class="rounded-md bg-green-50 p-4 mb-6">
                            <div class="flex">
                                <div class="flex-shrink-0">
                                    <svg class="h-5 w-5 text-green-400" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20"
                                        fill="currentColor">
                                        <path fill-rule="evenodd"
                                            d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z"
                                            clip-rule="evenodd" />
                                    </svg>
                                </div>
                                <div class="ml-3">
                                    <h3 class="text-sm font-medium text-green-800">
                                        {{ session('success') }}
                                    </h3>
                                </div>
                            </div>
                        </div>
                    @endif

                    <!-- Error Message -->
                    @if ($errors->any())
                        <div class="rounded-md bg-red-50 p-4 mb-6">
                            <div class="flex">
                                <div class="flex-shrink-0">
                                    <svg class="h-5 w-5 text-red-400" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20"
                                        fill="currentColor">
                                        <path fill-rule="evenodd"
                                            d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z"
                                            clip-rule="evenodd" />
                                    </svg>
                                </div>
                                <div class="ml-3">
                                    <h3 class="text-sm font-medium text-red-800">
                                        There were {{ $errors->count() }} error(s) with your submission
                                    </h3>
                                    <div class="mt-2 text-sm text-red-700">
                                        <ul class="list-disc pl-5 space-y-1">
                                            @foreach ($errors->all() as $error)
                                                <li>{{ $error }}</li>
                                            @endforeach
                                        </ul>
                                    </div>
                                </div>
                            </div>
                        </div>
                    @endif

                    <!-- Ticket Creation Form -->
                    <form action="{{ route('tickets.store') }}" method="POST" class="space-y-6" novalidate enctype="multipart/form-data">
                        @csrf

                        <!-- Hidden fields -->
                        <input type="hidden" name="recepient_id" value="{{ $recepient_id }}">

                        <div>
                            <label for="email" class="block text-sm font-medium text-gray-700">Email Address</label>
                            <div class="mt-1">
                                <input type="email" name="email" id="email"
                                    class="py-2 px-3 block w-full border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm"
                                    required value="{{ old('email', $email) }}">
                            </div>
                        </div>

                        <div>
                            <label for="category" class="block text-sm font-medium text-gray-700">Category</label>
                            <div class="mt-1">
                                <input list="category-list" name="category_name" id="category"
                                    class="py-2 px-3 block w-full border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm"
                                    required value="{{ old('category') }}">
                                <input type="hidden" name="category_id" id="category_id_input" value="{{ old('category_id') }}">
                                <datalist id="category-list">
                                    @if(isset($categories) && count($categories))
                                        @foreach($categories as $id => $name)
                                            <option value="{{ $name }}">
                                        @endforeach
                                    @else
                                        <!-- Fallback to the hard-coded list if controller did not provide categories -->
                                        <option value="Course Registration">
                                        <option value="Add or Drop Classes">
                                        <option value="Late Enrollment">
                                        <option value="Shifting to a Different Program">
                                        <option value="Transferring Between Schools">
                                        <option value="Schedule Conflicts">
                                        <option value="Tuition Fee Inquiries">
                                        <option value="Payment Methods (Online, Bank, etc.)">
                                        <option value="Scholarships & Financial Aid">
                                        <option value="Refund Issues">
                                        <option value="Billing and Invoice Problems">
                                        <option value="Merit-Based Scholarships">
                                        <option value="Need-Based Scholarships">
                                        <option value="Scholarship Application Status">
                                        <option value="Eligibility and Deadlines for Scholarships">
                                        <option value="Grades and Transcript Requests">
                                        <option value="Class Schedules">
                                        <option value="Academic Probation or Warnings">
                                        <option value="Course Prerequisites">
                                        <option value="Graduation Requirements">
                                        <option value="Thesis/Dissertation Submission">
                                        <option value="Exam Schedules">
                                        <option value="Exam Results">
                                        <option value="Re-scheduling Exams">
                                        <option value="Special Exam Accommodations">
                                        <option value="Career Counseling">
                                        <option value="Student Organizations & Activities">
                                        <option value="Mental Health Support">
                                        <option value="Peer Mentoring">
                                        <option value="Internship Assistance">
                                        <option value="Book Borrowing">
                                        <option value="Access to Digital Resources">
                                        <option value="Study Room Reservations">
                                        <option value="Library Fees and Fines">
                                        <option value="Research Assistance">
                                        <option value="Wi-Fi Issues">
                                        <option value="Software Installation">
                                        <option value="Email Issues">
                                        <option value="Computer Lab Problems">
                                        <option value="Learning Management System (LMS) Issues">
                                        <option value="Student Life Events">
                                        <option value="Student Rights and Responsibilities">
                                        <option value="Code of Conduct Violations">
                                        <option value="Disciplinary Actions">
                                        <option value="Visa Assistance">
                                        <option value="Scholarships for International Students">
                                        <option value="Cultural Integration Support">
                                        <option value="Study Abroad Programs">
                                        <option value="Graduation Requirements">
                                        <option value="Commencement Exercises">
                                        <option value="Diploma Requests">
                                        <option value="Alumni Services">
                                        <option value="Sports Club Registration">
                                        <option value="Physical Education Classes">
                                        <option value="Sports Event Tickets">
                                        <option value="Sports Scholarships">
                                    @endif
                                </datalist>
                            </div>
                        </div>

                        <div>
                            <label for="question" class="block text-sm font-medium text-gray-700">Question</label>
                            <div class="mt-1">
                                <textarea name="question" id="question" rows="4"
                                    class="py-2 px-3 block w-full border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm"
                                    required placeholder="Please describe your question or issue in detail...">{{ old('question') }}</textarea>
                            </div>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700">Attachments (Screenshots - Max 5MB per image)</label>
                            <div class="mt-1">
                                <button type="button" id="add-photo-btn-submit" class="inline-flex items-center px-4 py-2 border border-gray-300 rounded-md shadow-sm text-sm font-medium text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                                    <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
                                    </svg>
                                    Add Photo
                                </button>
                                <input type="file" name="attachments[]" id="attachments" multiple accept="image/*" class="hidden">
                                <div id="selected-thumbnails-submit" class="mt-2 grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 gap-2"></div>
                                <p class="mt-1 text-sm text-gray-500">You can upload 5 files only. (Only jpeg,jpg,png files are allowed.)</p>
                            </div>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700">Verification</label>
                            <div class="mt-1">
                                {!! app('captcha')->display() !!}
                                @error('g-recaptcha-response')
                                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                            </div>
                        </div>

                        <div class="flex items-center justify-end">
                            <button id="submitTicketBtn" type="submit"
                                class="inline-flex justify-center py-2 px-4 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                                Create Ticket
                            </button>
                        </div>
                    </form>

                    <script src="https://www.google.com/recaptcha/api.js" async defer></script>
                    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
                    <script>
                        document.addEventListener('DOMContentLoaded', function () {
                            // Add photo button
                            document.getElementById('add-photo-btn-submit').addEventListener('click', function() {
                                document.getElementById('attachments').click();
                            });

                            // Validate attachments count and display thumbnails
                            document.getElementById('attachments').addEventListener('change', function (e) {
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
                                submitBtn.innerHTML = '<svg class="animate-spin -ml-1 mr-3 h-5 w-5 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg>Creating...';
                                submitBtn.disabled = true;

                                // Allow form submission to continue
                                form.submit();
                            });

                            // Map category name -> id and keep hidden category_id in sync
                            const categoryMapCreate = {};
                            @if(isset($categories) && count($categories))
                                @foreach($categories as $id => $name)
                                    categoryMapCreate["{{ addslashes($name) }}"] = "{{ $id }}";
                                @endforeach
                            @endif

                            const cnameInput = document.getElementById('category');
                            const cidInput = document.getElementById('category_id_input');
                            if (cnameInput) {
                                cnameInput.addEventListener('input', function () {
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
                </div>
            </div>
        </div>
    </div>
@endsection
