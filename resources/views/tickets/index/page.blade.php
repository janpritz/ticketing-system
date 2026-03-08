@extends('layouts.app')

@section('title', 'Tickets')

@section('content')
    <!-- Public Navigation Bar -->
    <x-public-nav :logo-margin="'mr-4'" :logo-text="'SANGKAY'" />

    <div class="max-w-7xl mx-auto py-6 px-4 sm:px-6 lg:px-8">
        <div class="max-w-7xl mx-auto py-6 px-4 sm:px-6 lg:px-8">
            <div class="flex flex-col items-center justify-center">
                <div class="text-center">
                    <h2 class="text-2xl font-bold leading-7 text-gray-900 sm:text-3xl">
                        My Tickets
                    </h2>
                    @if ($isEmail && $identifier)
                        <div class="mt-2 space-y-1">
                            <p class="text-sm text-gray-500">
                                Viewing tickets for: <span class="font-medium text-indigo-600">{{ $identifier }}</span>
                            </p>
                            <p class="text-xs text-gray-400 italic">
                                For your security, this session will expire in 60 minutes. After that, you'll need to
                                re-verify your email to view your history.
                            </p>
                        </div>
                    @endif
                </div>

                <div class="mt-6 flex justify-center w-full">
                    <a href="#" id="createTicketBtn"
                        class="inline-flex items-center px-6 py-3 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-sangkay-orange hover:bg-sangkay-hover focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-sangkay-orange transition-all duration-200 cursor-pointer">
                        <svg class="-ml-1 mr-2 h-5 w-5" xmlns="http://www.w3.org/2000/svg" fill="none"
                            viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                        </svg>
                        Create New Ticket
                    </a>
                </div>
            </div>
        </div>

        <div class="mt-8">
            <!-- Success Message will be shown via SweetAlert -->

            <!-- Error Message -->
            @if (session('error'))
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
                                            {{ is_object($ticket->role) ? $ticket->role->name ?? '' : $ticket->getAttribute('role') ?? '' }}
                                        </p>
                                        <span
                                            class="ml-2 inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium {{ $ticket->status === 'Open' ? 'bg-green-100 text-green-800' : ($ticket->status === 'Re-routed' ? 'bg-yellow-100 text-yellow-800' : 'bg-gray-100 text-gray-800') }}">
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
                                    <button type="button"
                                        class="inline-flex items-center px-3 py-1 border border-transparent text-sm leading-4 font-medium rounded-md text-indigo-700 bg-indigo-100 hover:bg-indigo-200 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 edit-ticket-btn mr-2"
                                        data-id="{{ $ticket->id }}" data-role-id="{{ $ticket->role_id }}"
                                        data-role="{{ is_object($ticket->role) ? $ticket->role->name ?? '' : $ticket->getAttribute('role') ?? '' }}"
                                        data-question="{{ $ticket->question }}"
                                        data-attachments="{{ $ticket->attachments }}">
                                        Edit
                                    </button>
                                    <button type="button"
                                        class="inline-flex items-center px-3 py-1 border border-transparent text-sm leading-4 font-medium rounded-md text-red-700 bg-red-100 hover:bg-red-200 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500 delete-ticket-btn"
                                        data-id="{{ $ticket->id }}" data-role-id="{{ $ticket->role_id }}"
                                        data-role="{{ is_object($ticket->role) ? $ticket->role->name ?? '' : $ticket->getAttribute('role') ?? '' }}">
                                        Delete
                                    </button>
                                </div>
                            </div>
                        </li>
                    @empty
                        <li>
                            <div class="px-4 py-4 text-center sm:px-6">
                                <p class="text-sm text-gray-500 mb-4">
                                    No tickets found.
                                </p>
                                {{-- <button id="createTicketBtn" class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md shadow-sm text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                            <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
                            </svg>
                            Create your first ticket
                        </button> --}}
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
            <div
                class="bg-white shadow-2xl w-full h-full sm:h-auto sm:max-h-[95vh] sm:max-w-2xl overflow-hidden sm:rounded-2xl flex flex-col">
                <div class="px-4 sm:px-6 py-4 border-b border-gray-100 flex items-center justify-between shrink-0">
                    <div class="flex-1 min-w-0">
                        <h3 class="modal-title text-lg font-semibold text-gray-900">Edit Ticket</h3>
                        <div class="text-xs text-gray-500">Modify your ticket details</div>
                    </div>
                    <div class="flex items-center gap-2 ml-4">
                        <button type="button" class="text-gray-400 hover:text-gray-600 transition-colors p-1 rounded-lg"
                            aria-label="Close" data-modal-close
                            onclick="document.getElementById('editModal').classList.add('hidden')">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24"
                                stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                            </svg>
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
                                <input type="text" id="edit-category" name="category" readonly aria-readonly="true"
                                    class="mt-1 block w-full border border-gray-200 bg-gray-50 text-gray-500 rounded-md shadow-sm py-2 px-3 cursor-not-allowed focus:outline-none focus:ring-0 focus:border-gray-200 sm:text-sm">
                            </div>
                            <div>
                                <label for="edit-question" class="block text-sm font-medium text-gray-700">Question</label>
                                <textarea id="edit-question" name="question" rows="3"
                                    class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm py-2 px-3 focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm"></textarea>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700">Current Attachments</label>
                                <div id="edit-attachments-container"
                                    class="mt-1 grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 gap-2"></div>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700">Add Attachments (Screenshots - Max
                                    5MB per image)</label>
                                <div class="mt-1 flex items-center gap-3">
                                    <button type="button" id="add-photo-btn"
                                        class="inline-flex items-center px-3 py-2 border border-gray-300 rounded-md shadow-sm text-sm font-medium text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                                        <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor"
                                            viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
                                        </svg>
                                        Add Photo
                                    </button>
                                    <input type="file" name="attachments[]" id="edit-attachments" multiple
                                        accept="image/*" class="hidden">
                                </div>
                                <div id="selected-thumbnails"
                                    class="mt-2 grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 gap-2"></div>
                                <p class="mt-1 text-sm text-gray-500">You can upload up to 5 files. (Only jpeg,jpg,png
                                    files are allowed.)</p>
                                <input type="hidden" name="delete_attachments" id="delete-attachments">
                            </div>
                        </div>
                    </form>
                </div>

                <div class="px-4 sm:px-6 py-4 border-t border-gray-100 flex items-center justify-end shrink-0 gap-3">
                    <button type="button"
                        class="px-4 py-2 bg-gray-500 text-white text-sm font-medium rounded-md shadow-sm hover:bg-gray-600"
                        onclick="document.getElementById('editModal').classList.add('hidden')">Cancel</button>
                    <button type="button" id="editTicketSubmitBtn"
                        class="px-4 py-2 bg-indigo-600 text-white text-sm font-medium rounded-md shadow-sm hover:bg-indigo-700">Update</button>
                </div>
            </div>
        </div>
    </div>
@endsection
@include('tickets.index.scripts')
