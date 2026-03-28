<!-- FAQ Management -->
@extends('layouts.admin')

@section('title', 'FAQ Management')

@section('admin-content')
    <div class="sm:px-2">
        <!-- Header -->
        <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4">
            <div>
                <h1 class="text-2xl font-semibold text-slate-900">FAQ Management</h1>
                <p class="text-sm text-slate-500 mt-1">Review and approve FAQs from tickets</p>
            </div>
            <div class="flex items-center gap-3">
                <!-- Desktop filters (hidden on mobile) -->
                @include('dashboards.admin.faqs.components.desktop-filters')

                <!-- Mobile search -->
                @include('dashboards.admin.faqs.utils.mobile-search')

                <!-- Analyze Tickets Button -->
                @include('dashboards.admin.faqs.utils.analyze-tickets-button')

                <!-- Filters Button (Mobile) -->
                <button id="openFiltersBtn"
                    class="ml-2 rounded-md border border-gray-200 bg-white px-3 py-2 text-sm">Filters</button>
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
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script type="module">
        // Include each file's text body directly into the page
        @include('dashboards.admin.faqs.scripts.state')
        @include('dashboards.admin.faqs.scripts.utils')
        @include('dashboards.admin.faqs.scripts.http')
        @include('dashboards.admin.faqs.scripts.faq-manager')
        @include('dashboards.admin.faqs.scripts.ai-analyzer')
        @include('dashboards.admin.faqs.scripts.main')
    </script>
@endpush
