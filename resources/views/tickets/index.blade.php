@extends('layouts.app')

@section('title', 'Tickets')

@section('content')
<div class="max-w-7xl mx-auto py-6 px-4 sm:px-6 lg:px-8">
    <div class="md:flex md:items-center md:justify-between">
        <div class="flex-1 min-w-0">
            <h2 class="text-2xl text-center font-bold leading-7 text-gray-900 sm:text-3xl sm:truncate">
                My Tickets
            </h2>
        </div>
        <!-- <div class="mt-4 flex md:mt-0 md:ml-4">
            <a href="{{ route('tickets.create') }}" class="ml-3 inline-flex items-center px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                Create Ticket
            </a>
        </div> -->
    </div>

    <div class="mt-8">
        <!-- Success Message -->
        @if(session('success'))
        <div class="rounded-md bg-green-50 p-4 mb-6">
            <div class="flex">
                <div class="flex-shrink-0">
                    <svg class="h-5 w-5 text-green-400" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
                    </svg>
                </div>
                <div class="ml-3">
                    <p class="text-sm font-medium text-green-800">
                        {{ session('success') }}
                    </p>
                </div>
            </div>
        </div>
        @endif

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
                                    {{ $ticket->category }}
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
                                data-category="{{ $ticket->category }}"
                                data-question="{{ $ticket->question }}">
                                Edit
                            </button>
                            <button type="button" class="inline-flex items-center px-3 py-1 border border-transparent text-sm leading-4 font-medium rounded-md text-red-700 bg-red-100 hover:bg-red-200 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500 delete-ticket-btn"
                                data-id="{{ $ticket->id }}"
                                data-category="{{ $ticket->category }}">
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

<!-- Edit Ticket Modal -->
<div id="editModal" class="hidden fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full z-50">
    <div class="relative top-20 mx-auto p-5 border w-96 shadow-lg rounded-md bg-white">
        <div class="mt-3 text-center">
            <h3 class="text-lg leading-6 font-medium text-gray-900">Edit Ticket</h3>
            <form id="editTicketForm" action="" method="POST">
                @csrf
                @method('PUT')
                <input type="hidden" id="edit-ticket-id" name="id">
                <div class="mt-2 px-7 py-3">
                    <div class="mb-4">
                        <label for="edit-category" class="block text-sm font-medium text-gray-700">Category</label>
                        <input type="text" id="edit-category" name="category" readonly aria-readonly="true" class="mt-1 block w-full border border-gray-200 bg-gray-50 text-gray-500 rounded-md shadow-sm py-2 px-3 cursor-not-allowed focus:outline-none focus:ring-0 focus:border-gray-200 sm:text-sm">
                    </div>
                    <div class="mb-4">
                        <label for="edit-question" class="block text-sm font-medium text-gray-700">Question</label>
                        <textarea id="edit-question" name="question" rows="3" class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm py-2 px-3 focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm"></textarea>
                    </div>
                </div>
                <div class="items-center px-4 py-3">
                    <button type="button" class="px-4 py-2 bg-gray-500 text-white text-base font-medium rounded-md w-24 mr-2 shadow-sm hover:bg-gray-600 focus:outline-none focus:ring-2 focus:ring-gray-300" onclick="document.getElementById('editModal').classList.add('hidden')">
                        Cancel
                    </button>
                    <button type="submit" class="px-4 py-2 bg-indigo-600 text-white text-base font-medium rounded-md w-24 shadow-sm hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-indigo-300">
                        Update
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
<rasa-chatbot-widget error-message="Server is not running. Please come again in a few minutes." widget-title="Sangkay Chatbot" server-url="https://miniature-eureka-v6pxww557qqq36gg6-5005.app.github.dev/" bot-icon="{{ asset('logo-white.png') }}"
    initial-payload="As my sangkay, I would love to know your name. What is your name?" stream-messages="true" > 
    <style>:root { --color-primary: #184c1c;}</style>
</rasa-chatbot-widget>
<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Add event listeners to all edit buttons
        document.querySelectorAll('.edit-ticket-btn').forEach(button => {
            button.addEventListener('click', function() {
                const ticketId = this.getAttribute('data-id');
                const category = this.getAttribute('data-category');
                const question = this.getAttribute('data-question');

                // Set form values
                document.getElementById('edit-ticket-id').value = ticketId;
                document.getElementById('edit-category').value = category;
                document.getElementById('edit-question').value = question;

                // Update form action URL
                const form = document.getElementById('editTicketForm');
                form.action = "/tickets/" + ticketId;

                // Show modal
                document.getElementById('editModal').classList.remove('hidden');
            });
        });

        // Add event listeners to all delete buttons
        document.querySelectorAll('.delete-ticket-btn').forEach(button => {
            button.addEventListener('click', function() {
                const ticketId = this.getAttribute('data-id');
                const category = this.getAttribute('data-category');

                if (confirm(`Are you sure you want to delete the ticket "${category}"?`)) {
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
                    try {
                        localStorage.setItem('ts_tickets_changed', String(Date.now()));
                    } catch (e) {}
                    document.body.appendChild(form);
                    form.submit();
                }
            });
        });
    });
</script>
@endsection