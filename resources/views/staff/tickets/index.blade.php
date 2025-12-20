@extends('layouts.staff')

@section('title', 'Staff Tickets')

@section('staff-content')
<div class="mx-auto max-w-7xl px-4 py-6 sm:px-6 lg:px-8">
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
            <tr class="hover:bg-gray-50 cursor-pointer" onclick="openTicket(${ticket.id})">
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
    // Placeholder for opening ticket detail
    console.log('Open ticket:', id);
}
</script>
@endsection