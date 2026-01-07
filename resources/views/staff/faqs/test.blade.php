@extends('layouts.staff')

@section('title', 'Document Test')

@section('staff-content')
    <div class="mx-auto max-w-7xl px-4 py-6 sm:px-6 lg:px-8">
        <div class="mb-6">
            <h1 class="text-2xl font-bold text-gray-900">Document Test - Rasa Server Integration</h1>
            <p class="mt-1 text-sm text-gray-600">Test fetching documents from the Rasa server</p>
        </div>

        <div class="bg-white rounded-lg shadow-sm ring-1 ring-slate-900/5 mb-6">
            <div class="p-4 flex flex-col sm:flex-row gap-4 items-start sm:items-center justify-between">
                <div class="flex-1 max-w-md">
                    <button id="fetchFaqsBtn" type="button"
                        class="inline-flex items-center gap-2 rounded-lg bg-blue-600 hover:bg-blue-700 text-white text-sm font-medium px-4 py-2"
                        aria-label="Fetch FAQs">
                        <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
                        </svg>
                        <span>Fetch FAQs from Rasa</span>
                    </button>
                </div>
                <div class="flex items-center gap-3">
                    <div class="flex items-center gap-2">
                        <span class="text-sm text-gray-700">Server Status:</span>
                        <span id="serverStatus" class="inline-flex items-center gap-1 rounded-full px-2.5 py-1 text-xs font-medium">
                            <svg class="h-2 w-2 animate-spin text-gray-400" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                            </svg>
                            Checking...
                        </span>
                    </div>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-lg shadow-sm ring-1 ring-slate-900/5">
            <div class="px-4 py-3 border-b border-gray-200 sm:px-6">
                <h3 class="text-lg font-medium text-gray-900">FAQs from Rasa Server</h3>
            </div>
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th scope="col" class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">ID</th>
                            <th scope="col" class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Intent</th>
                            <th scope="col" class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Description</th>
                            <th scope="col" class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200" id="faqsTableBody">
                        <tr>
                            <td colspan="4" class="px-4 py-12 text-center">
                                <div class="flex flex-col items-center">
                                    <svg class="h-8 w-8 text-gray-400 mb-4" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.172 16.172a4 4 0 015.656 0M9 10h.01M15 10h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                                    </svg>
                                    <p class="text-gray-500">Click "Fetch FAQs from Rasa" to load FAQs</p>
                                </div>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Hidden state with URLs -->
    <div id="faqs-state" class="hidden"
         data-fetch-url="{{ route('staff.document_management.fetch') }}"></div>
@endsection

@section('staff-scripts')
    <script>
        (function() {
            const stateEl = document.getElementById('faqs-state');
            const FETCH_URL = stateEl.getAttribute('data-fetch-url');
            const csrf = document.querySelector('meta[name="csrf-token"]').getAttribute('content');

            const $ = (sel, root = document) => root.querySelector(sel);
            const $$ = (sel, root = document) => Array.from(root.querySelectorAll(sel));

            // Elements
            const faqsTableBody = $('#faqsTableBody');
            const fetchFaqsBtn = $('#fetchFaqsBtn');
            const serverStatus = $('#serverStatus');

            // Fetch FAQs from Rasa server
            async function fetchFaqs() {
                try {
                    faqsTableBody.innerHTML = `<tr>
                        <td colspan="4" class="px-4 py-12 text-center">
                            <div class="flex flex-col items-center">
                                <svg class="animate-spin h-8 w-8 text-gray-400 mb-4" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                </svg>
                                <p class="text-gray-500">Loading FAQs from Rasa server...</p>
                            </div>
                        </td>
                    </tr>`;

                    const response = await fetch(FETCH_URL, {
                        headers: {
                            'X-Requested-With': 'XMLHttpRequest',
                            'Accept': 'application/json'
                        }
                    });

                    if (!response.ok) {
                        throw new Error(`HTTP ${response.status}: ${response.statusText}`);
                    }

                    const data = await response.json();

                    if (!data.success) {
                        throw new Error(data.error || 'Failed to fetch FAQs');
                    }

                    renderFaqs(data.faqs || []);
                    updateServerStatus(true);

                } catch (error) {
                    console.error('Error fetching FAQs:', error);
                    showErrorState('Failed to load FAQs from Rasa server. The server may be offline or unreachable.');
                    updateServerStatus(false);
                }
            }

            // Render FAQs table
            function renderFaqs(faqs) {
                if (!faqs || faqs.length === 0) {
                    faqsTableBody.innerHTML = `<tr>
                        <td colspan="4" class="px-4 py-12 text-center">
                            <div class="flex flex-col items-center">
                                <svg class="h-12 w-12 text-gray-400 mb-4" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                                </svg>
                                <h3 class="text-sm font-medium text-gray-900 mb-1">No FAQs found</h3>
                                <p class="text-sm text-gray-500">No FAQs available from the Rasa server.</p>
                            </div>
                        </td>
                    </tr>`;
                    return;
                }

                const rows = faqs.map((faq, index) => {
                    const statusClass = faq.response_disabled ? 'text-red-700 bg-red-50 ring-red-600/20' : 'text-emerald-700 bg-emerald-50 ring-emerald-600/20';
                    const statusText = faq.response_disabled ? 'Disabled' : 'Active';

                    return `<tr class="hover:bg-gray-50">
                        <td class="px-4 py-4 whitespace-nowrap">
                            <div class="text-sm font-medium text-indigo-600">#${index + 1}</div>
                        </td>
                        <td class="px-4 py-4">
                            <div class="text-sm text-gray-900">${escapeHtml(faq.intent || 'No intent')}</div>
                        </td>
                        <td class="px-4 py-4">
                            <div class="text-sm text-gray-700">${escapeHtml(faq.description || 'No description')}</div>
                        </td>
                        <td class="px-4 py-4 whitespace-nowrap">
                            <span class="inline-flex items-center gap-1 rounded-full px-2.5 py-1 text-xs font-medium ${statusClass}">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-3.5 w-3.5" viewBox="0 0 24 24" fill="currentColor">
                                    <circle cx="12" cy="12" r="5"></circle>
                                </svg>
                                ${statusText}
                            </span>
                        </td>
                    </tr>`;
                }).join('');

                faqsTableBody.innerHTML = rows;
            }

            // Show error state
            function showErrorState(message = 'Failed to load FAQs. Please try again.') {
                faqsTableBody.innerHTML = `<tr>
                    <td colspan="4" class="px-4 py-12 text-center">
                        <div class="flex flex-col items-center">
                            <svg class="h-8 w-8 text-red-400 mb-4" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                            <p class="text-gray-500 mb-2">${message}</p>
                            <button onclick="fetchFaqs()" class="text-indigo-600 hover:text-indigo-500 text-sm font-medium">
                                Retry
                            </button>
                        </div>
                    </td>
                </tr>`;
            }

            // Update server status indicator
            function updateServerStatus(isOnline) {
                if (isOnline) {
                    serverStatus.className = 'inline-flex items-center gap-1 rounded-full px-2.5 py-1 text-xs font-medium text-emerald-700 bg-emerald-50 ring-emerald-600/20';
                    serverStatus.innerHTML = '<svg class="h-2 w-2 text-emerald-600" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor"><circle cx="10" cy="10" r="8"></circle></svg> Online';
                } else {
                    serverStatus.className = 'inline-flex items-center gap-1 rounded-full px-2.5 py-1 text-xs font-medium text-red-700 bg-red-50 ring-red-600/20';
                    serverStatus.innerHTML = '<svg class="h-2 w-2 text-red-600" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor"><circle cx="10" cy="10" r="8"></circle></svg> Offline';
                }
            }

            // Escape HTML
            function escapeHtml(s) {
                if (s === null || s === undefined) return '';
                return String(s)
                    .replaceAll('&', '&')
                    .replaceAll('<', '<')
                    .replaceAll('>', '>')
                    .replaceAll('"', '"')
                    .replaceAll("'", '&#039;');
            }

            // Event listeners
            if (fetchFaqsBtn) {
                fetchFaqsBtn.addEventListener('click', fetchFaqs);
            }

        })();
    </script>
@endsection