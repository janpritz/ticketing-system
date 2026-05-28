<!-- FAQ Management -->
@extends('layouts.admin')

@section('title', 'RAQ Management')

@section('admin-content')
    <div class="sm:px-2">
        <!-- Header -->
        <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4">
            <div>
                <h1 class="text-2xl font-semibold text-slate-900">RAQ Management</h1>
                <p class="text-sm text-slate-500 mt-1">Review and approve RAQs from tickets</p>
            </div>
            <div class="flex items-center gap-3">
                <!-- Desktop filters (hidden on mobile) -->
                @include('dashboards.admin.faqs.components.desktop-filters')

                <!-- Mobile search -->
                @include('dashboards.admin.faqs.utils.mobile-search')

                <!-- Analyze Tickets Button -->
                @include('dashboards.admin.faqs.utils.analyze-tickets-button')

                <!-- Create FAQ Button -->
                <button id="openCreateFAQModal"
                        class="ml-2 rounded-md bg-blue-600 px-4 py-2 text-sm font-medium text-white hover:bg-blue-700 transition-colors"
                        onclick="document.getElementById('create-faq-modal').classList.remove('hidden')">
                    Create FAQ
                </button>

                <!-- Filters Button (Mobile) -->
                <button id="openFiltersBtn"
                    class="ml-2 rounded-md border border-gray-200 bg-white px-3 py-2 text-sm">Filters</button>
            </div>
        </div>

        <!-- Create FAQ Modal -->
        <div id="create-faq-modal" class="hidden fixed inset-0 z-50 flex items-center justify-center bg-black/50">
            <div class="bg-white rounded-lg w-full max-w-2xl p-6">
                <div class="flex justify-between items-start mb-4">
                    <h2 class="text-xl font-semibold text-gray-900">Create New FAQ</h2>
                    <button id="closeCreateFAQModal"
                            class="rounded-md p-2 text-gray-400 hover:text-gray-500">
                        <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                  d="M6 18L18 6M6 6l12 12"/>
                        </svg>
                    </button>
                </div>
                <form id="createFAQForm" class="space-y-4">
                    <div class="grid grid-cols-1 gap-4">
                        <div>
                            <label for="general_topic" class="block text-sm font-medium text-gray-700 mb-1">General Topic</label>
                            <input type="text" id="general_topic" name="general_topic"
                                   class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
                                   placeholder="Enter general topic">
                        </div>
                        <div>
                            <label for="semantic_key" class="block text-sm font-medium text-gray-700 mb-1">Semantic Key</label>
                            <input type="text" id="semantic_key" name="semantic_key"
                                   class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
                                   placeholder="Enter semantic key">
                        </div>
                        <div>
                            <label for="suggested_q" class="block text-sm font-medium text-gray-700 mb-1">Suggested Question</label>
                            <textarea id="suggested_q" name="suggested_q"
                                      class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
                                      rows="3" placeholder="Enter suggested question"></textarea>
                        </div>
                        <div>
                            <label for="suggested_a" class="block text-sm font-medium text-gray-700 mb-1">Suggested Answer</label>
                            <textarea id="suggested_a" name="suggested_a"
                                      class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
                                      rows="4" placeholder="Enter suggested answer"></textarea>
                        </div>
                        <div class="flex items-center gap-4">
                            <div class="w-full">
                                <label for="faq_status" class="block text-sm font-medium text-gray-700 mb-1">Status</label>
                                <select id="faq_status" name="faq_status"
                                        class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                                    <option value="pending">Pending</option>
                                    <option value="publish">Publish</option>
                                    <option value="unpublish">Unpublish</option>
                                </select>
                            </div>
                        </div>
                    </div>
                    <div class="flex justify-end pt-4">
                        <button type="button"
                                id="createFAQBtn"
                                class="px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700">
                            Create FAQ
                        </button>
                        <button type="button"
                                id="cancelCreateFAQ"
                                class="ml-2 px-4 py-2 border border-gray-300 text-gray-700 rounded-md hover:bg-gray-50">
                            Cancel
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <!-- Table Container with Horizontal Scroll -->
        @include('dashboards.admin.faqs.components.table-container')
    </div>

    <!-- Drawer Overlay -->
    <div id="faqsDrawerOverlay" class="hidden fixed inset-0 bg-black/30 z-40"></div>

    <!-- Bottom Drawer: Filters -->
    @include('dashboards.admin.faqs.components.bottom-drawer-filters')

    {{-- Notifications --}}
    @include('dashboards.admin.faqs.utils.notifications')
    <!-- Analyze Tickets Modal - Modern Design -->
    @include('dashboards.admin.faqs.components.analyze-tickets-modal')
    @include('dashboards.admin.faqs.utils.hidden-urls')
@endsection

@push('scripts')
    @include('dashboards.admin.faqs.scripts')
@endpush
