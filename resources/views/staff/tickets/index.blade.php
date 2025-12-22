@extends('layouts.staff')

@section('title', 'Staff Tickets')

@section('staff-content')
<div class="sm-pt:2">
    <div class="mb-6">
        <h1 class="text-2xl font-bold text-gray-900">Tickets Management</h1>
        <p class="mt-1 text-sm text-gray-600">Manage and track all assigned tickets</p>
    </div>

    <!-- Filter Tabs -->
    <div class="mb-6">
        <div class="border-b border-gray-200">
            <nav class="-mb-px flex space-x-8" aria-label="Tabs">
                <button data-filter="all" class="filter-tab border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300 whitespace-nowrap py-2 px-1 border-b-2 font-medium text-sm active">
                    All Tickets
                    <span class="bg-gray-100 text-gray-900 ml-2 py-0.5 px-2 rounded-full text-xs" id="count-all">0</span>
                </button>
                <button data-filter="open" class="filter-tab border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300 whitespace-nowrap py-2 px-1 border-b-2 font-medium text-sm">
                    Open
                    <span class="bg-blue-100 text-blue-800 ml-2 py-0.5 px-2 rounded-full text-xs" id="count-open">0</span>
                </button>
                <button data-filter="forwarded" class="filter-tab border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300 whitespace-nowrap py-2 px-1 border-b-2 font-medium text-sm">
                    Forwarded
                    <span class="bg-amber-100 text-amber-800 ml-2 py-0.5 px-2 rounded-full text-xs" id="count-forwarded">0</span>
                </button>
                <button data-filter="closed" class="filter-tab border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300 whitespace-nowrap py-2 px-1 border-b-2 font-medium text-sm">
                    Closed
                    <span class="bg-emerald-100 text-emerald-800 ml-2 py-0.5 px-2 rounded-full text-xs" id="count-closed">0</span>
                </button>
            </nav>
        </div>
    </div>

    <!-- Search and Controls -->
    <div class="bg-white rounded-lg shadow-sm ring-1 ring-slate-900/5 mb-6">
        <div class="p-4 flex flex-col sm:flex-row gap-4 items-start sm:items-center justify-between">
            <div class="flex-1 max-w-md">
                <div class="relative">
                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                        <svg class="h-5 w-5 text-gray-400" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                            <path fill-rule="evenodd" d="M8 4a4 4 0 100 8 4 4 0 000-8zM2 8a6 6 0 1110.89 3.476l4.817 4.817a1 1 0 01-1.414 1.414l-4.816-4.816A6 6 0 012 8z" clip-rule="evenodd" />
                        </svg>
                    </div>
                    <input type="text" id="searchInput" placeholder="Search tickets..."
                        class="block w-full pl-10 pr-3 py-2 border border-gray-300 rounded-md leading-5 bg-white placeholder-gray-500 focus:outline-none focus:placeholder-gray-400 focus:ring-1 focus:ring-indigo-500 focus:border-indigo-500 text-sm">
                </div>
            </div>
            <div class="flex items-center gap-3">
                <div class="flex items-center gap-2">
                    <span class="text-sm text-gray-700 hidden sm:inline">Show</span>
                    <select id="perPageSelect" class="rounded-md border-gray-300 text-xs focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500">
                        <option value="10">10</option>
                        <option value="25">25</option>
                        <option value="50">50</option>
                        <option value="100">100</option>
                    </select>
                    <span class="text-sm text-gray-700 hidden sm:inline">per page</span>
                </div>
            </div>
        </div>
    </div>

    <!-- Tickets Table -->
    <div class="bg-white rounded-lg shadow-sm ring-1 ring-slate-900/5">
        <div class="px-4 py-3 border-b border-gray-200 sm:px-6">
            <h3 class="text-lg font-medium text-gray-900" id="tableTitle">All Tickets</h3>
        </div>
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th scope="col" class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            <button class="group inline-flex items-center gap-1" data-sort="id">
                                ID
                                <svg class="w-4 h-4 text-gray-400 group-hover:text-gray-600 sort-icon" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                                    <path d="M5 12a1 1 0 102 0V6.414l1.293 1.293a1 1 0 001.414-1.414l-3-3a1 1 0 00-1.414 0l-3 3a1 1 0 001.414 1.414L5 6.414V12zM15 8a1 1 0 10-2 0v5.586l-1.293-1.293a1 1 0 00-1.414 1.414l3 3a1 1 0 001.414 0l3-3a1 1 0 00-1.414-1.414L15 13.586V8z"/>
                                </svg>
                            </button>
                        </th>
                        <th scope="col" class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            <button class="group inline-flex items-center gap-1" data-sort="question">
                                Subject
                                <svg class="w-4 h-4 text-gray-400 group-hover:text-gray-600 sort-icon" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                                    <path d="M5 12a1 1 0 102 0V6.414l1.293 1.293a1 1 0 001.414-1.414l-3-3a1 1 0 00-1.414 0l-3 3a1 1 0 001.414 1.414L5 6.414V12zM15 8a1 1 0 10-2 0v5.586l-1.293-1.293a1 1 0 00-1.414 1.414l3 3a1 1 0 001.414 0l3-3a1 1 0 00-1.414-1.414L15 13.586V8z"/>
                                </svg>
                            </button>
                        </th>
                        <th scope="col" class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            Status
                        </th>
                        <th scope="col" class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            <button class="group inline-flex items-center gap-1" data-sort="date_created">
                                Created Date
                                <svg class="w-4 h-4 text-gray-400 group-hover:text-gray-600 sort-icon" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                                    <path d="M5 12a1 1 0 102 0V6.414l1.293 1.293a1 1 0 001.414-1.414l-3-3a1 1 0 00-1.414 0l-3 3a1 1 0 001.414 1.414L5 6.414V12zM15 8a1 1 0 10-2 0v5.586l-1.293-1.293a1 1 0 00-1.414 1.414l3 3a1 1 0 001.414 0l3-3a1 1 0 00-1.414-1.414L15 13.586V8z"/>
                                </svg>
                            </button>
                        </th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200" id="ticketsTableBody">
                    <tr>
                        <td colspan="4" class="px-4 py-12 text-center">
                            <div class="flex flex-col items-center">
                                <svg class="animate-spin h-8 w-8 text-gray-400 mb-4" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                </svg>
                                <p class="text-gray-500">Loading tickets...</p>
                            </div>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>

        <!-- Pagination -->
        <div class="bg-white px-4 py-3 flex items-center justify-between border-t border-gray-200 sm:px-6">
            <div class="flex-1 flex justify-between sm:hidden">
                <button id="mobilePrevBtn" class="relative inline-flex items-center px-4 py-2 border border-gray-300 text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50">
                    Previous
                </button>
                <button id="mobileNextBtn" class="ml-3 relative inline-flex items-center px-4 py-2 border border-gray-300 text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50">
                    Next
                </button>
            </div>
            <div class="hidden sm:flex-1 sm:flex sm:items-center sm:justify-between">
                <div>
                    <p class="text-sm text-gray-700">
                        Showing <span class="font-medium" id="showingFrom">1</span> to <span class="font-medium" id="showingTo">10</span> of
                        <span class="font-medium" id="totalResults">0</span> results
                    </p>
                </div>
                <div>
                    <nav class="relative z-0 inline-flex rounded-md shadow-sm -space-x-px" aria-label="Pagination">
                        <button id="prevPageBtn" class="relative inline-flex items-center px-2 py-2 rounded-l-md border border-gray-300 bg-white text-sm font-medium text-gray-500 hover:bg-gray-50">
                            <span class="sr-only">Previous</span>
                            <svg class="h-5 w-5" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                                <path fill-rule="evenodd" d="M12.707 5.293a1 1 0 010 1.414L9.414 10l3.293 3.293a1 1 0 01-1.414 1.414l-4-4a1 1 0 010-1.414l4-4a1 1 0 011.414 0z" clip-rule="evenodd" />
                            </svg>
                        </button>
                        <div id="pageNumbers" class="relative inline-flex items-center">
                        </div>
                        <button id="nextPageBtn" class="relative inline-flex items-center px-2 py-2 rounded-r-md border border-gray-300 bg-white text-sm font-medium text-gray-500 hover:bg-gray-50">
                            <span class="sr-only">Next</span>
                            <svg class="h-5 w-5" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                                <path fill-rule="evenodd" d="M7.293 14.707a1 1 0 010-1.414L10.586 10 7.293 6.707a1 1 0 011.414-1.414l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0z" clip-rule="evenodd" />
                            </svg>
                        </button>
                    </nav>
                </div>
            </div>
        </div>
    </div>

    <!-- Image Lightbox -->
    <div id="imageLightbox" class="fixed inset-0 z-60 hidden bg-black bg-opacity-75 flex items-center justify-center">
        <div class="relative w-full h-full">
            <img id="lightboxImage" src="" alt="" class="w-full h-full object-contain">
            <button id="lightboxPrev"
                class="absolute left-4 top-1/2 transform -translate-y-1/2 text-white text-4xl hover:text-gray-300 bg-black bg-opacity-50 rounded-full w-12 h-12 flex items-center justify-center">&larr;</button>
            <button id="lightboxNext"
                class="absolute right-4 top-1/2 transform -translate-y-1/2 text-white text-4xl hover:text-gray-300 bg-black bg-opacity-50 rounded-full w-12 h-12 flex items-center justify-center">&rarr;</button>
            <button id="lightboxClose"
                class="absolute top-4 right-4 text-white text-4xl hover:text-gray-300 bg-black bg-opacity-50 rounded-full w-12 h-12 flex items-center justify-center">&times;</button>
        </div>
    </div>

    <!-- Ticket Details Modal - Modern Design from Admin Dashboard -->
    <div id="ticketModal" class="fixed inset-0 z-50 hidden overflow-y-auto">
        <div class="absolute inset-0 bg-black/60 backdrop-blur-md transition-opacity" data-modal-backdrop></div>
        <!-- Centered panel with modern minimal design -->
        <div class="relative mx-auto my-0 sm:my-8 w-full h-full sm:h-auto sm:w-[95%] max-w-2xl flex items-center">
            <div
                class="bg-white shadow-2xl w-full h-full sm:h-auto sm:max-h-[95vh] sm:max-w-2xl overflow-hidden sm:rounded-2xl flex flex-col">

                <!-- Header - Minimal & Clean -->
                <div class="px-4 sm:px-6 py-4 border-b border-gray-100 flex items-center justify-between shrink-0">
                    <div class="flex-1 min-w-0">
                        <div class="flex items-center gap-3 mb-2">
                            <h3 id="tmTicketNo" class="text-lg font-semibold text-gray-900">Ticket #</h3>
                            <span id="tmStatus"
                                class="inline-flex items-center gap-1.5 rounded-full px-3 py-1 text-xs font-medium ring-1"></span>
                        </div>
                    </div>
                    <div class="flex items-center gap-2 ml-4">
                        <!-- Options Menu in Header -->
                        <div class="relative">
                            <button type="button" id="tmOptionsBtn"
                                class="inline-flex items-center gap-2 rounded-lg px-2 py-2 text-sm font-medium text-gray-700 hover:bg-gray-100 transition-colors"
                                aria-haspopup="true" aria-expanded="false">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" viewBox="0 0 24 24"
                                    fill="currentColor">
                                    <path
                                        d="M5 12a2 2 0 114 0 2 2 0 01-4 0zm5 0a2 2 0 114 0 2 2 0 01-4 0zm5 0a2 2 0 114 0 2 2 0 01-4 0z" />
                                </svg>
                                <span class="hidden sm:inline">Options</span>
                            </button>
                            <div id="tmOptionsMenu"
                                class="absolute right-0 top-full mt-2 w-48 bg-white rounded-lg shadow-lg ring-1 ring-black/5 hidden z-10 overflow-hidden">
                                <button type="button"
                                    class="w-full text-left px-4 py-2.5 text-sm text-gray-700 hover:bg-gray-50 transition-colors"
                                    data-option="toggle-history">Show History</button>
                                <button type="button" id="tmOptionForward"
                                    class="w-full text-left px-4 py-2.5 text-sm text-gray-700 hover:bg-gray-50 transition-colors border-t border-gray-100"
                                    data-option="show-forward">Forward Ticket</button>
                            </div>
                        </div>
                        <button type="button"
                            class="text-gray-400 hover:text-gray-600 transition-colors p-1 rounded-lg hover:bg-gray-100"
                            aria-label="Close" data-modal-close>
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24"
                                stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                            </svg>
                        </button>
                    </div>
                </div>

                <!-- Content - Scrollable -->
                <div class="flex-1 overflow-y-auto px-6 py-5 space-y-5">

                    <!-- Question/Issue - Primary Focus -->
                    <div class="bg-gray-50 rounded-xl p-4 border border-gray-100">
                        <div class="flex items-center gap-2 mb-2">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 text-gray-500" fill="none"
                                viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M8.228 9c.549-1.165 2.03-2 3.772-2 2.21 0 4 1.343 4 3 0 1.4-1.278 2.575-3.006 2.907-.542.104-.994.54-.994 1.093m0 3h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                            <span class="text-xs font-medium text-gray-500 uppercase tracking-wide">Issue</span>
                        </div>
                        <div id="tmQuestion" class="text-sm text-gray-900 whitespace-pre-wrap leading-relaxed"></div>
                    </div>

                    <!-- Attachments - Visible when present -->
                    <div id="tmAttachmentsBlock" class="hidden">
                        <div class="flex items-center gap-2 mb-3">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 text-gray-500" fill="none"
                                viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M15.172 7l-6.586 6.586a2 2 0 102.828 2.828l6.414-6.586a4 4 0 00-5.656-5.656l-6.415 6.585a6 6 0 108.486 8.486L20.5 13" />
                            </svg>
                            <span class="text-xs font-medium text-gray-700">Attachments</span>
                        </div>
                        <div id="tmAttachmentsList" class="flex flex-wrap gap-2"></div>
                    </div>

                    <!-- Sent Response - For closed tickets -->
                    <div id="tmStoredResponseBlock" class="hidden bg-emerald-50 rounded-xl p-4 border border-emerald-200">
                        <div class="flex items-center gap-2 mb-2">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 text-emerald-600" fill="none"
                                viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                            <span class="text-xs font-semibold text-emerald-700 uppercase tracking-wide">Sent
                                Response</span>
                        </div>
                        <div id="tmStoredResponse" class="text-sm text-gray-800 whitespace-pre-wrap leading-relaxed">
                        </div>
                    </div>

                    <!-- Collapsible Details Section -->
                    <div id="tmDetailsSection" class="border-t border-gray-100 pt-4">
                        <button type="button" id="tmToggleDetails"
                            class="flex items-center justify-between w-full text-left group">
                            <span class="text-sm font-medium text-gray-700 group-hover:text-gray-900">Show Details</span>
                            <svg id="tmDetailsChevron" xmlns="http://www.w3.org/2000/svg"
                                class="h-4 w-4 text-gray-500 transition-transform" fill="none" viewBox="0 0 24 24"
                                stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M19 9l-7 7-7-7" />
                            </svg>
                        </button>

                        <div id="tmDetailsContent" class="hidden mt-4 space-y-4 pb-2">
                            <!-- Category & Dates -->
                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                                <div>
                                    <label
                                        class="text-xs font-medium text-gray-500 uppercase tracking-wide">Category</label>
                                    <div id="tmCategory"
                                        class="mt-1 inline-flex items-center rounded-full bg-gray-100 px-2.5 py-1 text-xs font-medium text-gray-700">
                                    </div>
                                </div>
                                <div>
                                    <label
                                        class="text-xs font-medium text-gray-500 uppercase tracking-wide">Timeline</label>
                                    <div id="tmDates" class="mt-1 text-xs text-gray-700"></div>
                                </div>
                            </div>

                            <!-- Contact Info -->
                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                                <div>
                                    <label class="text-xs font-medium text-gray-500 uppercase tracking-wide">Email</label>
                                    <div id="tmEmail" class="mt-1 text-sm text-gray-800"></div>
                                </div>
                                <div>
                                    <label class="text-xs font-medium text-gray-500 uppercase tracking-wide">Recipient
                                        ID</label>
                                    <div id="tmRecepient" class="mt-1 text-sm text-gray-800"></div>
                                </div>
                            </div>

                            <!-- Routing History -->
                            <div id="tmHistorySection" class="hidden">
                                <label class="text-xs font-medium text-gray-500 uppercase tracking-wide">Routing
                                    History</label>
                                <ul id="tmHistoryList" class="mt-2 space-y-2"></ul>
                            </div>
                        </div>
                    </div>

                    <!-- Response Input -->
                    <div class="bg-gradient-to-br from-indigo-50 to-blue-50 rounded-xl p-4 border border-indigo-100">
                        <label for="tmResponse" class="flex items-center gap-2 text-sm font-medium text-indigo-900 mb-2">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24"
                                stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" />
                            </svg>
                            Your Response
                        </label>
                        <textarea id="tmResponse"
                            class="w-full rounded-lg border-indigo-200 text-sm focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 bg-white shadow-sm resize-none"
                            rows="4" placeholder="Type your response message here..."></textarea>
                    </div>

                    <!-- Forward Controls -->
                    <div id="tmForwardControls" class="hidden">
                        <div class="flex flex-col sm:flex-row sm:items-center gap-2">
                            <label for="tmForwardSelect" class="text-xs font-medium text-gray-700">Forward to:</label>
                            <select id="tmForwardSelect"
                                class="flex-1 sm:flex-none rounded-lg border-gray-300 text-sm focus:ring-2 focus:ring-indigo-500">
                                <option value="" selected disabled>Select user</option>
                            </select>
                            <button type="button" id="tmForwardApply"
                                class="inline-flex items-center justify-center rounded-lg bg-amber-600 px-4 py-2 text-sm font-medium text-white hover:bg-amber-700 transition-colors">
                                Forward Ticket
                            </button>
                        </div>
                    </div>
                </div>

                <!-- Footer - Actions -->
                <div class="px-6 py-4 border-t border-gray-100 bg-gray-50 shrink-0">
                    <!-- Main Actions -->
                    <div class="flex flex-col sm:flex-row items-stretch sm:items-center justify-between gap-3">
                        <div class="flex items-center gap-2">

                        </div>
                        <div class="flex items-center gap-2">
                            <button type="button"
                                class="flex-1 sm:flex-none inline-flex items-center justify-center rounded-lg bg-white px-4 py-2 text-sm font-medium text-gray-700 ring-1 ring-gray-300 hover:bg-gray-50 transition-colors"
                                data-modal-close>Cancel</button>
                            <button type="button" title="Send response" aria-label="Send response"
                                class="flex-1 sm:flex-none inline-flex items-center justify-center gap-2 rounded-lg bg-indigo-600 px-5 py-2 text-sm font-medium text-white hover:bg-indigo-700 transition-colors shadow-sm"
                                id="tmSendResponse">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" viewBox="0 0 24 24"
                                    fill="currentColor">
                                    <path d="M3 12l18-9-9 18-2-7-7-2z" />
                                </svg>
                                Send Response
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@section('staff-scripts')
<script>
let currentFilter = 'all';
let currentPage = 1;
let perPage = 10;
let searchTerm = '';
let sortField = 'date_created';
let sortDirection = 'desc';

document.addEventListener('DOMContentLoaded', function() {
    initializeEventListeners();
    loadTickets();
});

function initializeEventListeners() {
    // Search functionality
    document.getElementById('searchInput').addEventListener('input', function() {
        searchTerm = this.value.trim();
        currentPage = 1;
        loadTickets();
    });

    // Per page selector
    document.getElementById('perPageSelect').addEventListener('change', function() {
        perPage = parseInt(this.value);
        currentPage = 1;
        loadTickets();
    });

    // Filter tabs
    document.querySelectorAll('.filter-tab').forEach(tab => {
        tab.addEventListener('click', function() {
            setActiveFilter(this.dataset.filter);
        });
    });

    // Sort buttons
    document.querySelectorAll('[data-sort]').forEach(button => {
        button.addEventListener('click', function() {
            const field = this.dataset.sort;
            if (sortField === field) {
                sortDirection = sortDirection === 'asc' ? 'desc' : 'asc';
            } else {
                sortField = field;
                sortDirection = 'asc';
            }
            updateSortIcons();
            loadTickets();
        });
    });

    // Pagination
    document.getElementById('prevPageBtn').addEventListener('click', () => changePage(currentPage - 1));
    document.getElementById('nextPageBtn').addEventListener('click', () => changePage(currentPage + 1));
    document.getElementById('mobilePrevBtn').addEventListener('click', () => changePage(currentPage - 1));
    document.getElementById('mobileNextBtn').addEventListener('click', () => changePage(currentPage + 1));

    // Row click to open modal
    document.getElementById('ticketsTableBody').addEventListener('click', (e) => {
        const tr = e.target.closest('tr');
        if (!tr) return;
        const id = tr.getAttribute('data-id');
        if (!id) return;
        window.openModalFor(id);
    });
}

async function loadTickets() {
    try {
        const params = new URLSearchParams({
            status: currentFilter,
            search: searchTerm,
            page: currentPage,
            per_page: perPage,
            sort_by: sortField,
            sort_direction: sortDirection
        });

        const response = await fetch(`/staff/tickets/data?${params}`, {
            method: 'GET',
            headers: {
                'Accept': 'application/json',
                'X-Requested-With': 'XMLHttpRequest'
            },
            credentials: 'include'
        });

        if (!response.ok) {
            throw new Error(`HTTP ${response.status}: ${response.statusText}`);
        }

        const data = await response.json();

        updateCounts(data.counts);
        renderTickets(data.tickets);
        renderPagination(data.pagination);

    } catch (error) {
        console.error('Error loading tickets:', error);
        showErrorState('Failed to load tickets. Please try again.');
    }
}

function updateCounts(counts) {
    Object.keys(counts).forEach(key => {
        const element = document.getElementById(`count-${key}`);
        if (element) {
            element.textContent = counts[key];
        }
    });
}

function renderTickets(tickets) {
    const tbody = document.getElementById('ticketsTableBody');
    if (!tbody) return;

    if (!tickets || tickets.length === 0) {
        tbody.innerHTML = `
            <tr>
                <td colspan="4" class="px-4 py-12 text-center">
                    <div class="flex flex-col items-center">
                        <svg class="h-12 w-12 text-gray-400 mb-4" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                        </svg>
                        <h3 class="text-sm font-medium text-gray-900 mb-1">No tickets found</h3>
                        <p class="text-sm text-gray-500">No tickets match the current filter.</p>
                    </div>
                </td>
            </tr>
        `;
        return;
    }

    const rows = tickets.map(ticket => {
        const statusClass = getStatusClass(ticket.status);
        const createdDate = formatDate(ticket.date_created);

        return `
            <tr class="hover:bg-gray-50 cursor-pointer" data-id="${ticket.id}">
                <td class="px-4 py-4 whitespace-nowrap">
                    <div class="text-sm font-medium text-indigo-600">#${ticket.id}</div>
                </td>
                <td class="px-4 py-4">
                    <div class="text-sm text-gray-900 max-w-xs truncate" title="${ticket.question || ''}">${ticket.question || 'No subject'}</div>
                    <div class="flex items-center gap-2 mt-1">
                        <span class="inline-flex items-center rounded-full bg-gray-100 px-2 py-0.5 text-xs font-medium text-gray-700">${ticket.category || 'Uncategorized'}</span>
                    </div>
                </td>
                <td class="px-4 py-4 whitespace-nowrap">
                    <span class="inline-flex items-center gap-1 rounded-full px-2.5 py-1 text-xs font-medium ${statusClass}">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-3.5 w-3.5" viewBox="0 0 24 24" fill="currentColor">
                            <circle cx="12" cy="12" r="5"></circle>
                        </svg>
                        ${ticket.status}
                    </span>
                </td>
                <td class="px-4 py-4 whitespace-nowrap text-sm text-gray-500">
                    ${createdDate}
                </td>
            </tr>
        `;
    }).join('');

    tbody.innerHTML = rows;
}

function renderPagination(pagination) {
    // Update showing info
    document.getElementById('showingFrom').textContent = pagination.from || 0;
    document.getElementById('showingTo').textContent = pagination.to || 0;
    document.getElementById('totalResults').textContent = pagination.total || 0;

    // Update navigation buttons
    document.getElementById('prevPageBtn').disabled = pagination.current_page <= 1;
    document.getElementById('nextPageBtn').disabled = pagination.current_page >= pagination.last_page;
    document.getElementById('mobilePrevBtn').disabled = pagination.current_page <= 1;
    document.getElementById('mobileNextBtn').disabled = pagination.current_page >= pagination.last_page;

    // Render page numbers (simplified)
    const pageNumbers = document.getElementById('pageNumbers');
    pageNumbers.innerHTML = '';

    for (let i = Math.max(1, pagination.current_page - 2); i <= Math.min(pagination.last_page, pagination.current_page + 2); i++) {
        const button = document.createElement('button');
        button.className = `relative inline-flex items-center px-4 py-2 border text-sm font-medium ${
            i === pagination.current_page
                ? 'z-10 bg-indigo-50 border-indigo-500 text-indigo-600'
                : 'bg-white border-gray-300 text-gray-500 hover:bg-gray-50'
        }`;
        button.textContent = i;
        button.onclick = () => changePage(i);
        pageNumbers.appendChild(button);
    }
}

function setActiveFilter(filter) {
    currentFilter = filter;

    // Update tab appearance
    document.querySelectorAll('.filter-tab').forEach(tab => {
        tab.classList.remove('active', 'border-indigo-500', 'text-indigo-600');
        tab.classList.add('border-transparent', 'text-gray-500');

        if (tab.dataset.filter === filter) {
            tab.classList.add('active', 'border-indigo-500', 'text-indigo-600');
            tab.classList.remove('border-transparent', 'text-gray-500');
        }
    });

    // Update table title
    const titles = {
        all: 'All Tickets',
        open: 'Open Tickets',
        forwarded: 'Forwarded Tickets',
        closed: 'Closed Tickets'
    };

    document.getElementById('tableTitle').textContent = titles[filter] || 'Tickets';

    currentPage = 1;
    loadTickets();
}

function changePage(page) {
    if (page < 1) return;
    currentPage = page;
    loadTickets();
}

function updateSortIcons() {
    document.querySelectorAll('.sort-icon').forEach(icon => {
        icon.className = 'w-4 h-4 text-gray-400 group-hover:text-gray-600 sort-icon';
    });

    const activeButton = document.querySelector(`[data-sort="${sortField}"]`);
    if (activeButton) {
        const icon = activeButton.querySelector('.sort-icon');
        if (icon) {
            icon.className = `w-4 h-4 ${sortDirection === 'asc' ? 'text-indigo-600' : 'text-indigo-600 rotate-180'} sort-icon`;
        }
    }
}

function getStatusClass(status) {
    const classes = {
        'Open': 'text-blue-700 bg-blue-50 ring-blue-600/20',
        'Forwarded': 'text-amber-700 bg-amber-50 ring-amber-600/20',
        'Closed': 'text-emerald-700 bg-emerald-50 ring-emerald-600/20'
    };
    return classes[status] || 'text-slate-700 bg-slate-50 ring-slate-600/20';
}

function formatDate(dateString) {
    if (!dateString) return '-';
    try {
        const date = new Date(dateString);
        return date.toLocaleDateString();
    } catch (error) {
        return '-';
    }
}

function showErrorState(message = 'Failed to load tickets. Please try again.') {
    const tbody = document.getElementById('ticketsTableBody');
    if (tbody) {
        tbody.innerHTML = `
            <tr>
                <td colspan="4" class="px-4 py-12 text-center">
                    <div class="flex flex-col items-center">
                        <svg class="h-8 w-8 text-red-400 mb-4" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                        <p class="text-gray-500">${message}</p>
                        <button onclick="loadTickets()" class="mt-2 text-indigo-600 hover:text-indigo-500 text-sm font-medium">
                            Retry
                        </button>
                    </div>
                </td>
            </tr>
        `;
    }
}

function openTicket(id) {
    window.openModalFor(id);
}

// Modal functionality copied from admin dashboard
(function() {
    const state = document.createElement('div');
    state.id = 'staff-tickets-state';
    state.setAttribute('data-show-url-template', "{{ url('/staff/tickets') }}/__ID__");
    state.setAttribute('data-respond-url-template', "{{ url('/staff/tickets') }}/__ID__/respond");
    state.setAttribute('data-forward-url-template', "{{ url('/staff/tickets') }}/__ID__/forward");
    document.body.appendChild(state);

    const SHOW_TEMPLATE = state.getAttribute('data-show-url-template');
    const RESPOND_TEMPLATE = state.getAttribute('data-respond-url-template');
    const FORWARD_TEMPLATE = state.getAttribute('data-forward-url-template');
    const csrf = document.querySelector('meta[name="csrf-token"]').getAttribute('content');

    const ticketModal = document.getElementById('ticketModal');

    let currentTicketId = null;

    const statusStyles = {
        'Open': 'text-blue-700 bg-blue-50 ring-blue-600/20',
        'Forwarded': 'text-amber-700 bg-amber-50 ring-amber-600/20',
        'Closed': 'text-emerald-700 bg-emerald-50 ring-emerald-600/20',
    };

    function statusClassFor(s) {
        return statusStyles[s] || 'text-slate-700 bg-slate-50 ring-slate-600/20';
    }

    function fmtDate(d) {
        try {
            const dt = new Date(d);
            if (isNaN(dt.getTime())) return '';
            return dt.toLocaleString();
        } catch (_) {
            return '';
        }
    }

    function escapeHtml(s) {
        if (s == null) return '';
        return String(s).replace(/&/g, '&').replace(/</g, '<').replace(/>/g, '>').replace(/"/g,
            '"').replace(/'/g, "&#039;");
    }

    function ensureHistorySection() {
        let section = document.getElementById('tmHistorySection');
        const list = document.getElementById('tmHistoryList');
        return {
            section,
            list
        };
    }

    function renderHistory(histArr) {
        const {
            section,
            list
        } = ensureHistorySection();
        if (!section || !list) return;
        if (!Array.isArray(histArr) || histArr.length === 0) {
            list.innerHTML = '<li class="text-xs text-gray-500">No routing history.</li>';
            return;
        }
        const items = histArr.map(h => {
            const when = fmtDate(h.routed_at || h.created_at);
            const who = (h.staff && h.staff.name) ? h.staff.name : '-';
            const status = h.status || '';
            const notes = h.notes || '';
            return `
        <li class="text-xs text-gray-700">
          <div class="flex items-start justify-between">
            <div>
              <span class="font-medium">${status}</span>
              <span class="text-gray-500"> • ${who}</span>
            </div>
            <div class="text-gray-500">${when}</div>
          </div>
          ${notes ? `<div class="text-gray-600 mt-0.5">${notes}</div>` : ''}
        </li>
      `;
        });
        list.innerHTML = items.join('');
    }

    async function openModalFor(id) {
        currentTicketId = id;
        const url = SHOW_TEMPLATE.replace('__ID__', id);
        try {
            const res = await fetch(url, {
                headers: {
                    'X-Requested-With': 'XMLHttpRequest'
                },
                credentials: 'same-origin'
            });
            if (!res.ok) {
                console.error('Staff: failed to load ticket', res.status);
                return;
            }
            const t = await res.json();
            if (!t) return;

            const tmTicketNo = document.getElementById('tmTicketNo');
            const tmStatus = document.getElementById('tmStatus');
            const tmQuestion = document.getElementById('tmQuestion');
            const tmCategory = document.getElementById('tmCategory');
            const tmDates = document.getElementById('tmDates');
            const tmEmail = document.getElementById('tmEmail');
            const tmRecepient = document.getElementById('tmRecepient');
            const tmResponse = document.getElementById('tmResponse');

            const ticketNo = String(t.id);
            const createdAt = fmtDate(t.date_created || t.created_at);
            const updatedAt = fmtDate(t.updated_at);
            const category = t.category ?? '';
            const question = t.question ?? '';
            const email = t.email ?? '';
            const recepient = t.recepient_id ?? '';

            // Fill fields
            if (tmTicketNo) tmTicketNo.textContent = 'Ticket #' + ticketNo;
            if (tmDates) tmDates.textContent = createdAt ?
                `Created ${createdAt}${updatedAt ? ' • Updated ' + updatedAt : ''}` : '';
            if (tmStatus) {
                tmStatus.className =
                    'inline-flex items-center gap-1.5 rounded-full px-3 py-1 text-xs font-medium ring-1 ' +
                    statusClassFor(t.status);
                tmStatus.innerHTML =
                    `<svg xmlns="http://www.w3.org/2000/svg" class="h-3.5 w-3.5" viewBox="0 0 24 24" fill="currentColor"><circle cx="12" cy="12" r="5"></circle></svg> ${t.status ?? ''}`;
            }
            if (tmCategory) tmCategory.textContent = category;
            if (tmQuestion) tmQuestion.textContent = question;
            if (tmEmail) tmEmail.textContent = email;
            if (tmRecepient) tmRecepient.textContent = recepient;
            if (tmResponse) tmResponse.value = '';

            // Reset details section to collapsed state
            const detailsContent = document.getElementById('tmDetailsContent');
            const detailsChevron = document.getElementById('tmDetailsChevron');
            const toggleDetailsBtn = document.getElementById('tmToggleDetails');
            if (detailsContent) detailsContent.classList.add('hidden');
            if (detailsChevron) detailsChevron.style.transform = 'rotate(0deg)';
            if (toggleDetailsBtn) toggleDetailsBtn.querySelector('span').textContent = 'Show Details';

            // Handle attachments
            const attachmentsBlock = document.getElementById('tmAttachmentsBlock');
            const attachmentsList = document.getElementById('tmAttachmentsList');
            if (attachmentsBlock && attachmentsList) {
                attachmentsList.innerHTML = '';
                if (t.attachments) {
                    let attachments = [];
                    try {
                        attachments = JSON.parse(t.attachments);
                    } catch (e) {
                        attachments = [];
                    }
                    if (attachments.length > 0) {
                        attachments.forEach((path, index) => {
                            const img = document.createElement('img');
                            img.src = '/storage/' + path;
                            img.alt = 'Attachment ' + (index + 1);
                            img.className =
                                'max-w-16 max-h-16 object-cover rounded cursor-pointer border border-gray-300 hover:border-indigo-400';
                            img.onclick = () => openLightbox(attachments, index);
                            attachmentsList.appendChild(img);
                        });
                        attachmentsBlock.classList.remove('hidden');
                    } else {
                        attachmentsBlock.classList.add('hidden');
                    }
                } else {
                    attachmentsBlock.classList.add('hidden');
                }
            }

            // Hide forward controls initially
            const tmForwardControls = document.getElementById('tmForwardControls');
            if (tmForwardControls) tmForwardControls.classList.add('hidden');

            // Prepare and render history; keep hidden by default until toggled in Options
            const hsObj = ensureHistorySection();
            if (hsObj.section) hsObj.section.classList.add('hidden');
            const histories = t.routing_histories || t.routingHistories || [];
            renderHistory(Array.isArray(histories) ? histories : []);

            // Toggle forward option and response display based on status
            const isClosed = (t.status === 'Closed');
            const tmOptionForward = document.getElementById('tmOptionForward');
            const tmStoredResponseBlock = document.getElementById('tmStoredResponseBlock');
            const tmStoredResponse = document.getElementById('tmStoredResponse');
            const tmSendResponse = document.getElementById('tmSendResponse');

            if (tmOptionForward) tmOptionForward.classList.toggle('hidden', isClosed);
            if (tmForwardControls) tmForwardControls.classList.add('hidden');
            if (tmStoredResponseBlock) {
                if (isClosed) {
                    tmStoredResponseBlock.classList.remove('hidden');
                    if (tmStoredResponse) tmStoredResponse.textContent = t.response ? String(t.response) :
                        'No response on record.';
                } else {
                    tmStoredResponseBlock.classList.add('hidden');
                    if (tmStoredResponse) tmStoredResponse.textContent = '';
                }
            }
            if (tmResponse) {
                tmResponse.disabled = isClosed;
                tmResponse.placeholder = isClosed ? 'Ticket is closed. Response cannot be edited.' :
                    'Type your response message here...';
            }
            if (tmSendResponse) {
                tmSendResponse.disabled = isClosed;
                tmSendResponse.classList.toggle('opacity-50', isClosed);
                tmSendResponse.classList.toggle('pointer-events-none', isClosed);
            }

            // Populate forward select with users
            const tmForwardSelect = document.getElementById('tmForwardSelect');
            if (tmForwardSelect && t.users) {
                tmForwardSelect.innerHTML = '<option value="" selected disabled>Select user</option>';
                t.users.forEach(user => {
                    const option = document.createElement('option');
                    option.value = user.id;
                    option.textContent = user.name;
                    tmForwardSelect.appendChild(option);
                });
            }

            if (ticketModal) {
                ticketModal.classList.remove('hidden');
                document.body.classList.add('overflow-hidden');
            }
        } catch (err) {
            console.error('Staff: error loading ticket', err);
        }
    }

    function closeModal() {
        if (!ticketModal) return;
        ticketModal.classList.add('hidden');
        document.body.classList.remove('overflow-hidden');
        currentTicketId = null;
    }

    // Lightbox functions
    let currentLightboxImages = [];
    let currentLightboxIndex = 0;

    function openLightbox(images, index) {
        currentLightboxImages = images;
        currentLightboxIndex = index;
        const lightbox = document.getElementById('imageLightbox');
        const img = document.getElementById('lightboxImage');
        if (lightbox && img) {
            img.src = '/storage/' + images[index];
            lightbox.classList.remove('hidden');
            document.body.classList.add('overflow-hidden');
            updateLightboxButtons();
        }
    }

    function closeLightbox() {
        const lightbox = document.getElementById('imageLightbox');
        if (lightbox) {
            lightbox.classList.add('hidden');
            document.body.classList.remove('overflow-hidden');
        }
    }

    function updateLightboxButtons() {
        const prevBtn = document.getElementById('lightboxPrev');
        const nextBtn = document.getElementById('lightboxNext');
        if (prevBtn) prevBtn.style.display = currentLightboxIndex > 0 ? 'flex' : 'none';
        if (nextBtn) nextBtn.style.display = currentLightboxIndex < currentLightboxImages.length - 1 ? 'flex' :
            'none';
    }

    function prevImage() {
        if (currentLightboxIndex > 0) {
            currentLightboxIndex--;
            const img = document.getElementById('lightboxImage');
            if (img) img.src = '/storage/' + currentLightboxImages[currentLightboxIndex];
            updateLightboxButtons();
        }
    }

    function nextImage() {
        if (currentLightboxIndex < currentLightboxImages.length - 1) {
            currentLightboxIndex++;
            const img = document.getElementById('lightboxImage');
            if (img) img.src = '/storage/' + currentLightboxImages[currentLightboxIndex];
            updateLightboxButtons();
        }
    }

    // Toggle Details Section
    const tmToggleDetails = document.getElementById('tmToggleDetails');
    const tmDetailsContent = document.getElementById('tmDetailsContent');
    const tmDetailsChevron = document.getElementById('tmDetailsChevron');

    if (tmToggleDetails && tmDetailsContent && tmDetailsChevron) {
        tmToggleDetails.addEventListener('click', () => {
            const isHidden = tmDetailsContent.classList.contains('hidden');
            tmDetailsContent.classList.toggle('hidden');
            tmDetailsChevron.style.transform = isHidden ? 'rotate(180deg)' : 'rotate(0deg)';
            tmToggleDetails.querySelector('span').textContent = isHidden ? 'Hide Details' :
                'Show Details';
        });
    }

    // Options dropdown handlers
    function setupOptionsMenu(optionsBtnId, optionsMenuId) {
        const tmOptionsBtn = document.getElementById(optionsBtnId);
        const tmOptionsMenu = document.getElementById(optionsMenuId);

        if (!tmOptionsBtn || !tmOptionsMenu) return false;

        tmOptionsBtn.addEventListener('click', (e) => {
            e.stopPropagation();
            const isOpen = !tmOptionsMenu.classList.contains('hidden');
            tmOptionsMenu.classList.toggle('hidden', isOpen);
            tmOptionsBtn.setAttribute('aria-expanded', String(!isOpen));
        });

        document.addEventListener('click', (e) => {
            if (!tmOptionsMenu.contains(e.target) && !tmOptionsBtn.contains(e.target)) {
                tmOptionsMenu.classList.add('hidden');
                tmOptionsBtn.setAttribute('aria-expanded', 'false');
            }
        });

        tmOptionsMenu.addEventListener('click', (e) => {
            const btn = e.target.closest('[data-option]');
            if (!btn) return;
            const action = btn.getAttribute('data-option');

            // Hide menu after action
            tmOptionsMenu.classList.add('hidden');
            tmOptionsBtn.setAttribute('aria-expanded', 'false');

            if (action === 'toggle-history') {
                const hs = ensureHistorySection().section;
                if (hs) {
                    const willShow = hs.classList.contains('hidden');
                    hs.classList.toggle('hidden');
                    btn.textContent = willShow ? 'Hide History' : 'Show History';
                }
            } else if (action === 'show-forward') {
                const tmForwardControls = document.getElementById('tmForwardControls');
                if (tmForwardControls) tmForwardControls.classList.remove('hidden');
            }
        });

        return true;
    }

    // Setup both options menus
    setupOptionsMenu('tmOptionsBtn', 'tmOptionsMenu');

    // Forward via select + apply with SweetAlert
    const tmForwardApply = document.getElementById('tmForwardApply');
    const tmForwardSelect = document.getElementById('tmForwardSelect');

    if (tmForwardApply && tmForwardSelect) {
        tmForwardApply.addEventListener('click', async () => {
            if (!currentTicketId) return;
            if (!tmForwardSelect.value) {
                if (window.Swal) {
                    Swal.fire({
                        icon: 'warning',
                        title: 'Selection Required',
                        text: 'Please choose a user to forward to.',
                        confirmButtonText: 'OK'
                    });
                } else {
                    alert('Please choose a user to forward to.');
                }
                return;
            }
            const userId = tmForwardSelect.value;
            const fUrl = FORWARD_TEMPLATE.replace('__ID__', currentTicketId);
            try {
                const res = await fetch(fUrl, {
                    method: 'POST',
                    headers: {
                        'Accept': 'application/json',
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': csrf
                    },
                    credentials: 'same-origin',
                    body: JSON.stringify({
                        user_id: userId
                    })
                });
                console.log('Forward request sent to:', fUrl);
                console.log('Response status:', res.status, res.statusText);
                if (res.ok) {
                    const data = await res.json();
                    console.log('Forward successful:', data);
                    if (window.Swal) {
                        Swal.fire({
                            icon: 'success',
                            title: 'Ticket Forwarded',
                            text: 'Ticket has been forwarded successfully!',
                            timer: 3000,
                            timerProgressBar: true,
                            showConfirmButton: false,
                            position: 'top-end',
                            toast: true
                        });
                    } else {
                        alert('Ticket forwarded successfully.');
                    }
                    closeModal();
                    // Refresh the tickets list
                    loadTickets();
                } else {
                    const errorText = await res.text();
                    console.error('Forward failed', res.status, errorText);
                    if (window.Swal) {
                        Swal.fire({
                            icon: 'error',
                            title: 'Forward Failed',
                            text: 'Failed to forward ticket. Please try again. Error: ' +
                                res.status + ' ' + res.statusText,
                            confirmButtonText: 'OK'
                        });
                    } else {
                        alert('Forward failed. Please try again. Error: ' + res.status + ' ' + res
                            .statusText);
                    }
                }
            } catch (err) {
                console.error('Forward error', err);
                if (window.Swal) {
                    Swal.fire({
                        icon: 'error',
                        title: 'Network Error',
                        text: 'Network error during forward.',
                        confirmButtonText: 'OK'
                    });
                } else {
                    alert('Network error during forward.');
                }
            }
        });
    }

    // Send response with SweetAlert
    const tmSendResponse = document.getElementById('tmSendResponse');
    const tmResponse = document.getElementById('tmResponse');

    if (tmSendResponse && tmResponse) {
        tmSendResponse.addEventListener('click', async () => {
            const msg = tmResponse.value.trim();
            if (!msg) {
                if (window.Swal) {
                    Swal.fire({
                        icon: 'warning',
                        title: 'Message Required',
                        text: 'Please enter a response message.',
                        confirmButtonText: 'OK'
                    });
                } else {
                    alert('Please enter a response message.');
                }
                return;
            }
            if (!currentTicketId) {
                if (window.Swal) {
                    Swal.fire({
                        icon: 'error',
                        title: 'No Ticket Selected',
                        text: 'No ticket selected.',
                        confirmButtonText: 'OK'
                    });
                } else {
                    alert('No ticket selected.');
                }
                return;
            }
            try {
                tmSendResponse.disabled = true;
                tmSendResponse.classList.add('opacity-50', 'pointer-events-none');
                // Show spinner
                tmSendResponse.innerHTML = `<svg class="animate-spin h-4 w-4" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg> Sending...`;
                const rUrl = RESPOND_TEMPLATE.replace('__ID__', currentTicketId);
                const res = await fetch(rUrl, {
                    method: 'POST',
                    headers: {
                        'Accept': 'application/json',
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': csrf
                    },
                    credentials: 'same-origin',
                    body: JSON.stringify({
                        message: msg
                    })
                });
                if (res.ok) {
                    if (window.Swal) {
                        Swal.fire({
                            icon: 'success',
                            title: 'Response Sent',
                            text: 'Response email sent successfully!',
                            timer: 3000,
                            timerProgressBar: true,
                            showConfirmButton: false,
                            position: 'top-end',
                            toast: true
                        });
                    } else {
                        alert('Response email sent successfully.');
                    }
                    tmResponse.value = '';
                    closeModal();
                    // Refresh the tickets list
                    loadTickets();
                } else {
                    const txt = await res.text();
                    console.error('Send response failed', txt);
                    if (window.Swal) {
                        Swal.fire({
                            icon: 'error',
                            title: 'Failed to Send Response',
                            text: 'Failed to send response. Please check mail configuration.',
                            confirmButtonText: 'OK'
                        });
                    } else {
                        alert('Failed to send response. Please check mail configuration.');
                    }
                }
            } catch (err) {
                console.error('Send response error', err);
                if (window.Swal) {
                    Swal.fire({
                        icon: 'error',
                        title: 'Network Error',
                        text: 'Network error while sending response.',
                        confirmButtonText: 'OK'
                    });
                } else {
                    alert('Network error while sending response.');
                }
            } finally {
                tmSendResponse.disabled = false;
                tmSendResponse.classList.remove('opacity-50', 'pointer-events-none');
                // Restore button text
                tmSendResponse.innerHTML = `<svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" viewBox="0 0 24 24" fill="currentColor"><path d="M3 12l18-9-9 18-2-7-7-2z" /></svg> Send Response`;
            }
        });
    }

    // Close modal handlers
    document.addEventListener('click', (e) => {
        if (e.target && e.target.closest('[data-modal-backdrop]')) {
            closeModal();
        }
        if (e.target && e.target.getAttribute && e.target.getAttribute('data-modal-close') != null) {
            closeModal();
        }
        if (e.target && e.target.closest && e.target.closest('[data-modal-close]')) {
            closeModal();
        }
    });

    // Escape key to close modal
    document.addEventListener('keydown', (e) => {
        if (e.key === 'Escape' && ticketModal && !ticketModal.classList.contains('hidden')) {
            closeModal();
        }
    });

    // Lightbox event listeners
    const lightboxCloseBtn = document.getElementById('lightboxClose');
    const lightboxPrevBtn = document.getElementById('lightboxPrev');
    const lightboxNextBtn = document.getElementById('lightboxNext');
    const lightboxEl = document.getElementById('imageLightbox');

    if (lightboxCloseBtn) lightboxCloseBtn.addEventListener('click', closeLightbox);
    if (lightboxPrevBtn) lightboxPrevBtn.addEventListener('click', prevImage);
    if (lightboxNextBtn) lightboxNextBtn.addEventListener('click', nextImage);

    // Close lightbox on background click
    if (lightboxEl) {
        lightboxEl.addEventListener('click', (e) => {
            if (e.target === lightboxEl) closeLightbox();
        });
    }

    // Keyboard navigation for lightbox
    document.addEventListener('keydown', (e) => {
        if (lightboxEl && !lightboxEl.classList.contains('hidden')) {
            if (e.key === 'Escape') closeLightbox();
            else if (e.key === 'ArrowLeft') prevImage();
            else if (e.key === 'ArrowRight') nextImage();
        }
    });

    // Make openModalFor available globally
    window.openModalFor = openModalFor;
})();
</script>
@endsection